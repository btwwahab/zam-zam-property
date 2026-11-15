@php
  $img = $p->image ?: 'gated_community_aerial';
  $cat = ucfirst($p->category_slug);
@endphp
<div class="col-lg-4 col-md-6">
  <article class="property-card {{ $p->is_sold ? 'is-sold' : '' }}">
    <div class="property-thumb-wrap">
      <picture>
        <source type="image/webp" srcset="/assets/images/{{ $img }}-sm.webp">
        <img src="{{ img_src($img) }}" alt="{{ $p->title }}" class="property-thumb" width="512" height="512" loading="lazy" decoding="async">
      </picture>
      <div class="property-badges">
        @if($p->is_sold)
          <span class="badge-status sold">Sold</span>
        @else
          <span class="badge-status {{ $p->status_class }}">{{ $p->status }}</span>
          <span class="badge-status bg-navy">{{ $p->purpose }}</span>
        @endif
      </div>
      <div class="property-price-tag">{{ $p->price_formatted }}</div>
    </div>
    <div class="property-body">
      <div class="property-location"><i class="fa-solid fa-location-dot text-gold-ink" aria-hidden="true"></i> {{ $p->location }}</div>
      <h3 class="property-title h6"><a href="{{ route('property.detail') }}?id={{ urlencode($p->ref) }}" class="text-navy">{{ $p->title }}</a></h3>
      <div class="property-specs">
        @if($p->beds > 0)<div class="spec-item"><i class="fa-solid fa-bed" aria-hidden="true"></i> {{ $p->beds }} Beds</div>@endif
        @if($p->baths > 0)<div class="spec-item"><i class="fa-solid fa-bath" aria-hidden="true"></i> {{ $p->baths }} Baths</div>@endif
        <div class="spec-item"><i class="fa-solid fa-ruler-combined" aria-hidden="true"></i> {{ $p->size }}</div>
        <div class="spec-item"><i class="fa-solid fa-tag" aria-hidden="true"></i> {{ $cat }}</div>
      </div>
      <div class="property-footer">
        <div class="agent-mini"><span class="agent-name-mini">{{ $p->project_name ?: $site->name }}</span></div>
        <a href="{{ route('property.detail') }}?id={{ urlencode($p->ref) }}" class="btn btn-sm btn-outline-gold">Details <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
      </div>
    </div>
  </article>
</div>
