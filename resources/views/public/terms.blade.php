@extends('layouts.public')

@section('title', 'Terms & Conditions | ' . $site->name)
@section('description', 'Terms and conditions for using the ' . $site->name . ' website.')

@section('content')
  <section class="bg-navy py-5 text-white">
    <div class="container py-3">
      <h1 class="fs-1 fw-bold text-white mb-2">Terms &amp; Conditions</h1>
      <p class="text-light mb-0">Terms for using this website and the information presented on it.</p>
    </div>
  </section>

  <section class="section-padding bg-white">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-9">
          @foreach(legal_blocks($pages['terms_body'] ?? '') as $b)
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
