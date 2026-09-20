@extends('layouts.app')

@section('content')
<div class="container" style="text-align:center;padding-top:80px;">
  <div style="max-width:500px;margin:0 auto;">
    <h2 style="font-size:1.8rem;">Account Pending Verification</h2>
    <p style="color:var(--muted);font-size:1.05rem;margin:16px 0 24px;">
      A verification email has been sent to {{ auth()->user()->Email ?? 'your email' }}. Please check your inbox and click the verification link.
    </p>
    @if (session('success'))
      <div style="background:#d4edda;color:#155724;padding:10px 16px;border-radius:6px;margin:12px 0;font-size:0.9rem;">
        {{ session('success') }}
      </div>
    @endif
    <form method="POST" action="{{ route('resend.verification') }}" style="margin:16px 0;">
      @csrf
      <button type="submit" class="btn btn-primary">Resend Verification Email</button>
    </form>
    <p style="color:var(--muted);">
      Once verified, you'll gain full access to requests, matches, and messaging.
    </p>
    <div style="margin-top:30px;">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-secondary">Logout</button>
      </form>
    </div>
  </div>
</div>
@endsection
