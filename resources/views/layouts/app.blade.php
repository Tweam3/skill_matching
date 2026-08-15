<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
  <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
  <title>{{ $pageTitle ?? 'Skill Matching System — ISAT-U' }}</title>
  <meta name="description" content="Campus-based peer-to-peer skill exchange platform. Request help, offer mentorship, and connect with verified students at ISAT-U.">
  <link rel="canonical" href="{{ url()->current() }}">
  <meta property="og:title" content="{{ $pageTitle ?? 'Skill Matching System — ISAT-U' }}">
  <meta property="og:description" content="Campus-based peer-to-peer skill exchange platform. Request help, offer mentorship, and connect with verified students.">
  <meta property="og:type" content="website">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:image" content="{{ asset('images/og-image.png') }}">
  <meta name="twitter:card" content="summary_large_image">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  @stack('meta')
</head>
<body>
  @auth
    @include('layouts.navbar')
  @endauth
  <div class="container">
    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @yield('content')
   </div>
   @auth
     @include('layouts.footer')
   @endauth
    @stack('scripts')
    <script>
      document.addEventListener('click', function(e) {
        const btn = e.target.closest('.password-toggle');
        if (!btn) return;
        const input = btn.closest('.password-field').querySelector('input');
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        btn.innerHTML = isPassword
          ? '<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94\"></path><path d=\"M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19\"></path><path d=\"M14.12 14.12a3 3 0 1 1-4.24-4.24\"></path><line x1=\"1\" y1=\"1\" x2=\"23\" y2=\"23\"></line></svg>'
          : '<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z\"></path><circle cx=\"12\" cy=\"12\" r=\"3\"></circle></svg>';
      });
    </script>
</body>
</html>
