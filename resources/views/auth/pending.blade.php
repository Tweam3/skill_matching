@extends('layouts.app')

@section('content')
<div class="container" style="text-align:center;padding-top:80px;">
  <div style="max-width:500px;margin:0 auto;">
    <h2 style="font-size:1.8rem;">Account Pending Verification</h2>
    <p style="color:var(--muted);font-size:1.05rem;margin:16px 0 24px;">
      Your account has been created and is awaiting approval from an administrator.
    </p>
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
