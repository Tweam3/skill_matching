@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Student Dashboard</h2>
  <p style="color:var(--muted);margin-bottom:24px;">Welcome, {{ auth()->user()->Full_Name }}.</p>
  <div class="stat-cards">
    <div class="stat-card">
      <div class="stat-value">{{ $openRequests }}</div>
      <div class="stat-label">Open Requests</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">{{ $pendingApplications }}</div>
      <div class="stat-label">Pending Applications</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">{{ $matchesCount }}</div>
      <div class="stat-label">Matches</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">{{ number_format((float)$user->Avg_Rating, 2) }}</div>
      <div class="stat-label">My Rating</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">{{ $unreadCount }}</div>
      <div class="stat-label">Unread Notifications</div>
    </div>
  </div>
  <div class="page-header">
    <h3>Recent Requests</h3>
    <a href="{{ route('requests.index') }}" class="btn btn-primary btn-sm">+ New Request</a>
  </div>
  @if ($recent->isEmpty())
    <div class="alert alert-info">No requests yet.</div>
  @else
    <div class="cards-grid">
      @foreach ($recent as $r)
        @php $allSkills = $r->skills->merge([$r->skill])->unique('Skill_ID')->values(); @endphp
        <div class="card">
          <div class="card-title">{{ $r->Title }}</div>
          <div class="card-subtitle">{{ $allSkills->pluck('Skill_Title')->join(', ') }}</div>
          <div style="margin-top:10px;"><span class="badge badge-{{ strtolower($r->Status) }}">{{ $r->Status }}</span></div>
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
  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <a href="{{ route('requests.index') }}" class="btn btn-primary">+ New Request</a>
    <a href="{{ route('skills.index') }}" class="btn btn-success">+ Add Skill</a>
    <a href="{{ route('matches.index') }}" class="btn btn-secondary">Browse Matches</a>
    <a href="{{ route('messages.index') }}" class="btn btn-secondary">Messages</a>
    <a href="{{ route('reviews.index') }}" class="btn btn-secondary">Reviews</a>
    <a href="{{ route('profile.show', auth()->id()) }}" class="btn btn-secondary">My Profile</a>
  </div>
  <div class="card" style="margin-top:24px; padding:16px; background:#F0FDF4; border:1px solid #BBF7D0;">
    <h4 style="margin:0 0 8px; color:#065F46;">Quick Overview</h4>
    <ul style="margin:0; padding-left:20px; color:var(--muted); line-height:1.8;">
      <li><strong>+ New Request</strong> — Post a skill request and get matched with peers who can help.</li>
      <li><strong>+ Add Skill</strong> — Add skills you know to your profile so others can find you.</li>
      <li><strong>Browse Matches</strong> — View and accept skill requests that match your skills.</li>
      <li><strong>Messages</strong> — Chat with matched users.</li>
      <li><strong>Reviews</strong> — Leave or view reviews from completed sessions.</li>
      <li><strong>My Profile</strong> — Update your info, bio, and profile picture.</li>
    </ul>
  </div>
</div>
@endsection
