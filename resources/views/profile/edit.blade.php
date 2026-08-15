@extends('layouts.app')

@section('content')
<div class="container">
  <div class="page-header">
    <h2>Edit Profile</h2>
    <div class="page-header-actions">
      <a href="{{ route('profile.show', $user->User_ID) }}" class="btn btn-secondary btn-sm">Back to Profile</a>
    </div>
  </div>

  @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif
  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div style="display:grid; grid-template-columns: 1fr 2fr; gap:20px;">
    <div class="card">
      <div style="text-align:center; margin-bottom:16px;">
        @php
          $pic = $user->Profile_Picture ? asset('storage/'.$user->Profile_Picture) : asset('images/default-avatar.svg');
        @endphp
        <img src="{{ $pic }}" alt="Profile picture" style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:3px solid var(--primary); background:var(--bg);">
      </div>
      <h3 style="margin:0 0 4px; text-align:center; color:var(--primary);">{{ $user->Full_Name }}</h3>
      <p style="margin:0; color:var(--muted); font-size:0.9rem; text-align:center;">{{ $user->Email }}</p>
      <div style="margin-top:12px; display:flex; justify-content:center;">
        <span class="badge badge-{{ strtolower(str_replace(' ', '_', $user->Role)) }}">{{ $user->Role }}</span>
      </div>
    </div>

    <div>
      <div class="card" style="margin-bottom:20px;">
        <h3 style="margin-top:0;">Profile Picture</h3>
        <form method="POST" action="{{ route('profile.update', $user->User_ID) }}" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="section" value="picture">
          <div class="form-group">
            <label>Choose a photo</label>
            <input type="file" name="profile_picture" accept="image/*">
            @error('profile_picture')<div class="alert alert-danger" style="padding:8px 12px; margin-top:6px;">{{ $message }}</div>@enderror
          </div>
          @if ($user->Profile_Picture)
            <label style="display:flex; align-items:center; gap:8px; font-size:0.875rem; margin-bottom:12px;">
              <input type="checkbox" name="remove_profile_picture" value="1">
              Remove current picture
            </label>
          @endif
          <button type="submit" class="btn btn-primary btn-sm">Save Picture</button>
        </form>
      </div>

      <div class="card" style="margin-bottom:20px;">
        <h3 style="margin-top:0;">Name & Nickname</h3>
        <form method="POST" action="{{ route('profile.update', $user->User_ID) }}">
          @csrf
          <input type="hidden" name="section" value="name">
          <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" value="{{ old('name', $user->Full_Name) }}" required>
            @error('name')<div class="alert alert-danger" style="padding:8px 12px; margin-top:6px;">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email', $user->Email) }}" required>
            @error('email')<div class="alert alert-danger" style="padding:8px 12px; margin-top:6px;">{{ $message }}</div>@enderror
          </div>
          <button type="submit" class="btn btn-primary btn-sm">Save Name</button>
        </form>
      </div>

      <div class="card" style="margin-bottom:20px;">
        <h3 style="margin-top:0;">Password</h3>
        <form method="POST" action="{{ route('profile.update', $user->User_ID) }}">
          @csrf
          <input type="hidden" name="section" value="password">
          <div class="form-group">
            <label>Current Password</label>
            <div class="password-field">
              <input type="password" name="current_password" required>
              <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
              </button>
            </div>
            @error('current_password')<div class="alert alert-danger" style="padding:8px 12px; margin-top:6px;">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label>New Password</label>
            <div class="password-field">
              <input type="password" name="new_password" required minlength="8">
              <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
              </button>
            </div>
            @error('new_password')<div class="alert alert-danger" style="padding:8px 12px; margin-top:6px;">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label>Confirm New Password</label>
            <div class="password-field">
              <input type="password" name="new_password_confirmation" required minlength="8">
              <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
              </button>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-sm">Update Password</button>
        </form>
      </div>

      <div class="card">
        <h3 style="margin-top:0;">About</h3>
        <form method="POST" action="{{ route('profile.update', $user->User_ID) }}">
          @csrf
          <input type="hidden" name="section" value="about">
          <div class="form-group">
            <label>Bio</label>
            <textarea name="bio" rows="4" maxlength="500" placeholder="Tell us about yourself...">{{ old('bio', $user->Bio) }}</textarea>
            @error('bio')<div class="alert alert-danger" style="padding:8px 12px; margin-top:6px;">{{ $message }}</div>@enderror
          </div>
          <button type="submit" class="btn btn-primary btn-sm">Save Bio</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection