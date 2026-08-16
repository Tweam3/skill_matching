@extends('layouts.app')

@section('content')
<div class="container" style="text-align:center;padding-top:80px;">
  <div style="max-width:500px;margin:0 auto;">
    <h2 style="font-size:1.8rem; color:#C2410C;">Account Suspended</h2>
    <p style="color:var(--muted);font-size:1.05rem;margin:16px 0 24px;">
      Your account has been suspended due to policy violations. You cannot create requests, apply, or message other users while suspended.
    </p>
    <p style="color:var(--muted);">
      If you believe this is an error, please contact the administration team.
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
