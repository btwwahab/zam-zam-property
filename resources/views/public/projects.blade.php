@extends('layouts.public')

@section('title', 'Projects | ' . $navProjects->pluck('name')->join(' & ') . ', Multan | ' . $site->name)
@section('description', $site->name . ' projects in Multan along the Southern Bypass corridor.')

@section('head')
  @verbatim
  <style>
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
                    <picture><source type="image/webp" srcset="/assets/images/{{ $img }}.webp"><img src="/assets/images/{{ $img }}.png" alt="{{ $p->name }}, {{ $p->location }}" width="1024" height="1024" loading="lazy" decoding="async"></picture>
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
                    @if($p->packages->count())
                      <div class="row g-3 mb-3">
                        @foreach($p->packages as $pkg)
                          <div class="col-12">@include('partials.plot-package-card', ['pkg' => $pkg])</div>
                        @endforeach
                      </div>
                    @endif
                    <div class="d-flex flex-wrap gap-3 mt-2 pt-3 border-top">
                      <a href="{{ route('properties') }}?project={{ $p->slug }}" class="btn btn-gold"><i class="fa-solid {{ $isFarm ? 'fa-tree' : 'fa-building' }} me-1" aria-hidden="true"></i> {{ $isFarm ? 'Explore Farmhouse Land' : 'View ' . $p->name . ' Plots' }}</a>
                      <a href="{{ route('contact') }}" class="btn btn-outline-gold"><i class="fa-solid fa-headset me-1" aria-hidden="true"></i> Ask About Availability</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var btns = document.querySelectorAll('.project-filter-btn');
      var items = document.querySelectorAll('.project-item');
      function applyFilter(f) {
        var matched = false;
        btns.forEach(function (b) {
          var on = b.getAttribute('data-filter') === f;
          if (on) matched = true;
          b.classList.toggle('active', on);
          b.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        if (!matched) f = 'all';
        items.forEach(function (item) {
          item.hidden = !(f === 'all' || item.getAttribute('data-category') === f);
        });
        return matched;
      }

      btns.forEach(function (btn) {
        btn.addEventListener('click', function () {
          applyFilter(btn.getAttribute('data-filter'));
        });
      });

      function fromHash() {
        var slug = (location.hash || '').replace('#', '');
        if (!slug) return;
        if (applyFilter(slug)) {
          var card = document.getElementById(slug);
          if (card) window.requestAnimationFrame(function () {
            card.scrollIntoView({ behavior: 'smooth', block: 'start' });
          });
        }
      }
      fromHash();
      window.addEventListener('hashchange', fromHash);
    });
  </script>
@endpush
