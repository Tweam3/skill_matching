<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $pageTitle ?? 'Skill Matching System' }}</title>
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
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
