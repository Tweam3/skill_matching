@extends('layouts.app')

@section('content')
@guest
  <div style="background:linear-gradient(135deg, #002147 0%, #0a2d6e 100%); color:#fff; padding:80px 20px; text-align:center;">
    <div style="max-width:800px; margin:0 auto;">
      <div style="margin-bottom:20px;">
        <span class="university-badge" style="background:rgba(255,255,255,0.15); color:#FFC72C; padding:8px 18px; border-radius:999px; font-weight:700; letter-spacing:0.04em;">ISAT-U</span>
      </div>
      <h1 style="font-size:2.6rem; margin:0 0 16px; line-height:1.2;">Skill Matching System</h1>
      <p style="font-size:1.15rem; color:#cbd5e1; max-width:560px; margin:0 auto 32px; line-height:1.6;">
        Connect with peers based on skills. Request help, offer mentorship, and build together—on campus and beyond.
      </p>
      <div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
        <a href="{{ route('register') }}" class="btn btn-gold btn-lg">Get Started</a>
        <a href="{{ route('login') }}" class="btn btn-secondary btn-lg">Login</a>
      </div>
    </div>
  </div>

  <div style="max-width:1100px; margin:0 auto; padding:60px 20px;">
    <h2 style="text-align:center; margin-bottom:8px; color:var(--primary);">How it works</h2>
    <p style="text-align:center; color:var(--muted); margin-bottom:36px;">Three simple steps to get help or share your skills.</p>
    <div class="cards-grid how-it-works-grid">
      <div class="card" style="text-align:center; padding:28px;">
        <div style="font-size:2rem; margin-bottom:12px;">📝</div>
        <div class="card-title">Post a request</div>
        <div class="card-subtitle">Describe the skill you need and when you need it.</div>
      </div>
      <div class="card" style="text-align:center; padding:28px;">
        <div style="font-size:2rem; margin-bottom:12px;">🤝</div>
        <div class="card-title">Get matched</div>
        <div class="card-subtitle">We suggest verified peers with the right skills and strong ratings.</div>
      </div>
      <div class="card" style="text-align:center; padding:28px;">
        <div style="font-size:2rem; margin-bottom:12px;">⭐</div>
        <div class="card-title">Learn & review</div>
        <div class="card-subtitle">Complete the session, then leave a review to build trust.</div>
      </div>
    </div>
  </div>

  <div style="background:var(--surface); border-top:1px solid #e5e7eb; padding:50px 20px;">
    <div style="max-width:900px; margin:0 auto; text-align:center;">
      <h2 style="color:var(--primary); margin-bottom:10px;">Ready to start?</h2>
      <p style="color:var(--muted); margin-bottom:24px;">Join the ISAT-U skill-sharing community today.</p>
      <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Create an account</a>
    </div>
  </div>
@else
  <div style="background:linear-gradient(135deg, #002147 0%, #0a2d6e 100%); color:#fff; padding:48px 20px;">
    <div class="container" style="text-align:center;">
      <h1 style="margin:0 0 8px;">Welcome back, {{ auth()->user()->Full_Name }}</h1>
      <p style="color:#cbd5e1; margin:0;">Here's what's happening with your skill requests and matches.</p>
    </div>
  </div>

  <div class="container" style="padding:32px 20px;">
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

    <div class="page-header" style="margin-top:28px;">
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

    <div class="page-header" style="margin-top:28px;">
      <h3>Quick Links</h3>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
      <a href="{{ route('dashboard') }}" class="btn btn-primary">Dashboard</a>
      <a href="{{ route('matches.index') }}" class="btn btn-secondary">Browse Matches</a>
      <a href="{{ route('messages.index') }}" class="btn btn-secondary">Messages</a>
      <a href="{{ route('profile.show', auth()->id()) }}" class="btn btn-secondary">My Profile</a>
    </div>
  </div>
@endif
@endsection
