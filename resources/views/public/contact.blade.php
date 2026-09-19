@extends('layouts.public')

@section('title', 'Contact ' . $site->name . ' | Property Team, Multan')
@section('description', pc('contact_hero_sub'))

@section('content')
  <section class="bg-navy py-5 text-white">
    <div class="container py-3 text-center">
      <span class="badge-pill-tag gold mb-2">{{ pc('contact_hero_badge') }}</span>
      <h1 class="fs-1 fw-bold text-white mb-2">{{ pc('contact_hero_heading') }}</h1>
      <p class="text-light mx-auto" style="max-width: 620px;">{{ pc('contact_hero_sub') }}</p>
    </div>
  </section>

  <section class="section-padding bg-light">
    <div class="container">
      <div class="row g-4 mb-5">
        @php
          $waHref = 'https://wa.me/' . $site->whatsapp . '?text=' . rawurlencode('Hello, I am interested in exploring property opportunities with ' . $site->name . ' in Multan. Please share the latest available options.');
          $mapHref = 'https://maps.google.com/?q=' . urlencode($site->address_short);
          $links = ['whatsapp' => $waHref, 'call' => 'tel:' . $site->phone_raw, 'map' => $mapHref];
        @endphp
        @foreach(pc_list('contact_cards') as $card)
          @php $href = $links[$card['action'] ?? 'call'] ?? '#'; $accent = $card['accent'] ?? 'var(--primary-gold)'; @endphp
          <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100" style="border-top: 4px solid {{ $accent }} !important;">
              <span class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:58px;height:58px;background:rgba(212,175,55,.14);color:{{ $accent }};font-size:1.7rem;"><i class="{{ $card['icon'] }}" aria-hidden="true"></i></span>
              <h2 class="fw-bold text-navy h5 mb-2">{{ $card['title'] }}</h2>
              <p class="text-muted small mb-4">{{ $card['text'] }}</p>
              <a href="{{ $href }}" @if(($card['action'] ?? '') !== 'call') target="_blank" rel="noopener" @endif class="btn btn-navy w-100 justify-content-center fw-bold rounded-pill py-2 mt-auto">{{ $card['btn'] ?? 'Open' }}</a>
            </div>
          </div>
        @endforeach
      </div>

      <div class="row g-4">
        <div class="col-lg-7">
          <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 h-100">
            <span class="badge-pill-tag gold mb-3"><i class="fa-solid fa-paper-plane me-1" aria-hidden="true"></i> {{ pc('contact_form_badge') }}</span>
            <h2 class="fw-bold text-navy mb-2 h3">{{ pc('contact_form_heading') }}</h2>
            <p class="text-muted form-note mb-4">{{ pc('contact_form_note') }}</p>

            @if(session('enquiry_ok'))
              <div class="alert alert-success rounded-3 mb-4"><i class="fa-solid fa-circle-check me-2"></i>Thank you — your enquiry has been received. Our team will get back to you soon.</div>
            @endif
            <form class="inquiry-form" method="POST" action="{{ route('enquiries.store') }}" novalidate>
              @csrf
              <div class="hp-field" aria-hidden="true"><input type="text" name="zzhp_field" tabindex="-1" autocomplete="off" aria-hidden="true"></div>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-navy" for="c-name">Name *</label>
                  <input type="text" id="c-name" name="name" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-navy" for="c-phone">Phone Number *</label>
                  <input type="tel" id="c-phone" name="phone" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-navy" for="c-email">Email Address</label>
                  <input type="email" id="c-email" name="email" class="form-control">
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-navy" for="c-interest">Property Type *</label>
                  <select id="c-interest" name="interest" class="form-select" required>
                    <option value="">Select...</option>
                    @foreach($navCategories as $c)<option>{{ $c->name }}</option>@endforeach
                    <option>I want to sell a property</option>
                    <option>Other / not sure</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-navy" for="c-budget">Budget (PKR)</label>
                  <input type="text" id="c-budget" name="budget" class="form-control" placeholder="e.g. 45 - 85 Lakh">
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-navy" for="c-society">Preferred Location</label>
                  <input type="text" id="c-society" name="society" class="form-control" placeholder="e.g. {{ $navProjects->pluck('name')->join(' / ') }}">
                </div>
                <div class="col-12">
                  <label class="form-label small fw-bold text-navy" for="c-message">Message</label>
                  <textarea id="c-message" name="message" rows="4" class="form-control"></textarea>
                </div>
                <div class="col-12">
                  <button type="submit" class="btn btn-gold justify-content-center px-5">Send Inquiry</button>
                  <p class="form-status mt-3" role="status" aria-live="polite"></p>
                </div>
              </div>
            </form>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 h-100">
            <span class="badge-pill-tag navy mb-3"><i class="fa-solid fa-building me-1" aria-hidden="true"></i> Office</span>
            <h2 class="fw-bold text-navy mb-4 h3">{{ $site->name }}</h2>
            <div class="d-flex align-items-start gap-3 mb-4">
              <span class="icon-badge-sm"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span>
              <div><h3 class="fw-bold mb-1 text-navy h6">Office Location</h3><p class="text-muted small mb-0">{{ $site->address }}</p></div>
            </div>
            <div class="d-flex align-items-start gap-3 mb-4">
              <span class="icon-badge-sm"><i class="fa-solid fa-phone" aria-hidden="true"></i></span>
              <div><h3 class="fw-bold mb-1 text-navy h6">Phone</h3><p class="text-muted small mb-0"><a href="tel:{{ $site->phone_raw }}">{{ $site->phone }}</a></p></div>
            </div>
            <div class="d-flex align-items-start gap-3 mb-4">
              <span class="icon-badge-sm"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></span>
              <div><h3 class="fw-bold mb-1 text-navy h6">WhatsApp</h3><p class="text-muted small mb-0"><a href="https://wa.me/{{ $site->whatsapp }}" target="_blank" rel="noopener">Chat with {{ $site->name }}</a></p></div>
            </div>
            @if($site->office_hours)
            <div class="d-flex align-items-start gap-3 mb-4">
              <span class="icon-badge-sm"><i class="fa-solid fa-clock" aria-hidden="true"></i></span>
              <div><h3 class="fw-bold mb-1 text-navy h6">Office Hours</h3><p class="text-muted small mb-0">{{ $site->office_hours }}</p></div>
            </div>
            @endif
            <div class="rounded-4 overflow-hidden mt-2 border" style="height: 240px;">
              <iframe title="{{ $site->name }} area on Google Maps" src="https://www.google.com/maps?q={{ urlencode($site->address_short) }}&output=embed" width="100%" height="100%" style="border:0;" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <p class="small text-muted mt-2 mb-0">Map shows the general area. Confirm the exact office location by phone before visiting.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section-padding bg-white">
    <div class="container">
      <div class="text-center section-header">
        <span class="badge-pill-tag gold mb-2">{{ pc('contact_faq_badge') }}</span>
        <h2 class="section-title">{{ pc('contact_faq_heading') }}</h2>
      </div>
      <div class="row justify-content-center">
        <div class="col-lg-9">
          <div class="accordion faq-accordion" id="faqAccordion">
            @foreach(pc_list('faqs') as $i => $f)
              @php
                $q = strtr($f['q'] ?? '', ['{brand}' => $site->name, '{address}' => $site->address]);
                $a = strtr($f['a'] ?? '', ['{brand}' => $site->name, '{address}' => $site->address]);
              @endphp
              <div class="accordion-item">
                <h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $i }}">{{ $q }}</button></h3>
                <div id="faq{{ $i }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body text-muted">{{ $a }}</div></div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
