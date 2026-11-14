@php
  $initials = \Illuminate\Support\Str::upper(collect(preg_split('/\s+/', trim($m->name)))->filter()->take(2)->map(fn ($w) => \Illuminate\Support\Str::substr($w, 0, 1))->join(''));
@endphp
<div class="team-card h-100">
  {{-- Initials sit underneath; an uploaded photo is layered on top. No photo (or a missing file) = initials show. --}}
  <div class="team-card-photo team-card-photo--initials" @unless($m->image) role="img" aria-label="{{ $m->name }}" @endunless>
    <span aria-hidden="true">{{ $initials }}</span>
    @if($m->image)
      <picture>
        <source type="image/webp" srcset="/assets/images/{{ $m->image }}.webp">
        <img src="/assets/images/{{ $m->image }}.png" alt="{{ $m->name }}" width="600" height="600" loading="lazy" decoding="async" onerror="this.closest('picture').remove()">
      </picture>
    @endif
  </div>
  <div class="team-card-body">
    <h3 class="h6 fw-bold text-navy mb-1">{{ $m->name }}</h3>
    @if($m->role)<p class="text-gold-ink small fw-semibold mb-1">{{ $m->role }}</p>@endif
    @if($m->phone)<p class="team-card-phone small mb-2"><a href="tel:{{ preg_replace('/[^+\d]/', '', $m->phone) }}">{{ $m->phone }}</a></p>@endif
    <div class="team-card-links">
      @if($m->phone)<a href="tel:{{ preg_replace('/[^+\d]/', '', $m->phone) }}" aria-label="Call {{ $m->name }}"><i class="fa-solid fa-phone" aria-hidden="true"></i></a>@endif
      @if($m->whatsapp)<a href="https://wa.me/{{ preg_replace('/\D/', '', $m->whatsapp) }}" target="_blank" rel="noopener" aria-label="WhatsApp {{ $m->name }}"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></a>@endif
    </div>
  </div>
</div>
