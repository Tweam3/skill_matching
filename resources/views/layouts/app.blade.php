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
</body>
</html>
