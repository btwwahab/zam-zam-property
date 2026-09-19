@extends('layouts.admin')
@section('title', $project->exists ? 'Edit Project' : 'Add Project')

@section('content')
  <div class="section-head">
    <div><div class="crumb"><a href="{{ route('admin.projects.index') }}" style="text-decoration:none;color:var(--text-faint)">Projects</a> / {{ $project->exists ? $project->name : 'New' }}</div>
    <h2>{{ $project->exists ? 'Edit Project' : 'Add Project' }}</h2></div>
    <a class="btn-a btn-ghost" href="{{ route('admin.projects.index') }}"><i class="fa-solid fa-arrow-left"></i> Back</a>
  </div>

  <div class="card-a"><div class="card-a-body">
    <form class="form-a" method="POST" action="{{ $project->exists ? route('admin.projects.update', $project) : route('admin.projects.store') }}" enctype="multipart/form-data">
      @csrf
      @if($project->exists) @method('PUT') @endif
      <div class="fld"><label>Project name<span class="req-star">*</span></label>
        <input type="text" name="name" value="{{ old('name', $project->name) }}" placeholder="e.g. Horizon Heights" required maxlength="150" class="@error('name') has-err @enderror">
        @error('name')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>Slug <span class="hint">(auto from name if blank)</span></label>
        <input type="text" name="slug" value="{{ old('slug', $project->slug) }}" placeholder="horizon-heights" maxlength="150" class="@error('slug') has-err @enderror">
        @error('slug')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>Location</label>
        <input type="text" name="location" value="{{ old('location', $project->location) }}" placeholder="Southern Bypass, Multan" maxlength="190" class="@error('location') has-err @enderror">
        @error('location')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>Status<span class="req-star">*</span></label>
        <select name="status" required class="@error('status') has-err @enderror">@foreach(['Active','Upcoming','Sold Out','Archived'] as $x)<option @selected(old('status', $project->status ?: 'Active') === $x)>{{ $x }}</option>@endforeach</select>
        @error('status')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>Tag / badge text</label>
        <input type="text" name="tag" value="{{ old('tag', $project->tag) }}" placeholder="Residential & Commercial" maxlength="80" class="@error('tag') has-err @enderror">
        @error('tag')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld col-span-2"><label>Description</label>
        <textarea name="description" rows="3" maxlength="2000" class="@error('description') has-err @enderror">{{ old('description', $project->description) }}</textarea>
        @error('description')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <x-image-field label="Card image" sub="Shown on this project's card on the homepage and Projects page" :current="$project->image" />
      <div class="form-actions">
        <button type="submit" class="btn-a btn-gold"><i class="fa-solid fa-check"></i> Save project</button>
        <a href="{{ route('admin.projects.index') }}" class="btn-a btn-ghost">Cancel</a>
      </div>
    </form>
  </div></div>
@endsection
