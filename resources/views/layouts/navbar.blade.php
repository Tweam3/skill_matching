<nav class="navbar">
  <div class="navbar-inner">
    <div class="navbar-brand">
      <span class="university-badge">ISAT-U</span> Skill Matching System
    </div>
    <ul class="navbar-links">
      <li><a href="{{ route('home') }}">Home</a></li>
      <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li><a href="{{ route('matches.index') }}">Matches</a></li>
      <li><a href="{{ route('search.index') }}">Search</a></li>
      <li><a href="{{ route('messages.index') }}">Messages</a></li>
      <li><a href="{{ route('notifications.index') }}">Notifications</a></li>
      @if (auth()->user()->Role === 'Admin')
        <li><a href="{{ route('admin.index') }}">Admin Panel</a></li>
        <li><a href="{{ route('admin.analytics') }}">Analytics</a></li>
      @endif
      <li><a href="{{ route('profile.show', auth()->id()) }}" class="btn btn-secondary btn-sm">View Profile</a></li>
      <li>
        <form method="POST" action="{{ route('logout') }}" style="display:inline;">
          @csrf
          <button type="submit" class="btn btn-secondary btn-sm">Logout</button>
        </form>
      </li>
    </ul>
  </div>
</nav>
