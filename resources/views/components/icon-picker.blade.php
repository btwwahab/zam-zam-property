@props([
  'name' => 'icon',
  'current' => null,
  'label' => 'Icon',
  'sub' => 'Pick the symbol shown on this card',
])

@php
  $icons = [
    'fa-house' => 'Homes',
    'fa-building' => 'Apartments',
    'fa-city' => 'City',
    'fa-store' => 'Commercial',
    'fa-briefcase' => 'Offices',
    'fa-warehouse' => 'Warehouse',
    'fa-industry' => 'Industrial',
    'fa-tree' => 'Farmhouse',
    'fa-seedling' => 'Agricultural',
    'fa-mountain-sun' => 'Plots / Land',
    'fa-map-location-dot' => 'Location',
    'fa-key' => 'Rentals',
    'fa-chart-line' => 'Investment',
    'fa-gem' => 'Luxury',
    'fa-house-chimney-window' => 'Villas',
    'fa-hotel' => 'Hospitality',
  ];
  $val = old($name, $current) ?: 'fa-house';
  // keep an unknown/legacy value usable rather than silently dropping it
  if (! array_key_exists($val, $icons)) {
      $icons = [$val => 'Current'] + $icons;
  }
@endphp

<div class="fld col-span-2">
  <label>{{ $label }}</label>
  @if($sub)<div class="u-sub" style="margin:-2px 0 8px">{{ $sub }}</div>@endif
  <input type="hidden" name="{{ $name }}" value="{{ $val }}">
  <div class="icon-pick" role="radiogroup" aria-label="{{ $label }}" data-icon-pick>
    @foreach($icons as $ic => $title)
      <button type="button" class="icon-pick-opt {{ $val === $ic ? 'is-sel' : '' }}"
              data-icon="{{ $ic }}" title="{{ $title }}"
              aria-label="{{ $title }}" aria-pressed="{{ $val === $ic ? 'true' : 'false' }}">
        <i class="fa-solid {{ $ic }}" aria-hidden="true"></i>
      </button>
    @endforeach
  </div>
</div>
