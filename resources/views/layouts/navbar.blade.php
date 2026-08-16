<nav class="navbar">
  <div class="navbar-inner">
    <a href="{{ route('home') }}" class="navbar-brand">
      <span class="university-badge">ISAT-U</span>
      <span class="navbar-title">Skill Matching System</span>
    </a>
    <button class="navbar-toggle" id="navbar-toggle" aria-label="Toggle navigation">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="24" height="24"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
    </button>
    <ul class="navbar-links" id="navbar-links">
      <li><a href="{{ route('home') }}">Home</a></li>
      <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li><a href="{{ route('matches.index') }}">Matches</a></li>
      <li><a href="{{ route('search.index') }}">Search</a></li>
      <li><a href="{{ route('messages.index') }}">Messages</a></li>
      <li><a href="{{ route('notifications.index') }}">Notifications @if($unreadCount = auth()->user()->notifications()->where('Status', 'Unread')->count())<span class="badge badge-open" style="margin-left:6px;">{{ $unreadCount }}</span>@endif</a></li>
      @if (auth()->user()->Role === 'Admin')
        <li><a href="{{ route('admin.index') }}">Admin Panel</a></li>
        <li><a href="{{ route('admin.analytics') }}">Analytics</a></li>
      @endif
      <li><a href="{{ route('profile.show', auth()->id()) }}">Profile</a></li>
      <li><a href="{{ route('settings.index') }}">Settings</a></li>
      <li>
        <form method="POST" action="{{ route('logout') }}" style="display:inline;">
          @csrf
          <button type="submit" class="btn btn-secondary btn-sm">Logout</button>
        </form>
      </li>
    </ul>
  </div>
</nav>
