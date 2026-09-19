@extends('layouts.public')

@section('title', 'Property Detail | ' . $site->name . ', Multan')
@section('description', 'Property details, features and enquiry for ' . $site->name . ' opportunities in Multan.')

@section('content')
  <div class="print-brand container">
    <strong>{{ $site->name }}</strong> &mdash; Property Enquiry Sheet<br>
    {{ $site->address }} &nbsp;&bull;&nbsp; {{ $site->phone }} &nbsp;&bull;&nbsp; wa.me/{{ $site->whatsapp }}
  </div>

  <section class="bg-navy text-white py-4 border-bottom border-secondary" id="pd-header">
    <div class="container"><p class="text-light mb-0">Loading property&hellip;</p></div>
  </section>

  <section class="section-padding bg-light">
    <div id="pd-content"></div>
    <div id="pd-similar" class="mt-5"></div>
  </section>
@endsection

@push('scripts')
  <script src="{{ asset_v('/js/detail.js') }}"></script>
@endpush
