@php
  $waGeneral = 'https://wa.me/' . $site->whatsapp . '?text=' . rawurlencode('Hello, I am interested in exploring property opportunities with ' . $site->name . ' in Multan. Please share the latest available options.');
  $waFooter  = 'https://wa.me/' . $site->whatsapp . '?text=' . rawurlencode('Hello ' . $site->name . ', I would like to know more about your available properties in Multan.');
@endphp
<div class="floating-actions">
  <a href="{{ $waGeneral }}" class="floating-btn whatsapp" target="_blank" rel="noopener" aria-label="Chat with {{ $site->name }} on WhatsApp"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></a>
  <a href="tel:{{ $site->phone_raw }}" class="floating-btn phone" aria-label="Call {{ $site->name }}"><i class="fa-solid fa-phone" aria-hidden="true"></i></a>
</div>
<footer class="footer-custom">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4 col-md-6">
        <a class="d-flex align-items-center mb-3" href="{{ route('home') }}">
          <img src="/assets/images/logo-footer.svg" alt="{{ $site->name }}" class="brand-logo-img" style="height: 48px; width: auto;">
        </a>
        <p class="text-light small mb-2 fw-semibold">{{ $site->tagline }}</p>
        <p class="text-light small mb-0">Residential &bull; Commercial &bull; Farmhouse &bull; Land &bull; Investment</p>
      </div>
      <div class="col-lg-2 col-md-6">
        <h2 class="footer-title h5">Quick Links</h2>
        <ul class="footer-links">
          <li><a href="{{ route('home') }}">Home</a></li>
          <li><a href="{{ route('about') }}">About</a></li>
          <li><a href="{{ route('projects') }}">Projects</a></li>
          <li><a href="{{ route('contact') }}">Contact</a></li>
          <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
          <li><a href="{{ route('terms') }}">Terms &amp; Conditions</a></li>
        </ul>
      </div>
      <div class="col-lg-3 col-md-6">
        <h2 class="footer-title h5">Property Categories</h2>
        <ul class="footer-links">
          @foreach($navCategories as $c)
            <li><a href="{{ route('properties') }}?category={{ $c->slug }}">{{ $c->name }}</a></li>
          @endforeach
        </ul>
      </div>
      <div class="col-lg-3 col-md-6">
        <h2 class="footer-title h5">Office</h2>
        <div class="footer-contact-item"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span>{{ $site->address }}</span></div>
        <div class="footer-contact-item"><i class="fa-solid fa-phone" aria-hidden="true"></i><a href="tel:{{ $site->phone_raw }}">{{ $site->phone }}</a></div>
        <div class="footer-contact-item"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i><a href="{{ $waFooter }}" target="_blank" rel="noopener">Chat on WhatsApp</a></div>
      </div>
    </div>
    <div class="copyright-bar text-center"><p class="mb-0">&copy; {{ date('Y') }} {{ $site->legal_name ?: $site->name }}. All rights reserved.</p></div>
  </div>
</footer>
