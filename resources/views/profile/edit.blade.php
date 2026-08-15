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
        <form id="adjust-picture-form" method="POST" action="{{ route('profile.adjustPicture', $user->User_ID) }}" enctype="multipart/form-data">
          @csrf
          <div class="form-group">
            <label>Choose a photo</label>
            <input type="file" id="picture-input" name="profile_picture" accept="image/*">
            @error('profile_picture')<div class="alert alert-danger" style="padding:8px 12px; margin-top:6px;">{{ $message }}</div>@enderror
          </div>
        </form>

        <div id="picture-preview-area" style="display:none; margin-top:16px;">
          <div style="position:relative; width:260px; height:260px; overflow:hidden; border-radius:50%; border:3px solid var(--primary); margin:0 auto; background:#f3f4f6; cursor:grab;">
            <img id="preview-img" src="" alt="Preview" style="position:absolute; left:0; top:0; transform-origin:0 0; user-select:none; pointer-events:none;">
          </div>
          <div style="margin-top:16px; display:flex; flex-direction:column; gap:10px; align-items:center;">
            <label style="font-size:0.875rem; font-weight:600;">Zoom</label>
            <input type="range" id="zoom-range" min="1" max="3" step="0.05" value="1" style="width:260px;">
            <div style="display:flex; gap:10px;">
              <button type="button" id="save-picture-btn" class="btn btn-primary btn-sm">Save Picture</button>
              <button type="button" id="cancel-picture-btn" class="btn btn-secondary btn-sm">Cancel</button>
            </div>
          </div>
        </div>

        @if ($user->Profile_Picture)
          <form method="POST" action="{{ route('profile.update', $user->User_ID) }}" enctype="multipart/form-data" style="margin-top:16px;">
            @csrf
            <input type="hidden" name="section" value="picture">
            <input type="hidden" name="remove_profile_picture" value="1">
            <button type="submit" class="btn btn-danger btn-sm">Remove Current Picture</button>
          </form>
        @endif
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
          @if (auth()->user()->Role === 'Admin')
          <div class="form-group">
            <label>Role</label>
            <select name="role" style="padding:8px;border-radius:6px;border:1px solid #E5E7EB;">
              <option value="Student" {{ $user->Role === 'Student' ? 'selected' : '' }}>Student</option>
              <option value="Faculty" {{ $user->Role === 'Faculty' ? 'selected' : '' }}>Faculty</option>
              <option value="Staff" {{ $user->Role === 'Staff' ? 'selected' : '' }}>Staff</option>
              <option value="Admin" {{ $user->Role === 'Admin' ? 'selected' : '' }}>Admin</option>
            </select>
          </div>
          <div class="form-group">
            <label>Council</label>
            <select name="council" style="padding:8px;border-radius:6px;border:1px solid #E5E7EB;">
              <option value="" {{ !$user->Council ? 'selected' : '' }}>None</option>
              <option value="HBM" {{ $user->Council === 'HBM' ? 'selected' : '' }}>HBM</option>
              <option value="CSC" {{ $user->Council === 'CSC' ? 'selected' : '' }}>CSC</option>
              <option value="BIT" {{ $user->Council === 'BIT' ? 'selected' : '' }}>BIT</option>
              <option value="EDUC" {{ $user->Council === 'EDUC' ? 'selected' : '' }}>EDUC</option>
              <option value="Unaffiliated" {{ $user->Council === 'Unaffiliated' ? 'selected' : '' }}>Unaffiliated</option>
            </select>
          </div>
          @endif
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

<script>
let currentFile = null;
let scale = 1;
let panX = 0;
let panY = 0;
let isDragging = false;
let startX = 0;
let startY = 0;

const fileInput = document.getElementById('picture-input');
const previewArea = document.getElementById('picture-preview-area');
const previewImg = document.getElementById('preview-img');
const zoomRange = document.getElementById('zoom-range');
const saveBtn = document.getElementById('save-picture-btn');
const cancelBtn = document.getElementById('cancel-picture-btn');
const previewBox = previewImg ? previewImg.parentElement : null;

function updateTransform() {
  if (!previewImg) return;
  previewImg.style.width = previewImg.naturalWidth + 'px';
  previewImg.style.height = previewImg.naturalHeight + 'px';
  previewImg.style.transform = `translate(${panX}px, ${panY}px) scale(${scale})`;
}

function clampPan() {
  if (!previewImg || !previewBox) return;
  const w = previewImg.naturalWidth * scale;
  const h = previewImg.naturalHeight * scale;
  const boxW = previewBox.clientWidth;
  const boxH = previewBox.clientHeight;
  panX = Math.min(0, Math.max(panX, boxW - w));
  panY = Math.min(0, Math.max(panY, boxH - h));
}

if (fileInput) {
  fileInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;
    currentFile = file;
    const reader = new FileReader();
    reader.onload = (ev) => {
      previewImg.src = ev.target.result;
      previewArea.style.display = 'block';
      previewImg.onload = () => {
        scale = 1;
        panX = 0;
        panY = 0;
        zoomRange.value = 1;
        fitPreview();
      };
    };
    reader.readAsDataURL(file);
  });
}

function fitPreview() {
  if (!previewImg || !previewBox) return;
  const boxW = previewBox.clientWidth;
  const boxH = previewBox.clientHeight;
  const naturalW = previewImg.naturalWidth;
  const naturalH = previewImg.naturalHeight;
  const baseScale = Math.max(boxW / naturalW, boxH / naturalH);
  scale = baseScale;
  panX = (boxW - naturalW * scale) / 2;
  panY = (boxH - naturalH * scale) / 2;
  zoomRange.min = 0.5;
  zoomRange.max = 3;
  zoomRange.step = 0.05;
  zoomRange.value = 1;
  updateTransform();
}

if (zoomRange) {
  zoomRange.addEventListener('input', () => {
    if (!previewImg || !previewBox) return;
    const boxW = previewBox.clientWidth;
    const boxH = previewBox.clientHeight;
    const naturalW = previewImg.naturalWidth;
    const naturalH = previewImg.naturalHeight;
    const baseScale = Math.max(boxW / naturalW, boxH / naturalH);
    const newScale = parseFloat(zoomRange.value) * baseScale;
    const centerX = boxW / 2;
    const centerY = boxH / 2;
    panX = centerX - (centerX - panX) * (newScale / scale);
    panY = centerY - (centerY - panY) * (newScale / scale);
    scale = newScale;
    clampPan();
    updateTransform();
  });
}

if (previewBox) {
  previewBox.addEventListener('mousedown', (e) => {
    isDragging = true;
    startX = e.clientX - panX;
    startY = e.clientY - panY;
    previewBox.style.cursor = 'grabbing';
  });
}

window.addEventListener('mousemove', (e) => {
  if (!isDragging) return;
  panX = e.clientX - startX;
  panY = e.clientY - startY;
  clampPan();
  updateTransform();
});

window.addEventListener('mouseup', () => {
  isDragging = false;
  if (previewBox) previewBox.style.cursor = 'grab';
});

if (saveBtn) {
  saveBtn.addEventListener('click', () => {
    if (!currentFile || !previewImg) return;
    const size = 400;
    const ratio = size / 260;
    const canvas = document.createElement('canvas');
    canvas.width = size;
    canvas.height = size;
    const ctx = canvas.getContext('2d');

    ctx.drawImage(
      previewImg,
      panX * ratio,
      panY * ratio,
      previewImg.naturalWidth * scale * ratio,
      previewImg.naturalHeight * scale * ratio
    );

    canvas.toBlob((blob) => {
      const input = document.getElementById('picture-input');
      const dt = new DataTransfer();
      dt.items.add(new File([blob], 'adjusted.png', { type: 'image/png' }));
      input.files = dt.files;

      const form = document.getElementById('adjust-picture-form');
      form.submit();
    }, 'image/png');
  });
}

if (cancelBtn) {
  cancelBtn.addEventListener('click', () => {
    previewArea.style.display = 'none';
    currentFile = null;
    previewImg.src = '';
    fileInput.value = '';
  });
}
</script>
@endsection