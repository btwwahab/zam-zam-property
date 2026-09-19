@extends('layouts.admin')
@section('title', 'Categories')

@section('content')
  <div class="section-head">
    <div><h2>Categories</h2><p>Used by the property filters and the footer menu.</p></div>
    <a class="btn-a btn-gold" href="{{ route('admin.categories.create') }}"><i class="fa-solid fa-plus"></i> Add Category</a>
  </div>

  <div class="card-a"><div class="table-wrap"><table class="table-a">
    <thead><tr><th>Icon</th><th>Name</th><th>Slug</th><th>Description</th><th>Listings</th><th></th></tr></thead>
    <tbody>
      @foreach($categories as $c)
        <tr>
          <td><span class="stat-ico tone-gold" style="width:34px;height:34px;font-size:14px;margin:0"><i class="fa-solid {{ $c->icon }}"></i></span></td>
          <td><strong>{{ $c->name }}</strong></td>
          <td><span class="ref-pill">{{ $c->slug }}</span></td>
          <td style="color:var(--text-muted)">{{ $c->description }}</td>
          <td>{{ $c->property_count }}</td>
          <td><div class="row-actions">
            <a class="btn-a btn-ghost" style="padding:6px 9px" href="{{ route('admin.categories.edit', $c) }}"><i class="fa-solid fa-pen"></i></a>
            <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" data-confirm="Delete {{ $c->name }}?">@csrf @method('DELETE')<button class="danger"><i class="fa-solid fa-trash"></i></button></form>
          </div></td>
        </tr>
      @endforeach
    </tbody>
  </table></div></div>
@endsection
