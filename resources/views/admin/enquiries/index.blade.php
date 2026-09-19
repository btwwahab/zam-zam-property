@extends('layouts.admin')
@section('title', 'Enquiries')

@section('content')
  <div class="section-head">
    <div><h2>Enquiries</h2><p>Form submissions captured from the website.</p></div>
  </div>

  <div class="stat-grid" style="grid-template-columns:repeat(3,1fr)">
    <div class="stat-card"><span class="stat-ico tone-blue"><i class="fa-solid fa-envelope"></i></span><div class="stat-val">{{ $counts['new'] }}</div><div class="stat-lbl">New</div><div class="stat-delta">Awaiting first contact</div></div>
    <div class="stat-card"><span class="stat-ico tone-amber"><i class="fa-solid fa-headset"></i></span><div class="stat-val">{{ $counts['contacted'] }}</div><div class="stat-lbl">Contacted</div><div class="stat-delta">Follow-up in progress</div></div>
    <div class="stat-card"><span class="stat-ico tone-green"><i class="fa-solid fa-circle-check"></i></span><div class="stat-val">{{ $counts['closed'] }}</div><div class="stat-lbl">Closed</div><div class="stat-delta">Resolved or archived</div></div>
  </div>

  <form method="GET" class="toolbar">
    <div class="search"><i class="fa-solid fa-magnifying-glass"></i><input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search name or phone…"></div>
    <select name="status" onchange="this.form.submit()">
      <option value="">All statuses</option>
      @foreach(['new','contacted','closed'] as $s)<option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst($s) }}</option>@endforeach
    </select>
    <button class="btn-a btn-ghost btn-sm" type="submit"><i class="fa-solid fa-filter"></i> Apply</button>
    <a class="btn-a btn-ghost btn-sm" href="{{ route('admin.enquiries.index') }}">Clear</a>
  </form>

  <div class="card-a"><div class="table-wrap"><table class="table-a">
    <thead><tr><th>Date</th><th>Name</th><th>Phone</th><th>Interest</th><th>Property</th><th>Message</th><th>Status</th><th></th></tr></thead>
    <tbody>
      @forelse($enquiries as $e)
        <tr>
          <td>{{ $e->created_at->format('Y-m-d') }}</td>
          <td><strong>{{ $e->name }}</strong>@if($e->email)<br><span class="ref-pill">{{ $e->email }}</span>@endif</td>
          <td><span class="ref-pill">{{ $e->phone }}</span></td>
          <td>{{ $e->interest ?: '—' }}</td>
          <td><span class="ref-pill">{{ $e->property_ref ?: ($e->society ?: '—') }}</span></td>
          <td style="max-width:280px;color:var(--text-muted)">{{ $e->message }}</td>
          <td><span class="badge-a badge-{{ $e->status }}">{{ ucfirst($e->status) }}</span></td>
          <td><div class="row-actions">
            <form method="POST" action="{{ route('admin.enquiries.update', $e) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="contacted"><button title="Mark contacted"><i class="fa-solid fa-headset"></i></button></form>
            <form method="POST" action="{{ route('admin.enquiries.update', $e) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="closed"><button title="Close"><i class="fa-solid fa-check"></i></button></form>
            <form method="POST" action="{{ route('admin.enquiries.destroy', $e) }}" data-confirm="Delete this enquiry?">@csrf @method('DELETE')<button class="danger" title="Delete"><i class="fa-solid fa-trash"></i></button></form>
          </div></td>
        </tr>
      @empty
        <tr><td colspan="8" style="color:var(--text-muted);padding:24px 16px">No enquiries yet.</td></tr>
      @endforelse
    </tbody>
  </table></div>
  <div style="padding:14px 16px">{{ $enquiries->links() }}</div>
  </div>
@endsection
