@extends('layouts.public')

@section('title', 'Properties in Multan | ' . $site->name)
@section('description', 'Browse residential, commercial and farmhouse property opportunities in Multan with ' . $site->name . '.')

@section('content')
  <section class="bg-navy py-5 text-white position-relative">
    <div class="container py-3">
      <div class="row align-items-center">
        <div class="col-md-8">
          <span class="badge-pill-tag gold mb-2">Multan &bull; Southern Bypass Corridor</span>
          <h1 class="fs-1 fw-bold text-white mb-2">Property Opportunities</h1>
          <p class="text-light mb-0">Residential plots, commercial plots and farmhouse land across {{ $navProjects->pluck('name')->join(' and ') }}. Prices and availability shown are indicative &mdash; confirm current details with our team.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
          <nav aria-label="Breadcrumb">
            <ol class="breadcrumb justify-content-md-end mb-0">
              <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-gold">Home</a></li>
              <li class="breadcrumb-item active text-white" aria-current="page">Properties</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </section>

  <section class="section-padding bg-light">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-3">
          <form class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 100px; z-index: 10;" id="filter-form" aria-label="Filter properties">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h2 class="fw-bold mb-0 text-navy h5"><i class="fa-solid fa-filter text-gold-ink me-2" aria-hidden="true"></i> Filter Search</h2>
              <button type="reset" class="btn btn-sm btn-link text-muted p-0" id="filter-reset">Reset</button>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold text-navy" for="filter-search">Keyword</label>
              <input type="text" id="filter-search" name="keyword" class="form-control" placeholder="e.g. 5 Marla, plot, farmhouse...">
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold text-navy" for="filter-category">Category</label>
              <select id="filter-category" name="category" class="form-select">
                <option value="all">All Categories</option>
                @foreach($navCategories as $c)
                  <option value="{{ $c->slug }}">{{ $c->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold text-navy" for="filter-project">Project</label>
              <select id="filter-project" name="project" class="form-select">
                <option value="all">All Projects</option>
                @foreach($navProjects as $p)
                  <option value="{{ $p->slug }}">{{ $p->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold text-navy" for="filter-purpose">Purpose</label>
              <select id="filter-purpose" name="purpose" class="form-select">
                <option value="all">Buy &amp; Rent</option>
                <option value="For Sale">For Sale</option>
                <option value="For Rent">For Rent</option>
              </select>
            </div>
            <div class="mb-2">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label small fw-bold text-navy mb-0" for="filter-max-price">Max Budget</label>
                <span id="price-val-display" class="small text-gold-ink fw-bold">Any</span>
              </div>
              <input type="range" id="filter-max-price" name="maxPrice" class="form-range" min="4000000" max="40000000" step="1000000" value="40000000">
            </div>
          </form>
        </div>

        <div class="col-lg-9">
          <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
              <div><span class="fw-bold text-navy"><span id="result-count">{{ count($propertiesJson) }}</span> Properties</span> found</div>
              <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                  <label class="small fw-bold text-muted text-nowrap" for="filter-sort">Sort:</label>
                  <select id="filter-sort" class="form-select form-select-sm" style="width: 160px;">
                    <option value="default">Featured</option>
                    <option value="price-low">Price: Low to High</option>
                    <option value="price-high">Price: High to Low</option>
                  </select>
                </div>
                <div class="btn-group btn-group-sm" role="group" aria-label="View mode">
                  <button id="btn-grid-view" type="button" class="btn btn-outline-secondary active" aria-pressed="true" aria-label="Grid view"><i class="fa-solid fa-border-all" aria-hidden="true"></i></button>
                  <button id="btn-list-view" type="button" class="btn btn-outline-secondary" aria-pressed="false" aria-label="List view"><i class="fa-solid fa-list" aria-hidden="true"></i></button>
                </div>
              </div>
            </div>
          </div>

          <div id="properties-grid" class="row g-4" aria-live="polite"></div>
          <nav id="pagination" class="mt-5" aria-label="Property listings pages"></nav>

          <p class="small text-muted mt-4 mb-0"><i class="fa-solid fa-circle-info text-gold-ink me-1" aria-hidden="true"></i> Property prices, plot numbers, categories and availability shown here are indicative placeholders. Current inventory, pricing and payment plans should be confirmed directly with the {{ $site->name }} sales office.</p>
        </div>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  <script src="{{ asset_v('/js/properties.js') }}"></script>
@endpush
