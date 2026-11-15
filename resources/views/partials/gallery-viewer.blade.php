{{-- Photo viewer (lightbox) + "Load more" for a .gal-grid[data-gal-group]. Include once, at the end of the page content. --}}
<div class="gal-lightbox" id="galLightbox" role="dialog" aria-modal="true" aria-label="Photo viewer" hidden>
  <button type="button" class="gl-btn gl-close" aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
  <button type="button" class="gl-btn gl-prev" aria-label="Previous photo"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
  <figure class="gl-stage"><img id="glImg" src="" alt=""><figcaption id="glCap"></figcaption></figure>
  <button type="button" class="gl-btn gl-next" aria-label="Next photo"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
  <div class="gl-count" id="glCount"></div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // ---- Load more: the first 6 photos, then 6 more per click
    document.querySelectorAll('[data-more-group]').forEach(function (row) {
      var cards = row.children;
      var limit = parseInt(row.getAttribute('data-more-limit'), 10) || 6;
      var step = parseInt(row.getAttribute('data-more-step'), 10) || limit;
      if (cards.length <= limit) return;
      for (var i = limit; i < cards.length; i++) cards[i].classList.add('d-none');
      var shown = limit;
      var btn = document.createElement('button');
      btn.type = 'button'; btn.className = 'btn btn-outline-gold mx-auto mt-4 d-flex';
      function paint() { btn.textContent = 'Load more (' + (cards.length - shown) + ')'; }
      paint();
      row.after(btn);
      btn.addEventListener('click', function () {
        var to = Math.min(cards.length, shown + step);
        for (var j = shown; j < to; j++) cards[j].classList.remove('d-none');
        shown = to;
        if (shown >= cards.length) btn.remove(); else paint();
      });
    });

    // ---- Viewer (prev / next, arrow keys, swipe). It walks through every photo, including ones not loaded yet.
    var box = document.getElementById('galLightbox'); if (!box) return;
    var img = document.getElementById('glImg'), cap = document.getElementById('glCap'), cnt = document.getElementById('glCount');
    var list = [], i = 0, lastFocus = null;
    function show(n) {
      i = (n + list.length) % list.length;
      var b = list[i];
      img.src = b.getAttribute('data-gal-src');
      img.alt = b.getAttribute('data-gal-title') || '';
      cap.textContent = b.getAttribute('data-gal-title') || '';
      cnt.textContent = (i + 1) + ' / ' + list.length;
      box.classList.toggle('single', list.length < 2);
    }
    function open(group, btn) {
      list = Array.prototype.slice.call(group.querySelectorAll('[data-gal-src]'));
      lastFocus = document.activeElement;
      box.hidden = false; document.body.classList.add('gal-open');
      show(list.indexOf(btn)); box.querySelector('.gl-close').focus();
    }
    function close() { box.hidden = true; document.body.classList.remove('gal-open'); img.src = ''; if (lastFocus) lastFocus.focus(); }
    document.querySelectorAll('[data-gal-group]').forEach(function (g) {
      g.addEventListener('click', function (e) { var b = e.target.closest('[data-gal-src]'); if (b) open(g, b); });
    });
    box.querySelector('.gl-close').addEventListener('click', close);
    box.querySelector('.gl-prev').addEventListener('click', function () { show(i - 1); });
    box.querySelector('.gl-next').addEventListener('click', function () { show(i + 1); });
    box.addEventListener('click', function (e) { if (e.target === box || e.target.classList.contains('gl-stage')) close(); });
    document.addEventListener('keydown', function (e) {
      if (box.hidden) return;
      if (e.key === 'Escape') close(); else if (e.key === 'ArrowLeft') show(i - 1); else if (e.key === 'ArrowRight') show(i + 1);
    });
    var x0 = null;
    box.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    box.addEventListener('touchend', function (e) {
      if (x0 === null) return; var dx = e.changedTouches[0].clientX - x0; x0 = null;
      if (Math.abs(dx) > 50) show(dx < 0 ? i + 1 : i - 1);
    });
  });
</script>
@endpush
