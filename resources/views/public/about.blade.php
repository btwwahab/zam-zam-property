@extends('layouts.public')

@section('title', 'About ' . $site->name . ' | Real Estate in Multan')
@section('description', pc('about_hero_sub'))

@section('content')
  <section class="py-5 text-white cta-band">
    <picture><source type="image/webp" srcset="/assets/images/gated_community_aerial.webp"><img class="cta-bg" src="/assets/images/gated_community_aerial.png" alt="" aria-hidden="true" width="1024" height="1024" loading="lazy" decoding="async"></picture>
    <div class="container py-3">
      <div class="row align-items-center">
        <div class="col-md-8">
          <span class="badge-pill-tag gold mb-2">{{ pc('about_hero_badge') }}</span>
          <h1 class="fs-1 fw-bold text-white mb-2">{{ pc('about_hero_heading') }}</h1>
          <p class="text-light mb-0">{{ pc('about_hero_sub') }}</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
          <nav aria-label="Breadcrumb">
            <ol class="breadcrumb justify-content-md-end mb-0">
              <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-gold">Home</a></li>
              <li class="breadcrumb-item active text-white" aria-current="page">About</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </section>

  <section class="section-padding bg-white">
    <div class="container">
      <div class="row g-5 g-lg-4 align-items-center">
        <div class="col-lg-6">
          <div class="showcase-media">
            <picture><source type="image/webp" srcset="/assets/images/gated_community_aerial.webp"><img src="/assets/images/gated_community_aerial.png" alt="Aerial view of a planned housing society near Multan" width="1024" height="1024" loading="lazy" decoding="async"></picture>
            <div class="sm-badge">
              <span class="sm-mono">{{ \Illuminate\Support\Str::substr(strtoupper(collect(explode(' ', $site->name))->map(fn($w) => $w[0] ?? '')->join('')), 0, 2) }}</span>
              <span><span class="sm-title d-block">Southern Bypass Corridor</span><span class="sm-sub">Khera Chowk &bull; Budhla Road, Multan</span></span>
            </div>
          </div>
        </div>
        <div class="col-lg-6 ps-lg-5">
          <span class="badge-pill-tag gold mb-2">{{ pc('about_overview_badge') }}</span>
          <h2 class="section-title mb-3">{{ pc('about_overview_heading') }}</h2>
          <p class="text-muted mb-3">{{ pc('about_overview') }}</p>
          <p class="text-muted mb-3">{{ pc('about_overview_p2') }}</p>
          <p class="text-muted mb-0">{{ pc('about_overview_p3') }}</p>
        </div>
      </div>
    </div>
  </section>

  <section class="section-padding bg-light">
    <div class="container">
      <div class="row g-5 g-lg-4 align-items-center">
        <div class="col-lg-6 order-lg-2">
          <div class="showcase-media flip">
            <picture><source type="image/webp" srcset="/assets/images/modern_villa_1kanal.webp"><img src="/assets/images/modern_villa_1kanal.png" alt="Modern house in a Multan housing society" width="1024" height="1024" loading="lazy" decoding="async"></picture>
            <div class="sm-badge">
              <span class="sm-mono">{{ \Illuminate\Support\Str::substr(strtoupper(preg_replace('/[^A-Z]/', '', strtoupper($navProjects->first()->name ?? 'RGC'))), 0, 3) ?: 'RGC' }}</span>
              <span><span class="sm-title d-block">{{ $navProjects->first()->name ?? 'Royal Grace City' }}</span><span class="sm-sub">Featured project</span></span>
            </div>
          </div>
        </div>
        <div class="col-lg-6 order-lg-1 pe-lg-5">
          <span class="badge-pill-tag gold mb-2">{{ pc('about_story_badge') }}</span>
          <h2 class="section-title mb-3">{{ pc('about_story_heading') }}</h2>
          <p class="text-muted mb-3">{{ pc('about_story_p1') }}</p>
          <p class="text-muted mb-3">{{ pc('about_story_p2') }}</p>
          <p class="text-navy fw-semibold mb-0">{{ pc('about_story_p3') }}</p>
        </div>
      </div>
    </div>
  </section>

  <section class="section-padding bg-navy text-white">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-6">
          <div class="feature-tile h-100 p-4 p-lg-5">
            <span class="icon-badge mb-4"><i class="fa-solid fa-bullseye" aria-hidden="true"></i></span>
            <h2 class="h3 fw-bold text-white mb-2">Our Mission</h2>
            <p class="text-light mb-2">{{ pc('about_mission') }}</p>
            <p class="text-light mb-0">{{ pc('about_mission_p2') }}</p>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="feature-tile h-100 p-4 p-lg-5">
            <span class="icon-badge mb-4"><i class="fa-solid fa-eye" aria-hidden="true"></i></span>
            <h2 class="h3 fw-bold text-white mb-2">Our Vision</h2>
            <p class="text-light mb-2">{{ pc('about_vision') }}</p>
            <p class="text-light mb-0">{{ pc('about_vision_p2') }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section-padding bg-white">
    <div class="container">
      <div class="text-center section-header">
        <span class="badge-pill-tag gold mb-2">{{ pc('about_values_badge') }}</span>
        <h2 class="section-title">{{ pc('about_values_heading') }}</h2>
      </div>
      <div class="row g-4">
        @foreach(pc_list('about_values') as $i => $v)
          <div class="col-lg-4 col-md-6"><div class="value-card" data-index="{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}"><span class="v-icon"><i class="fa-solid {{ $v['icon'] }}" aria-hidden="true"></i></span><h3 class="fw-bold h5 mb-2">{{ $v['title'] }}</h3><p class="text-muted small mb-0">{{ $v['text'] }}</p></div></div>
        @endforeach
      </div>
    </div>
  </section>

  <section class="section-padding bg-light">
    <div class="container">
      <div class="founder-panel">
        <div class="row g-4 g-lg-5 align-items-center">
          <div class="col-lg-3 text-center">
            <span class="founder-monogram mx-auto">{{ \Illuminate\Support\Str::substr(strtoupper(collect(explode(' ', $site->name))->map(fn($w) => $w[0] ?? '')->join('')), 0, 2) }}</span>
          </div>
          <div class="col-lg-9">
            <span class="badge-pill-tag gold mb-2">{{ pc('about_leadership_badge') }}</span>
            <h2 class="h2 fw-bold text-white mb-3">{{ pc('about_leadership_heading') }}</h2>
            <div class="founder-quote">
              <p class="text-light mb-2">{{ pc('about_leadership_p1') }}</p>
              <p class="text-light mb-0">{{ pc('about_leadership_p2') }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  @if($team->count())
  <section class="section-padding bg-white">
    <div class="container">
      <div class="text-center section-header">
        <span class="badge-pill-tag gold mb-2">{{ pc('about_team_badge', 'Our Team') }}</span>
        <h2 class="section-title">{{ pc('about_team_heading', 'The people behind ' . $site->name) }}</h2>
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

  <section class="section-padding bg-white">
    <div class="container">
      <div class="text-center section-header">
        <span class="badge-pill-tag gold mb-2">{{ pc('about_journey_badge') }}</span>
        <h2 class="section-title">{{ pc('about_journey_heading') }}</h2>
      </div>
      <div class="row g-4 steps">
        @foreach(pc_list('about_journey') as $i => $s)
          <div class="col-lg-4 col-md-6"><div class="step"><span class="step-num">{{ $i + 1 }}</span><h3 class="fw-bold h6 mb-1 text-navy">{{ $s['title'] }}</h3><p class="text-muted small mb-0">{{ $s['text'] }}</p></div></div>
        @endforeach
      </div>
    </div>
  </section>

  <section class="section-padding cta-band text-white text-center">
    <picture><source type="image/webp" srcset="/assets/images/golf_community_estate.webp"><img class="cta-bg" src="/assets/images/golf_community_estate.png" alt="" aria-hidden="true" width="1024" height="1024" loading="lazy" decoding="async"></picture>
    <div class="container">
      <h2 class="fs-1 fw-bold text-white mb-3">{{ pc('about_cta_heading') }}</h2>
      <p class="text-light mx-auto mb-4" style="max-width: 600px;">{{ pc('about_cta_text') }}</p>
      <div class="d-flex flex-wrap gap-2 justify-content-center">
        <a href="{{ route('contact') }}" class="btn btn-gold"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Submit Your Requirement</a>
        <a href="tel:{{ $site->phone_raw }}" class="btn btn-outline-gold"><i class="fa-solid fa-phone" aria-hidden="true"></i> {{ $site->phone }}</a>
      </div>
    </div>
  </section>
@endsection
