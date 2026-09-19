@extends('layouts.admin')
@section('title', $member->exists ? 'Edit Member' : 'Add Member')

@section('content')
  <div class="section-head">
    <div><div class="crumb"><a href="{{ route('admin.team.index') }}" style="text-decoration:none;color:var(--text-faint)">Team</a> / {{ $member->exists ? $member->name : 'New' }}</div>
    <h2>{{ $member->exists ? 'Edit Member' : 'Add Member' }}</h2></div>
    <a class="btn-a btn-ghost" href="{{ route('admin.team.index') }}"><i class="fa-solid fa-arrow-left"></i> Back</a>
  </div>

  <div class="card-a"><div class="card-a-body">
    <form class="form-a" method="POST" action="{{ $member->exists ? route('admin.team.update', $member) : route('admin.team.store') }}" enctype="multipart/form-data">
      @csrf
      @if($member->exists) @method('PUT') @endif
      <div class="fld"><label>Full name<span class="req-star">*</span></label>
        <input type="text" name="name" value="{{ old('name', $member->name) }}" required maxlength="150" class="@error('name') has-err @enderror">
        @error('name')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>Role</label>
        <input type="text" name="role" value="{{ old('role', $member->role) }}" maxlength="120" class="@error('role') has-err @enderror">
        @error('role')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>Phone</label>
        <input type="tel" name="phone" value="{{ old('phone', $member->phone) }}" maxlength="60" class="@error('phone') has-err @enderror">
        @error('phone')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>WhatsApp</label>
        <input type="tel" name="whatsapp" value="{{ old('whatsapp', $member->whatsapp) }}" maxlength="40" class="@error('whatsapp') has-err @enderror">
        @error('whatsapp')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld col-span-2"><label>Photo</label>
        @if($member->image)
          <div class="u-current" style="display:flex;align-items:center;gap:10px">
            <img src="/assets/images/{{ $member->image }}.webp" alt="" style="width:44px;height:44px;border-radius:8px;object-fit:cover" onerror="this.style.display='none'">
            <span><i class="fa-solid fa-image"></i> Current photo — upload a new one to replace it</span>
          </div>
        @endif
        <div class="upload-drop">
          <input type="file" name="image_file" accept="image/*" class="@error('image_file') has-err @enderror">
          <div class="u-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
          <div class="u-main" data-drop-label>Drag &amp; drop a photo, or click to choose</div>
          <div class="u-sub">Shown on the About page team section</div>
          <div class="u-hints"><span>JPG</span><span>PNG</span><span>WEBP</span><span>MAX 8 MB</span></div>
        </div>
        @error('image_file')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="form-actions">
        <button type="submit" class="btn-a btn-gold"><i class="fa-solid fa-check"></i> Save member</button>
        <a href="{{ route('admin.team.index') }}" class="btn-a btn-ghost">Cancel</a>
      </div>
    </form>
  </div></div>
@endsection
