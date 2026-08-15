@extends('layouts.app')

@section('content')
<div class="auth-container">
  <div class="auth-box">
    <h1>Welcome Back</h1>
    <p class="auth-sub">Login to Skill Matching System</p>
    @if ($errors->any())
      <div class="alert alert-danger">
        @foreach ($errors->all() as $err)
          <div>{{ $err }}</div>
        @endforeach
      </div>
    @endif
    <form method="POST" action="{{ route('login') }}">
      @csrf
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required value="{{ old('email') }}">
      </div>
      <div class="form-group">
        <label>Password</label>
        <div class="password-field">
          <input type="password" name="password" required>
          <button type="button" class="password-toggle" aria-label="Toggle password visibility">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
          </button>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>
    <p style="text-align:center;margin:18px 0 0;color:var(--muted);font-size:0.9rem;">
      No account? <a href="{{ route('register') }}" style="color:var(--primary);">Register</a>
    </p>
  </div>
</div>
@endsection
