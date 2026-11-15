@extends('layouts.public')

@section('title', $project->name . ' Gallery | ' . $site->name)
@section('description', 'Photos of the work done at ' . $project->name . ', ' . $project->location . '.')

@section('content')
  @php $banner = $project->image ?: 'hero-slide'; $n = $project->gallery->count(); @endphp
  <section class="py-5 text-white cta-band page-banner page-banner-projects">
    <picture><source type="image/webp" srcset="{{ img_srcset($banner) }}" sizes="100vw"><img class="cta-bg" src="{{ img_src($banner) }}" alt="" width="1600" height="893" decoding="async"></picture>
    <div class="container py-3">
      <div class="row align-items-center">
        <div class="col-md-8">
          <span class="badge-pill-tag gold mb-2">{{ $project->location }}</span>
          <h1 class="fs-1 fw-bold text-white mb-2">{{ $project->name }} Gallery</h1>
          <p class="text-light mb-0">{{ $n ? $n . ' ' . \Illuminate\Support\Str::plural('photo', $n) . ' of the work done on this project.' : 'Photos of the work done on this project.' }}</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
          <nav aria-label="Breadcrumb">
            <ol class="breadcrumb justify-content-md-end mb-0">
              <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-gold">Home</a></li>
              <li class="breadcrumb-item"><a href="{{ route('gallery') }}" class="text-gold">Gallery</a></li>
              <li class="breadcrumb-item active text-white" aria-current="page">{{ $project->name }}</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </section>

  {{-- jump to another project's gallery --}}
  <section class="bg-white py-4 border-bottom">
    <div class="container">
      <div class="d-flex flex-wrap justify-content-center gap-2" id="project-filters" role="navigation" aria-label="Other galleries">
        <a href="{{ route('gallery') }}" class="project-filter-btn text-decoration-none">All</a>
        @foreach($navProjects as $np)
          <a href="{{ route('gallery.project', $np->slug) }}" class="project-filter-btn text-decoration-none {{ $np->slug === $project->slug ? 'active' : '' }}" @if($np->slug === $project->slug) aria-current="page" @endif>{{ $np->name }}</a>
        @endforeach
      </div>
    </div>
  </section>

  <section class="section-padding bg-light">
    <div class="container">
      @if($n)
        <div class="gal-grid" data-gal-group="{{ $project->slug }}" data-more-group data-more-limit="6" data-more-step="6">
          @foreach($project->gallery as $g)
            @php $gTitle = $g->title ?: $project->name . ' — photo ' . $loop->iteration; @endphp
            <button type="button" class="gal-item" data-gal-src="/assets/gallery/{{ $g->file }}" data-gal-title="{{ $g->title }}" aria-label="Open photo: {{ $gTitle }}">
              <img src="/assets/gallery/{{ is_file(public_path('assets/gallery/' . $g->gallery_thumb)) ? $g->gallery_thumb : $g->file }}" alt="{{ $gTitle }}" loading="lazy" decoding="async">
              <span class="gal-zoom" aria-hidden="true"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
              @if($g->title)<span class="gal-cap">{{ $g->title }}</span>@endif
            </button>
          @endforeach
        </div>
      @else
        <div class="text-center py-5">
          <i class="fa-solid fa-images fa-3x text-muted mb-3" aria-hidden="true"></i>
          <h2 class="h4 fw-bold">Photos coming soon</h2>
          <p class="text-muted mb-3">We are adding photos of the work done at {{ $project->name }}. Please check back shortly.</p>
        </div>
      @endif

      <div class="d-flex flex-wrap justify-content-center gap-2 mt-5">
        <a href="{{ route('projects') }}#{{ $project->slug }}" class="btn btn-gold"><i class="fa-solid fa-building me-1" aria-hidden="true"></i> View {{ $project->name }}</a>
        <a href="{{ route('gallery') }}" class="btn btn-outline-gold"><i class="fa-solid fa-images me-1" aria-hidden="true"></i> All galleries</a>
        <a href="{{ route('contact') }}" class="btn btn-outline-gold"><i class="fa-solid fa-headset me-1" aria-hidden="true"></i> Ask About Availability</a>
      </div>
    </div>
  </section>

  @include('partials.gallery-viewer')
@endsection

@push('scripts')
  <script>
    if (window.markNavProject) window.markNavProject('{{ $project->slug }}', 'gallery');
    // phones: the project pills are one swipeable row, so bring the current one into view
    (function () {
      var strip = document.getElementById('project-filters'), cur = strip && strip.querySelector('.active');
      if (strip && cur && strip.scrollWidth > strip.clientWidth) strip.scrollLeft = cur.offsetLeft - (strip.clientWidth - cur.offsetWidth) / 2;
    })();
  </script>
@endpush
