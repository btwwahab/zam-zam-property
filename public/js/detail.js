/* ==========================================================================
   PRIMENEST - PROPERTY DETAIL VIEW
   Renders property-detail.html from ?id= against window.PRIMENEST_PROPERTIES.
   ========================================================================== */
(function () {
  'use strict';

  var DATA = window.PRIMENEST_PROPERTIES || [];
  var AGENTS = window.PRIMENEST_AGENTS || {};

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function wa(msg, phone) {
    return 'https://wa.me/' + (phone || (window.PN && window.PN.SITE.whatsapp) || '923096660071') +
      '?text=' + encodeURIComponent(msg);
  }
  function pic(base, alt, cls, small, style, eager) {
    var webp = 'assets/images/' + base + (small ? '-sm' : '') + '.webp';
    var png = 'assets/images/' + base + '.png';
    var w = small ? 512 : 1024;
    return '<picture>' +
      '<source type="image/webp" srcset="' + webp + '">' +
      '<img src="' + png + '" alt="' + esc(alt) + '" class="' + (cls || '') + '" width="' + w + '" height="' + w + '"' +
      (style ? ' style="' + style + '"' : '') +
      (eager ? ' fetchpriority="high"' : ' loading="lazy"') + ' decoding="async"></picture>';
  }

  document.addEventListener('DOMContentLoaded', function () {
    var q = new URLSearchParams(location.search);
    var id = (q.get('id') || '').trim();
    var p = DATA.filter(function (x) { return x.id.toLowerCase() === id.toLowerCase(); })[0];

    if (!p) {
      mount('pd-header', '<div class="container"><h1 class="fs-3 fw-bold text-white mb-2">Property not found</h1>' +
        '<p class="text-light mb-0">This listing may have been sold or removed.</p></div>');
      mount('pd-content', '<div class="container text-center py-4">' +
        '<a href="properties.html" class="btn btn-gold">Browse all properties</a></div>');
      setTimeout(function () { location.replace('properties.html'); }, 2500);
      return;
    }

    var agent = AGENTS[p.agent] || {
      name: 'Zam Zam Estate', role: 'Property Team', image: 'senior_realtor_male',
      phoneRaw: '+923096660071', whatsapp: '923096660071'
    };

    document.title = p.title + ' | ' + p.projectName + ' | Zam Zam Estate';
    setMeta('description', String(p.description[0]).slice(0, 155));
    var canon = document.querySelector('link[rel="canonical"]');
    if (canon) canon.setAttribute('href', location.origin + location.pathname + '?id=' + encodeURIComponent(p.id));
    injectJsonLd(p);

    mount('pd-header', headerBand(p));
    mount('pd-content', contentRow(p, agent));
    mount('pd-similar', similarBlock(p));

    if (window.initDetailActions) window.initDetailActions();
    if (window.initEnquiryForms) window.initEnquiryForms();
    if (window.bootstrap) {
      var g = document.getElementById('propertyGallery');
      if (g) new window.bootstrap.Carousel(g, { interval: 6000, ride: 'carousel' });
    }
  });

  function mount(id, html) {
    var el = document.getElementById(id);
    if (el) el.innerHTML = html;
  }
  function setMeta(name, content) {
    var m = document.querySelector('meta[name="' + name + '"]');
    if (!m) { m = document.createElement('meta'); m.setAttribute('name', name); document.head.appendChild(m); }
    m.setAttribute('content', content);
  }
  function injectJsonLd(p) {
    var base = location.origin + location.pathname.replace(/property-detail\.html$/, '');
    var ld = {
      '@context': 'https://schema.org', '@type': 'Product', name: p.title,
      image: base + 'assets/images/' + p.image + '.png',
      description: String(p.description[0]),
      category: 'Real Estate / ' + p.category,
      brand: { '@type': 'Brand', name: 'Zam Zam Estate' },
      offers: {
        '@type': 'Offer', price: p.pricePkr, priceCurrency: 'PKR',
        availability: 'https://schema.org/InStock',
        url: location.href
      }
    };
    var s = document.createElement('script');
    s.type = 'application/ld+json';
    s.textContent = JSON.stringify(ld);
    document.head.appendChild(s);
  }

  function headerBand(p) {
    return '' +
    '<div class="container">' +
      '<div class="row align-items-center g-3">' +
        '<div class="col-lg-8">' +
          '<nav aria-label="Breadcrumb" class="mb-2"><ol class="breadcrumb mb-0 small">' +
            '<li class="breadcrumb-item"><a href="index.html" class="text-gold">Home</a></li>' +
            '<li class="breadcrumb-item"><a href="properties.html" class="text-gold">Properties</a></li>' +
            '<li class="breadcrumb-item active text-light" aria-current="page">' + esc(p.id) + '</li>' +
          '</ol></nav>' +
          '<div class="d-flex align-items-center gap-2 mb-2 flex-wrap">' +
            '<span class="badge-status ' + p.statusClass + '">' + esc(p.status) + '</span>' +
            '<span class="badge-status bg-emerald">' + esc(p.projectName) + '</span>' +
            '<span class="badge-status bg-navy border border-secondary">' + esc(p.purpose) + '</span>' +
            '<span class="text-gold small fw-bold">ID: ' + esc(p.id) + '</span>' +
          '</div>' +
          '<h1 class="fs-2 fw-bold text-white mb-2">' + esc(p.title) + '</h1>' +
          '<p class="text-light small mb-0"><i class="fa-solid fa-location-dot text-gold me-1" aria-hidden="true"></i> ' + esc(p.address) + '</p>' +
        '</div>' +
        '<div class="col-lg-4 text-lg-end">' +
          '<div class="text-gold fw-bold fs-1 mb-2">' + esc(p.priceFormatted) + '</div>' +
          '<div class="d-flex justify-content-lg-end gap-2 flex-wrap">' +
            '<button type="button" class="btn btn-sm btn-outline-gold" data-action="print"><i class="fa-solid fa-print me-1" aria-hidden="true"></i> Print</button>' +
            '<button type="button" class="btn btn-sm btn-gold" data-action="share"><i class="fa-solid fa-share-nodes me-1" aria-hidden="true"></i> Share</button>' +
          '</div>' +
        '</div>' +
      '</div>' +
    '</div>';
  }

  function contentRow(p, agent) {
    var gallery = (p.gallery && p.gallery.length ? p.gallery : [p.image]);
    var slides = gallery.map(function (g, i) {
      return '<div class="carousel-item' + (i === 0 ? ' active' : '') + '">' +
        pic(g, p.title + ' - image ' + (i + 1), 'd-block w-100', false, 'height:480px;object-fit:cover', i === 0) + '</div>';
    }).join('');
    var controls = gallery.length > 1 ? (
      '<button class="carousel-control-prev" type="button" data-bs-target="#propertyGallery" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span></button>' +
      '<button class="carousel-control-next" type="button" data-bs-target="#propertyGallery" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span></button>'
    ) : '';

    var catLabel = { residential: 'Residential', commercial: 'Commercial', farmhouse: 'Farmhouse' }[p.category] || p.category;
    var specs = [];
    if (p.beds > 0) specs.push(['fa-bed', p.beds + ' Beds', 'Bedrooms']);
    if (p.baths > 0) specs.push(['fa-bath', p.baths + ' Baths', 'Bathrooms']);
    specs.push(['fa-ruler-combined', esc(p.size), p.sizeYds ? '~' + p.sizeYds + ' Sq Yds' : 'Plot area']);
    specs.push(['fa-tag', esc(p.subType || catLabel), 'Category']);
    specs.push(['fa-diagram-project', esc(p.projectName), 'Project']);
    specs.push(['fa-handshake', esc(p.purpose), 'Purpose']);
    specs.push(['fa-hashtag', esc(p.id), 'Reference']);
    var specHTML = specs.map(function (s, i) {
      return '<div class="col-4 col-md-2' + (i < specs.length - 1 ? ' border-end' : '') + '">' +
        '<i class="fa-solid ' + s[0] + ' fa-2x text-gold-ink mb-2" aria-hidden="true"></i>' +
        '<div class="fw-bold">' + s[1] + '</div><span class="small text-muted">' + esc(s[2]) + '</span></div>';
    }).join('');

    var descHTML = p.description.map(function (d) { return '<p class="text-muted">' + esc(d) + '</p>'; }).join('');
    var featHTML = p.features.map(function (f) {
      return '<div class="col-md-4 col-6"><i class="fa-solid fa-check-circle text-gold-ink me-2" aria-hidden="true"></i> ' + esc(f) + '</div>';
    }).join('');
    // Sample house layout - shown only on residential plots (illustrates what a
    // buyer could build). Not shown for commercial / farmhouse land.
    var layoutCard = (p.category === 'residential') ?
      ('<div class="card border-0 shadow-sm rounded-4 p-4 mb-4"><h2 class="fw-bold mb-3 text-navy h5">Sample House Layout</h2>' +
      '<div class="text-center">' +
        pic('floorplan_sample', 'Example house layout for a plot like ' + p.title, 'img-fluid rounded-3 border', false, 'max-height:440px;width:auto') +
        '<p class="small text-muted mt-2 mb-0">An example layout for illustration only &mdash; the plot is sold as land. Design and construction are arranged separately once the plot is transferred.</p>' +
      '</div></div>') : '';

    var locationCard =
      '<div class="card border-0 shadow-sm rounded-4 p-4 mb-4"><h2 class="fw-bold mb-3 text-navy h5">Location &amp; Connectivity</h2>' +
      '<p class="text-muted mb-2"><i class="fa-solid fa-location-dot text-gold-ink me-2" aria-hidden="true"></i>' + esc(p.address) + '</p>' +
      '<p class="text-muted small mb-0">Positioned within Multan\'s Southern Bypass corridor, with access associated with Budhla Road and the surrounding road network toward major parts of the city. Confirm exact plot location on a site visit.</p></div>';

    var disclaimer =
      '<div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-light"><p class="small text-muted mb-0">' +
      '<i class="fa-solid fa-circle-info text-gold-ink me-2" aria-hidden="true"></i>' +
      'Price, plot number, category and availability shown here are indicative. Please confirm current details, payment plans and documentation directly with the Zam Zam Estate sales office before making any commitment.</p></div>';

    var waMsg = 'Hello Zam Zam Estate, I am interested in ' + p.title + ' (Ref: ' + p.id + ') at ' + p.projectName + '. Please share current details.';

    return '' +
    '<div class="container"><div class="row g-4">' +
      '<div class="col-lg-8">' +
        '<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">' +
          '<div id="propertyGallery" class="carousel slide"><div class="carousel-inner">' + slides + '</div>' + controls + '</div>' +
        '</div>' +
        '<div class="card border-0 shadow-sm rounded-4 p-4 mb-4"><h2 class="fw-bold mb-3 text-navy h5">Property Key Overview</h2>' +
          '<div class="row g-3 text-center">' + specHTML + '</div></div>' +
        '<div class="card border-0 shadow-sm rounded-4 p-4 mb-4"><h2 class="fw-bold mb-3 text-navy h5">Detailed Property Overview</h2>' + descHTML + '</div>' +
        '<div class="card border-0 shadow-sm rounded-4 p-4 mb-4"><h2 class="fw-bold mb-4 text-navy h5">Features &amp; Highlights</h2><div class="row g-3">' + featHTML + '</div></div>' +
        layoutCard +
        locationCard +
        disclaimer +
      '</div>' +
      '<div class="col-lg-4">' +
        '<div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top:100px;z-index:10">' +
          '<h2 class="fw-bold mb-3 text-navy h5">Enquire About This Property</h2>' +
          '<div class="d-flex align-items-center gap-3 mb-3">' +
            '<span class="brand-icon bg-gold text-navy rounded-circle d-flex align-items-center justify-content-center">ZZ</span>' +
            '<div><div class="fw-bold h6 mb-0">Zam Zam Estate</div>' +
            '<span class="text-gold-ink small fw-bold">Property Team &middot; Multan</span></div>' +
          '</div>' +
          '<a href="' + wa(waMsg, agent.whatsapp) + '" target="_blank" rel="noopener" class="btn btn-gold w-100 justify-content-center mb-2"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Chat on WhatsApp</a>' +
          '<a href="tel:' + esc(agent.phoneRaw) + '" class="btn btn-navy w-100 justify-content-center mb-4"><i class="fa-solid fa-phone" aria-hidden="true"></i> Call ' + esc((window.PN && window.PN.SITE.phone) || '+92 309 666 0071') + '</a>' +
          '<hr class="mb-4">' +
          '<h3 class="fw-bold text-navy h6 mb-3">Send an Enquiry</h3>' +
          '<form class="inquiry-form" novalidate>' +
            '<input type="hidden" name="propertyId" value="' + esc(p.id) + '">' +
            '<input type="hidden" name="propertyTitle" value="' + esc(p.title) + '">' +
            '<div class="hp-field" aria-hidden="true"><input type="text" name="zzhp_field" tabindex="-1" autocomplete="off" aria-hidden="true"></div>' +
            '<div class="mb-2"><label class="form-label small fw-bold text-navy" for="iq-name">Name</label><input type="text" id="iq-name" name="name" class="form-control form-control-sm" required></div>' +
            '<div class="mb-2"><label class="form-label small fw-bold text-navy" for="iq-phone">Phone / WhatsApp</label><input type="tel" id="iq-phone" name="phone" class="form-control form-control-sm" required></div>' +
            '<div class="mb-2"><label class="form-label small fw-bold text-navy" for="iq-msg">Message <span class="text-muted fw-normal">(optional)</span></label><textarea id="iq-msg" name="message" rows="2" class="form-control form-control-sm"></textarea></div>' +
            '<button type="submit" class="btn btn-gold w-100 justify-content-center btn-sm">Send Enquiry</button>' +
            '<p class="form-status mt-2" role="status" aria-live="polite"></p>' +
          '</form>' +
          '<div class="bg-light p-3 rounded-3 text-center mt-3">' +
            '<div class="small text-muted"><i class="fa-solid fa-location-dot text-gold-ink me-1" aria-hidden="true"></i> ' +
            esc((window.PN && window.PN.SITE.address) || 'Near Khera Chowk, Budhla Road, Southern Bypass, Multan') + '</div></div>' +
        '</div>' +
      '</div>' +
    '</div></div>';
  }

  function similarBlock(p) {
    var sims = DATA.filter(function (x) {
      return x.id !== p.id && (x.category === p.category || x.project === p.project);
    }).slice(0, 3);
    if (!sims.length) return '';
    var cards = sims.map(function (s) {
      var a = AGENTS[s.agent] || { name: 'Zam Zam Estate' };
      return '<div class="col-lg-4 col-md-6"><article class="property-card">' +
        '<div class="property-thumb-wrap">' + pic(s.image, s.title, 'property-thumb', true) +
          '<div class="property-badges"><span class="badge-status ' + s.statusClass + '">' + esc(s.status) + '</span></div>' +
          '<div class="property-price-tag">' + esc(s.priceFormatted) + '</div></div>' +
        '<div class="property-body"><div class="property-location"><i class="fa-solid fa-location-dot text-gold-ink" aria-hidden="true"></i> ' + esc(s.location) + '</div>' +
          '<h3 class="property-title h6"><a href="property-detail.html?id=' + encodeURIComponent(s.id) + '" class="text-navy">' + esc(s.title) + '</a></h3>' +
          '<div class="property-footer"><span class="agent-name-mini">' + esc(a.name) + '</span>' +
          '<a href="property-detail.html?id=' + encodeURIComponent(s.id) + '" class="btn btn-sm btn-outline-gold">Details <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>' +
        '</div></article></div>';
    }).join('');
    return '<div class="container"><h2 class="section-title h3 mb-4">Similar Properties</h2><div class="row g-4">' + cards + '</div></div>';
  }
})();
