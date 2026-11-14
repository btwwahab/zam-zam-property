{{-- One 16:9 video player (YouTube loads only on play; uploaded files use the native player). Expects $v. --}}
<div class="project-video-frame">
  @if($v->youtube_id)
    <button type="button" class="yt-lite" data-yt="{{ $v->youtube_id }}" aria-label="Play video{{ $v->title ? ': ' . $v->title : '' }}">
      <img src="https://i.ytimg.com/vi/{{ $v->youtube_id }}/hqdefault.jpg" alt="" loading="lazy" width="480" height="360">
      <span class="yt-lite-play"><i class="fa-solid fa-play" aria-hidden="true"></i></span>
    </button>
  @else
    <video controls preload="metadata" playsinline><source src="/assets/videos/{{ $v->file }}"></video>
  @endif
</div>
