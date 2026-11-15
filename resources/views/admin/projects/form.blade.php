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

  @if($project->exists)
    @php $media = $project->media->where('type', '!=', 'gallery'); $galleryCount = $project->media->where('type', 'gallery')->count(); @endphp
    <div class="card-a" style="margin-top:20px" id="media">
      <div class="card-a-head">
        <h3>Videos &amp; Maps</h3>
        <span class="head-note">Shown on the public Projects page: videos first, then maps. Add as many as you like.</span>
      </div>
      <div class="card-a-body">

        @if($media->count())
          <div class="table-wrap media-list"><table class="table-a">
            <thead><tr><th>Type</th><th>Title</th><th>Source</th><th></th></tr></thead>
            <tbody>
              @foreach($media as $m)
                <tr>
                  <td><span class="badge-a {{ $m->type === 'video' ? 'badge-new' : 'badge-featured' }}">{{ $m->is_pdf ? 'Map PDF' : ['video' => 'Video', 'layout' => 'Map image', 'location' => 'Location map'][$m->type] }}</span></td>
                  <td>{{ $m->title ?: '—' }}</td>
                  <td class="media-src">
                    @if($m->type === 'layout')<a href="/assets/maps/{{ $m->file }}" target="_blank" rel="noopener">{{ $m->file }}</a>
                    @elseif($m->file)<a href="/assets/videos/{{ $m->file }}" target="_blank" rel="noopener">{{ $m->file }} (uploaded)</a>
                    @else<a href="{{ $m->url }}" target="_blank" rel="noopener">{{ $m->url }}</a>@endif
                  </td>
                  <td><div class="row-actions">
                    <form method="POST" action="{{ route('admin.projects.media.destroy', $m) }}" data-confirm="Remove this {{ $m->type === 'video' ? 'video' : 'map' }}?">
                      @csrf @method('DELETE')
                      <button class="danger" title="Remove"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </div></td>
                </tr>
              @endforeach
            </tbody>
          </table></div>
        @endif

        <div class="media-add-grid">

          {{-- video --}}
          <form class="form-a media-add" method="POST" action="{{ route('admin.projects.media.store', $project) }}" enctype="multipart/form-data">
            @csrf <input type="hidden" name="type" value="video">
            <div class="media-add-head"><span class="media-add-ico"><i class="fa-solid fa-film"></i></span><div><h4>Add a video</h4><p>YouTube link or a short MP4</p></div></div>
            <div class="fld"><label>Title <span class="hint">(optional)</span></label>
              <input type="text" name="title" maxlength="150" placeholder="e.g. Site tour"></div>
            <div class="fld"><label>YouTube link</label>
              <input type="url" name="video_url" value="{{ old('type') === 'video' ? old('video_url') : '' }}" placeholder="https://www.youtube.com/watch?v=…" class="@error('video_url') has-err @enderror">
              @error('video_url')<span class="fld-err">{{ $message }}</span>@enderror
              <span class="fld-help">Best option: upload to YouTube (Unlisted is fine) and paste the link. It keeps the website fast.</span></div>
            <div class="media-or"><span>or</span></div>
            <div class="fld"><label>Upload a video file</label>
              <div class="upload-drop is-compact">
                <input type="file" name="video_file" accept="video/mp4,video/webm,video/ogg">
                <div class="u-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                <div class="u-main" data-drop-label>Drag &amp; drop a video, or click to choose</div>
                <div class="u-hints"><span>MP4</span><span>WEBM</span><span>MAX 60 MB</span></div>
              </div>
              @error('video_file')<span class="fld-err">{{ $message }}</span>@enderror</div>
            <div class="form-actions"><button type="submit" class="btn-a btn-gold"><i class="fa-solid fa-plus"></i> Add video</button></div>
          </form>

          {{-- layout image --}}
          <form class="form-a media-add" method="POST" action="{{ route('admin.projects.media.store', $project) }}" enctype="multipart/form-data">
            @csrf <input type="hidden" name="type" value="layout">
            <div class="media-add-head"><span class="media-add-ico"><i class="fa-solid fa-map"></i></span><div><h4>Add a map</h4><p>Master plan or layout plan &mdash; PDF or image</p></div></div>
            <div class="fld"><label>Title <span class="hint">(optional)</span></label>
              <input type="text" name="title" maxlength="150" placeholder="e.g. Master plan"></div>
            <div class="fld"><label>Map file <span class="hint">(PDF recommended)</span></label>
              <div class="upload-drop is-compact">
                <input type="file" name="layout_file" accept="application/pdf,.pdf,image/*">
                <div class="u-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                <div class="u-main" data-drop-label>Drag &amp; drop a map, or click to choose</div>
                <div class="u-hints"><span>PDF</span><span>JPG</span><span>PNG</span><span>WEBP</span><span>PDF MAX 20 MB</span></div>
              </div>
              @error('layout_file')<span class="fld-err">{{ $message }}</span>@enderror
              <span class="fld-help"><strong>Upload a PDF if you have one:</strong> a PDF stays perfectly sharp however far visitors zoom in. An image gets blurry when zoomed, so use your sharpest one.</span></div>
            <div class="form-actions"><button type="submit" class="btn-a btn-gold"><i class="fa-solid fa-plus"></i> Add map</button></div>
          </form>

          {{-- location --}}
          <form class="form-a media-add" method="POST" action="{{ route('admin.projects.media.store', $project) }}">
            @csrf <input type="hidden" name="type" value="location">
            <div class="media-add-head"><span class="media-add-ico"><i class="fa-solid fa-location-dot"></i></span><div><h4>Add a location map</h4><p>Google Maps</p></div></div>
            <div class="fld"><label>Title <span class="hint">(optional)</span></label>
              <input type="text" name="title" maxlength="150" placeholder="e.g. Location on Google Maps"></div>
            <div class="fld"><label>Google Maps link or embed code</label>
              <input type="text" name="location_url" value="{{ old('type') === 'location' ? old('location_url') : '' }}" placeholder="Paste the link or the Embed code" class="@error('location_url') has-err @enderror">
              @error('location_url')<span class="fld-err">{{ $message }}</span>@enderror
              <span class="fld-help">In Google Maps: Share &rarr; <strong>Embed a map</strong> &rarr; Copy HTML, then paste it here to show the live map on the page. A normal share link only shows an &ldquo;Open in Google Maps&rdquo; button.</span></div>
            <div class="form-actions"><button type="submit" class="btn-a btn-gold"><i class="fa-solid fa-plus"></i> Add location</button></div>
          </form>
        </div>
      </div>
    </div>
    <div class="card-a" style="margin-top:20px">
      <div class="card-a-body" style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;justify-content:space-between">
        <div class="media-add-head" style="margin:0;padding:0;border:0"><span class="media-add-ico"><i class="fa-solid fa-images"></i></span><div><h4>Photo gallery <span class="hint">({{ $galleryCount }} {{ \Illuminate\Support\Str::plural('photo', $galleryCount) }})</span></h4><p>Photos of work done are managed in the Gallery section</p></div></div>
        <a class="btn-a btn-gold" href="{{ route('admin.gallery.show', $project) }}"><i class="fa-solid fa-images"></i> Manage gallery</a>
      </div>
    </div>
  @endif
@endsection
