@extends('layouts.admin')
@section('title', 'Page Text')

@section('content')
  <div class="section-head">
    <div><h2>Page Text</h2><p>Edit the wording on your public pages. Plain text only — the design stays the same.</p></div>
  </div>

  <div class="tabs" id="page-tabs">
    @foreach($groups as $g => $fields)
      <button type="button" class="{{ $loop->first ? 'active' : '' }}" data-tab="g{{ $loop->index }}">{{ $g }}</button>
    @endforeach
  </div>

  <form method="POST" action="{{ route('admin.pages.update') }}">
    @csrf @method('PUT')

    @foreach($groups as $g => $fields)
      <div class="card-a pg-pane" data-pane="g{{ $loop->index }}" @if(! $loop->first) hidden @endif style="margin-bottom:18px">
        <div class="card-a-head"><h3>{{ $g }}</h3></div>
        <div class="card-a-body pg-fields">
          @foreach($fields as $f)
            @php [$key, $label, $type, $hint] = $f; $val = $pages[$key] ?? ''; @endphp
            <div class="pg-field">
              <label>{{ $label }}</label>
              @if($hint)<p class="pg-hint">{{ $hint }}</p>@endif
              @if($type === 'text')
                <input type="text" name="pages[{{ $key }}]" value="{{ $val }}">
              @elseif($type === 'textarea-lg')
                <textarea name="pages[{{ $key }}]" rows="10">{{ $val }}</textarea>
              @else
                <textarea name="pages[{{ $key }}]" rows="3">{{ $val }}</textarea>
              @endif
            </div>
          @endforeach
        </div>
      </div>
    @endforeach

    <button type="submit" class="btn-a btn-gold"><i class="fa-solid fa-check"></i> Save page text</button>
  </form>

  <style>
    .pg-fields { display: flex; flex-direction: column; gap: 24px; }
    .pg-field { display: flex; flex-direction: column; }
    .pg-field label { display: block; font-size: 12.5px; font-weight: 700; color: var(--text-main); margin-bottom: 7px; }
    .pg-field .pg-hint { margin: -3px 0 8px; font-size: 11.5px; color: var(--text-muted); }
    .pg-field input, .pg-field textarea {
      width: 100%; padding: 10px 13px; border: 1px solid var(--border-light);
      border-radius: 9px; font-size: 13.5px; font-family: inherit; color: var(--text-main); background: #fff;
      line-height: 1.5;
    }
    .pg-field textarea { resize: vertical; }
    .pg-field input:focus, .pg-field textarea:focus {
      outline: none; border-color: var(--primary-gold); box-shadow: 0 0 0 3px rgba(212,175,55,.15);
    }
  </style>
@endsection
