@extends('layouts.app')

@section('content')
<div class="auth-container">
  <div class="auth-box">
    <h1>Create Account</h1>
    <p class="auth-sub">Join our skill-matching community</p>
    @if ($errors->any())
      <div class="alert alert-danger">
        @foreach ($errors->all() as $err)
          <div>{{ $err }}</div>
        @endforeach
      </div>
    @endif
    <form method="POST" action="{{ route('register') }}">
      @csrf
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="name" required value="{{ old('name') }}">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required value="{{ old('email') }}">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required minlength="6">
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="password_confirmation" required>
      </div>
      @if (auth()->check() && auth()->user()->Role === 'Admin')
      <div class="form-group">
        <label>Role</label>
        <select name="role">
          <option value="Student">Student</option>
          <option value="Faculty">Faculty</option>
          <option value="Staff">Staff</option>
          <option value="Admin">Admin</option>
        </select>
      </div>
      @endif
      <div class="form-group">
        <label>Council</label>
        <select name="council">
          <option value="">Select Council</option>
          <option value="HBM">HBM</option>
          <option value="CSC">CSC</option>
          <option value="BIT">BIT</option>
          <option value="EDUC">EDUC</option>
          <option value="Unaffiliated">Unaffiliated (e.g. PE faculty)</option>
        </select>
      </div>
      @if (auth()->check() && auth()->user()->Role === 'Admin')
      <div class="form-group">
        <label><input type="checkbox" name="as_admin" value="1"> Register as Admin</label>
      </div>
      @endif
      <button type="submit" class="btn btn-primary btn-block">Register</button>
    </form>
    <p style="text-align:center;margin:18px 0 0;color:var(--muted);font-size:0.9rem;">
      Already have an account? <a href="{{ route('login') }}" style="color:var(--primary);">Login</a>
    </p>
    @if (auth()->check() && auth()->user()->Role === 'Admin')
      <p style="text-align:center;margin-top:10px;"><a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">Cancel</a></p>
    @endif
  </div>
</div>
@endsection
