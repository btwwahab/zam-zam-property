@extends('layouts.public')

@section('title', 'Gallery | Our Projects in Multan | ' . $site->name)
@section('description', 'Photos of the work done across ' . $site->name . ' projects in Multan. Pick a project to see its gallery.')

@section('content')
  @php
    $__banner = optional($projects->firstWhere('slug', 'al-falah-model-city'))->image ?: ($projects->first(fn ($x) => $x->image)->image ?? 'hero-slide');
  @endphp
  <section class="py-5 text-white cta-band page-banner page-banner-projects">
    <picture><source type="image/webp" srcset="{{ img_srcset($__banner) }}" sizes="100vw"><img class="cta-bg" src="{{ img_src($__banner) }}" alt="" width="1600" height="893" decoding="async"></picture>
    <div class="container py-3">
      <div class="row align-items-center">
        <div class="col-md-8">
          <span class="badge-pill-tag gold mb-2">Our Work</span>
          <h1 class="fs-1 fw-bold text-white mb-2">Project Galleries</h1>
          <p class="text-light mb-0">Photos of the work we have done on every project. Choose a project to see its gallery.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
          <nav aria-label="Breadcrumb">
            <ol class="breadcrumb justify-content-md-end mb-0">
              <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-gold">Home</a></li>
              <li class="breadcrumb-item active text-white" aria-current="page">Gallery</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </section>

  <section class="section-padding bg-light">
    <div class="container">
      <div class="row g-4 justify-content-center">
        @foreach($projects as $p)
          @php
            $cover = $p->gallery->first();
            $coverSrc = $cover
              ? '/assets/gallery/' . (is_file(public_path('assets/gallery/' . $cover->gallery_thumb)) ? $cover->gallery_thumb : $cover->file)
              : img_src($p->image ?: 'project-placeholder');
            $n = $p->gallery->count();
          @endphp
          <div class="col-md-6">
            <a href="{{ route('gallery.project', $p->slug) }}" class="gal-proj-card">
              <img src="{{ $coverSrc }}" alt="{{ $p->name }} gallery" loading="lazy" decoding="async">
              <span class="gal-proj-count"><i class="fa-solid fa-images" aria-hidden="true"></i> {{ $n ? $n . ' ' . \Illuminate\Support\Str::plural('photo', $n) : 'Coming soon' }}</span>
              <span class="gal-proj-body">
                <span class="gal-proj-loc">{{ $p->location }}</span>
                <span class="gal-proj-name">{{ $p->name }}</span>
                <span class="gal-proj-go">View Gallery <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
              </span>
            </a>
          </div>
        @endforeach
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  <script>if (window.markNavProject) window.markNavProject('all', 'gallery');</script>
@endpush
