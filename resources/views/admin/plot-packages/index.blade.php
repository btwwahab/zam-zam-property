@extends('layouts.admin')
@section('title', 'Plot Packages')

@section('content')
  <div class="section-head">
    <div><h2>Plot Packages</h2><p>Cash &amp; installment pricing shown on the homepage &amp; Projects page.</p></div>
    <a class="btn-a btn-gold" href="{{ route('admin.plot-packages.create') }}"><i class="fa-solid fa-plus"></i> Add Package</a>
  </div>

  <div class="card-a"><div class="table-wrap"><table class="table-a">
    <thead><tr><th>Project</th><th>Label</th><th>Size</th><th>Cash / Marla</th><th>Installment / Marla</th><th>Monthly</th><th>Featured</th><th></th></tr></thead>
    <tbody>
      @forelse($packages as $p)
        <tr>
          <td>{{ $p->project->name ?? $p->project_slug }}</td>
          <td><strong>{{ $p->label }}</strong></td>
          <td>{{ rtrim(rtrim(number_format($p->size_kanal, 2), '0'), '.') }} Kanal <span style="color:var(--text-muted)">({{ number_format($p->total_marla) }} Marla)</span></td>
          <td>{{ number_format($p->price_cash_per_marla) }}</td>
          <td>{{ number_format($p->price_installment_per_marla) }}</td>
          <td>PKR {{ number_format($p->monthly_installment) }}<span style="color:var(--text-muted)"> /mo</span></td>
          <td>@if($p->is_featured)<span class="badge-featured">Featured</span>@endif</td>
          <td><div class="row-actions">
            <a class="btn-a btn-ghost" style="padding:6px 9px" href="{{ route('admin.plot-packages.edit', $p) }}"><i class="fa-solid fa-pen"></i></a>
            <form method="POST" action="{{ route('admin.plot-packages.destroy', $p) }}" data-confirm="Delete {{ $p->label }}?">@csrf @method('DELETE')<button class="danger"><i class="fa-solid fa-trash"></i></button></form>
          </div></td>
        </tr>
      @empty
        <tr><td colspan="8" style="color:var(--text-muted);text-align:center;padding:26px">No plot packages yet — add one to show pricing on the public site.</td></tr>
      @endforelse
    </tbody>
  </table></div></div>
@endsection
