@extends('layouts.public')

@section('title', $project->name . ' | ' . $site->name . ', Multan')
@section('description', $project->description ?: ($project->name . ' — a ' . $site->name . ' project in Multan.'))

@section('content')
  <section class="py-5 text-white cta-band">
    <picture><source type="image/webp" srcset="/assets/images/{{ $project->img }}.webp"><img class="cta-bg" src="/assets/images/{{ $project->img }}.png" alt="" aria-hidden="true" width="1024" height="1024" loading="lazy" decoding="async"></picture>
    <div class="container py-3">
      <div class="row align-items-center">
        <div class="col-md-8">
          <span class="badge-pill-tag gold mb-2">{{ $project->location }}</span>
          <h1 class="fs-1 fw-bold text-white mb-2">{{ $project->name }}</h1>
          <p class="text-light mb-0">{{ $project->tag ?: ($project->is_farm ? 'Farmhouse & Land' : 'Residential & Commercial') }}</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
          <nav aria-label="Breadcrumb">
            <ol class="breadcrumb justify-content-md-end mb-0">
              <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-gold">Home</a></li>
              <li class="breadcrumb-item"><a href="{{ route('projects') }}" class="text-gold">Projects</a></li>
              <li class="breadcrumb-item active text-white" aria-current="page">{{ $project->name }}</li>
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
            <picture><source type="image/webp" srcset="/assets/images/{{ $project->img }}.webp"><img src="/assets/images/{{ $project->img }}.png" alt="{{ $project->name }}, {{ $project->location }}" width="1024" height="1024" loading="lazy" decoding="async"></picture>
          </div>
        </div>
        <div class="col-lg-6 ps-lg-5">
          <span class="badge-pill-tag {{ $project->is_farm ? 'navy' : 'gold' }} mb-2">{{ $project->location }}</span>
          <h2 class="section-title mb-3">{{ $project->name }}</h2>
          <p class="text-muted mb-3">{{ $project->description }}</p>

          @if($project->sizes->count())
            <h3 class="h6 fw-bold text-navy mb-2">Available plot sizes</h3>
            <div class="feature-grid-list mb-3" style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px 20px;">
              @foreach($project->sizes as $s)
                <div class="d-flex align-items-center gap-2 fw-semibold text-navy"><i class="fa-solid fa-circle-check text-gold-ink" aria-hidden="true"></i> {{ $s }}</div>
              @endforeach
            </div>
          @endif

          <p class="small text-muted mb-4">Current inventory, pricing, payment plans and the on-ground status of facilities should be confirmed directly with the {{ $site->name }} sales office.</p>

          <div class="d-flex flex-wrap gap-3">
            <a href="{{ route('properties') }}?project={{ $project->slug }}" class="btn btn-gold"><i class="fa-solid {{ $project->is_farm ? 'fa-tree' : 'fa-building' }} me-1" aria-hidden="true"></i> {{ $project->is_farm ? 'Explore Farmhouse Land' : 'View ' . $project->name . ' Plots' }}</a>
            <a href="{{ route('contact') }}" class="btn btn-outline-gold"><i class="fa-solid fa-headset me-1" aria-hidden="true"></i> Ask About Availability</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  @if($project->packages->count())
    <section class="section-padding packages-section">
      <div class="container">
        <div class="text-center section-header">
          <span class="badge-pill-tag gold mb-2">{{ $project->name }}</span>
          <h2 class="section-title">Available Plans</h2>
        </div>
        <div class="row g-4 justify-content-center">
          @foreach($project->packages as $pkg)
            <div class="col-lg-4 col-md-6">
              @include('partials.plot-package-card', ['pkg' => $pkg])
            </div>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  <section class="py-4 bg-light border-top">
    <div class="container text-center">
      <a href="{{ route('projects') }}" class="btn btn-outline-gold"><i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i> Back to All Projects</a>
    </div>
  </section>
@endsection
