/* ==========================================================================
   PRIMENEST - CORE UX
   Navbar scroll, animated counters, hero search tabs, hero slider,
   share/print, enquiry forms.
   ========================================================================== */
(function () {
  'use strict';

  function boot() {
    // Each init is isolated — a failure in one must never stop the others
    // (especially the enquiry form binding).
    [initNavbarScroll, initCounters, initHeroSearchTabs, initHeroSlider,
     window.initDetailActions, window.initEnquiryForms].forEach(function (fn) {
      try { fn(); } catch (e) { if (window.console) console.error('init failed:', e); }
    });
  }

  /* ---- Navbar sticky state ------------------------------------------------ */
  function initNavbarScroll() {
    var navbar = document.querySelector('.navbar-custom');
    if (!navbar) return;
    var onScroll = function () {
      navbar.classList.toggle('scrolled', window.scrollY > 50);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ---- Animated statistics --------------------------------------------- */
  function initCounters() {
    var nums = document.querySelectorAll('.stat-number');
    if (!nums.length || !('IntersectionObserver' in window)) return;
    var obs = new IntersectionObserver(function (entries, o) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) { animateCounter(entry.target); o.unobserve(entry.target); }
      });
    }, { threshold: 0.5 });
    nums.forEach(function (n) { obs.observe(n); });
  }

  function animateCounter(el) {
    var target = parseInt(el.getAttribute('data-target') || '0', 10);
    var suffix = el.getAttribute('data-suffix') || '';
    var prefix = el.getAttribute('data-prefix') || '';
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      el.textContent = prefix + target.toLocaleString() + suffix;
      return;
    }
    var count = 0;
    var step = Math.max(1, Math.ceil(target / 60));
    var timer = setInterval(function () {
      count += step;
      if (count >= target) { count = target; clearInterval(timer); }
      el.textContent = prefix + count.toLocaleString() + suffix;
    }, 25);
  }

  /* ---- Toast (used by the share button) ------------------------------ */
  window.showToast = function (message) {
    var container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      container.style.cssText = 'position:fixed;bottom:90px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px';
      document.body.appendChild(container);
    }
    var toast = document.createElement('div');
    toast.setAttribute('role', 'status');
    toast.style.cssText = 'background:rgba(15,23,42,.95);color:#fff;border-left:4px solid var(--primary-gold);padding:12px 20px;border-radius:8px;box-shadow:0 10px 25px rgba(0,0,0,.3);font-size:.9rem;font-weight:600;display:flex;align-items:center;gap:10px;transform:translateX(120%);transition:transform .3s cubic-bezier(.4,0,.2,1)';
    toast.innerHTML = '<i class="fa-solid fa-circle-check text-gold" aria-hidden="true"></i> <span></span>';
    toast.querySelector('span').textContent = message;
    container.appendChild(toast);
    requestAnimationFrame(function () { toast.style.transform = 'translateX(0)'; });
    setTimeout(function () {
      toast.style.transform = 'translateX(120%)';
      setTimeout(function () { toast.remove(); }, 300);
    }, 3200);
  };

  /* ---- Hero search tabs -------------------------------------------- */
  function initHeroSearchTabs() {
    var tabs = document.querySelectorAll('.search-tabs .home-type-btn');
    var catSelect = document.querySelector('.search-form-grid select[name="category"]');
    if (!tabs.length) return;
    tabs.forEach(function (tab) {
      tab.addEventListener('click', function (e) {
        e.preventDefault();
        tabs.forEach(function (t) { t.classList.remove('active'); });
        tab.classList.add('active');
        if (catSelect) catSelect.value = tab.getAttribute('data-type');
      });
    });
  }

  /* ---- Hero background slider -------------------------------------- */
  function initHeroSlider() {
    var slides = document.querySelectorAll('#heroSlider .hero-slide');
    if (slides.length < 2) return;
    var idx = 0;
    setInterval(function () {
      slides[idx].classList.remove('active');
      idx = (idx + 1) % slides.length;
      slides[idx].classList.add('active');
    }, 6000);
  }

  /* ---- Detail page: share / print -------------------------------- */
  window.initDetailActions = function () {
    var shareBtn = document.querySelector('[data-action="share"]');
    if (shareBtn && !shareBtn.dataset.bound) {
      shareBtn.dataset.bound = '1';
      shareBtn.addEventListener('click', function () {
        var data = { title: document.title, url: location.href };
        if (navigator.share) { navigator.share(data).catch(function () {}); return; }
        if (navigator.clipboard) {
          navigator.clipboard.writeText(location.href).then(function () {
            window.showToast('Property link copied to clipboard');
          });
        }
      });
    }
    var printBtn = document.querySelector('[data-action="print"]');
    if (printBtn && !printBtn.dataset.bound) {
      printBtn.dataset.bound = '1';
      printBtn.addEventListener('click', function () { window.print(); });
    }
  };

  /* ---- Enquiry / contact forms (no backend) ---------------------- */
  /* On submit we build a message from the fields and show a confirmation panel
     with WhatsApp + email links the user taps. Real link clicks are never
     popup-blocked (unlike window.open), so this works on desktop and mobile. */
  window.initEnquiryForms = function () {
    document.querySelectorAll('.inquiry-form').forEach(function (form) {
      if (form.dataset.bound) return;
      form.dataset.bound = '1';
      var statusEl = form.querySelector('.form-status');
      function setStatus(msg, kind) {
        if (!statusEl) return;
        statusEl.textContent = msg || '';
        statusEl.style.display = msg ? 'block' : 'none';
        statusEl.style.color = kind === 'error' ? '#dc2626' : (kind === 'ok' ? '#15803d' : '');
        statusEl.style.fontWeight = '600';
      }

      var handledAt = 0;

      function handleSubmit(e) {
        if (e) e.preventDefault();
        // de-dupe: submit + click can both fire
        if (Date.now() - handledAt < 800) { return; }
        handledAt = Date.now();

        setStatus('Sending…');       // immediate visible feedback so the user knows the click registered
       try {
        // NOTE: honeypot is checked server-side only. Doing it client-side broke
        // real submissions when a browser/password-manager autofilled the hidden
        // field. The server still silently drops bot submissions.

        // required-field check with a VISIBLE message (not just the native tooltip)
        var firstBad = null;
        form.querySelectorAll('[required]').forEach(function (el) {
          if (!firstBad && !el.checkValidity()) firstBad = el;
        });
        if (firstBad) {
          setStatus('Please fill the required fields marked * (Name, Phone, Property Type).', 'error');
          try { firstBad.focus(); firstBad.reportValidity(); } catch (x) {}
          return;
        }
        setStatus('');

        // A real person never touches the hidden anti-spam field, but a browser
        // password-manager / autofill sometimes does — clear it so it never
        // blocks a genuine submission. Direct (no-JS) bot POSTs still get caught.
        var hp = form.querySelector('[name="zzhp_field"]');
        if (hp) hp.value = '';

        var fd = new FormData(form);
        var submitBtn = form.querySelector('[type="submit"], button:not([type])');
        if (submitBtn) { submitBtn.disabled = true; submitBtn.dataset.busy = '1'; }

        // Persist the enquiry to the backend. The confirmation panel is shown
        // regardless (WhatsApp/call fallback always works), but we track whether
        // the save succeeded so the wording can be truthful.
        var saved = null; // null = pending, true/false = known
        try {
          var endpoint = window.ENQUIRY_ENDPOINT;
          var tokenEl = document.querySelector('meta[name="csrf-token"]');
          if (endpoint && tokenEl) {
            fetch(endpoint, {
              method: 'POST',
              headers: { 'X-CSRF-TOKEN': tokenEl.getAttribute('content'), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
              body: fd
            }).then(function (r) {
              saved = r.ok;
              var s = document.querySelector('.enquiry-sent .es-status');
              if (s) s.textContent = r.ok
                ? 'Your details have been saved — our team will get back to you.'
                : 'We could not save it automatically — please send it on WhatsApp so we get it.';
            }).catch(function () {
              saved = false;
              var s = document.querySelector('.enquiry-sent .es-status');
              if (s) s.textContent = 'Network issue saving it — please send it on WhatsApp so we get it.';
            });
          }
        } catch (err) { /* no-op */ }

        var name = (fd.get('name') || '').toString().trim();
        var order = ['name', 'phone', 'email', 'interest', 'society', 'budget', 'propertyId', 'propertyTitle', 'message'];
        var labels = {
          name: 'Name', phone: 'Phone', email: 'Email', interest: 'Property type',
          society: 'Preferred area', budget: 'Budget', propertyId: 'Ref',
          propertyTitle: 'Property', message: 'Message'
        };
        var lines = [((window.PN && window.PN.SITE.name) || 'Website') + ' website enquiry', ''];
        order.forEach(function (k) {
          var v = (fd.get(k) || '').toString().trim();
          if (v) lines.push(labels[k] + ': ' + v);
        });
        var body = lines.join('\n');

        var waNum = (window.PN && window.PN.SITE.whatsapp) || '923096660071';
        var phone = (window.PN && window.PN.SITE.phone) || '+92 309 666 0071';
        var phoneRaw = (window.PN && window.PN.SITE.phoneRaw) || '+923096660071';
        var wa = 'https://wa.me/' + waNum + '?text=' + encodeURIComponent(body);

        // Build / show the confirmation panel next to the form
        var panel = form.parentNode.querySelector('.enquiry-sent');
        if (!panel) {
          panel = document.createElement('div');
          panel.className = 'enquiry-sent';
          form.parentNode.insertBefore(panel, form.nextSibling);
        }
        panel.innerHTML =
          '<div class="enquiry-sent-inner">' +
            '<div class="enquiry-sent-tick"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></div>' +
            '<h3 class="h5 fw-bold text-navy mb-1">Thanks' + (name ? ', ' + escapeHtml(name.split(' ')[0]) : '') + '! Your enquiry has been received.</h3>' +
            '<p class="text-muted small mb-3 es-status">Saving your details&hellip;</p>' +
            '<p class="text-muted small mb-2">Want a faster reply? Reach us directly:</p>' +
            '<div class="d-grid gap-2">' +
              '<a href="' + wa + '" target="_blank" rel="noopener" class="btn btn-gold justify-content-center"><i class="fa-brands fa-whatsapp me-2" aria-hidden="true"></i> Message us on WhatsApp</a>' +
              '<a href="tel:' + phoneRaw + '" class="btn btn-outline-navy justify-content-center"><i class="fa-solid fa-phone me-2" aria-hidden="true"></i> Call ' + escapeHtml(phone) + '</a>' +
            '</div>' +
            '<p class="small text-muted mt-3 mb-0">' +
              '<button type="button" class="btn btn-link btn-sm p-0 align-baseline enquiry-edit">Submit another enquiry</button></p>' +
          '</div>';
        form.hidden = true;
        panel.hidden = false;
        panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
        var waBtn = panel.querySelector('a[target="_blank"]');
        if (waBtn) waBtn.focus();

        var editBtn = panel.querySelector('.enquiry-edit');
        if (editBtn) {
          editBtn.addEventListener('click', function () {
            form.reset();
            if (submitBtn) { submitBtn.disabled = false; delete submitBtn.dataset.busy; }
            panel.hidden = true;
            form.hidden = false;
            setStatus('');
            form.scrollIntoView({ behavior: 'smooth', block: 'center' });
          });
        }
       } catch (err) {
        // Never leave the user stuck with a dead button.
        var sb = form.querySelector('[type="submit"], button:not([type])');
        if (sb) sb.disabled = false;
        setStatus('Could not send (' + (err && err.message ? err.message : err) + '). Please WhatsApp us at ' +
          ((window.PN && window.PN.SITE && window.PN.SITE.phone) || '+92 309 666 0071') + '.', 'error');
        if (window.console) console.error('enquiry submit failed:', err);
       }
      }

      // Bind on BOTH the form's submit AND the button's click — if anything
      // (an extension, another script) swallows the submit event, the click
      // path still runs.
      form.addEventListener('submit', handleSubmit);
      var btn = form.querySelector('button[type="submit"], input[type="submit"], button:not([type])');
      if (btn) btn.addEventListener('click', function (e) { handleSubmit(e); });
    });
  };

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  // Run now if the DOM is already parsed (this script is at the end of <body>),
  // otherwise wait for DOMContentLoaded.
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
