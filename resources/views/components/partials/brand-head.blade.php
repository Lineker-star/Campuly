{{-- Campuly brand head: favicons, app icons, install manifest and browser theme colour. --}}
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/brand/icon-32.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('img/brand/icon-192.png') }}">
<link rel="apple-touch-icon" href="{{ asset('img/brand/apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<meta name="application-name" content="{{ config('app.name') }}">
<meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="description" content="{{ __('Campuly — Learn · Interact · Excel. School management for every campus.') }}">
<meta name="theme-color" content="{{ config('app.theme_color') }}" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#0B135A" media="(prefers-color-scheme: dark)">
