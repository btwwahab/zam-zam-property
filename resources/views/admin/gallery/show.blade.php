@extends('layouts.admin')
@section('title', $project->name . ' Gallery')

@section('content')
  <div class="section-head">
    <div><div class="crumb"><a href="{{ route('admin.gallery.index') }}" style="text-decoration:none;color:var(--text-faint)">Gallery</a> / {{ $project->name }}</div>
    <h2>{{ $project->name }} Gallery</h2></div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <a class="btn-a btn-ghost" href="{{ route('gallery.project', $project->slug) }}" target="_blank" rel="noopener"><i class="fa-solid fa-up-right-from-square"></i> View on website</a>
      <a class="btn-a btn-ghost" href="{{ route('admin.gallery.index') }}"><i class="fa-solid fa-arrow-left"></i> Back</a>
    </div>
  </div>

  {{-- switch to another project's gallery --}}
  <div class="gal-switch">
    @foreach($projects as $p)
      <a href="{{ route('admin.gallery.show', $p) }}" class="{{ $p->id === $project->id ? 'active' : '' }}">{{ $p->name }}</a>
    @endforeach
  </div>

  <div class="card-a"><div class="card-a-body">
    @include('admin.gallery._manager')
  </div></div>
@endsection
