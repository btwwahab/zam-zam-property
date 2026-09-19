@extends('layouts.admin')
@section('title', 'Projects')

@section('content')
  <div class="section-head">
    <div><h2>Projects</h2><p>Developments that group your listings.</p></div>
    <a class="btn-a btn-gold" href="{{ route('admin.projects.create') }}"><i class="fa-solid fa-plus"></i> Add Project</a>
  </div>

  <div class="card-a"><div class="table-wrap"><table class="table-a">
    <thead><tr><th>Name</th><th>Slug</th><th>Location</th><th>Listings</th><th>Status</th><th></th></tr></thead>
    <tbody>
      @foreach($projects as $p)
        <tr>
          <td><strong>{{ $p->name }}</strong><br><span style="color:var(--text-muted);font-size:12px">{{ $p->description }}</span></td>
          <td><span class="ref-pill">{{ $p->slug }}</span></td>
          <td>{{ $p->location }}</td>
          <td>{{ $p->property_count }}</td>
          <td><span class="badge-a badge-verified">{{ $p->status }}</span></td>
          <td><div class="row-actions">
            <a class="btn-a btn-ghost" style="padding:6px 9px" href="{{ route('admin.projects.edit', $p) }}"><i class="fa-solid fa-pen"></i></a>
            <form method="POST" action="{{ route('admin.projects.destroy', $p) }}" data-confirm="Delete {{ $p->name }}?">@csrf @method('DELETE')<button class="danger"><i class="fa-solid fa-trash"></i></button></form>
          </div></td>
        </tr>
      @endforeach
    </tbody>
  </table></div></div>
@endsection
