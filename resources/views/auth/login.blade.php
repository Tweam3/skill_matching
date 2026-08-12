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
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>
    <p style="text-align:center;margin:18px 0 0;color:var(--muted);font-size:0.9rem;">
      No account? <a href="{{ route('register') }}" style="color:var(--primary);">Register</a>
    </p>
  </div>
</div>
@endsection
