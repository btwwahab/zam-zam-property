<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Admin Login &middot; {{ $site->name }}</title>
  <link rel="icon" href="/favicon.svg?v=3" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
  <link href="{{ asset_v('/css/admin.css') }}" rel="stylesheet">
  <style>
    body {
      margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
      font-family: var(--font-body);
      background-color: var(--navy-dark);
      background-image:
        linear-gradient(rgba(0, 0, 0, 0.35), rgba(0, 0, 0, 0.35)),
        url('/assets/images/farm-house-3.jpeg');
      background-size: cover, cover;
      background-position: center, center;
      background-repeat: no-repeat;
      background-attachment: fixed;
      padding: 24px;
    }

    .auth-wrap {
      width: 100%; max-width: 960px; display: grid; grid-template-columns: 1.05fr 1fr;
      background: var(--bg-white); border-radius: 22px; overflow: hidden;
      box-shadow: 0 40px 90px -30px rgba(0,0,0,.55), 0 0 0 1px rgba(255,255,255,.04);
    }

    /* ---------- brand side ---------- */
    .auth-brand {
      position: relative; padding: 46px 44px; color: #fff; overflow: hidden;
      background: linear-gradient(150deg, var(--navy-slate) 0%, var(--navy-dark) 70%);
      display: flex; flex-direction: column;
    }
    .auth-brand::before,
    .auth-brand::after {
      content: ""; position: absolute; border-radius: 50%; filter: blur(10px); opacity: .5;
      background: var(--gold-gradient);
    }
    .auth-brand::before { width: 260px; height: 260px; right: -110px; top: -90px; opacity: .28; }
    .auth-brand::after  { width: 200px; height: 200px; left: -90px; bottom: -80px; opacity: .18; }

    .auth-brand .b-inner { position: relative; z-index: 1; display: flex; flex-direction: column; height: 100%; }
    .auth-logo { display: flex; align-items: center; gap: 12px; margin-bottom: 40px; }
    .auth-logo img { width: 44px; height: 44px; border-radius: 12px; }
    .auth-logo span { font-family: var(--font-heading); font-weight: 800; font-size: 17px; letter-spacing: .3px; }

    .auth-brand h2 {
      font-family: var(--font-heading); font-weight: 800; font-size: 27px; line-height: 1.25;
      margin: 0 0 12px;
    }
    .auth-brand p.lead { color: rgba(255,255,255,.68); font-size: 13.5px; line-height: 1.6; margin: 0 0 30px; max-width: 340px; }

    .auth-feats { list-style: none; margin: auto 0 0; padding: 0; display: grid; gap: 14px; }
    .auth-feats li { display: flex; align-items: center; gap: 12px; font-size: 13px; color: rgba(255,255,255,.85); }
    .auth-feats i {
      width: 30px; height: 30px; flex: none; display: grid; place-items: center;
      border-radius: 9px; background: rgba(255,255,255,.09); color: var(--primary-gold-light); font-size: 12px;
    }

    /* ---------- form side ---------- */
    .auth-form { padding: 48px 44px; display: flex; flex-direction: column; justify-content: center; }
    .auth-form h1 { font-family: var(--font-heading); font-size: 22px; margin: 0 0 6px; color: var(--text-main); }
    .auth-form p.sub { color: var(--text-muted); font-size: 13px; margin: 0 0 26px; }

    .fgrp { margin-bottom: 16px; }
    .fgrp label { display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 7px; }
    .finput { position: relative; }
    .finput > i {
      position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
      color: var(--text-faint); font-size: 13px; pointer-events: none;
    }
    .finput input {
      width: 100%; padding: 12px 14px 12px 38px; border: 1px solid var(--border-light);
      border-radius: 11px; font-size: 14px; font-family: inherit; color: var(--text-main);
      background: #fff; transition: border-color .15s, box-shadow .15s;
    }
    .finput input:focus {
      outline: none; border-color: var(--primary-gold);
      box-shadow: 0 0 0 4px color-mix(in srgb, var(--primary-gold) 18%, transparent);
    }
    .finput .toggle-pw {
      position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
      border: none; background: none; color: var(--text-faint); cursor: pointer;
      padding: 8px; font-size: 13px; border-radius: 8px;
    }
    .finput .toggle-pw:hover { color: var(--text-muted); }

    .auth-row { display: flex; align-items: center; justify-content: space-between; margin: 6px 0 22px; }
    .auth-row label { display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: var(--text-muted); font-weight: 500; cursor: pointer; }
    .auth-row input { width: 15px; height: 15px; margin: 0; accent-color: var(--primary-gold-dark); }

    .btn-signin {
      width: 100%; border: none; cursor: pointer; color: #fff; font-family: var(--font-heading);
      font-weight: 700; font-size: 14.5px; padding: 13px 18px; border-radius: 11px;
      background: var(--gold-gradient); background-size: 160% 160%;
      display: inline-flex; align-items: center; justify-content: center; gap: 9px;
      box-shadow: 0 12px 26px -10px color-mix(in srgb, var(--primary-gold) 65%, transparent);
      transition: transform .12s ease, box-shadow .15s ease, background-position .3s ease;
    }
    .btn-signin:hover { transform: translateY(-1px); background-position: 100% 0; box-shadow: 0 16px 32px -10px color-mix(in srgb, var(--primary-gold) 70%, transparent); }
    .btn-signin:active { transform: translateY(0); }

    .login-err {
      display: flex; align-items: center; gap: 9px;
      background: #FEF2F2; color: var(--crimson); border: 1px solid #FECACA;
      border-radius: 10px; padding: 10px 13px; font-size: 12.5px; font-weight: 600; margin-bottom: 18px;
    }

    @media (max-width: 780px) {
      .auth-wrap { grid-template-columns: 1fr; max-width: 420px; }
      .auth-brand { display: none; }
      .auth-form { padding: 38px 30px; }
    }
  </style>
</head>

<body>
  <div class="auth-wrap">
    <div class="auth-brand">
      <div class="b-inner">
        <div class="auth-logo">
          <img src="/favicon.svg?v=3" alt="">
          <span>{{ $site->name }}</span>
        </div>
        <h2>Welcome back.<br>Manage your listings with ease.</h2>
        <p class="lead">{{ $site->tagline ?: 'The control room for your properties, projects, team and enquiries — all in one place.' }}</p>
        <ul class="auth-feats">
          <li><i class="fa-solid fa-building"></i> Add &amp; edit properties, projects and categories</li>
          <li><i class="fa-solid fa-envelope-open-text"></i> Track every enquiry from the website</li>
          <li><i class="fa-solid fa-sliders"></i> Control hero, page text and site settings</li>
        </ul>
      </div>
    </div>

    <form class="auth-form" method="POST" action="{{ route('admin.login.submit') }}">
      @csrf
      <h1>Sign in to the admin panel</h1>
      <p class="sub">Enter your credentials to continue.</p>

      @if($errors->any())
        <div class="login-err"><i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
      @endif

      <div class="fgrp">
        <label for="email">Email address</label>
        <div class="finput">
          <i class="fa-solid fa-envelope"></i>
          <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
        </div>
      </div>

      <div class="fgrp">
        <label for="password">Password</label>
        <div class="finput">
          <i class="fa-solid fa-lock"></i>
          <input type="password" id="password" name="password" placeholder="••••••••" required>
          <button type="button" class="toggle-pw" aria-label="Show password" data-pw-toggle><i class="fa-solid fa-eye"></i></button>
        </div>
      </div>

      <div class="auth-row">
        <label><input type="checkbox" name="remember"> Keep me signed in</label>
      </div>

      <button type="submit" class="btn-signin"><i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In</button>
    </form>
  </div>

  <script>
    document.querySelectorAll('[data-pw-toggle]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var inp = btn.parentNode.querySelector('input');
        var show = inp.type === 'password';
        inp.type = show ? 'text' : 'password';
        btn.innerHTML = show ? '<i class="fa-solid fa-eye-slash"></i>' : '<i class="fa-solid fa-eye"></i>';
      });
    });
  </script>
</body>

</html>
