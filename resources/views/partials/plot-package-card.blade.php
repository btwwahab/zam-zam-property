@php
  $marla = $pkg->total_marla;
@endphp
<div class="package-card {{ $pkg->is_featured ? 'is-featured' : '' }}">
  <svg class="package-icon" width="90" height="90" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <path d="M12 52 L50 20 L88 52"/>
    <path d="M24 46 V85 H76 V46"/>
    <path d="M42 85 V60 H58 V85"/>
    <path d="M60 30 V18 H70 V38"/>
  </svg>
  @if($pkg->is_featured)<span class="package-offer">Limited Time Offer</span>@endif
  <div class="package-head">
    <h3 class="package-label">{{ $pkg->label }}</h3>
    <p class="package-size">{{ number_format($marla) }} Marla total</p>
  </div>
  <div class="package-divider"></div>
  <div class="package-prices">
    <div class="package-price-col">
      <span class="package-price-tag">Cash</span>
      <strong class="package-price-val">{{ money_pk($pkg->cash_total) }}</strong>
      <span class="package-price-rate">{{ number_format($pkg->price_cash_per_marla) }} / Marla</span>
    </div>
    <div class="package-price-sep" aria-hidden="true"></div>
    <div class="package-price-col">
      <span class="package-price-tag">Installments</span>
      <strong class="package-price-val">{{ money_pk($pkg->installment_total) }}</strong>
      <span class="package-price-rate">{{ number_format($pkg->price_installment_per_marla) }} / Marla</span>
    </div>
  </div>
  <p class="package-plan">{{ $pkg->advance_percent }}% advance (PKR {{ number_format($pkg->advance_amount) }}) + PKR {{ number_format($pkg->monthly_installment) }} / month for {{ $pkg->tenure_years }} {{ \Illuminate\Support\Str::plural('year', $pkg->tenure_years) }}</p>
  <a href="{{ route('contact') }}" class="package-cta"><i class="fa-solid fa-headset" aria-hidden="true"></i> Book This Plot</a>
</div>
