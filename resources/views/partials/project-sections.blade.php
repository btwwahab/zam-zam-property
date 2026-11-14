{{-- Everything about one project, shown right under its card on the Projects page.
     Expects $p: a project decorated by PublicController::decorateProject(). --}}
@php $sid = $p->slug; @endphp
<div class="project-panel" id="{{ $sid }}-details">

  {{-- ============ VIDEOS ============ --}}
  @if($p->videos->count())
    <div class="project-sub" id="{{ $sid }}-videos">
      <h3 class="project-sub-title"><i class="fa-solid fa-film" aria-hidden="true"></i> {{ $p->name }} in Video</h3>

      @if($p->videos->count() === 1)
        @php $v = $p->videos->first(); @endphp
        <figure class="project-video mb-0">
          @include('partials.project-video-frame', ['v' => $v])
          @if($v->title)<figcaption class="project-video-cap">{{ $v->title }}</figcaption>@endif
        </figure>
      @else
        {{-- several videos: big player + prev/next (Swiper) --}}
        <div class="video-slider" data-video-slider>
          <div class="swiper video-main">
            <div class="swiper-wrapper">
              @foreach($p->videos as $v)
                <div class="swiper-slide" data-title="{{ $v->title ?: 'Video ' . $loop->iteration }}">
                  @include('partials.project-video-frame', ['v' => $v])
                </div>
              @endforeach
            </div>
          </div>
          <div class="video-bar">
            <button type="button" class="video-nav video-prev" aria-label="Previous video"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
            <div class="video-now" aria-live="polite"><span class="video-now-title"></span><span class="video-now-count"></span></div>
            <button type="button" class="video-nav video-next" aria-label="Next video"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
          </div>
        </div>
      @endif
    </div>
  @endif

  {{-- ============ MAPS + LOCATION ============ --}}
  @if($p->layouts->count() || $p->locations->count())
    <div class="project-sub" id="{{ $sid }}-maps">
      <h3 class="project-sub-title"><i class="fa-solid fa-map" aria-hidden="true"></i> {{ $p->name }} Maps</h3>
      <div class="project-stack">
        @if($p->layouts->count())
          @php $mn = $p->layouts->count(); $mcol = $mn === 1 ? 'col-lg-9' : ($mn === 2 ? 'col-md-6' : 'col-md-6 col-lg-4'); @endphp
          <div class="row g-4 justify-content-center {{ $mn > 1 ? 'map-row-multi' : '' }}">
            @foreach($p->layouts as $m)
              @php $mTitle = $m->title ?: $p->name . ' map'; $mSrc = '/assets/maps/' . $m->file; @endphp
              <div class="{{ $mcol }}">
                <figure class="map-card">
                  <button type="button" class="map-thumb" data-map-src="{{ $mSrc }}" data-map-title="{{ $mTitle }}" aria-label="Open map: {{ $mTitle }}">
                    <img src="/assets/maps/{{ is_file(public_path('assets/maps/' . $m->thumb_file)) ? $m->thumb_file : $m->file }}" alt="{{ $mTitle }}" loading="lazy" decoding="async">
                  </button>
                  <figcaption class="map-cap">
                    @if($m->title)<span class="map-cap-title" title="{{ $m->title }}">{{ $m->title }}</span>@endif
                    <button type="button" class="map-cap-hint" data-map-src="{{ $mSrc }}" data-map-title="{{ $mTitle }}"><i class="fa-solid fa-magnifying-glass-plus" aria-hidden="true"></i> Click to zoom</button>
                  </figcaption>
                </figure>
              </div>
            @endforeach
          </div>
        @endif
        @foreach($p->locations as $m)
          <div class="location-block">
            @if($m->title)<h4 class="h6 fw-bold text-navy mb-2"><i class="fa-solid fa-location-dot text-gold-ink me-1" aria-hidden="true"></i> {{ $m->title }}</h4>@endif
            @if($m->is_embeddable_map)
              <div class="map-embed"><iframe src="{{ $m->url }}" title="{{ $m->title ?: $p->name . ' location' }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>
            @else
              <a href="{{ $m->url }}" target="_blank" rel="noopener" class="btn btn-navy"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Open in Google Maps</a>
            @endif
          </div>
        @endforeach
      </div>
    </div>
  @endif

  {{-- ============ PLOT PLANS ============ --}}
  @if($p->packages->count())
    <div class="project-sub" id="{{ $sid }}-plans">
      <h3 class="project-sub-title"><i class="fa-solid fa-tags" aria-hidden="true"></i> Available Plans</h3>
      <div class="row g-4 justify-content-center">
        @foreach($p->packages as $pkg)
          <div class="col-lg-4 col-md-6">@include('partials.plot-package-card', ['pkg' => $pkg])</div>
        @endforeach
      </div>
    </div>
  @endif

  {{-- ============ PROPERTIES: AVAILABLE / SOLD ============ --}}
  <div class="project-sub" id="{{ $sid }}-properties">
    <h3 class="project-sub-title"><i class="fa-solid fa-building" aria-hidden="true"></i> Properties</h3>

    @if($p->soldProps->count())
      <ul class="nav nav-pills avail-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation"><button class="nav-link active" id="tab-available-btn-{{ $sid }}" data-bs-toggle="pill" data-bs-target="#tab-available-{{ $sid }}" type="button" role="tab" aria-controls="tab-available-{{ $sid }}" aria-selected="true">Available <span class="avail-count">{{ $p->availableProps->count() }}</span></button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-sold-btn-{{ $sid }}" data-bs-toggle="pill" data-bs-target="#tab-sold-{{ $sid }}" type="button" role="tab" aria-controls="tab-sold-{{ $sid }}" aria-selected="false">Sold <span class="avail-count">{{ $p->soldProps->count() }}</span></button></li>
      </ul>
    @endif

    <div class="tab-content">
      <div class="tab-pane fade show active" id="tab-available-{{ $sid }}" role="tabpanel" aria-labelledby="tab-available-btn-{{ $sid }}" tabindex="0">
        @if($p->availableProps->count())
          <div class="row g-4 justify-content-center" data-more-group>
            @foreach($p->availableProps as $prop)
              @include('partials.property-card', ['p' => $prop])
            @endforeach
          </div>
        @else
          <div class="text-center py-4">
            <i class="fa-solid fa-house-circle-check fa-3x text-muted mb-3" aria-hidden="true"></i>
            <h4 class="h5 fw-bold">No properties available right now</h4>
            <p class="text-muted mb-3">New plots are added regularly. Contact us and we will tell you what is coming up in {{ $p->name }}.</p>
            <a href="{{ route('contact') }}" class="btn btn-gold"><i class="fa-solid fa-headset" aria-hidden="true"></i> Ask About Availability</a>
          </div>
        @endif
      </div>
      @if($p->soldProps->count())
        <div class="tab-pane fade" id="tab-sold-{{ $sid }}" role="tabpanel" aria-labelledby="tab-sold-btn-{{ $sid }}" tabindex="0">
          <div class="row g-4 justify-content-center" data-more-group>
            @foreach($p->soldProps as $prop)
              @include('partials.property-card', ['p' => $prop])
            @endforeach
          </div>
        </div>
      @endif
    </div>
  </div>
</div>
