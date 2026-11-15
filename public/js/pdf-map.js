/* ==========================================================================
   PDF MAPS — draws a PDF map with PDF.js (self-hosted in /vendor/pdfjs).
   A PDF is vector, so it is re-drawn at every zoom level and stays sharp.
   - grid cards: <canvas data-pdf-thumb="/assets/maps/x.pdf"> gets page 1 drawn on it
   - zoom window: PdfMap.open(url) / PdfMap.close(); buttons, wheel, pinch, double-click, drag to pan
   ========================================================================== */
(function () {
  'use strict';

  var LIB = '/vendor/pdfjs/pdf.min.js', WORKER = '/vendor/pdfjs/pdf.worker.min.js';
  var MAX_PIXELS = 14e6;          // keep the canvas under what phones can allocate
  var MIN_Z = 1, MAX_Z = 8;

  /* ---- load PDF.js once, on demand ---- */
  var libPromise = null;
  function lib() {
    if (window.pdfjsLib) { window.pdfjsLib.GlobalWorkerOptions.workerSrc = WORKER; return Promise.resolve(window.pdfjsLib); }
    if (!libPromise) {
      libPromise = new Promise(function (ok, fail) {
        var s = document.createElement('script');
        s.src = LIB;
        s.onload = function () { window.pdfjsLib.GlobalWorkerOptions.workerSrc = WORKER; ok(window.pdfjsLib); };
        s.onerror = function () { libPromise = null; fail(new Error('PDF viewer could not load')); };
        document.head.appendChild(s);
      });
    }
    return libPromise;
  }

  var docs = {};                  // url -> Promise<PDFDocumentProxy>
  function load(url) {
    if (!docs[url]) {
      docs[url] = lib().then(function (p) { return p.getDocument({ url: url }).promise; });
      docs[url].catch(function () { delete docs[url]; });
    }
    return docs[url];
  }

  /* ---- grid thumbnails ---- */
  function drawThumb(canvas) {
    var url = canvas.getAttribute('data-pdf-thumb');
    var host = canvas.closest('.map-thumb');
    load(url).then(function (doc) { return doc.getPage(1); }).then(function (page) {
      var base = page.getViewport({ scale: 1 });
      var cssW = Math.max(canvas.parentElement.clientWidth || 600, 300);
      var dpr = Math.min(window.devicePixelRatio || 1, 2);
      var scale = (cssW * dpr) / base.width;
      var vp = page.getViewport({ scale: scale });
      canvas.width = Math.round(vp.width);
      canvas.height = Math.round(vp.height);
      return page.render({ canvasContext: canvas.getContext('2d'), viewport: vp }).promise.then(function () {
        if (host) host.classList.add('is-ready');
      });
    }).catch(function () { if (host) host.classList.add('is-failed'); });
  }

  function initThumbs() {
    var list = document.querySelectorAll('canvas[data-pdf-thumb]');
    if (!list.length) return;
    if (!('IntersectionObserver' in window)) { list.forEach(drawThumb); return; }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        io.unobserve(e.target);
        drawThumb(e.target);
      });
    }, { rootMargin: '300px' });
    list.forEach(function (c) { io.observe(c); });
    // a project that was hidden by the filter has no width until shown: draw when it appears
    document.addEventListener('click', function (e) {
      if (!e.target.closest('.project-filter-btn,[data-view-project]')) return;
      setTimeout(function () {
        list.forEach(function (c) { if (!c.width || c.width <= 300) { if (c.offsetParent) drawThumb(c); } });
      }, 120);
    });
  }

  /* ---- zoom window ---- */
  var box, scroller, canvas, msg, zoomLabel, pagesBox, pageLabel, openLink;
  var doc = null, pageNo = 1, zoom = 1, renderTimer = null, token = 0, task = null, url = null, baseW = 0, baseH = 0;

  function els() {
    if (box) return true;
    box = document.getElementById('pdfViewer');
    if (!box) return false;
    scroller = document.getElementById('pdfScroll');
    canvas = document.getElementById('pdfCanvas');
    msg = document.getElementById('pdfMsg');
    zoomLabel = document.getElementById('pdfZoomLabel');
    pagesBox = document.getElementById('pdfPages');
    pageLabel = document.getElementById('pdfPageLabel');
    openLink = document.getElementById('pdfOpen');
    bind();
    return true;
  }

  function fitWidth() { return Math.max(scroller.clientWidth - 2, 200); }

  /* CSS size follows the zoom instantly; the sharp bitmap is drawn right after (debounced) */
  function applySize() {
    var w = fitWidth() * zoom;
    canvas.style.width = w + 'px';
    canvas.style.height = (w * baseH / baseW) + 'px';
    zoomLabel.textContent = Math.round(zoom * 100) + '%';
  }

  function render() {
    if (!doc) return;
    var my = ++token;
    if (task) { try { task.cancel(); } catch (e) {} }
    doc.getPage(pageNo).then(function (page) {
      if (my !== token) return;
      var base = page.getViewport({ scale: 1 });
      baseW = base.width; baseH = base.height;
      applySize();
      var cssW = fitWidth() * zoom;
      var dpr = window.devicePixelRatio || 1;
      var scale = (cssW * dpr) / base.width;
      // never ask the canvas for more pixels than a phone can hold
      var px = (base.width * scale) * (base.height * scale);
      if (px > MAX_PIXELS) scale *= Math.sqrt(MAX_PIXELS / px);
      var vp = page.getViewport({ scale: scale });
      // draw off-screen, then swap in, so zooming never flashes blank
      var buf = document.createElement('canvas');
      buf.width = Math.round(vp.width); buf.height = Math.round(vp.height);
      task = page.render({ canvasContext: buf.getContext('2d'), viewport: vp });
      return task.promise.then(function () {
        if (my !== token) return;
        canvas.width = buf.width; canvas.height = buf.height;
        canvas.getContext('2d').drawImage(buf, 0, 0);
        msg.hidden = true;
      });
    }).catch(function (e) {
      if (e && e.name === 'RenderingCancelledException') return;
      msg.hidden = false; msg.textContent = 'The map could not be shown here. Please use “Open PDF”.';
    });
  }

  function scheduleRender() { clearTimeout(renderTimer); renderTimer = setTimeout(render, 140); }

  /* zoom keeping the point under (cx, cy) — viewport coordinates of the scroller — where it is */
  function setZoom(z, cx, cy) {
    z = Math.min(MAX_Z, Math.max(MIN_Z, z));
    if (Math.abs(z - zoom) < 0.001) return;
    var r = scroller.getBoundingClientRect();
    var px = (cx == null ? r.left + r.width / 2 : cx) - r.left, py = (cy == null ? r.top + r.height / 2 : cy) - r.top;
    var fx = (scroller.scrollLeft + px) / (canvas.offsetWidth || 1), fy = (scroller.scrollTop + py) / (canvas.offsetHeight || 1);
    zoom = z;
    applySize();
    scroller.scrollLeft = fx * canvas.offsetWidth - px;
    scroller.scrollTop = fy * canvas.offsetHeight - py;
    scheduleRender();
  }

  function showPage(n) {
    if (!doc) return;
    pageNo = Math.min(doc.numPages, Math.max(1, n));
    pageLabel.textContent = pageNo + ' / ' + doc.numPages;
    zoom = 1; scroller.scrollLeft = 0; scroller.scrollTop = 0;
    render();
  }

  function bind() {
    box.addEventListener('click', function (e) {
      var b = e.target.closest('[data-pdf-act]'); if (!b) return;
      var a = b.getAttribute('data-pdf-act');
      if (a === 'in') setZoom(zoom * 1.5);
      else if (a === 'out') setZoom(zoom / 1.5);
      else if (a === 'fit') { setZoom(1); scroller.scrollLeft = 0; scroller.scrollTop = 0; }
      else if (a === 'prev') showPage(pageNo - 1);
      else if (a === 'next') showPage(pageNo + 1);
    });
    scroller.addEventListener('dblclick', function (e) { setZoom(zoom > 1.2 ? 1 : 2.5, e.clientX, e.clientY); });
    scroller.addEventListener('wheel', function (e) {
      if (!e.ctrlKey && !e.metaKey) return;               // plain wheel keeps scrolling the map
      e.preventDefault();
      setZoom(zoom * (e.deltaY < 0 ? 1.15 : 1 / 1.15), e.clientX, e.clientY);
    }, { passive: false });

    // mouse drag to pan; two fingers to pinch (one finger pans by normal touch scrolling)
    var ptrs = {}, panning = null, pinch = null;
    scroller.addEventListener('pointerdown', function (e) {
      ptrs[e.pointerId] = { x: e.clientX, y: e.clientY };
      var ids = Object.keys(ptrs);
      if (ids.length === 2) {
        var a = ptrs[ids[0]], b = ptrs[ids[1]];
        pinch = { d: Math.hypot(a.x - b.x, a.y - b.y), z: zoom };
        panning = null;
      } else if (e.pointerType === 'mouse') {
        panning = { x: e.clientX, y: e.clientY, l: scroller.scrollLeft, t: scroller.scrollTop };
        scroller.classList.add('is-panning');
      }
    });
    scroller.addEventListener('pointermove', function (e) {
      if (!ptrs[e.pointerId]) return;
      ptrs[e.pointerId] = { x: e.clientX, y: e.clientY };
      var ids = Object.keys(ptrs);
      if (pinch && ids.length === 2) {
        var a = ptrs[ids[0]], b = ptrs[ids[1]];
        setZoom(pinch.z * Math.hypot(a.x - b.x, a.y - b.y) / (pinch.d || 1), (a.x + b.x) / 2, (a.y + b.y) / 2);
      } else if (panning) {
        scroller.scrollLeft = panning.l - (e.clientX - panning.x);
        scroller.scrollTop = panning.t - (e.clientY - panning.y);
      }
    });
    function up(e) {
      delete ptrs[e.pointerId];
      if (Object.keys(ptrs).length < 2) pinch = null;
      if (!Object.keys(ptrs).length) { panning = null; scroller.classList.remove('is-panning'); }
    }
    scroller.addEventListener('pointerup', up);
    scroller.addEventListener('pointercancel', up);
    window.addEventListener('resize', function () { if (doc && !box.hidden) { applySize(); scheduleRender(); } });
  }

  function open(src) {
    if (!els()) return;
    url = src; doc = null; zoom = 1; pageNo = 1;
    openLink.href = src;
    msg.hidden = false; msg.textContent = 'Loading map…';
    canvas.width = 1; canvas.height = 1; canvas.style.width = '0'; canvas.style.height = '0';
    scroller.scrollLeft = 0; scroller.scrollTop = 0;
    pagesBox.hidden = true;
    load(src).then(function (d) {
      if (url !== src) return;
      doc = d;
      pagesBox.hidden = d.numPages < 2;
      showPage(1);
    }).catch(function () {
      msg.hidden = false; msg.textContent = 'The map could not be shown here. Please use “Open PDF”.';
    });
  }

  function close() {
    token++; clearTimeout(renderTimer);
    if (task) { try { task.cancel(); } catch (e) {} }
    url = null; doc = null;
    if (canvas) { canvas.width = 1; canvas.height = 1; }
  }

  window.PdfMap = { open: open, close: close };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initThumbs); else initThumbs();
})();
