@extends('layouts.public')

@section('title', 'Projects | ' . $navProjects->pluck('name')->join(' & ') . ', Multan | ' . $site->name)
@section('description', $site->name . ' projects in Multan: Southern Bypass, Mattital Road, Kassi Road and Qadirpur Ran.')

@section('head')
  @if($needsSwiper)<link rel="stylesheet" href="{{ asset_v('/vendor/swiper/swiper-bundle.min.css') }}">@endif
  @verbatim
  <style>
    #projects-container { --bs-gutter-y: 4rem; }
    .project-hero-card { background: var(--bg-white); border-radius: var(--radius-lg); border: 1px solid var(--border-light); box-shadow: var(--shadow-md); overflow: hidden; transition: var(--transition); }
    .project-hero-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-4px); }
    .project-img-wrapper { position: relative; min-height: 380px; height: 100%; overflow: hidden; }
    .project-img-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
    .project-hero-card:hover .project-img-wrapper img { transform: scale(1.05); }
    .project-content-wrap { padding: 40px; display: flex; flex-direction: column; justify-content: space-between; height: 100%; }
    .feature-grid-list { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px 20px; margin: 20px 0; }
    .feature-grid-item { display: flex; align-items: center; gap: 10px; font-size: 0.9rem; font-weight: 600; color: var(--navy-dark); }
    .project-filter-btn { background: transparent; border: 2px solid var(--border-light); color: var(--text-main); font-weight: 600; padding: 10px 24px; border-radius: var(--radius-full); transition: var(--transition); }
    .project-filter-btn.active, .project-filter-btn:hover { background: var(--navy-dark); border-color: var(--navy-dark); color: var(--primary-gold); }
    @media (max-width: 991px) { .project-img-wrapper { min-height: 240px; } .project-content-wrap { padding: 28px; } }
  </style>
  @endverbatim
@endsection

@section('content')
  <section class="bg-navy py-5 text-white position-relative">
    <div class="container py-3">
      <div class="row align-items-center">
        <div class="col-md-8">
          <span class="badge-pill-tag gold mb-2">{{ pc('projects_hero_badge') }}</span>
          <h1 class="fs-1 fw-bold text-white mb-2">{{ pc('projects_hero_heading') }}</h1>
          <p class="text-light mb-0">{{ pc('projects_hero_sub') }}</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
          <nav aria-label="Breadcrumb">
            <ol class="breadcrumb justify-content-md-end mb-0">
              <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-gold">Home</a></li>
              <li class="breadcrumb-item active text-white" aria-current="page">Projects</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </section>

  <section class="bg-white py-4 border-bottom">
    <div class="container">
      <div class="d-flex flex-wrap justify-content-center gap-2" id="project-filters" role="group" aria-label="Filter projects">
        <button type="button" class="project-filter-btn active" data-filter="all">All</button>
        @foreach($projects as $p)
          <button type="button" class="project-filter-btn" data-filter="{{ $p->slug }}">{{ $p->name }}</button>
        @endforeach
      </div>
    </div>
  </section>

  <section class="section-padding bg-light">
    <div class="container">
      <div class="row g-5" id="projects-container">
        @foreach($projects as $p)
          @php $img = $p->img; $isFarm = $p->is_farm; $sizes = $p->sizes; $flip = $p->flip; $tag = $p->tag; @endphp
          <div class="col-12 project-item" data-category="{{ $p->slug }}">
            <div class="project-hero-card" id="{{ $p->slug }}">
              <div class="row g-0 align-items-stretch">
                <div class="col-lg-6 {{ $flip ? 'order-lg-2' : '' }}">
                  <div class="project-img-wrapper">
                    <picture><source type="image/webp" srcset="{{ img_srcset($img) }}" sizes="(min-width: 992px) 560px, 100vw"><img src="/assets/images/{{ $img }}.png" alt="{{ $p->name }}, {{ $p->location }}" width="1024" height="1024" loading="lazy" decoding="async"></picture>
                    <span class="badge-status {{ $isFarm ? 'hot' : 'featured' }} position-absolute top-0 {{ $flip ? 'end-0' : 'start-0' }} m-4">{{ $tag ?: ($isFarm ? 'Farmhouse & Land' : 'Residential & Commercial') }}</span>
                  </div>
                </div>
                <div class="col-lg-6 {{ $flip ? 'order-lg-1' : '' }}">
                  <div class="project-content-wrap">
                    <div>
                      <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <span class="badge-pill-tag {{ $isFarm ? 'navy' : 'gold' }}">{{ $p->location }}</span>
                      </div>
                      <h2 class="fw-bold text-navy fs-2 mb-3">{{ $p->name }}</h2>
                      <p class="text-muted mb-3">{{ $p->description }}</p>
                      @if($sizes->count())
                        <h3 class="h6 fw-bold text-navy mb-2">Available plot sizes</h3>
                        <div class="feature-grid-list">
                          @foreach($sizes as $s)
                            <div class="feature-grid-item"><i class="fa-solid fa-circle-check text-gold-ink" aria-hidden="true"></i> {{ $s }}</div>
                          @endforeach
                        </div>
                      @endif
                      <p class="small text-muted mb-3">Current inventory, pricing, payment plans and the on-ground status of facilities should be confirmed directly with the {{ $site->name }} sales office.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-2 pt-3 border-top">
                      <a href="#{{ $p->slug }}-properties" class="btn btn-gold"><i class="fa-solid fa-building me-1" aria-hidden="true"></i> Properties ({{ $p->availableProps->count() }})</a>
                      @if($p->videos->count())<a href="#{{ $p->slug }}-videos" class="btn btn-outline-gold"><i class="fa-solid fa-film me-1" aria-hidden="true"></i> Videos</a>@endif
                      @if($p->layouts->count() || $p->locations->count())<a href="#{{ $p->slug }}-maps" class="btn btn-outline-gold"><i class="fa-solid fa-map me-1" aria-hidden="true"></i> Maps</a>@endif
                      <a href="{{ route('contact') }}" class="btn btn-outline-gold"><i class="fa-solid fa-headset me-1" aria-hidden="true"></i> Ask About Availability</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            @include('partials.project-sections', ['p' => $p])
          </div>
        @endforeach
      </div>
    </div>
  </section>

  <div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content map-modal">
        <div class="modal-header border-0 pb-0">
          <h2 class="modal-title h6 fw-bold" id="mapModalTitle">Map</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body"><div class="map-zoom-wrap" id="mapZoomWrap"><img id="mapModalImg" src="" alt=""></div><p class="small text-muted text-center mt-2 mb-0">Click the map to zoom in or out.</p></div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  @if($needsSwiper)<script src="{{ asset_v('/vendor/swiper/swiper-bundle.min.js') }}"></script>@endif
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var btns = document.querySelectorAll('.project-filter-btn');
      var items = document.querySelectorAll('.project-item');
      var slugs = Array.prototype.map.call(btns, function (b) { return b.getAttribute('data-filter'); })
        .filter(function (f) { return f !== 'all'; })
        .sort(function (a, b) { return b.length - a.length; });   // longest first, so "x-orchard" beats "x"

      function applyFilter(f) {
        btns.forEach(function (b) {
          var on = b.getAttribute('data-filter') === f;
          b.classList.toggle('active', on);
          b.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        items.forEach(function (item) {
          item.hidden = !(f === 'all' || item.getAttribute('data-category') === f);
        });
      }

      btns.forEach(function (btn) {
        btn.addEventListener('click', function () { applyFilter(btn.getAttribute('data-filter')); });
      });

      // #slug opens that project; #slug-videos / -maps / -plans / -properties / -sold jump inside it
      function fromHash() {
        var hash = (location.hash || '').replace('#', '');
        if (!hash) return;
        var slug = slugs.filter(function (s) { return hash === s || hash.indexOf(s + '-') === 0; })[0];
        if (!slug) return;
        applyFilter(slug);
        if (hash === slug + '-sold') {
          var t = document.getElementById('tab-sold-btn-' + slug);
          if (t && window.bootstrap) bootstrap.Tab.getOrCreateInstance(t).show();
          hash = slug + '-properties';
        }
        var target = document.getElementById(hash);
        if (target) window.requestAnimationFrame(function () { target.scrollIntoView({ behavior: 'smooth', block: 'start' }); });
      }
      fromHash();
      window.addEventListener('hashchange', fromHash);

      // ---- YouTube: load the player only when someone presses play (keeps the page fast).
      // Delegated, so the thumbnail button keeps working after a slider restores it.
      document.addEventListener('click', function (e) {
        var b = e.target.closest('.yt-lite');
        if (!b) return;
        var f = document.createElement('iframe');
        f.src = 'https://www.youtube-nocookie.com/embed/' + b.dataset.yt + '?autoplay=1&rel=0';
        f.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
        f.allowFullscreen = true;
        f.title = 'Project video';
        b.replaceWith(f);
      });

      // ---- Video sliders (projects with more than one video)
      var sliders = [];
      if (window.Swiper) {
        document.querySelectorAll('[data-video-slider]').forEach(function (root) {
          var frames = root.querySelectorAll('.video-main .project-video-frame');
          frames.forEach(function (fr) { fr.__orig = fr.innerHTML; });   // to put the lite thumbnail back after leaving a slide

          var main = new Swiper(root.querySelector('.video-main'), {
            spaceBetween: 24, speed: 450, rewind: true,
            keyboard: { enabled: true, onlyInViewport: true },
            noSwipingSelector: 'video, iframe, .yt-lite',      // dragging the player must not swipe the slider
            observer: true, observeParents: true, observeSlideChildren: true
          });

          var title = root.querySelector('.video-now-title'), count = root.querySelector('.video-now-count');
          function sync() {
            var slide = main.slides[main.activeIndex];
            title.textContent = slide ? slide.getAttribute('data-title') : '';
            count.textContent = (main.activeIndex + 1) + ' / ' + main.slides.length;
            // stop whatever was playing on the other slides
            frames.forEach(function (fr, i) {
              if (i === main.activeIndex) return;
              var v = fr.querySelector('video'); if (v) v.pause();
              if (fr.querySelector('iframe')) fr.innerHTML = fr.__orig;
            });
          }
          main.on('slideChange', sync); sync();

          root.querySelector('.video-prev').addEventListener('click', function () { main.slidePrev(); });
          root.querySelector('.video-next').addEventListener('click', function () { main.slideNext(); });
          root.classList.add('is-ready');
          sliders.push(main);
        });
      }
      // a project that was hidden by the filter has no width until it is shown again
      document.querySelectorAll('.project-filter-btn').forEach(function (b) {
        b.addEventListener('click', function () { setTimeout(function () { sliders.forEach(function (s) { s.update(); }); }, 30); });
      });

      // ---- Map zoom modal
      var modalEl = document.getElementById('mapModal');
      if (modalEl && window.bootstrap) {
        var modal = new bootstrap.Modal(modalEl);
        var img = document.getElementById('mapModalImg');
        var wrap = document.getElementById('mapZoomWrap');
        document.querySelectorAll('[data-map-src]').forEach(function (b) {
          b.addEventListener('click', function () {
            img.src = b.dataset.mapSrc; img.alt = b.dataset.mapTitle;
            document.getElementById('mapModalTitle').textContent = b.dataset.mapTitle;
            wrap.classList.remove('is-zoomed');
            modal.show();
          });
        });
        wrap.addEventListener('click', function () { wrap.classList.toggle('is-zoomed'); });
      }

      // ---- Big lists: show the first 9 cards (6 on phones), the rest behind "Show more"
      var LIMIT = window.matchMedia('(max-width: 767px)').matches ? 6 : 9;
      document.querySelectorAll('[data-more-group]').forEach(function (row) {
        var cards = row.children;
        if (cards.length <= LIMIT) return;
        for (var i = LIMIT; i < cards.length; i++) cards[i].classList.add('d-none');
        var btn = document.createElement('button');
        btn.type = 'button'; btn.className = 'btn btn-outline-gold mx-auto mt-4 d-flex';
        btn.textContent = 'Show more (' + (cards.length - LIMIT) + ')';
        row.after(btn);
        btn.addEventListener('click', function () {
          Array.prototype.forEach.call(cards, function (c) { c.classList.remove('d-none'); });
          btn.remove();
        });
      });
    });
  </script>
@endpush
