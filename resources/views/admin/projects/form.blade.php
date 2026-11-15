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
    @php $allMedia = $project->media; $media = $allMedia->where('type', '!=', 'gallery'); $gallery = $allMedia->where('type', 'gallery'); @endphp
    <div class="card-a" style="margin-top:20px" id="media">
      <div class="card-a-head">
        <h3>Videos, Maps &amp; Gallery</h3>
        <span class="head-note">Videos and maps show on the public Projects page. The photo gallery has its own Gallery page on the website. Add as many as you like.</span>
      </div>
      <div class="card-a-body">

        @if($media->count())
          <div class="table-wrap media-list"><table class="table-a">
            <thead><tr><th>Type</th><th>Title</th><th>Source</th><th></th></tr></thead>
            <tbody>
              @foreach($media as $m)
                <tr>
                  <td><span class="badge-a {{ $m->type === 'video' ? 'badge-new' : 'badge-featured' }}">{{ ['video' => 'Video', 'layout' => 'Map image', 'location' => 'Location map'][$m->type] }}</span></td>
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

        {{-- gallery: photos of work done --}}
        <div class="gal-admin" id="gallery">
          <div class="gal-admin-head">
            <div class="media-add-head" style="margin:0"><span class="media-add-ico"><i class="fa-solid fa-images"></i></span><div><h4>Photo gallery <span class="hint">({{ $gallery->count() }})</span></h4><p>Photos of work done on this project &mdash; shown to visitors as a gallery</p></div></div>
          </div>

          @if($gallery->count())
            <div class="gal-admin-grid">
              @foreach($gallery as $g)
                <div class="gal-admin-item">
                  <img src="/assets/gallery/{{ is_file(public_path('assets/gallery/' . $g->gallery_thumb)) ? $g->gallery_thumb : $g->file }}" alt="{{ $g->title ?: 'Gallery photo' }}" loading="lazy">
                  <form method="POST" action="{{ route('admin.projects.media.destroy', $g) }}" data-confirm="Remove this photo from the gallery?">
                    @csrf @method('DELETE')
                    <button class="gal-del" title="Remove photo"><i class="fa-solid fa-xmark"></i></button>
                  </form>
                </div>
              @endforeach
            </div>
          @endif

          <form class="form-a media-add gal-add" method="POST" action="{{ route('admin.projects.media.store', $project) }}" enctype="multipart/form-data" data-gallery-form>
            @csrf <input type="hidden" name="type" value="gallery">
            <div class="fld"><label>Add photos</label>
              <div class="upload-drop is-compact">
                <input type="file" name="gallery_files[]" accept="image/*" multiple>
                <div class="u-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                <div class="u-main" data-drop-label>Drag &amp; drop photos, or click to choose (you can pick many)</div>
                <div class="u-hints"><span>JPG</span><span>PNG</span><span>WEBP</span><span>UP TO 20 AT A TIME</span></div>
              </div>
              @error('gallery_files')<span class="fld-err">{{ $message }}</span>@enderror
              @error('gallery_files.*')<span class="fld-err">{{ $message }}</span>@enderror
              <span class="fld-help" data-gallery-status>Photos are resized automatically, so big phone photos are fine.</span></div>
            <div class="fld"><label>Caption <span class="hint">(optional &mdash; only used when adding a single photo)</span></label>
              <input type="text" name="title" maxlength="150" placeholder="e.g. Main boulevard completed"></div>
            <div class="form-actions"><button type="submit" class="btn-a btn-gold"><i class="fa-solid fa-plus"></i> Add to gallery</button></div>
          </form>
        </div>

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
            <div class="media-add-head"><span class="media-add-ico"><i class="fa-solid fa-map"></i></span><div><h4>Add a map image</h4><p>Master plan or layout plan</p></div></div>
            <div class="fld"><label>Title <span class="hint">(optional)</span></label>
              <input type="text" name="title" maxlength="150" placeholder="e.g. Master plan"></div>
            <div class="fld"><label>Map image</label>
              <div class="upload-drop is-compact">
                <input type="file" name="layout_file" accept="image/*">
                <div class="u-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                <div class="u-main" data-drop-label>Drag &amp; drop a map, or click to choose</div>
                <div class="u-hints"><span>JPG</span><span>PNG</span><span>WEBP</span><span>MAX 12 MB</span></div>
              </div>
              @error('layout_file')<span class="fld-err">{{ $message }}</span>@enderror
              <span class="fld-help">Visitors can click the map to zoom. Use the sharpest image you have.</span></div>
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
  @endif
@endsection

@push('scripts')
<script>
// Shrink big photos in the browser before they upload (phone photos are 5-10 MB; the site never shows them bigger than ~1800px)
(function () {
  var form = document.querySelector('[data-gallery-form]'); if (!form) return;
  var input = form.querySelector('input[type=file]'), status = form.querySelector('[data-gallery-status]');
  var base = status ? status.textContent : '';
  function shrink(file) {
    if (!/^image\/(jpeg|png|webp)$/.test(file.type) || file.size < 400 * 1024) return Promise.resolve(file);
    return createImageBitmap(file).then(function (bmp) {
      var r = Math.min(1, 1800 / Math.max(bmp.width, bmp.height)), c = document.createElement('canvas');
      c.width = Math.round(bmp.width * r); c.height = Math.round(bmp.height * r);
      c.getContext('2d').drawImage(bmp, 0, 0, c.width, c.height);
      return new Promise(function (res) {
        c.toBlob(function (b) { res(b && b.size < file.size ? new File([b], file.name.replace(/\.\w+$/, '') + '.webp', { type: 'image/webp' }) : file); }, 'image/webp', 0.82);
      });
    }).catch(function () { return file; });
  }
  input.addEventListener('change', function () {
    var files = Array.prototype.slice.call(input.files); if (!files.length) return;
    if (status) status.textContent = 'Preparing ' + files.length + ' photo' + (files.length > 1 ? 's' : '') + '…';
    Promise.all(files.map(shrink)).then(function (out) {
      try { var dt = new DataTransfer(); out.forEach(function (f) { dt.items.add(f); }); input.files = dt.files; } catch (e) {}
      if (status) status.textContent = out.length + ' photo' + (out.length > 1 ? 's' : '') + ' ready to add.';
    });
  });
})();
</script>
@endpush
