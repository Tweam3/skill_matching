@extends('layouts.app')

@section('content')
@guest
  <div style="text-align:center;padding:80px 20px;">
    <div style="margin-bottom:24px;">
      <span class="university-badge">ISAT-U</span>
    </div>
    <h1 style="font-size:2.2rem;color:var(--primary);margin-bottom:8px;">Skill Matching System</h1>
    <p style="color:var(--muted);font-size:1.1rem;max-width:500px;margin:16px auto 30px;">
      Iloilo Science and Technology University<br>
      <strong>Labor is Honor</strong> &mdash; Connect with peers based on skills. Request help. Offer mentorship. Build together.
    </p>
    <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
      <a href="{{ route('register') }}" class="btn btn-gold btn-lg">Get Started</a>
      <a href="{{ route('login') }}" class="btn btn-secondary btn-lg">Login</a>
    </div>
  </div>
@else
  <h2>Dashboard</h2>
  @if (auth()->user()->Role === 'Admin')
    <div style="margin-bottom:20px;">
      <a href="{{ route('admin.index') }}" class="btn btn-primary">Open Admin Panel</a>
    </div>
  @endif
  <div class="stat-cards">
    <div class="stat-card">
      <div class="stat-value">{{ $stats['requests'] }}</div>
      <div class="stat-label">My Requests</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">{{ $stats['matches'] }}</div>
      <div class="stat-label">Matches</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">{{ $stats['unread'] }}</div>
      <div class="stat-label">Unread Notifications</div>
    </div>
  </div>
  <div class="page-header">
    <h3>Recent Requests</h3>
    <a href="{{ route('requests.index') }}" class="btn btn-primary btn-sm">+ New Request</a>
  </div>
  @if ($stats['latest']->isEmpty())
    <div class="alert alert-info">No requests yet.</div>
  @else
    <div class="cards-grid">
      @foreach ($stats['latest'] as $r)
        @php $allSkills = $r->skills->merge([$r->skill])->unique('Skill_ID')->values(); @endphp
        <div class="card">
          <div class="card-title">{{ $r->Title }}</div>
          <div class="card-subtitle">{{ $allSkills->pluck('Skill_Title')->join(', ') }}</div>
          <div><span class="badge badge-{{ strtolower($r->Status) }}">{{ $r->Status }}</span></div>
          <div style="margin-top:10px;">
            <a href="{{ route('requests.show', $r->Request_ID) }}" class="btn btn-secondary btn-sm">View</a>
          </div>
        </div>
      @endforeach
    </div>
  @endif
@endif
@endsection
