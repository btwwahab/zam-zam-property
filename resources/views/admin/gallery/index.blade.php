@extends('layouts.admin')
@section('title', 'Gallery')

@section('content')
  <div class="section-head">
    <div><h2>Gallery</h2><p>Photos of the work done on each project. Choose a project to add or remove photos.</p></div>
    <a class="btn-a btn-ghost" href="{{ route('gallery') }}" target="_blank" rel="noopener"><i class="fa-solid fa-up-right-from-square"></i> View on website</a>
  </div>

  <div class="gal-proj-grid">
    @foreach($projects as $p)
      @php
        $cover = $p->gallery->first();
        $n = $p->gallery->count();
      @endphp
      <a class="gal-proj-admin" href="{{ route('admin.gallery.show', $p) }}">
        <span class="gpa-media">
          @if($cover)
            <img src="/assets/gallery/{{ is_file(public_path('assets/gallery/' . $cover->gallery_thumb)) ? $cover->gallery_thumb : $cover->file }}" alt="" loading="lazy">
          @else
            <span class="gpa-empty"><i class="fa-solid fa-images"></i><span>No photos yet</span></span>
          @endif
        </span>
        <span class="gpa-body">
          <strong>{{ $p->name }}</strong>
          <span class="gpa-meta">{{ $n }} {{ \Illuminate\Support\Str::plural('photo', $n) }} &middot; {{ $p->location }}</span>
          <span class="gpa-go">{{ $n ? 'Manage photos' : 'Add photos' }} <i class="fa-solid fa-arrow-right"></i></span>
        </span>
      </a>
    @endforeach
  </div>
@endsection
