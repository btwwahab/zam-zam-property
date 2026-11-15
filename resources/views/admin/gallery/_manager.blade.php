{{-- Photo gallery manager for one project. Expects $project and $gallery (the project's gallery media rows). --}}
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
