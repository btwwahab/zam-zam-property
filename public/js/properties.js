/* ==========================================================================
   PRIMENEST - PROPERTY GRID + FILTER ENGINE
   Reads window.PRIMENEST_PROPERTIES (js/data.js).
   - #properties-grid[data-mode="featured"]  -> featured items, no controls
   - #properties-grid (with #filter-* controls) -> full directory
   ========================================================================== */
(function () {
  'use strict';

  var DATA = window.PRIMENEST_PROPERTIES || [];
  var AGENTS = window.PRIMENEST_AGENTS || {};
  var PAGE_SIZE = 9; // 9 per page (3x3 grid); pagination shows only when results exceed this

  document.addEventListener('DOMContentLoaded', function () {
    var container = document.getElementById('properties-grid');
    if (!container) return;

    if (container.getAttribute('data-mode') === 'featured') {
      // Show 6: flagged featured first, then top up with the rest.
      var feat = DATA.filter(function (p) { return p.featured; });
      var rest = DATA.filter(function (p) { return !p.featured; });
      renderProperties(feat.concat(rest).slice(0, 6), container, false);
      return;
    }
    initFilterControls(container);
  });

  function pic(base, alt, cls, small) {
    var webp = 'assets/images/' + base + (small ? '-sm' : '') + '.webp';
    var png = 'assets/images/' + base + '.png';
    var w = small ? 512 : 1024;
    return '<picture>' +
      '<source type="image/webp" srcset="' + webp + '">' +
      '<img src="' + png + '" alt="' + esc(alt) + '" class="' + cls + '" width="' + w + '" height="' + w +
      '" loading="lazy" decoding="async"></picture>';
  }

  function esc(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  var CAT_LABEL = { residential: 'Residential', commercial: 'Commercial', farmhouse: 'Farmhouse' };

  function cardHTML(item, isList) {
    var specs = '';
    if (item.beds > 0) specs += '<div class="spec-item"><i class="fa-solid fa-bed" aria-hidden="true"></i> ' + item.beds + ' Beds</div>';
    if (item.baths > 0) specs += '<div class="spec-item"><i class="fa-solid fa-bath" aria-hidden="true"></i> ' + item.baths + ' Baths</div>';
    specs += '<div class="spec-item"><i class="fa-solid fa-ruler-combined" aria-hidden="true"></i> ' + esc(item.size) + '</div>';
    specs += '<div class="spec-item"><i class="fa-solid fa-tag" aria-hidden="true"></i> ' + esc(CAT_LABEL[item.category] || item.category) + '</div>';

    return '' +
    '<div class="' + (isList ? 'col-12' : 'col-lg-4 col-md-6') + '">' +
      '<article class="property-card' + (isList ? ' property-card-horizontal' : '') + '">' +
        '<div class="property-thumb-wrap">' +
          pic(item.image, item.title, 'property-thumb', true) +
          '<div class="property-badges">' +
            '<span class="badge-status ' + item.statusClass + '">' + esc(item.status) + '</span>' +
            '<span class="badge-status bg-navy">' + esc(item.purpose) + '</span>' +
          '</div>' +
          '<div class="property-price-tag">' + esc(item.priceFormatted) + '</div>' +
        '</div>' +
        '<div class="property-body">' +
          '<div class="property-location"><i class="fa-solid fa-location-dot text-gold-ink" aria-hidden="true"></i> ' + esc(item.location) + '</div>' +
          '<h3 class="property-title h6"><a href="property-detail.html?id=' + encodeURIComponent(item.id) + '" class="text-navy">' + esc(item.title) + '</a></h3>' +
          '<div class="property-specs">' + specs + '</div>' +
          '<div class="property-footer">' +
            '<div class="agent-mini"><span class="brand-icon brand-icon-xs bg-gold text-navy rounded-circle d-flex align-items-center justify-content-center">ZZ</span>' +
              '<span class="agent-name-mini">' + esc(item.projectName || 'Zam Zam Estate') + '</span></div>' +
            '<a href="property-detail.html?id=' + encodeURIComponent(item.id) + '" class="btn btn-sm btn-outline-gold">Details <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>' +
          '</div>' +
        '</div>' +
      '</article>' +
    '</div>';
  }

  function renderProperties(list, container, isList) {
    if (!list.length) {
      container.className = 'row g-4';
      container.innerHTML =
        '<div class="col-12 text-center py-5">' +
        '<i class="fa-solid fa-house-circle-xmark fa-3x text-muted mb-3" aria-hidden="true"></i>' +
        '<h3 class="fw-bold h4">No matching properties</h3>' +
        '<p class="text-muted">Try widening your price range, location or property type.</p></div>';
      return;
    }
    container.className = isList ? 'row g-4 list-view-mode' : 'row g-4';
    container.innerHTML = list.map(function (i) { return cardHTML(i, isList); }).join('');
  }

  /* ---- Pagination -------------------------------------------------- */
  function pageWindow(current, total) {
    if (total <= 7) {
      var all = [];
      for (var i = 1; i <= total; i++) all.push(i);
      return all;
    }
    var out = [1];
    var lo = Math.max(2, current - 1);
    var hi = Math.min(total - 1, current + 1);
    if (lo > 2) out.push('...');
    for (var j = lo; j <= hi; j++) out.push(j);
    if (hi < total - 1) out.push('...');
    out.push(total);
    return out;
  }

  function pageLi(label, target, disabled, active, aria) {
    return '<li class="page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '') + '">' +
      (disabled || target == null
        ? '<span class="page-link">' + label + '</span>'
        : '<a class="page-link" href="#" data-page="' + target + '" aria-label="' + aria + '"' +
          (active ? ' aria-current="page"' : '') + '>' + label + '</a>') +
      '</li>';
  }

  function renderPager(nav, total, current, totalPages) {
    if (!nav) return;
    if (total <= PAGE_SIZE) { nav.innerHTML = ''; return; }
    var html = '<ul class="pagination justify-content-center mb-0">';
    html += pageLi('&laquo;', current > 1 ? current - 1 : null, current === 1, false, 'Previous page');
    pageWindow(current, totalPages).forEach(function (p) {
      html += (p === '...')
        ? '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>'
        : pageLi(String(p), p, false, p === current, 'Page ' + p);
    });
    html += pageLi('&raquo;', current < totalPages ? current + 1 : null, current === totalPages, false, 'Next page');
    html += '</ul>';
    nav.innerHTML = html;
  }

  function initFilterControls(container) {
    var el = {
      search: document.getElementById('filter-search'),
      category: document.getElementById('filter-category'),
      project: document.getElementById('filter-project'),
      purpose: document.getElementById('filter-purpose'),
      maxPrice: document.getElementById('filter-max-price'),
      priceOut: document.getElementById('price-val-display'),
      sort: document.getElementById('filter-sort'),
      count: document.getElementById('result-count'),
      grid: document.getElementById('btn-grid-view'),
      listv: document.getElementById('btn-list-view'),
      reset: document.getElementById('filter-reset'),
      form: document.getElementById('filter-form'),
      pager: document.getElementById('pagination')
    };
    var listMode = false;
    var page = 1;

    function apply() {
      var kw = el.search ? el.search.value.toLowerCase().trim() : '';
      var cat = el.category ? el.category.value : 'all';
      var proj = el.project ? el.project.value : 'all';
      var purpose = el.purpose ? el.purpose.value : 'all';
      var maxPrice = el.maxPrice ? parseInt(el.maxPrice.value, 10) : 2e8;
      var sort = el.sort ? el.sort.value : 'default';

      if (el.priceOut) {
        el.priceOut.textContent = (maxPrice >= 2e8) ? 'Any' : 'Max PKR ' + (maxPrice / 1e7).toFixed(1).replace(/\.0$/, '') + ' Cr';
      }

      var out = DATA.filter(function (p) {
        if (kw && (p.title + ' ' + p.location + ' ' + p.id + ' ' + p.projectName + ' ' + p.subType).toLowerCase().indexOf(kw) === -1) return false;
        if (cat === 'investment') { if (!p.investment) return false; }
        else if (cat !== 'all' && p.category !== cat) return false;
        if (proj !== 'all' && p.project !== proj) return false;
        if (purpose !== 'all' && p.purpose !== purpose) return false;
        if (p.pricePkr > maxPrice) return false;
        return true;
      });

      if (sort === 'price-low') out.sort(function (a, b) { return a.pricePkr - b.pricePkr; });
      else if (sort === 'price-high') out.sort(function (a, b) { return b.pricePkr - a.pricePkr; });
      else out.sort(function (a, b) { return (b.featured ? 1 : 0) - (a.featured ? 1 : 0); });

      if (el.count) el.count.textContent = out.length;

      var totalPages = Math.max(1, Math.ceil(out.length / PAGE_SIZE));
      if (page > totalPages) page = totalPages;
      var start = (page - 1) * PAGE_SIZE;
      renderProperties(out.slice(start, start + PAGE_SIZE), container, listMode);
      renderPager(el.pager, out.length, page, totalPages);
    }

    function applyFromFilter() { page = 1; apply(); }

    // Seed from URL params (home hero search)
    var q = new URLSearchParams(location.search);
    if (el.search && q.get('keyword')) el.search.value = q.get('keyword');
    if (el.category && q.get('category') && q.get('category') !== 'all') el.category.value = q.get('category');
    if (el.project && q.get('project') && q.get('project') !== 'all') el.project.value = q.get('project');
    if (el.purpose && q.get('purpose')) el.purpose.value = q.get('purpose');
    if (el.maxPrice && q.get('maxPrice')) el.maxPrice.value = q.get('maxPrice');

    apply();

    ['search', 'category', 'project', 'purpose', 'maxPrice', 'sort'].forEach(function (k) {
      if (!el[k]) return;
      el[k].addEventListener(k === 'search' || k === 'maxPrice' ? 'input' : 'change', applyFromFilter);
    });

    if (el.pager) {
      el.pager.addEventListener('click', function (e) {
        var a = e.target.closest('a[data-page]');
        if (!a) return;
        e.preventDefault();
        var t = parseInt(a.getAttribute('data-page'), 10);
        if (isNaN(t)) return;
        page = t;
        apply();
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    }

    if (el.grid && el.listv) {
      var setView = function (list) {
        listMode = list;
        el.grid.classList.toggle('active', !list);
        el.listv.classList.toggle('active', list);
        el.grid.setAttribute('aria-pressed', String(!list));
        el.listv.setAttribute('aria-pressed', String(list));
        apply();
      };
      el.grid.addEventListener('click', function () { setView(false); });
      el.listv.addEventListener('click', function () { setView(true); });
    }

    if (el.reset && el.form) {
      el.reset.addEventListener('click', function () {
        setTimeout(function () {
          if (el.maxPrice) el.maxPrice.value = el.maxPrice.max;
          page = 1;
          apply();
        }, 0);
      });
    }
  }

  window.renderProperties = renderProperties;
})();
