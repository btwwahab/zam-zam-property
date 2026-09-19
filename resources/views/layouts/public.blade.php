<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', $site->name . ' | Real Estate in Multan')</title>
  <meta name="description" content="@yield('description', $site->tagline)">
  <meta name="theme-color" content="#0F172A">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="{{ $site->name }}">
  <meta property="og:title" content="@yield('title', $site->name)">
  <meta property="og:description" content="@yield('description', $site->tagline)">
  @yield('head')

  <link rel="icon" href="/favicon.svg?v=3" type="image/svg+xml">
  <link rel="icon" href="/favicon-32.png?v=3" sizes="32x32" type="image/png">
  <link rel="icon" href="/favicon-16.png?v=3" sizes="16x16" type="image/png">
  <link rel="shortcut icon" href="/favicon.ico?v=3">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
  <link href="{{ asset_v('/css/style.css') }}" rel="stylesheet">
</head>

<body class="page-{{ $active ?: 'x' }}">
<a class="skip-link" href="#main">Skip to main content</a>

@include('partials.header', ['active' => $active ?? ''])

<main id="main">
@yield('content')
</main>

@include('partials.footer')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  window.PRIMENEST_PROPERTIES = @json($propertiesJson ?? []);
  window.PRIMENEST_AGENTS = @json($agentsJson ?? []);
  window.PN = {
    SITE: @json($siteJs),
    waLink: function (msg, phone) {
      return 'https://wa.me/' + (phone || this.SITE.whatsapp) + '?text=' + encodeURIComponent(msg);
    }
  };
  window.ENQUIRY_ENDPOINT = '{{ route('enquiries.store') }}';
</script>
<script src="{{ asset_v('/js/main.js') }}"></script>
@stack('scripts')
</body>

</html>
