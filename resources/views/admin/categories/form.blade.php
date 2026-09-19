@extends('layouts.admin')
@section('title', $category->exists ? 'Edit Category' : 'Add Category')

@section('content')
  <div class="section-head">
    <div><div class="crumb"><a href="{{ route('admin.categories.index') }}" style="text-decoration:none;color:var(--text-faint)">Categories</a> / {{ $category->exists ? $category->name : 'New' }}</div>
    <h2>{{ $category->exists ? 'Edit Category' : 'Add Category' }}</h2></div>
    <a class="btn-a btn-ghost" href="{{ route('admin.categories.index') }}"><i class="fa-solid fa-arrow-left"></i> Back</a>
  </div>

  <div class="card-a"><div class="card-a-body">
    <form class="form-a" method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data">
      @csrf
      @if($category->exists) @method('PUT') @endif
      <div class="fld"><label>Name<span class="req-star">*</span></label>
        <input type="text" name="name" value="{{ old('name', $category->name) }}" required maxlength="120" class="@error('name') has-err @enderror">
        @error('name')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>Slug <span class="hint">(auto if blank)</span></label>
        <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" maxlength="120" class="@error('slug') has-err @enderror">
        @error('slug')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>Sort order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" step="1" class="@error('sort_order') has-err @enderror">
        @error('sort_order')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld col-span-2"><label>Description</label>
        <textarea name="description" rows="2" maxlength="500" class="@error('description') has-err @enderror">{{ old('description', $category->description) }}</textarea>
        @error('description')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <x-icon-picker :current="$category->icon" sub="Pick the symbol shown on this category's card" />
      <x-image-field label="Card image" sub="Shown on this category's card on the homepage" :current="$category->image" />
      <div class="form-actions">
        <button type="submit" class="btn-a btn-gold"><i class="fa-solid fa-check"></i> Save category</button>
        <a href="{{ route('admin.categories.index') }}" class="btn-a btn-ghost">Cancel</a>
      </div>
    </form>
  </div></div>
@endsection
