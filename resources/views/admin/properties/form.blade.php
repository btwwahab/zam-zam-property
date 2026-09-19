@extends('layouts.admin')
@section('title', $property->exists ? 'Edit Property' : 'Add Property')

@section('content')
  <div class="section-head">
    <div>
      <div class="crumb"><a href="{{ route('admin.properties.index') }}" style="text-decoration:none;color:var(--text-faint)">Properties</a> / {{ $property->exists ? $property->ref : 'New' }}</div>
      <h2>{{ $property->exists ? 'Edit Property' : 'Add Property' }}</h2>
    </div>
    <a class="btn-a btn-ghost" href="{{ route('admin.properties.index') }}"><i class="fa-solid fa-arrow-left"></i> Back</a>
  </div>

  <div class="card-a"><div class="card-a-body">
    <form class="form-a" method="POST" action="{{ $property->exists ? route('admin.properties.update', $property->ref) : route('admin.properties.store') }}" enctype="multipart/form-data">
      @csrf
      @if($property->exists) @method('PUT') @endif

      <div class="fld"><label>Reference ID<span class="req-star">*</span></label>
        <input type="text" name="ref" value="{{ old('ref', $property->ref) }}" placeholder="e.g. HP-108" maxlength="40" {{ $property->exists ? 'readonly' : 'required' }} class="@error('ref') has-err @enderror">
        @error('ref')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>Title<span class="req-star">*</span></label>
        <input type="text" name="title" value="{{ old('title', $property->title) }}" placeholder="e.g. 8 Marla Residential Plot" required maxlength="190" class="@error('title') has-err @enderror">
        @error('title')<span class="fld-err">{{ $message }}</span>@enderror</div>

      <div class="fld"><label>Sub-type</label>
        <input type="text" name="sub_type" value="{{ old('sub_type', $property->sub_type) }}" placeholder="Residential Plot / Commercial Plot / Farmhouse Land" maxlength="120" class="@error('sub_type') has-err @enderror">
        @error('sub_type')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>Category<span class="req-star">*</span></label>
        <select name="category_slug" required class="@error('category_slug') has-err @enderror">
          @foreach($categories as $c)<option value="{{ $c->slug }}" @selected(old('category_slug', $property->category_slug) === $c->slug)>{{ $c->name }}</option>@endforeach
        </select>
        @error('category_slug')<span class="fld-err">{{ $message }}</span>@enderror</div>

      <div class="fld"><label>Project</label>
        <select name="project_slug" class="@error('project_slug') has-err @enderror">
          <option value="">— none —</option>
          @foreach($projects as $p)<option value="{{ $p->slug }}" @selected(old('project_slug', $property->project_slug) === $p->slug)>{{ $p->name }}</option>@endforeach
        </select>
        @error('project_slug')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>Purpose<span class="req-star">*</span></label>
        <select name="purpose" required class="@error('purpose') has-err @enderror">
          @foreach(['For Sale','For Rent'] as $x)<option @selected(old('purpose', $property->purpose ?: 'For Sale') === $x)>{{ $x }}</option>@endforeach
        </select>
        @error('purpose')<span class="fld-err">{{ $message }}</span>@enderror</div>

      <div class="fld"><label>Price (PKR)</label>
        <input type="number" name="price_pkr" value="{{ old('price_pkr', $property->price_pkr) }}" placeholder="4500000" min="0" step="1" class="@error('price_pkr') has-err @enderror">
        @error('price_pkr')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>Price (display)</label>
        <input type="text" name="price_formatted" value="{{ old('price_formatted', $property->price_formatted) }}" placeholder="PKR 45 Lakh" maxlength="60" class="@error('price_formatted') has-err @enderror">
        @error('price_formatted')<span class="fld-err">{{ $message }}</span>@enderror</div>

      <div class="fld"><label>Size</label>
        <input type="text" name="size" value="{{ old('size', $property->size) }}" placeholder="5 Marla" maxlength="60" class="@error('size') has-err @enderror">
        @error('size')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>Size (sq. yds)</label>
        <input type="number" name="size_yds" value="{{ old('size_yds', $property->size_yds) }}" placeholder="125" min="0" step="1" class="@error('size_yds') has-err @enderror">
        @error('size_yds')<span class="fld-err">{{ $message }}</span>@enderror</div>

      <div class="fld"><label>Status<span class="req-star">*</span></label>
        <select name="status" required class="@error('status') has-err @enderror">
          @foreach(['Featured','Verified','Hot Deal'] as $x)<option @selected(old('status', $property->status ?: 'Verified') === $x)>{{ $x }}</option>@endforeach
        </select>
        @error('status')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld check"><input type="checkbox" name="featured" value="1" id="f-featured" @checked(old('featured', $property->featured))><label for="f-featured">Show in homepage featured section</label></div>
      <div class="fld check"><input type="checkbox" name="investment" value="1" id="f-invest" @checked(old('investment', $property->investment))><label for="f-invest">Flag as investment opportunity</label></div>

      <div class="fld col-span-2"><label>Location</label>
        <input type="text" name="location" value="{{ old('location', $property->location) }}" placeholder="Royal Grace City, Southern Bypass, Multan" maxlength="190" class="@error('location') has-err @enderror">
        @error('location')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld col-span-2"><label>Address</label>
        <input type="text" name="address" value="{{ old('address', $property->address) }}" maxlength="255" class="@error('address') has-err @enderror">
        @error('address')<span class="fld-err">{{ $message }}</span>@enderror</div>

      @php
        $imgList = array_values(array_filter(array_unique(array_merge(
          $property->image ? [$property->image] : [],
          is_array($property->gallery) ? $property->gallery : []
        ))));
      @endphp
      <div class="fld col-span-2">
        <label>Property images</label>
        <p class="pg-hint" style="margin:2px 0 10px;font-size:11.5px;color:var(--text-muted)">Drag to reorder — the first image is the <strong>main photo</strong> used on cards and the detail page. Up to 10.</p>
        <div class="img-manager" id="imgManager" data-existing='@json($imgList)' data-assets='@json($imageAssets)'>
          <div class="im-list" id="imList"></div>
          <div class="upload-drop im-add" id="imDrop">
            <input type="file" accept="image/*" multiple id="imFileInput">
            <div class="u-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
            <div class="u-main" id="imDropLabel">Drag &amp; drop images, or click to add</div>
            <div class="u-hints"><span>JPG</span><span>PNG</span><span>WEBP</span><span id="imCount">0 / 10</span></div>
          </div>
        </div>
        <input type="hidden" name="image_order" id="imOrder">
        <input type="file" name="gallery_files[]" id="imHiddenFiles" multiple hidden>
      </div>

      <div class="fld col-span-2"><label>Description <span class="hint">(one paragraph per line)</span></label>
        <textarea name="description" rows="4">{{ old('description', is_array($property->description) ? implode("\n", $property->description) : '') }}</textarea></div>

      <div class="fld col-span-2"><label>Features <span class="hint">(comma separated)</span></label>
        <textarea name="features" rows="3">{{ old('features', is_array($property->features) ? implode(', ', $property->features) : '') }}</textarea></div>

      <div class="form-actions">
        <button type="submit" class="btn-a btn-gold"><i class="fa-solid fa-check"></i> {{ $property->exists ? 'Save changes' : 'Create property' }}</button>
        <a href="{{ route('admin.properties.index') }}" class="btn-a btn-ghost">Cancel</a>
        @if($property->exists)
          <button form="del-form" class="btn-a btn-danger-soft" style="margin-left:auto"><i class="fa-solid fa-trash"></i> Delete</button>
        @endif
      </div>
    </form>
    @if($property->exists)
      <form id="del-form" method="POST" action="{{ route('admin.properties.destroy', $property->ref) }}" data-confirm="Delete {{ $property->ref }}?">@csrf @method('DELETE')</form>
    @endif
  </div></div>
@endsection

@push('scripts')
<script>
(function () {
  var wrap = document.getElementById('imgManager');
  if (!wrap) return;
  var list      = document.getElementById('imList');
  var fileInput = document.getElementById('imFileInput');
  var drop      = document.getElementById('imDrop');
  var dropLabel = document.getElementById('imDropLabel');
  var counter   = document.getElementById('imCount');
  var orderFld  = document.getElementById('imOrder');
  var hiddenF   = document.getElementById('imHiddenFiles');
  var form      = wrap.closest('form');
  var MAX = 10;

  var items = [];   // { type:'existing', name } | { type:'new', file, url }
  try {
    (JSON.parse(wrap.dataset.existing || '[]') || []).forEach(function (n) {
      if (n) items.push({ type: 'existing', name: n });
    });
  } catch (e) {}

  function atMax() { return items.length >= MAX; }

  function render() {
    list.innerHTML = '';
    items.forEach(function (it, i) {
      var row = document.createElement('div');
      row.className = 'im-row';

      var thumb = document.createElement('div');
      thumb.className = 'im-thumb';
      var img = document.createElement('img');
      img.src = it.type === 'new' ? it.url : ('/assets/images/' + it.name + '.webp');
      img.onerror = function () {
        if (it.type !== 'new' && img.src.endsWith('.webp')) { img.src = '/assets/images/' + it.name + '.png'; }
        else { img.style.visibility = 'hidden'; }
      };
      thumb.appendChild(img);
      row.appendChild(thumb);

      var meta = document.createElement('div');
      meta.className = 'im-meta';
      var nm = document.createElement('div');
      nm.className = 'im-name';
      nm.textContent = it.type === 'new' ? it.file.name : it.name;
      meta.appendChild(nm);
      var tag = document.createElement('div');
      tag.className = 'im-sub';
      tag.textContent = i === 0 ? 'Main photo' : (it.type === 'new' ? 'New upload' : 'Existing image');
      meta.appendChild(tag);
      row.appendChild(meta);

      if (i === 0) {
        var badge = document.createElement('span');
        badge.className = 'im-main-badge';
        badge.textContent = 'MAIN';
        row.appendChild(badge);
      }

      var ctl = document.createElement('div');
      ctl.className = 'im-ctl';
      ctl.appendChild(btn('fa-arrow-up', 'Move up', i === 0, function () { move(i, -1); }));
      ctl.appendChild(btn('fa-arrow-down', 'Move down', i === items.length - 1, function () { move(i, 1); }));
      ctl.appendChild(btn('fa-xmark', 'Remove', false, function () { items.splice(i, 1); render(); }, 'im-del'));
      row.appendChild(ctl);

      list.appendChild(row);
    });

    counter.textContent = items.length + ' / ' + MAX;
    dropLabel.textContent = atMax()
      ? 'Maximum ' + MAX + ' images reached'
      : 'Drag & drop images, or click to add';
    drop.classList.toggle('is-disabled', atMax());
    fileInput.disabled = atMax();
  }

  function btn(icon, title, disabled, fn, extra) {
    var b = document.createElement('button');
    b.type = 'button';
    b.className = 'im-btn' + (extra ? ' ' + extra : '');
    b.title = title;
    b.disabled = !!disabled;
    b.innerHTML = '<i class="fa-solid ' + icon + '"></i>';
    if (!disabled) b.addEventListener('click', fn);
    return b;
  }

  function move(i, dir) {
    var j = i + dir;
    if (j < 0 || j >= items.length) return;
    var tmp = items[i]; items[i] = items[j]; items[j] = tmp;
    render();
  }

  function addFiles(fileList) {
    [].forEach.call(fileList, function (f) {
      if (atMax()) return;
      if (!/^image\//.test(f.type)) return;
      items.push({ type: 'new', file: f, url: URL.createObjectURL(f) });
    });
    render();
  }

  fileInput.addEventListener('change', function () {
    addFiles(fileInput.files);
    fileInput.value = '';
  });

  ['dragenter', 'dragover'].forEach(function (e) {
    drop.addEventListener(e, function (ev) { ev.preventDefault(); if (!atMax()) drop.classList.add('dragover'); });
  });
  ['dragleave', 'drop'].forEach(function (e) {
    drop.addEventListener(e, function (ev) { ev.preventDefault(); drop.classList.remove('dragover'); });
  });
  drop.addEventListener('drop', function (ev) {
    if (ev.dataTransfer.files && ev.dataTransfer.files.length) addFiles(ev.dataTransfer.files);
  });

  form.addEventListener('submit', function () {
    var tokens = [];
    var dt = new DataTransfer();
    var n = 0;
    items.forEach(function (it) {
      if (it.type === 'new') {
        tokens.push('__upload_' + n);
        dt.items.add(it.file);
        n++;
      } else {
        tokens.push(it.name);
      }
    });
    orderFld.value = JSON.stringify(tokens);
    hiddenF.files = dt.files;
  });

  render();
})();
</script>
@endpush
