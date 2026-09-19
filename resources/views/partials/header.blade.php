@php($active = $active ?? '')
<style>
  .nav-projects { position: relative; }
  .nav-projects > .nav-link-custom { display: inline-flex; align-items: center; }
  .nav-caret { font-size: 10px; margin-left: 6px; opacity: .75; transition: transform .16s ease; }
  .nav-projects-menu {
    list-style: none; margin: 0; padding: 8px;
    background: var(--bg-white, #fff);
    border: 1px solid var(--border-light, #e6e8ec);
    border-radius: 14px;
    box-shadow: 0 20px 44px -14px rgba(15, 23, 42, .28);
  }
  .nav-projects-menu .dropdown-item {
    display: block; border-radius: 9px; padding: 9px 14px;
    font-size: 14px; font-weight: 500; text-decoration: none; white-space: nowrap;
    color: var(--text-main, #1f2937);
  }
  .nav-projects-menu .dropdown-item:hover,
  .nav-projects-menu .dropdown-item:focus {
    background: var(--primary-gold-light, #dcfce7); color: var(--primary-gold-dark, #15803d); font-weight: 600;
  }
  .nav-projects-menu .dropdown-item.is-all { font-weight: 700; }
  .nav-projects-menu .menu-sep { height: 1px; background: var(--border-light, #e6e8ec); margin: 6px 4px; }

  @media (min-width: 992px) {
    .nav-projects-menu {
      position: absolute; top: 100%; left: 50%;
      transform: translateX(-50%) translateY(10px);
      min-width: 240px; z-index: 1080;
      opacity: 0; visibility: hidden; pointer-events: none;
      transition: opacity .16s ease, transform .16s ease, visibility .16s;
    }
    .nav-projects::after {
      content: ""; position: absolute; left: -8px; right: -8px; top: 100%; height: 16px;
    }
    .nav-projects:hover .nav-projects-menu,
    .nav-projects:focus-within .nav-projects-menu {
      opacity: 1; visibility: visible; pointer-events: auto;
      transform: translateX(-50%) translateY(4px);
    }
    .nav-projects:hover .nav-caret,
    .nav-projects:focus-within .nav-caret { transform: rotate(180deg); }
  }

  @media (max-width: 991.98px) {
    #navbarMain {
      background: #fff;
      border: 1px solid var(--border-light, #e6e8ec);
      border-radius: 16px;
      box-shadow: 0 24px 48px -16px rgba(15, 23, 42, .22);
      padding: 12px;
      margin-top: 12px;
    }
    #navbarMain .navbar-nav { width: 100%; }
    #navbarMain .nav-link-custom { padding: 10px 12px !important; }
    #navbarMain > .btn-gold { display: flex; width: 100%; justify-content: center; margin-top: 12px; }

    .nav-projects > .nav-link-custom { display: flex; align-items: center; justify-content: space-between; }
    .nav-caret {
      display: inline-block; margin-left: auto; margin-right: -10px;
      padding: 6px 10px; font-size: 12px; opacity: .8;
    }
    .nav-projects-menu {
      box-shadow: none; border: none; background: transparent;
      padding: 2px 0 6px 12px; border-left: 2px solid var(--border-light, #e6e8ec); margin: 4px 0 4px 6px;
      display: none;
    }
    .nav-projects.is-open > .nav-projects-menu { display: block; }
    .nav-projects.is-open .nav-caret { transform: rotate(180deg); }
    .nav-projects-menu .dropdown-item { padding: 7px 10px; }
    .nav-projects-menu .menu-sep { display: none; }
  }
</style>
<div class="top-header d-none d-lg-block">
  <div class="container d-flex justify-content-between align-items-center">
    <span><i class="fa-solid fa-location-dot text-gold me-2" aria-hidden="true"></i> {{ $site->address_short }}</span>
    <a href="tel:{{ $site->phone_raw }}"><i class="fa-solid fa-phone text-gold me-2" aria-hidden="true"></i> {{ $site->phone }}</a>
  </div>
</div>
<nav class="navbar navbar-expand-lg sticky-top navbar-custom navbar-light">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
      <img src="/assets/images/logo.svg" alt="{{ $site->name }}" class="brand-logo-img" style="height: 48px; width: auto;">
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain"
      aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarMain">
      <ul class="navbar-nav mx-auto">
        <li class="nav-item"><a class="nav-link nav-link-custom {{ $active === 'home' ? 'active' : '' }}" href="{{ route('home') }}" @if($active === 'home') aria-current="page" @endif>Home</a></li>
        <li class="nav-item"><a class="nav-link nav-link-custom {{ $active === 'properties' ? 'active' : '' }}" href="{{ route('properties') }}" @if($active === 'properties') aria-current="page" @endif>Properties</a></li>
        <li class="nav-item nav-projects">
          <a class="nav-link nav-link-custom {{ $active === 'projects' ? 'active' : '' }}" href="{{ route('projects') }}" @if($active === 'projects') aria-current="page" @endif>Projects<i class="fa-solid fa-chevron-down nav-caret" aria-hidden="true"></i></a>
          @if(!empty($navProjects) && count($navProjects))
          <ul class="nav-projects-menu">
            <li><a class="dropdown-item is-all" href="{{ route('projects') }}">All Projects</a></li>
            <li class="menu-sep" aria-hidden="true"></li>
            @foreach($navProjects as $np)
              <li><a class="dropdown-item" href="{{ route('projects') }}#{{ $np->slug }}">{{ $np->name }}</a></li>
            @endforeach
          </ul>
          @endif
        </li>
        <li class="nav-item"><a class="nav-link nav-link-custom {{ $active === 'about' ? 'active' : '' }}" href="{{ route('about') }}" @if($active === 'about') aria-current="page" @endif>About</a></li>
      </ul>
      <a href="{{ route('contact') }}" class="btn btn-gold"><i class="fa-solid fa-headset me-1" aria-hidden="true"></i> Contact Us</a>
    </div>
  </div>
</nav>
<script>
  (function () {
    var wrap = document.querySelector('.nav-projects');
    if (!wrap) return;
    var link = wrap.querySelector(':scope > .nav-link-custom');
    var menu = wrap.querySelector(':scope > .nav-projects-menu');
    if (!link || !menu) return;
    var mq = window.matchMedia('(max-width: 991.98px)');

    link.addEventListener('click', function (e) {
      if (!mq.matches) return;            // desktop keeps hover behaviour
      e.preventDefault();                 // first tap opens the sub-menu
      wrap.classList.toggle('is-open');
    });
    // reset when switching back to desktop
    mq.addEventListener('change', function () { if (!mq.matches) wrap.classList.remove('is-open'); });
  })();
</script>
