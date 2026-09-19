@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
  <div class="section-head">
    <div><h2>Dashboard</h2><p>Snapshot of listings, projects and enquiries.</p></div>
    <a class="btn-a btn-gold" href="{{ route('admin.properties.create') }}"><i class="fa-solid fa-plus"></i> Add Property</a>
  </div>

  <div class="stat-grid">
    <div class="stat-card"><span class="stat-ico tone-gold"><i class="fa-solid fa-building"></i></span><div class="stat-val">{{ $totalProperties }}</div><div class="stat-lbl">Total Properties</div><div class="stat-delta">Across {{ $projectsCount }} projects</div></div>
    <div class="stat-card"><span class="stat-ico tone-amber"><i class="fa-solid fa-star"></i></span><div class="stat-val">{{ $featuredCount }}</div><div class="stat-lbl">Featured Listings</div><div class="stat-delta">Shown on homepage</div></div>
    <div class="stat-card"><span class="stat-ico tone-blue"><i class="fa-solid fa-diagram-project"></i></span><div class="stat-val">{{ $projectsCount }}</div><div class="stat-lbl">Projects</div><div class="stat-delta">{{ $byProject->pluck('name')->join(', ') }}</div></div>
    <div class="stat-card"><span class="stat-ico tone-green"><i class="fa-solid fa-envelope"></i></span><div class="stat-val">{{ $newEnquiries }}</div><div class="stat-lbl">New Enquiries</div><div class="stat-delta"><span class="up">{{ $totalEnquiries }} total</span> logged</div></div>
  </div>

  <div class="grid-2">
    <div class="card-a">
      <div class="card-a-head"><h3>Recent Enquiries</h3><a class="btn-a btn-ghost btn-sm" href="{{ route('admin.enquiries.index') }}">View all</a></div>
      <div class="table-wrap"><table class="table-a">
        <thead><tr><th>Name</th><th>Interest</th><th>Property</th><th>Date</th><th>Status</th></tr></thead>
        <tbody>
          @forelse($recentEnquiries as $e)
            <tr>
              <td><strong>{{ $e->name }}</strong><br><span class="ref-pill">{{ $e->phone }}</span></td>
              <td>{{ $e->interest ?: '—' }}</td>
              <td><span class="ref-pill">{{ $e->property_ref ?: '—' }}</span></td>
              <td>{{ $e->created_at->format('Y-m-d') }}</td>
              <td><span class="badge-a badge-{{ $e->status }}">{{ ucfirst($e->status) }}</span></td>
            </tr>
          @empty
            <tr><td colspan="5" style="color:var(--text-muted)">No enquiries yet.</td></tr>
          @endforelse
        </tbody>
      </table></div>
    </div>

    <div class="card-a">
      <div class="card-a-head"><h3>Properties by Category</h3></div>
      <div class="card-a-body">
        @php($maxC = max(1, $byCategory->max('count')))
        @foreach($byCategory as $row)
          <div class="bar-row">
            <span class="bar-name">{{ $row['name'] }}</span>
            <span class="bar-track"><span class="bar-fill" style="width: {{ round($row['count'] / $maxC * 100) }}%"></span></span>
            <span class="bar-val">{{ $row['count'] }}</span>
          </div>
        @endforeach
        <hr style="border:none;border-top:1px solid var(--border-light);margin:18px 0 16px">
        <div style="font-size:12px;font-weight:700;color:var(--text-faint);letter-spacing:.6px;text-transform:uppercase;margin-bottom:12px">By Project</div>
        @php($maxP = max(1, $byProject->max('count')))
        @foreach($byProject as $row)
          <div class="bar-row">
            <span class="bar-name">{{ $row['name'] }}</span>
            <span class="bar-track"><span class="bar-fill" style="width: {{ round($row['count'] / $maxP * 100) }}%"></span></span>
            <span class="bar-val">{{ $row['count'] }}</span>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="card-a" style="margin-top:18px">
    <div class="card-a-head"><h3>Latest Properties</h3><a class="btn-a btn-ghost btn-sm" href="{{ route('admin.properties.index') }}">Manage</a></div>
    <div class="table-wrap"><table class="table-a">
      <thead><tr><th></th><th>Ref</th><th>Title</th><th>Category</th><th>Project</th><th>Price</th><th>Status</th></tr></thead>
      <tbody>
        @foreach($latestProperties as $p)
          <tr>
            <td><img class="thumb" src="/assets/images/{{ $p->image }}.webp" alt="" onerror="this.style.visibility='hidden'"></td>
            <td><span class="ref-pill">{{ $p->ref }}</span></td>
            <td><strong>{{ $p->title }}</strong></td>
            <td>{{ $p->category?->name ?? ucfirst($p->category_slug) }}</td>
            <td>{{ $p->project?->name ?? $p->project_name ?: '—' }}</td>
            <td>{{ $p->price_formatted }}</td>
            <td><span class="badge-a badge-{{ $p->status_class }}">{{ $p->status }}</span></td>
          </tr>
        @endforeach
      </tbody>
    </table></div>
  </div>
@endsection
