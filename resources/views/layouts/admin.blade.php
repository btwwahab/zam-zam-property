<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="robots" content="noindex, nofollow">
  <title>@yield('title', 'Admin') &middot; {{ $site->name }}</title>

  <link rel="icon" href="/favicon.svg?v=3" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
  <link href="{{ asset_v('/css/admin.css') }}" rel="stylesheet">
</head>

<body>
<div class="admin-shell">

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <img src="/assets/images/logo-footer.svg" alt="{{ $site->name }}">
      <span class="brand-sub">Admin</span>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-label">Overview</div>
      <a href="{{ route('admin.dashboard') }}" title="Dashboard" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="fa-solid fa-gauge-high"></i><span class="nav-text">Dashboard</span></a>

      <div class="nav-label">Catalogue</div>
      <a href="{{ route('admin.properties.index') }}" title="Properties" class="{{ request()->routeIs('admin.properties.*') ? 'active' : '' }}"><i class="fa-solid fa-building"></i><span class="nav-text">Properties</span></a>
      <a href="{{ route('admin.projects.index') }}" title="Projects" class="{{ request()->routeIs('admin.projects.*') ? 'active' : '' }}"><i class="fa-solid fa-diagram-project"></i><span class="nav-text">Projects</span></a>
      <a href="{{ route('admin.categories.index') }}" title="Categories" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"><i class="fa-solid fa-layer-group"></i><span class="nav-text">Categories</span></a>
      <a href="{{ route('admin.plot-packages.index') }}" title="Plot Packages" class="{{ request()->routeIs('admin.plot-packages.*') ? 'active' : '' }}"><i class="fa-solid fa-tags"></i><span class="nav-text">Plot Packages</span></a>
      <a href="{{ route('admin.team.index') }}" title="Team" class="{{ request()->routeIs('admin.team.*') ? 'active' : '' }}"><i class="fa-solid fa-users"></i><span class="nav-text">Team</span></a>

      <div class="nav-label">Engagement</div>
      <a href="{{ route('admin.enquiries.index') }}" title="Enquiries" class="{{ request()->routeIs('admin.enquiries.*') ? 'active' : '' }}"><i class="fa-solid fa-envelope"></i><span class="nav-text">Enquiries</span></a>

      <div class="nav-label">Configuration</div>
      <a href="{{ route('admin.settings.edit') }}" title="Site Settings" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"><i class="fa-solid fa-gear"></i><span class="nav-text">Site Settings</span></a>
      <a href="{{ route('admin.pages.edit') }}" title="Pages" class="{{ request()->routeIs('admin.pages.*') ? 'active' : '' }}"><i class="fa-solid fa-file-lines"></i><span class="nav-text">Pages</span></a>
    </nav>
    <div class="sidebar-foot">
      <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" class="sf-logout" title="Log out"><i class="fa-solid fa-right-from-bracket"></i><span class="nav-text"> Log out</span></button>
      </form>
    </div>
  </aside>
  <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

  <div class="main-wrap">
    <header class="topbar">
      <button class="icon-btn menu-toggle" id="menu-toggle" aria-label="Open menu"><i class="fa-solid fa-bars"></i></button>
      <h1 class="page-title">@yield('title', 'Admin')</h1>
      <div style="flex:1"></div>
      <a class="icon-btn" href="{{ route('home') }}" target="_blank" title="Open site"><i class="fa-solid fa-globe"></i></a>
      <div class="admin-avatar" title="{{ auth()->user()->name ?? 'Admin' }}">{{ \Illuminate\Support\Str::substr(strtoupper(collect(explode(' ', $site->name))->map(fn($w) => $w[0] ?? '')->join('')), 0, 2) ?: 'AD' }}</div>
      <form method="POST" action="{{ route('admin.logout') }}" class="d-inline">
        @csrf
        <button class="icon-btn" type="submit" title="Log out"><i class="fa-solid fa-right-from-bracket"></i></button>
      </form>
    </header>

    <main class="content">
      @yield('content')
    </main>
  </div>
</div>

<div class="cmodal" id="confirm-modal" hidden>
  <div class="cmodal-backdrop" data-cm-cancel></div>
  <div class="cmodal-card" role="alertdialog" aria-modal="true" aria-labelledby="cm-title">
    <div class="cmodal-ico"><i class="fa-solid fa-triangle-exclamation"></i></div>
    <h3 id="cm-title">Are you sure?</h3>
    <p id="cm-text">This action cannot be undone.</p>
    <div class="cmodal-actions">
      <button type="button" class="btn-a btn-ghost" data-cm-cancel>Cancel</button>
      <button type="button" class="btn-a btn-danger-soft" id="cm-confirm"><i class="fa-solid fa-trash"></i> Delete</button>
    </div>
  </div>
</div>

<div id="toast-stack">
  @if(session('ok'))<div class="toast-a"><i class="fa-solid fa-circle-check" style="color:var(--emerald)"></i><span>{{ session('ok') }}</span></div>@endif
  @if($errors->any())<div class="toast-a" data-err="1" style="border-left-color:var(--crimson)"><i class="fa-solid fa-triangle-exclamation" style="color:var(--crimson)"></i><span>{{ $errors->count() > 1 ? $errors->count() . ' problems — check the highlighted fields.' : $errors->first() }}</span></div>@endif
</div>

<script>
  (function () {
    var sb = document.getElementById('sidebar');
    var shell = document.querySelector('.admin-shell');
    var mq = window.matchMedia('(max-width: 860px)');
    try { if (localStorage.getItem('hp_admin_nav') === 'collapsed') shell.classList.add('nav-collapsed'); } catch (e) {}

    document.getElementById('menu-toggle').addEventListener('click', function () {
      if (mq.matches) {
        sb.classList.toggle('open');
      } else {
        shell.classList.toggle('nav-collapsed');
        try { localStorage.setItem('hp_admin_nav', shell.classList.contains('nav-collapsed') ? 'collapsed' : 'open'); } catch (e) {}
      }
    });
    document.getElementById('sidebar-backdrop').addEventListener('click', function () { sb.classList.remove('open'); });
    document.querySelectorAll('#toast-stack .toast-a').forEach(function (t) {
      setTimeout(function () { t.style.transition = 'opacity .3s,transform .3s'; t.style.opacity = '0'; t.style.transform = 'translateY(8px)'; setTimeout(function () { t.remove(); }, 320); }, t.hasAttribute('data-err') ? 9000 : 3600);
    });
    var tabs = document.getElementById('page-tabs');
    if (tabs) {
      tabs.addEventListener('click', function (e) {
        var b = e.target.closest('button'); if (!b) return;
        tabs.querySelectorAll('button').forEach(function (x) { x.classList.toggle('active', x === b); });
        document.querySelectorAll('[data-pane]').forEach(function (p) { p.hidden = p.getAttribute('data-pane') !== b.getAttribute('data-tab'); });
      });
    }

    /* --- file-upload dropzones (used on Settings / Category / Project / Property) --- */
    document.querySelectorAll('.upload-drop').forEach(function (drop) {
      var input = drop.querySelector('input[type=file]');
      if (!input) return;
      var label = drop.querySelector('[data-drop-label]') || drop.querySelector('.u-main');
      var def = label ? label.textContent : '';
      ['dragenter', 'dragover'].forEach(function (e) { drop.addEventListener(e, function (ev) { ev.preventDefault(); drop.classList.add('dragover'); }); });
      ['dragleave', 'drop'].forEach(function (e) { drop.addEventListener(e, function (ev) { ev.preventDefault(); drop.classList.remove('dragover'); }); });
      drop.addEventListener('drop', function (ev) {
        if (ev.dataTransfer.files && ev.dataTransfer.files.length) { input.files = ev.dataTransfer.files; input.dispatchEvent(new Event('change')); }
      });
      input.addEventListener('change', function () {
        var names = [].map.call(input.files, function (f) { return f.name; }).join(', ');
        if (label) label.textContent = names ? ('Selected: ' + names) : def;
        drop.classList.toggle('has-file', input.files.length > 0);
      });
    });

    /* --- "or pick existing" toggles --- */
    document.querySelectorAll('[data-toggle-target]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var t = document.querySelector(btn.getAttribute('data-toggle-target'));
        if (t) t.hidden = !t.hidden;
      });
    });

    /* --- visual icon picker (Categories) --- */
    document.querySelectorAll('[data-icon-pick]').forEach(function (grid) {
      var field = grid.closest('.fld');
      var hidden = field ? field.querySelector('input[type=hidden]') : null;
      grid.addEventListener('click', function (e) {
        var opt = e.target.closest('.icon-pick-opt');
        if (!opt) return;
        grid.querySelectorAll('.icon-pick-opt').forEach(function (o) {
          var on = o === opt;
          o.classList.toggle('is-sel', on);
          o.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        if (hidden) hidden.value = opt.getAttribute('data-icon');
      });
    });

    /* --- styled confirm modal (replaces native confirm() on delete forms) --- */
    var modal = document.getElementById('confirm-modal');
    if (modal) {
      var cmText = document.getElementById('cm-text');
      var cmOk = document.getElementById('cm-confirm');
      var cmPending = null;
      var cmClose = function () { modal.hidden = true; cmPending = null; };
      modal.querySelectorAll('[data-cm-cancel]').forEach(function (el) { el.addEventListener('click', cmClose); });
      document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) cmClose(); });
      cmOk.addEventListener('click', function () {
        if (!cmPending) return;
        var f = cmPending; cmPending = null; modal.hidden = true;
        f.dataset.cmConfirmed = '1';
        if (f.requestSubmit) f.requestSubmit(); else f.submit();
      });
      document.querySelectorAll('form[data-confirm]').forEach(function (f) {
        f.addEventListener('submit', function (e) {
          if (f.dataset.cmConfirmed === '1') { delete f.dataset.cmConfirmed; return; }
          e.preventDefault();
          cmPending = f;
          cmText.textContent = f.getAttribute('data-confirm') || 'This action cannot be undone.';
          modal.hidden = false;
          cmOk.focus();
        });
      });
    }
  })();
</script>
@stack('scripts')
</body>

</html>
