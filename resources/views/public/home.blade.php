@extends('layouts.public')

@section('title', $site->name . ' Multan | Residential, Commercial & Farmhouse Properties')
@section('description', 'Explore residential, commercial, land and farmhouse property opportunities in Multan with ' . $site->name . '.')

@section('head')
  @if($site->hero_type !== 'video')
    <link rel="preload" as="image" href="/assets/images/{{ ($heroSlides->first()->image ?? $site->hero_poster) }}.webp" fetchpriority="high">
  @endif
  @php
    $ld = ['@context' => 'https://schema.org', '@type' => 'RealEstateAgent', 'name' => $site->name, 'telephone' => $site->phone_raw, 'areaServed' => 'Multan, Punjab, Pakistan', 'address' => ['@type' => 'PostalAddress', 'streetAddress' => $site->address_short, 'addressLocality' => 'Multan', 'addressRegion' => 'Punjab', 'addressCountry' => 'PK']];
  @endphp
  <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@section('content')
  {{-- ============ HERO ============ --}}
  <section class="hero-section">
    @if($site->hero_type === 'video' && $site->hero_video)
      @php $vsrc = \Illuminate\Support\Str::startsWith($site->hero_video, ['http://', 'https://', '/']) ? $site->hero_video : '/assets/videos/' . $site->hero_video; @endphp
      <video class="hero-video-bg" autoplay loop muted playsinline preload="auto">
        <source src="{{ $vsrc }}" type="video/mp4">
      </video>
    @elseif($site->hero_type === 'image')
      <div class="hero-slider" aria-hidden="true">
        <div class="hero-slide active" style="background-image: url('/assets/images/{{ ($heroSlides->first()->image ?? $site->hero_poster) }}.webp');"></div>
      </div>
    @else
      <div class="hero-slider" id="heroSlider" aria-hidden="true">
        @forelse($heroSlides as $s)
          <div class="hero-slide {{ $loop->first ? 'active' : '' }}" style="background-image: url('/assets/images/{{ $s->image }}.webp');"></div>
        @empty
          <div class="hero-slide active" style="background-image: url('/assets/images/{{ $site->hero_poster ?: 'gated_community_aerial' }}.webp');"></div>
        @endforelse
      </div>
    @endif
    <div class="hero-bg-overlay"></div>
    <div class="container hero-content">
      <div class="row align-items-center g-4">
        <div class="col-lg-8 text-center text-lg-start">
          @if($site->hero_badge)<span class="badge-pill-tag gold mb-3"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $site->hero_badge }}</span>@endif
          <h1 class="hero-title">{!! nl2br(e(pc('home_hero_heading', 'Your Trusted Property Partner in Multan'))) !!}</h1>
          <p class="hero-subtitle">{{ pc('home_hero_subheading') }}</p>
          <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-lg-start">
            <a href="{{ $site->hero_cta_primary_link ?: route('properties') }}" class="btn btn-gold"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> {{ $site->hero_cta_primary_text ?: 'Explore Properties' }}</a>
            <a href="{{ $site->hero_cta_secondary_link ?: route('contact') }}" class="btn btn-glass-light"><i class="fa-solid fa-headset" aria-hidden="true"></i> {{ $site->hero_cta_secondary_text ?: 'Contact Us' }}</a>
          </div>
          @if($site->hero_note)<p class="small text-light mt-3 mb-0"><i class="fa-solid fa-location-dot text-gold me-1" aria-hidden="true"></i> {{ $site->hero_note }}</p>@endif
        </div>

        <div class="col-lg-4">
          <div class="hero-form-card">
            <h2 class="hero-form-title">Book Your Plot Today</h2>
            <p class="hero-form-sub">Speak with a {{ $site->name }} sales advisor.</p>

            <form class="inquiry-form hero-form" method="POST" action="{{ route('enquiries.store') }}" novalidate>
              @csrf
              <div class="hp-field" aria-hidden="true"><input type="text" name="zzhp_field" tabindex="-1" autocomplete="off" aria-hidden="true"></div>
              <div class="hero-field">
                <i class="fa-solid fa-user" aria-hidden="true"></i>
                <input type="text" name="name" class="form-control" placeholder="Name" required>
              </div>
              <div class="hero-field">
                <i class="fa-solid fa-phone" aria-hidden="true"></i>
                <input type="tel" name="phone" class="form-control" placeholder="Phone Number" required>
              </div>
              <div class="hero-field hero-select" data-hero-select>
                <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                <span class="hero-select-label" data-hero-select-label aria-hidden="true">Select Property Interest</span>
                <i class="fa-solid fa-chevron-down hero-select-caret" aria-hidden="true"></i>
                <select name="interest" class="hero-select-native form-select" required>
                  <option value="">Select Property Interest</option>
                  @foreach($navCategories as $c)<option>{{ $c->name }}</option>@endforeach
                </select>
                <ul class="hero-select-menu" hidden aria-hidden="true">
                  <li data-value="" class="is-selected">Select Property Interest</li>
                  @foreach($navCategories as $c)<li data-value="{{ $c->name }}">{{ $c->name }}</li>@endforeach
                </ul>
              </div>
              <div class="hero-field hero-field-textarea">
                <i class="fa-solid fa-comment-dots" aria-hidden="true"></i>
                <textarea name="message" class="form-control" rows="2" placeholder="Message (optional)"></textarea>
              </div>
              <button type="submit" class="btn btn-gold w-100 justify-content-center">Send Enquiry <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
              <a href="https://wa.me/{{ $site->whatsapp }}?text={{ rawurlencode('Hello, I am interested in Zam Zam Orchard farmhouse plots. Please share the latest availability.') }}" target="_blank" rel="noopener" class="btn btn-outline-whatsapp w-100 justify-content-center mt-2"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Get Details on WhatsApp</a>
              <p class="form-status mt-2 mb-0 small" role="status" aria-live="polite"></p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ HIGHLIGHTS ============ --}}
  <section class="bg-white">
    <div class="container">
      <div class="highlight-strip">
        <div class="row g-0">
          @foreach(pc_list('home_highlights') as $h)
            <div class="col-12 col-sm-6 col-lg-3"><div class="highlight-item"><i class="{{ str_contains($h['icon'], ' ') ? $h['icon'] : 'fa-solid ' . $h['icon'] }}" aria-hidden="true"></i><span><span class="h-title d-block">{{ $h['title'] }}</span><span class="h-sub">{{ $h['sub'] }}</span></span></div></div>
          @endforeach
        </div>
      </div>
    </div>
  </section>

  {{-- ============ FEATURED PROJECTS ============ --}}
  <section class="section-padding bg-light">
    <div class="container">
      <div class="d-flex justify-content-between align-items-end section-header">
        <div>
          <span class="badge-pill-tag gold mb-2">{{ pc('home_projects_badge') }}</span>
          <h2 class="section-title">{{ pc('home_projects_heading') }}</h2>
        </div>
        <a href="{{ route('projects') }}" class="btn btn-outline-gold d-none d-md-inline-flex">All Projects <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
      </div>
      <div class="row g-4">
        @foreach($navProjects as $p)
          @php $img = $p->image ?: 'gated_community_aerial'; @endphp
          <div class="col-lg-6">
            <article class="project-card">
              <picture><source type="image/webp" srcset="/assets/images/{{ $img }}.webp"><img src="/assets/images/{{ $img }}.png" alt="{{ $p->name }}, {{ $p->location }}" class="project-card-img" width="1024" height="1024" loading="lazy" decoding="async"></picture>
              <div class="project-card-overlay">
                <span class="project-tag">{{ $p->tag ?: 'Development' }}</span>
                <h3 class="fw-bold fs-2 text-white mb-1">{{ $p->name }}</h3>
                <p class="text-light fs-6 mb-3">{{ $p->description }}</p>
                <div class="d-flex gap-2">
                  <a href="{{ route('properties') }}?project={{ $p->slug }}" class="btn btn-sm btn-gold">View Plots</a>
                  <a href="{{ route('projects') }}#{{ $p->slug }}" class="btn btn-glass-light"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Details</a>
                </div>
              </div>
            </article>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ============ PACKAGES ============ --}}
  @if($packages->count())
  <section class="section-padding packages-section">
    <div class="container">
      <div class="text-center section-header">
        <span class="badge-pill-tag gold mb-2">{{ pc('home_packages_badge', 'Zam Zam Orchard') }}</span>
        <h2 class="section-title">{{ pc('home_packages_heading', 'Farmhouse Plots — Cash & Easy Installments') }}</h2>
      </div>
      <div class="row g-4 justify-content-center">
        @foreach($packages as $pkg)
          <div class="col-lg-4 col-md-6">
            @include('partials.plot-package-card', ['pkg' => $pkg])
          </div>
        @endforeach
      </div>
    </div>
  </section>
  @endif

  {{-- ============ WHY US ============ --}}
  <section class="section-padding why-us-section text-white position-relative overflow-hidden">
    <div class="why-us-bg-overlay"></div>
    <div class="container position-relative" style="z-index: 2;">
      <div class="text-center section-header">
        <span class="badge-pill-tag gold mb-3"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> {{ pc('home_why_badge') }}</span>
        <h2 class="fs-1 fw-bold text-white mb-0">{{ pc('home_why_heading') }}</h2>
      </div>
      <div class="row g-4">
        @foreach(pc_list('home_why_items') as $it)
          <div class="col-lg-4 col-md-6"><div class="feature-tile"><span class="icon-badge mb-4"><i class="fa-solid {{ $it['icon'] }}" aria-hidden="true"></i></span><h3 class="fw-bold text-white h5 mb-2">{{ $it['title'] }}</h3><p class="text-light small mb-0">{{ $it['text'] }}</p></div></div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ============ CATEGORIES ============ --}}
  <section class="section-padding bg-light">
    <div class="container">
      <div class="text-center section-header">
        <span class="badge-pill-tag gold mb-2">{{ pc('home_categories_badge') }}</span>
        <h2 class="section-title">{{ pc('home_categories_heading') }}</h2>
      </div>
      <div class="row g-4">
        @foreach($navCategories as $c)
          @php $img = $c->image ?: 'gated_community_aerial'; @endphp
          <div class="col-lg-3 col-md-6">
            <a class="cat-card h-100" href="{{ route('properties') }}?category={{ $c->slug }}">
              <picture><source type="image/webp" srcset="/assets/images/{{ $img }}.webp"><img src="/assets/images/{{ $img }}.png" alt="{{ $c->name }} property near Multan" width="1024" height="1024" loading="lazy" decoding="async"></picture>
              <div class="cat-overlay">
                <span class="cat-icon"><i class="fa-solid {{ $c->icon }}" aria-hidden="true"></i></span>
                <h3 class="fw-bold h5">{{ $c->name }}</h3>
                <p>{{ $c->description }}</p>
                <span class="cat-cta">Explore {{ $c->name }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
              </div>
            </a>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ============ FEATURED PROPERTIES ============ --}}
  <section class="section-padding bg-white">
    <div class="container">
      <div class="text-center section-header">
        <span class="badge-pill-tag gold mb-2">{{ pc('home_featured_badge') }}</span>
        <h2 class="section-title">{{ pc('home_featured_heading') }}</h2>
        <p class="text-muted mx-auto" style="max-width: 620px;">{{ pc('home_featured_sub') }}</p>
      </div>
      <div id="properties-grid" class="row g-4" data-mode="featured" aria-live="polite"></div>
      <div class="text-center mt-5">
        <a href="{{ route('properties') }}" class="btn btn-navy">{{ pc('home_featured_btn') }} <i class="fa-solid fa-arrow-right text-gold ms-2" aria-hidden="true"></i></a>
      </div>
    </div>
  </section>

  {{-- ============ INTRO ============ --}}
  <section class="section-padding bg-light">
    <div class="container">
      <div class="row g-5 align-items-center">
        <div class="col-lg-6">
          <span class="badge-pill-tag gold mb-2">{{ pc('home_intro_badge') }}</span>
          <h2 class="section-title mb-3">{{ pc('home_intro_heading') }}</h2>
          <p class="text-muted mb-3">{{ pc('home_intro_p1') }}</p>
          <p class="text-muted mb-4">{{ pc('home_intro_p2') }}</p>
          <a href="{{ route('about') }}" class="btn btn-navy">{{ pc('home_intro_btn') }} <i class="fa-solid fa-arrow-right text-gold ms-1" aria-hidden="true"></i></a>
        </div>
        <div class="col-lg-6">
          <div class="d-flex flex-column gap-3">
            @foreach(pc_list('home_intro_cards') as $c)
              <div class="d-flex align-items-start gap-3 p-4 rounded-4 bg-white border">
                <span class="icon-badge flex-shrink-0"><i class="fa-solid {{ $c['icon'] }}" aria-hidden="true"></i></span>
                <div><h3 class="h6 fw-bold text-navy mb-1">{{ $c['title'] }}</h3><p class="text-muted small mb-0">{{ $c['text'] }}</p></div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ ABOUT SHORT ============ --}}
  <section class="section-padding bg-white">
    <div class="container">
      <div class="row g-5 g-lg-4 align-items-center">
        <div class="col-lg-6">
          <div class="showcase-media">
            <picture><source type="image/webp" srcset="/assets/images/gated_community_aerial.webp"><img src="/assets/images/gated_community_aerial.png" alt="Southern Bypass property corridor, Multan" width="1024" height="1024" loading="lazy" decoding="async"></picture>
            <div class="sm-badge">
              <span class="sm-mono">{{ \Illuminate\Support\Str::substr(strtoupper(collect(explode(' ', $site->name))->map(fn($w) => $w[0] ?? '')->join('')), 0, 2) }}</span>
              <span><span class="sm-title d-block">Flagship Projects</span><span class="sm-sub">{{ $navProjects->pluck('name')->join(' • ') }}</span></span>
            </div>
          </div>
        </div>
        <div class="col-lg-6 ps-lg-5">
          <span class="badge-pill-tag gold mb-2">About {{ $site->name }}</span>
          <h2 class="section-title mb-3">{{ pc('home_about_heading') }}</h2>
          <p class="text-muted mb-3">{{ pc('home_about_paragraph') }}</p>
          <p class="text-muted mb-3">{{ pc('home_about_p2') }}</p>
          <ul class="check-list mb-4">
            @foreach(pc_list('home_about_checklist') as $li)
              <li><i class="fa-solid fa-check" aria-hidden="true"></i> {{ is_array($li) ? ($li['text'] ?? '') : $li }}</li>
            @endforeach
          </ul>
          <div class="d-flex flex-wrap align-items-center gap-3">
            <a href="{{ route('about') }}" class="btn btn-navy">{{ pc('home_about_btn') }} <i class="fa-solid fa-arrow-right text-gold ms-1" aria-hidden="true"></i></a>
            <a href="tel:{{ $site->phone_raw }}" class="fw-bold text-navy text-decoration-none"><i class="fa-solid fa-phone text-gold-ink me-1" aria-hidden="true"></i> {{ $site->phone }}</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ TEAM ============ --}}
  @if($team->count())
  <section class="section-padding bg-light">
    <div class="container">
      <div class="text-center section-header">
        <span class="badge-pill-tag gold mb-2">{{ pc('home_team_badge', 'Our Team') }}</span>
        <h2 class="section-title">{{ pc('home_team_heading', 'Meet the ' . $site->name . ' team') }}</h2>
      </div>
      <div class="row g-4 justify-content-center">
        @foreach($team as $m)
          <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="team-card h-100">
              <div class="team-card-photo">
                <picture>
                  <source type="image/webp" srcset="/assets/images/{{ $m->image ?: 'senior_realtor_male' }}.webp">
                  <img src="/assets/images/{{ $m->image ?: 'senior_realtor_male' }}.png" alt="{{ $m->name }}" width="600" height="600" loading="lazy" decoding="async" onerror="this.style.visibility='hidden'">
                </picture>
              </div>
              <div class="team-card-body">
                <h3 class="h6 fw-bold text-navy mb-1">{{ $m->name }}</h3>
                @if($m->role)<p class="text-gold-ink small fw-semibold mb-2">{{ $m->role }}</p>@endif
                <div class="team-card-links">
                  @if($m->phone)<a href="tel:{{ preg_replace('/[^+\d]/', '', $m->phone) }}" aria-label="Call {{ $m->name }}"><i class="fa-solid fa-phone" aria-hidden="true"></i></a>@endif
                  @if($m->whatsapp)<a href="https://wa.me/{{ $m->whatsapp }}" target="_blank" rel="noopener" aria-label="WhatsApp {{ $m->name }}"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></a>@endif
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>
  @endif

  {{-- ============ CTA ============ --}}
  <section class="section-padding cta-band text-white text-center">
    <picture><source type="image/webp" srcset="/assets/images/gated_community_aerial.webp"><img class="cta-bg" src="/assets/images/gated_community_aerial.png" alt="" aria-hidden="true" width="1024" height="1024" loading="lazy" decoding="async"></picture>
    <div class="container">
      <span class="badge-pill-tag gold mb-3"><i class="fa-solid fa-phone" aria-hidden="true"></i> {{ pc('home_cta_badge') }}</span>
      <h2 class="fs-1 fw-bold text-white mb-3">{{ pc('home_cta_heading') }}</h2>
      <p class="text-light mx-auto mb-4" style="max-width: 640px;">{{ pc('home_cta_text') }}</p>
      <div class="d-flex flex-wrap gap-2 justify-content-center">
        <a href="tel:{{ $site->phone_raw }}" class="btn btn-gold"><i class="fa-solid fa-phone" aria-hidden="true"></i> Call {{ $site->phone }}</a>
        <a href="https://wa.me/{{ $site->whatsapp }}?text={{ rawurlencode('Hello, I am interested in exploring property opportunities with ' . $site->name . ' in Multan. Please share the latest available options.') }}" target="_blank" rel="noopener" class="btn btn-outline-gold"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp Us</a>
        <a href="{{ route('properties') }}" class="btn btn-glass-light"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Explore Properties</a>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  <script src="{{ asset_v('/js/properties.js') }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('[data-hero-select]').forEach(function (wrap) {
        var nativeSel = wrap.querySelector('.hero-select-native');
        var label = wrap.querySelector('[data-hero-select-label]');
        var menu = wrap.querySelector('.hero-select-menu');
        var items = menu.querySelectorAll('li');
        if (!nativeSel || !label || !menu) return;

        function close() { menu.hidden = true; wrap.classList.remove('is-open'); }
        function open() { menu.hidden = false; wrap.classList.add('is-open'); }
        function sync() {
          var val = nativeSel.value, match = null;
          items.forEach(function (li) {
            var isSel = li.getAttribute('data-value') === val;
            li.classList.toggle('is-selected', isSel);
            if (isSel) match = li;
          });
          if (match) label.textContent = match.textContent;
        }

        wrap.addEventListener('click', function (e) {
          if (e.target.closest('.hero-select-menu')) return;
          menu.hidden ? open() : close();
        });
        items.forEach(function (li) {
          li.addEventListener('click', function (e) {
            e.stopPropagation();
            nativeSel.value = li.getAttribute('data-value');
            nativeSel.dispatchEvent(new Event('change', { bubbles: true }));
            close();
          });
        });
        nativeSel.addEventListener('change', sync);
        nativeSel.addEventListener('focus', function () { wrap.classList.add('is-focused'); });
        nativeSel.addEventListener('blur', function () { wrap.classList.remove('is-focused'); close(); });
        document.addEventListener('click', function (e) { if (!wrap.contains(e.target)) close(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
      });
    });
  </script>
@endpush
