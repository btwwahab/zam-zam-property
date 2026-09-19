@extends('layouts.admin')
@section('title', $package->exists ? 'Edit Plot Package' : 'Add Plot Package')

@section('content')
  <div class="section-head">
    <div><div class="crumb"><a href="{{ route('admin.plot-packages.index') }}" style="text-decoration:none;color:var(--text-faint)">Plot Packages</a> / {{ $package->exists ? $package->label : 'New' }}</div>
    <h2>{{ $package->exists ? 'Edit Plot Package' : 'Add Plot Package' }}</h2></div>
    <a class="btn-a btn-ghost" href="{{ route('admin.plot-packages.index') }}"><i class="fa-solid fa-arrow-left"></i> Back</a>
  </div>

  <div class="card-a"><div class="card-a-body">
    <form class="form-a" id="ppForm" method="POST" action="{{ $package->exists ? route('admin.plot-packages.update', $package) : route('admin.plot-packages.store') }}">
      @csrf
      @if($package->exists) @method('PUT') @endif

      <div class="fld"><label>Project<span class="req-star">*</span></label>
        <select name="project_slug" required class="@error('project_slug') has-err @enderror">
          <option value="">— choose —</option>
          @foreach($projects as $slug => $name)
            <option value="{{ $slug }}" @selected(old('project_slug', $package->project_slug) === $slug)>{{ $name }}</option>
          @endforeach
        </select>
        @error('project_slug')<span class="fld-err">{{ $message }}</span>@enderror</div>

      <div class="fld"><label>Package name<span class="req-star">*</span></label>
        <input type="text" name="label" value="{{ old('label', $package->label) }}" required maxlength="120" placeholder="e.g. 2 Kanal Farmhouse Plot" class="@error('label') has-err @enderror">
        @error('label')<span class="fld-err">{{ $message }}</span>@enderror</div>

      <div class="fld"><label>Plot size (Kanal)<span class="req-star">*</span></label>
        <input type="number" step="0.25" min="0.25" id="pp-size" name="size_kanal" value="{{ old('size_kanal', $package->size_kanal) }}" required class="@error('size_kanal') has-err @enderror">
        @error('size_kanal')<span class="fld-err">{{ $message }}</span>@enderror</div>

      <div class="fld"><label>Cash price — per Marla (PKR)<span class="req-star">*</span></label>
        <input type="number" step="1" min="1000" id="pp-cash" name="price_cash_per_marla" value="{{ old('price_cash_per_marla', $package->price_cash_per_marla) }}" required class="@error('price_cash_per_marla') has-err @enderror">
        @error('price_cash_per_marla')<span class="fld-err">{{ $message }}</span>@enderror</div>

      <div class="fld"><label>Installment price — per Marla (PKR)<span class="req-star">*</span></label>
        <input type="number" step="1" min="1000" id="pp-inst" name="price_installment_per_marla" value="{{ old('price_installment_per_marla', $package->price_installment_per_marla) }}" required class="@error('price_installment_per_marla') has-err @enderror">
        @error('price_installment_per_marla')<span class="fld-err">{{ $message }}</span>@enderror</div>

      <div class="fld"><label>Advance payment (%)</label>
        <input type="number" step="1" min="0" max="100" id="pp-adv" name="advance_percent" value="{{ old('advance_percent', $package->advance_percent ?? 25) }}" class="@error('advance_percent') has-err @enderror">
        @error('advance_percent')<span class="fld-err">{{ $message }}</span>@enderror</div>

      <div class="fld"><label>Installment tenure (years)</label>
        <input type="number" step="1" min="1" max="15" id="pp-years" name="tenure_years" value="{{ old('tenure_years', $package->tenure_years ?? 3) }}" class="@error('tenure_years') has-err @enderror">
        @error('tenure_years')<span class="fld-err">{{ $message }}</span>@enderror</div>

      <div class="fld check"><input type="checkbox" name="is_featured" value="1" id="f-featured" @checked(old('is_featured', $package->is_featured))><label for="f-featured">Show "Limited Time Offer" ribbon on this package</label></div>

      <div class="fld col-span-2" id="pp-preview" style="background:var(--bg-app);border-radius:var(--radius);padding:14px 16px;font-size:13.5px;line-height:1.9;color:var(--text-main)"></div>

      <div class="form-actions">
        <button type="submit" class="btn-a btn-gold"><i class="fa-solid fa-check"></i> Save package</button>
        <a href="{{ route('admin.plot-packages.index') }}" class="btn-a btn-ghost">Cancel</a>
      </div>
    </form>
  </div></div>
@endsection

@push('scripts')
<script>
(function () {
  var size = document.getElementById('pp-size'),
      cash = document.getElementById('pp-cash'),
      inst = document.getElementById('pp-inst'),
      adv  = document.getElementById('pp-adv'),
      years = document.getElementById('pp-years'),
      out  = document.getElementById('pp-preview');
  if (!size || !out) return;

  function money(n) {
    n = Math.round(n || 0);
    if (n >= 10000000) return 'PKR ' + (Math.round(n / 100000) / 100).toString().replace(/\.00$/, '') + ' Crore';
    if (n >= 100000) return 'PKR ' + (Math.round(n / 1000) / 100).toString().replace(/\.00$/, '') + ' Lakh';
    return 'PKR ' + n.toLocaleString('en-PK');
  }

  function recalc() {
    var s = parseFloat(size.value) || 0;
    var marla = s * 20;
    var cashRate = parseFloat(cash.value) || 0;
    var instRate = parseFloat(inst.value) || 0;
    var advPct = parseFloat(adv.value) || 0;
    var yrs = parseFloat(years.value) || 0;
    var cashTotal = marla * cashRate;
    var instTotal = marla * instRate;
    var advAmt = instTotal * advPct / 100;
    var months = Math.max(1, yrs * 12);
    var monthly = (instTotal - advAmt) / months;

    if (!marla || !cashRate || !instRate) { out.textContent = 'Fill in the fields above to see the calculated totals.'; return; }
    out.innerHTML =
      '<div style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:8px">' +
        '<div>' + marla + ' Marla total</div>' +
        '<div>Cash: <strong>' + money(cashTotal) + '</strong></div>' +
        '<div>Installment: <strong>' + money(instTotal) + '</strong></div>' +
      '</div>' +
      '<div>Advance (' + (advPct || 0) + '%): PKR ' + Math.round(advAmt).toLocaleString('en-PK') +
      ' + PKR ' + Math.round(monthly).toLocaleString('en-PK') + ' / month for ' + (yrs || 0) + ' years</div>';
  }

  [size, cash, inst, adv, years].forEach(function (el) { el.addEventListener('input', recalc); });
  recalc();
})();
</script>
@endpush
