@props([
  'name' => 'image_file',
  'selectName' => 'image',
  'current' => null,
  'assets' => [],
  'label' => 'Image',
  'sub' => '',
  'multiple' => false,
])

<div class="fld col-span-2">
  <label>{{ $label }}</label>
  @if($current)
    <div class="u-current" style="display:flex;align-items:center;gap:10px">
      <img src="/assets/images/{{ $current }}.webp" alt="" style="width:44px;height:44px;border-radius:8px;object-fit:cover" onerror="this.style.display='none'">
      <span><i class="fa-solid fa-image"></i> Current image &mdash; upload a new one to replace it</span>
    </div>
  @endif

  <div class="upload-drop">
    <input type="file" name="{{ $multiple ? $name . '[]' : $name }}" accept="image/*" @if($multiple) multiple @endif>
    <div class="u-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
    <div class="u-main" data-drop-label>Drag &amp; drop {{ $multiple ? 'images' : 'an image' }}, or click to choose</div>
    @if($sub)<div class="u-sub">{{ $sub }}</div>@endif
    <div class="u-hints"><span>JPG</span><span>PNG</span><span>WEBP</span><span>MAX 8 MB</span></div>
  </div>
</div>
