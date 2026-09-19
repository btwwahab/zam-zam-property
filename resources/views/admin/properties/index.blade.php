@extends('layouts.admin')
@section('title', 'Properties')

@section('content')
  <div class="section-head">
    <div><h2>Properties</h2><p>{{ $properties->total() }} listings.</p></div>
    <a class="btn-a btn-gold" href="{{ route('admin.properties.create') }}"><i class="fa-solid fa-plus"></i> Add Property</a>
  </div>

  <form method="GET" class="toolbar">
    <div class="search"><i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search ref or title…">
    </div>
    <select name="category" onchange="this.form.submit()">
      <option value="">All Categories</option>
      @foreach($categories as $c)<option value="{{ $c->slug }}" @selected(($filters['category'] ?? '') === $c->slug)>{{ $c->name }}</option>@endforeach
    </select>
    <select name="project" onchange="this.form.submit()">
      <option value="">All Projects</option>
      @foreach($projects as $p)<option value="{{ $p->slug }}" @selected(($filters['project'] ?? '') === $p->slug)>{{ $p->name }}</option>@endforeach
    </select>
    <select name="status" onchange="this.form.submit()">
      <option value="">Any Status</option>
      @foreach(['Featured','Verified','Hot Deal'] as $s)<option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ $s }}</option>@endforeach
    </select>
    <button class="btn-a btn-ghost btn-sm" type="submit"><i class="fa-solid fa-filter"></i> Apply</button>
    <a class="btn-a btn-ghost btn-sm" href="{{ route('admin.properties.index') }}">Clear</a>
  </form>

  <div class="card-a">
    <div class="table-wrap"><table class="table-a">
      <thead><tr><th></th><th>Ref</th><th>Title</th><th>Category</th><th>Project</th><th>Size</th><th>Price</th><th>Status</th><th>Featured</th><th></th></tr></thead>
      <tbody>
        @forelse($properties as $p)
          <tr>
            <td><img class="thumb" src="/assets/images/{{ $p->image }}.webp" alt="" onerror="this.style.visibility='hidden'"></td>
            <td><span class="ref-pill">{{ $p->ref }}</span></td>
            <td><strong>{{ $p->title }}</strong> @if($p->investment)<span class="badge-a badge-new">Investment</span>@endif</td>
            <td>{{ ucfirst($p->category_slug) }}</td>
            <td>{{ $p->project_name }}</td>
            <td>{{ $p->size }}</td>
            <td>{{ $p->price_formatted }}</td>
            <td><span class="badge-a badge-{{ $p->status_class }}">{{ $p->status }}</span></td>
            <td>@if($p->featured)<i class="fa-solid fa-circle-check" style="color:var(--emerald)"></i>@else<i class="fa-regular fa-circle" style="color:var(--text-faint)"></i>@endif</td>
            <td><div class="row-actions">
              <a class="btn-a btn-ghost" style="padding:6px 9px" href="{{ route('admin.properties.edit', $p->ref) }}" title="Edit"><i class="fa-solid fa-pen"></i></a>
              <form method="POST" action="{{ route('admin.properties.destroy', $p->ref) }}" data-confirm="Delete {{ $p->ref }}?">
                @csrf @method('DELETE')
                <button class="danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
              </form>
            </div></td>
          </tr>
        @empty
          <tr><td colspan="10" style="color:var(--text-muted);padding:24px 16px">No properties match your filters.</td></tr>
        @endforelse
      </tbody>
    </table></div>
    <div style="padding:14px 16px">{{ $properties->links() }}</div>
  </div>
@endsection
