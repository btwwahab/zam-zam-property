@extends('layouts.admin')
@section('title', 'Site Settings')

@section('content')
  <div class="section-head"><div><h2>Site Settings</h2><p>Contact details, and the homepage hero.</p></div></div>

  <div class="card-a"><div class="card-a-head"><h3>Business &amp; contact</h3></div><div class="card-a-body">
    <form class="form-a" id="settingsForm" method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
      @csrf @method('PUT')
      <div class="fld"><label>Business name<span class="req-star">*</span></label>
        <input type="text" name="name" value="{{ old('name', $setting->name) }}" required maxlength="150" class="@error('name') has-err @enderror">
        @error('name')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>Legal / trading name</label>
        <input type="text" name="legal_name" value="{{ old('legal_name', $setting->legal_name) }}" maxlength="150" class="@error('legal_name') has-err @enderror">
        @error('legal_name')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld col-span-2"><label>Tagline</label>
        <input type="text" name="tagline" value="{{ old('tagline', $setting->tagline) }}" maxlength="190" class="@error('tagline') has-err @enderror">
        @error('tagline')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>Phone (display)</label>
        <input type="tel" name="phone" value="{{ old('phone', $setting->phone) }}" maxlength="60" class="@error('phone') has-err @enderror">
        @error('phone')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>Phone (raw / tel:)</label>
        <input type="tel" name="phone_raw" value="{{ old('phone_raw', $setting->phone_raw) }}" maxlength="40" class="@error('phone_raw') has-err @enderror">
        @error('phone_raw')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>WhatsApp number</label>
        <input type="tel" name="whatsapp" value="{{ old('whatsapp', $setting->whatsapp) }}" maxlength="40" class="@error('whatsapp') has-err @enderror">
        @error('whatsapp')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>Office hours</label>
        <input type="text" name="office_hours" value="{{ old('office_hours', $setting->office_hours) }}" maxlength="120" class="@error('office_hours') has-err @enderror">
        @error('office_hours')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld col-span-2"><label>Address (full)</label>
        <textarea name="address" rows="2" maxlength="500" class="@error('address') has-err @enderror">{{ old('address', $setting->address) }}</textarea>
        @error('address')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>Address (short)</label>
        <input type="text" name="address_short" value="{{ old('address_short', $setting->address_short) }}" maxlength="190" class="@error('address_short') has-err @enderror">
        @error('address_short')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>Facebook URL</label>
        <input type="url" name="facebook" value="{{ old('facebook', $setting->facebook) }}" placeholder="https://facebook.com/…" maxlength="255" class="@error('facebook') has-err @enderror">
        @error('facebook')<span class="fld-err">{{ $message }}</span>@enderror</div>
      <div class="fld"><label>Instagram URL</label>
        <input type="url" name="instagram" value="{{ old('instagram', $setting->instagram) }}" placeholder="https://instagram.com/…" maxlength="255" class="@error('instagram') has-err @enderror">
        @error('instagram')<span class="fld-err">{{ $message }}</span>@enderror</div>

      <div class="fld col-span-2" style="border-top:1px solid var(--border-light);padding-top:14px;margin-top:6px">
        <label style="font-size:14px">Homepage hero</label>
      </div>

      {{-- CTA plumbing kept so it isn't wiped on save --}}
      <input type="hidden" name="hero_poster" value="{{ $setting->hero_poster }}">
      <input type="hidden" name="hero_cta_primary_link" value="{{ $setting->hero_cta_primary_link ?: '/properties.html' }}">
      <input type="hidden" name="hero_cta_secondary_link" value="{{ $setting->hero_cta_secondary_link ?: '/contact.html' }}">
      <input type="hidden" name="hero_cta_primary_text" value="{{ $setting->hero_cta_primary_text ?: 'Explore Properties' }}">
      <input type="hidden" name="hero_cta_secondary_text" value="{{ $setting->hero_cta_secondary_text ?: 'Contact Us' }}">

      @php($ht = old('hero_type', $setting->hero_type ?: 'slider'))

      @if($heroSwitchable)
        <div class="fld col-span-2">
          <label>Hero style</label>
          <div class="hero-type-pick" id="heroTypePick">
            <label class="htp-opt @if($ht==='slider') is-on @endif"><input type="radio" name="hero_type" value="slider" @checked($ht==='slider')><i class="fa-solid fa-images"></i> Image slider</label>
            <label class="htp-opt @if($ht==='video') is-on @endif"><input type="radio" name="hero_type" value="video" @checked($ht==='video')><i class="fa-solid fa-film"></i> Background video</label>
          </div>
          <p class="pg-hint" style="margin:6px 0 0;font-size:11.5px;color:var(--text-muted)">Pick one — whichever you choose shows on the homepage after you save.</p>
          @error('hero_type')<span class="fld-err">{{ $message }}</span>@enderror
        </div>
      @else
        <input type="hidden" name="hero_type" value="{{ $setting->hero_type }}">
      @endif

      {{-- VIDEO panel --}}
      @if($heroSwitchable || $setting->hero_type === 'video')
      <div class="fld col-span-2 hero-panel" data-hero-panel="video" @if($ht !== 'video') hidden @endif>
        <label>Hero video</label>
        @if($setting->hero_video)
          <div class="u-current"><i class="fa-solid fa-film"></i> Currently playing: <span class="u-name">{{ $setting->hero_video }}</span></div>
        @endif
        <div class="upload-drop">
          <input type="file" name="hero_video_file" class="hero-upload-input" data-kind="video" accept="video/mp4,video/webm">
          <div class="u-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
          <div class="u-main" data-drop-label>Drag &amp; drop a video, or click to choose</div>
          <div class="u-sub">Replaces the homepage background video</div>
          <div class="u-hints"><span>MP4</span><span>WEBM</span><span>MAX 60 MB</span></div>
        </div>
        @error('hero_video_file')<span class="fld-err">{{ $message }}</span>@enderror
        <input type="hidden" name="hero_video" value="{{ $setting->hero_video }}">
      </div>
      @endif

      {{-- SLIDER panel (note) --}}
      @if($heroSwitchable || $setting->hero_type === 'slider')
      <div class="fld col-span-2 hero-panel" data-hero-panel="slider" @if($ht !== 'slider') hidden @endif>
        <span class="hint"><i class="fa-solid fa-circle-info"></i> The hero cycles through the images in the <strong>Hero slider images</strong> box below.</span>
      </div>
      @endif

      {{-- IMAGE panel (legacy single-image mode) --}}
      @if(! $heroSwitchable && $setting->hero_type === 'image')
        <div class="fld col-span-2">
          <label>Hero image</label>
          @if($setting->hero_poster)
            <div class="u-current"><i class="fa-solid fa-image"></i> Current: <span class="u-name">{{ $setting->hero_poster }}</span></div>
          @endif
          <div class="upload-drop">
            <input type="file" name="hero_poster_file" class="hero-upload-input" data-kind="image" accept="image/*">
            <div class="u-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
            <div class="u-main" data-drop-label>Drag &amp; drop an image, or click to choose</div>
            <div class="u-hints"><span>JPG</span><span>PNG</span><span>WEBP</span></div>
          </div>
          @error('hero_poster_file')<span class="fld-err">{{ $message }}</span>@enderror
        </div>
      @endif

      <div class="fld"><label>Badge pill text</label><input type="text" name="hero_badge" value="{{ old('hero_badge', $setting->hero_badge) }}" placeholder="Multan, Punjab, Pakistan" maxlength="120"></div>
      <div class="fld"><label>Small note line</label><input type="text" name="hero_note" value="{{ old('hero_note', $setting->hero_note) }}" maxlength="255"></div>

      <div class="form-actions">
        <button type="submit" class="btn-a btn-gold"><i class="fa-solid fa-check"></i> Save settings</button>
      </div>
    </form>
  </div></div>

  @if($setting->hero_type === 'slider' || $heroSwitchable)
  @php($slideMax = 5)
  <div class="card-a" id="hsSliderCard" style="margin-top:18px" @if($ht !== 'slider') hidden @endif><div class="card-a-head"><h3>Hero slider images</h3><span class="hint" id="hsCount">{{ $heroSlides->count() }} / {{ $slideMax }}</span></div><div class="card-a-body">
    <p class="hint" style="margin:0 0 10px">Drag a row, or use the arrows, to set the order slides appear in the hero.</p>
    <div class="table-wrap"><table class="table-a">
      <thead><tr><th style="width:34px"></th><th></th><th>Image</th><th>Order</th><th></th></tr></thead>
      <tbody id="hsRows" data-reorder-endpoint="{{ route('admin.hero.slide.reorder') }}">
        @forelse($heroSlides as $s)
          <tr data-id="{{ $s->id }}" draggable="true">
            <td class="hs-grip" title="Drag to reorder"><i class="fa-solid fa-grip-vertical"></i></td>
            <td><img class="thumb" src="/assets/images/{{ $s->image }}.webp" onerror="this.style.visibility='hidden'" alt=""></td>
            <td><span class="ref-pill">{{ $s->image }}</span></td>
            <td>
              <span class="hs-ord">{{ $loop->iteration }}</span>
              <button type="button" class="im-btn hs-up" title="Move up"><i class="fa-solid fa-arrow-up"></i></button>
              <button type="button" class="im-btn hs-down" title="Move down"><i class="fa-solid fa-arrow-down"></i></button>
            </td>
            <td><form method="POST" action="{{ route('admin.hero.slide.remove', $s) }}" data-confirm="Remove this slide?">@csrf @method('DELETE')<button class="btn-a btn-danger-soft btn-sm"><i class="fa-solid fa-trash"></i> Remove</button></form></td>
          </tr>
        @empty
          <tr><td colspan="5" style="color:var(--text-muted)">No slides yet — drop images below.</td></tr>
        @endforelse
      </tbody>
    </table></div>

    <div class="upload-drop" id="hsDrop"
         data-endpoint="{{ route('admin.hero.slide.add') }}"
         data-count="{{ $heroSlides->count() }}" data-max="{{ $slideMax }}" style="margin-top:16px">
      <input type="file" id="hsFile" accept="image/*" multiple>
      <div class="u-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
      <div class="u-main" id="hsLabel">Drag &amp; drop images here — they upload automatically</div>
      <div class="u-sub">Up to {{ $slideMax }} slides. No save button — each image is added as soon as it uploads.</div>
      <div class="u-hints"><span>JPG</span><span>PNG</span><span>WEBP</span><span>MAX 8 MB</span></div>
    </div>
    <div class="hs-queue" id="hsQueue"></div>
  </div></div>
  @endif

  <div class="up-overlay" id="upOverlay" hidden>
    <div class="up-box">
      <div class="up-spin" id="upSpin" aria-hidden="true"></div>
      <div class="up-err" id="upErr" hidden aria-hidden="true"><i class="fa-solid fa-circle-exclamation"></i></div>
      <div class="up-title" id="upTitle">Uploading…</div>
      <div class="up-bar" id="upBar"><div class="up-bar-fill" id="upBarFill"></div></div>
      <div class="up-row" id="upRow"><span class="up-pct" id="upPct">0%</span><span class="up-status" id="upStatus">Please keep this tab open</span></div>
      <p class="up-msg" id="upMsg" hidden></p>
      <button type="button" class="btn-a btn-ghost" id="upClose" hidden>Close</button>
    </div>
  </div>
@endsection

@push('scripts')
<script>
/* ---- Hero style toggle (slider <-> video): show matching panel, disable hidden inputs ---- */
(function () {
  var pick = document.getElementById('heroTypePick');
  if (!pick) return;
  var panels = document.querySelectorAll('.hero-panel');
  var sliderCard = document.getElementById('hsSliderCard');

  function apply(val) {
    panels.forEach(function (pl) {
      var on = pl.getAttribute('data-hero-panel') === val;
      pl.hidden = !on;
      pl.querySelectorAll('input, select, textarea, button').forEach(function (el) { el.disabled = !on; });
    });
    if (sliderCard) sliderCard.hidden = (val !== 'slider');
    pick.querySelectorAll('.htp-opt').forEach(function (o) {
      o.classList.toggle('is-on', o.querySelector('input').checked);
    });
  }
  pick.addEventListener('change', function (e) {
    if (e.target.name === 'hero_type') apply(e.target.value);
  });
  var checked = pick.querySelector('input:checked');
  apply(checked ? checked.value : 'slider');
})();
</script>
<script>
(function () {
  var form = document.getElementById('settingsForm');
  var overlay = document.getElementById('upOverlay');
  if (!form || !overlay) return;

  var spin = document.getElementById('upSpin');
  var errIco = document.getElementById('upErr');
  var bar = document.getElementById('upBar');
  var fill = document.getElementById('upBarFill');
  var row = document.getElementById('upRow');
  var pct = document.getElementById('upPct');
  var status = document.getElementById('upStatus');
  var title = document.getElementById('upTitle');
  var msg = document.getElementById('upMsg');
  var closeBtn = document.getElementById('upClose');

  function close() {
    overlay.hidden = true;
    document.body.style.overflow = '';
  }
  closeBtn.addEventListener('click', close);

  function showError(text) {
    spin.hidden = true;
    bar.hidden = true;
    row.hidden = true;
    errIco.hidden = false;
    title.textContent = 'Upload failed';
    title.classList.add('is-err');
    msg.textContent = text;
    msg.hidden = false;
    closeBtn.hidden = false;
  }

  form.addEventListener('submit', function (e) {
    var picked = null;
    form.querySelectorAll('.hero-upload-input').forEach(function (inp) {
      if (inp.files && inp.files.length) picked = inp;
    });
    if (!picked) return; // text-only save -> normal browser submit

    e.preventDefault();

    var kind = picked.dataset.kind === 'video' ? 'video' : 'image';
    var sizeMB = (picked.files[0].size / 1048576).toFixed(1);

    // reset overlay to "uploading" state
    overlay.hidden = false;
    document.body.style.overflow = 'hidden';
    spin.hidden = false; errIco.hidden = true;
    bar.hidden = false; row.hidden = false;
    msg.hidden = true; closeBtn.hidden = true;
    title.classList.remove('is-err');
    fill.style.width = '0%';
    pct.textContent = '0%';
    title.textContent = 'Uploading ' + kind + ' (' + sizeMB + ' MB)…';
    status.textContent = 'Please keep this tab open';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', form.action, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    xhr.upload.addEventListener('progress', function (ev) {
      if (!ev.lengthComputable) return;
      var p = Math.round(ev.loaded / ev.total * 100);
      fill.style.width = p + '%';
      pct.textContent = p + '%';
      if (p >= 100) {
        title.textContent = 'Upload complete';
        status.textContent = 'Saving changes…';
      }
    });

    xhr.addEventListener('load', function () {
      if (xhr.status >= 200 && xhr.status < 400) {
        fill.style.width = '100%';
        pct.textContent = '100%';
        title.textContent = 'Saved';
        status.textContent = 'Done — reloading…';
        window.location.href = xhr.responseURL || form.action;
      } else if (xhr.status === 413) {
        showError('This file is too large for the server. The maximum size is 60 MB. Try a shorter or compressed clip, then upload again.');
      } else if (xhr.status === 422) {
        showError('The server rejected this file. Make sure it is an MP4 or WEBM video and under 60 MB.');
      } else {
        showError('Something went wrong (HTTP ' + xhr.status + '). Please try again.');
      }
    });

    xhr.addEventListener('error', function () {
      showError('Network error during upload. Check your connection and try again.');
    });
    xhr.addEventListener('timeout', function () {
      showError('The upload timed out. Try a smaller file.');
    });

    xhr.send(new FormData(form));
  });
})();
</script>
<script>
/* ---- Hero slider: instant multi-upload with per-file progress ---- */
(function () {
  var drop = document.getElementById('hsDrop');
  if (!drop) return;
  var input = document.getElementById('hsFile');
  var label = document.getElementById('hsLabel');
  var queue = document.getElementById('hsQueue');
  var countEl = document.getElementById('hsCount');
  var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  var endpoint = drop.dataset.endpoint;
  var max = parseInt(drop.dataset.max, 10) || 5;
  var count = parseInt(drop.dataset.count, 10) || 0;
  var inFlight = 0;
  var done = 0;

  function slotsLeft() { return max - count - inFlight; }

  function refresh() {
    if (countEl) countEl.textContent = (count + inFlight) + ' / ' + max;
    var full = slotsLeft() <= 0;
    drop.classList.toggle('is-disabled', full);
    input.disabled = full;
    label.textContent = full
      ? 'Maximum ' + max + ' slides reached — remove one to add another'
      : 'Drag & drop images here — they upload automatically';
  }

  function row(name) {
    var el = document.createElement('div');
    el.className = 'hs-item';
    el.innerHTML = '<span class="hs-name"></span><span class="hs-bar"><span class="hs-bar-fill"></span></span><span class="hs-stat">0%</span>';
    el.querySelector('.hs-name').textContent = name;
    queue.appendChild(el);
    return el;
  }

  function upload(file) {
    inFlight++;
    refresh();
    var el = row(file.name);
    var fill = el.querySelector('.hs-bar-fill');
    var stat = el.querySelector('.hs-stat');

    var fd = new FormData();
    fd.append('_token', token);
    fd.append('image_file', file);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', endpoint, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('Accept', 'application/json');

    xhr.upload.addEventListener('progress', function (ev) {
      if (!ev.lengthComputable) return;
      var p = Math.round(ev.loaded / ev.total * 100);
      fill.style.width = p + '%';
      stat.textContent = p < 100 ? p + '%' : 'Saving…';
    });

    xhr.addEventListener('load', function () {
      inFlight--;
      if (xhr.status >= 200 && xhr.status < 300) {
        count++;
        done++;
        fill.style.width = '100%';
        el.classList.add('ok');
        stat.innerHTML = '<i class="fa-solid fa-check"></i>';
      } else {
        el.classList.add('err');
        var msg = 'Failed';
        try { msg = JSON.parse(xhr.responseText).message || msg; } catch (e) {}
        stat.textContent = msg;
      }
      refresh();
      if (inFlight === 0 && done > 0) {
        setTimeout(function () { window.location.reload(); }, 700);
      }
    });

    xhr.addEventListener('error', function () {
      inFlight--;
      el.classList.add('err');
      stat.textContent = 'Network error';
      refresh();
    });

    xhr.send(fd);
  }

  function handle(files) {
    var imgs = [].filter.call(files, function (f) { return /^image\//.test(f.type); });
    if (!imgs.length) return;
    var left = slotsLeft();
    if (left <= 0) { refresh(); return; }
    if (imgs.length > left) {
      imgs = imgs.slice(0, left);
      var note = document.createElement('div');
      note.className = 'hs-note';
      note.textContent = 'Only ' + left + ' more slide' + (left === 1 ? '' : 's') + ' allowed — extra files were skipped.';
      queue.appendChild(note);
    }
    imgs.forEach(upload);
  }

  // drag/drop is wired globally for .upload-drop (it sets input.files + fires change)
  input.addEventListener('change', function () {
    if (input.files && input.files.length) handle(input.files);
    input.value = '';
  });

  refresh();
})();

/* ---- Hero slider: reorder (drag rows + up/down arrows) ---------- */
(function () {
  var body = document.getElementById('hsRows');
  if (!body) return;
  var endpoint = body.dataset.reorderEndpoint;
  var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  var saveTimer = null;

  function rows() { return [].slice.call(body.querySelectorAll('tr[data-id]')); }

  function renumber() {
    rows().forEach(function (tr, i) {
      var o = tr.querySelector('.hs-ord');
      if (o) o.textContent = i + 1;
      var up = tr.querySelector('.hs-up'), dn = tr.querySelector('.hs-down');
      if (up) up.disabled = i === 0;
      if (dn) dn.disabled = i === rows().length - 1;
    });
  }

  function persist() {
    clearTimeout(saveTimer);
    saveTimer = setTimeout(function () {
      var order = rows().map(function (tr) { return parseInt(tr.dataset.id, 10); });
      var xhr = new XMLHttpRequest();
      xhr.open('POST', endpoint, true);
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr.setRequestHeader('X-CSRF-TOKEN', token);
      xhr.setRequestHeader('Content-Type', 'application/json');
      xhr.setRequestHeader('Accept', 'application/json');
      xhr.send(JSON.stringify({ order: order }));
    }, 400);
  }

  function move(tr, dir) {
    if (dir < 0 && tr.previousElementSibling) body.insertBefore(tr, tr.previousElementSibling);
    else if (dir > 0 && tr.nextElementSibling) body.insertBefore(tr.nextElementSibling, tr);
    else return;
    renumber();
    persist();
  }

  body.addEventListener('click', function (e) {
    var btn = e.target.closest('.hs-up, .hs-down');
    if (!btn) return;
    move(btn.closest('tr'), btn.classList.contains('hs-up') ? -1 : 1);
  });

  // native drag-and-drop reordering
  var dragging = null;
  body.addEventListener('dragstart', function (e) {
    var tr = e.target.closest('tr[data-id]');
    if (!tr) return;
    dragging = tr;
    tr.classList.add('is-dragging');
    e.dataTransfer.effectAllowed = 'move';
  });
  body.addEventListener('dragend', function () {
    if (dragging) dragging.classList.remove('is-dragging');
    dragging = null;
    renumber();
    persist();
  });
  body.addEventListener('dragover', function (e) {
    e.preventDefault();
    if (!dragging) return;
    var tr = e.target.closest('tr[data-id]');
    if (!tr || tr === dragging) return;
    var rect = tr.getBoundingClientRect();
    var after = (e.clientY - rect.top) / rect.height > 0.5;
    body.insertBefore(dragging, after ? tr.nextElementSibling : tr);
  });

  renumber();
})();
</script>
@endpush

