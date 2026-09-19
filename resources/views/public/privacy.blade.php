@extends('layouts.public')

@section('title', 'Privacy Policy | ' . $site->name)
@section('description', 'Privacy Policy for the ' . $site->name . ' website.')

@section('content')
  <section class="bg-navy py-5 text-white">
    <div class="container py-3">
      <h1 class="fs-1 fw-bold text-white mb-2">Privacy Policy</h1>
      <p class="text-light mb-0">How information you share through this website is handled.</p>
    </div>
  </section>

  <section class="section-padding bg-white">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-9">
          @foreach(legal_blocks($pages['privacy_body'] ?? '') as $b)
            @if($b['type'] === 'h')
              <h2 class="h5 fw-bold text-navy mt-4">{{ $b['text'] }}</h2>
            @else
              <p class="text-muted">{!! nl2br(e($b['text'])) !!}</p>
            @endif
          @endforeach
        </div>
      </div>
    </div>
  </section>
@endsection
