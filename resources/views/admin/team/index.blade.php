@extends('layouts.admin')
@section('title', 'Team')

@section('content')
  <div class="section-head">
    <div><h2>Team</h2><p>People shown on listings and the contact page.</p></div>
    <a class="btn-a btn-gold" href="{{ route('admin.team.create') }}"><i class="fa-solid fa-plus"></i> Add Member</a>
  </div>

  <div class="team-grid">
    @foreach($members as $m)
      @php($initials = \Illuminate\Support\Str::of(strtoupper(collect(explode(' ', $m->name))->map(fn($w) => $w[0] ?? '')->join('')))->substr(0, 2))
      <div class="team-card">
        <div class="t-avatar">
          @if($m->image)<img src="/assets/images/{{ $m->image }}.webp" alt="{{ $m->name }}" onerror="this.remove()">@endif
          <span class="t-avatar-fallback">{{ $initials }}</span>
        </div>
        <h4>{{ $m->name }}</h4>
        <div class="t-role">{{ $m->role }}</div>
        <div class="t-meta">
          <span><i class="fa-solid fa-phone" style="color:var(--primary-gold-dark)"></i> {{ $m->phone }}</span>
          <span><i class="fa-brands fa-whatsapp" style="color:var(--emerald)"></i> {{ $m->whatsapp }}</span>
        </div>
        <div class="row-actions" style="justify-content:center;margin-top:14px">
          <a class="btn-a btn-ghost" style="padding:6px 9px" href="{{ route('admin.team.edit', $m) }}"><i class="fa-solid fa-pen"></i></a>
          <form method="POST" action="{{ route('admin.team.destroy', $m) }}" data-confirm="Remove {{ $m->name }}?">@csrf @method('DELETE')<button class="danger"><i class="fa-solid fa-trash"></i></button></form>
        </div>
      </div>
    @endforeach
    <a class="team-card" href="{{ route('admin.team.create') }}" style="display:flex;flex-direction:column;align-items:center;justify-content:center;border-style:dashed;text-decoration:none;color:inherit">
      <span class="stat-ico tone-gold" style="margin:0 0 10px"><i class="fa-solid fa-plus"></i></span>
      <div style="font-weight:700;color:var(--text-muted)">Add team member</div>
    </a>
  </div>
@endsection
