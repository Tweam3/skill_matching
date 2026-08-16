@extends('layouts.app')

@section('content')
<div class="container">
  <div class="page-header">
    <h2>Settings</h2>
  </div>

  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  <div class="settings-grid">
    <div class="settings-sidebar">
      <div class="card">
        <a href="#appearance" class="settings-sidebar-item active" onclick="switchSettingsTab(event, 'appearance')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
          Appearance
        </a>
        <a href="#notifications" class="settings-sidebar-item" onclick="switchSettingsTab(event, 'notifications')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
          Notifications
        </a>
        <a href="#privacy" class="settings-sidebar-item" onclick="switchSettingsTab(event, 'privacy')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          Privacy
        </a>
        <a href="#language" class="settings-sidebar-item" onclick="switchSettingsTab(event, 'language')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
          Language
        </a>
      </div>
    </div>

    <div class="settings-content">
      <form method="POST" action="{{ route('settings.update') }}">
        @csrf
        @method('PUT')

        <div id="appearance" class="settings-section card">
          <h3>Appearance</h3>
          <div style="display:flex; align-items:center; justify-content:space-between;">
            <div>
              <div style="font-weight:600;">Dark Mode</div>
              <div style="color:var(--muted); font-size:0.9rem;">Switch between light and dark theme</div>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" name="dark_mode" value="1" {{ !empty($settings['dark_mode']) ? 'checked' : '' }} onchange="applyDarkMode(this.checked)">
              <span class="toggle-slider"></span>
            </label>
          </div>
        </div>

        <div id="notifications" class="settings-section card" style="display:none;">
          <h3>Notifications</h3>
          <div style="display:flex; align-items:center; justify-content:space-between;">
            <div>
              <div style="font-weight:600;">Email Notifications</div>
              <div style="color:var(--muted); font-size:0.9rem;">Receive email updates about your requests and matches</div>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" name="email_notifications" value="1" {{ !empty($settings['email_notifications']) ? 'checked' : '' }}>
              <span class="toggle-slider"></span>
            </label>
          </div>
        </div>

        <div id="privacy" class="settings-section card" style="display:none;">
          <h3>Privacy</h3>
          <div class="form-group">
            <label>Profile Visibility</label>
            <select name="profile_visibility">
              <option value="public" {{ ($settings['profile_visibility'] ?? 'public') === 'public' ? 'selected' : '' }}>Public — everyone can see my profile</option>
              <option value="private" {{ ($settings['profile_visibility'] ?? '') === 'private' ? 'selected' : '' }}>Private — only I and admins can see my profile</option>
            </select>
          </div>
        </div>

        <div id="language" class="settings-section card" style="display:none;">
          <h3>Language</h3>
          <div class="form-group">
            <label>Preferred Language</label>
            <select name="language">
              <option value="en" {{ ($settings['language'] ?? 'en') === 'en' ? 'selected' : '' }}>English</option>
              <option value="fil" {{ ($settings['language'] ?? '') === 'fil' ? 'selected' : '' }}>Filipino</option>
            </select>
          </div>
        </div>

        <div style="margin-top:20px;">
          <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function applyDarkMode(checked) {
  var html = document.documentElement;
  if (checked) {
    html.setAttribute('data-theme', 'dark');
    localStorage.setItem('dark_mode', '1');
  } else {
    html.removeAttribute('data-theme');
    localStorage.removeItem('dark_mode');
  }
}

function switchSettingsTab(event, tabId) {
  event.preventDefault();
  document.querySelectorAll('.settings-section').forEach(function(el) {
    el.style.display = 'none';
  });
  document.querySelectorAll('.settings-sidebar-item').forEach(function(el) {
    el.classList.remove('active');
  });
  var target = document.getElementById(tabId);
  if (target) target.style.display = 'block';
  event.currentTarget.classList.add('active');
}
document.addEventListener('DOMContentLoaded', function() {
  var hash = window.location.hash.replace('#', '');
  if (hash) {
    switchSettingsTab(new Event('click'), hash);
    var link = document.querySelector('.settings-sidebar-item[href="#' + hash + '"]');
    if (link) link.classList.add('active');
  }
});
</script>
@endpush
