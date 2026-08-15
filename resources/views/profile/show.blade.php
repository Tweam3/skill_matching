@extends('layouts.app')

@section('content')
<div class="container">
  <div class="page-header">
    <h2>Profile</h2>
    <div class="page-header-actions">
      <a href="{{ route('skills.index') }}" class="btn btn-secondary btn-sm">Add Skill</a>
    </div>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <div class="card">
      <h3 style="margin-top:0">User Info</h3>
      <div style="text-align:center; margin-bottom:16px;">
        @php
          $pic = $user->Profile_Picture ? asset('storage/'.$user->Profile_Picture) : asset('images/default-avatar.svg');
        @endphp
        <img src="{{ $pic }}" alt="Profile picture" style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:3px solid var(--primary); background:var(--bg);">
      </div>
      <p><strong>Name:</strong> {{ $user->Full_Name }}</p>
      <p><strong>Email:</strong> {{ $user->Email }}</p>
       <p><strong>Role:</strong> {{ $user->Role }}</p>
       <p><strong>Council:</strong> {{ $user->Council ?? 'Unaffiliated' }}</p>
       <p><strong>Member since:</strong> {{ $user->Created_At }}</p>
      <p><strong>Avg Rating:</strong> {{ number_format((float)$user->Avg_Rating, 2) }} / 5</p>
      <p><strong>Completed tasks:</strong> {{ $user->Total_Completed }}</p>
        @if (auth()->user()->User_ID === $user->User_ID || auth()->user()->Role === 'Admin')
        <div style="margin-top:16px;">
          <button class="btn btn-primary btn-sm" onclick="toggleEditForm()">Edit Profile</button>
        </div>
        <div id="edit-form" style="display:none;margin-top:16px;">
         <form method="POST" action="{{ route('profile.update', $user->User_ID) }}" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:8px;">
           @csrf
           <input type="text" name="name" value="{{ $user->Full_Name }}" placeholder="Full Name" required>
           <input type="email" name="email" value="{{ $user->Email }}" placeholder="Email" required>
           <label style="font-size:0.875rem; font-weight:600;">Profile Picture</label>
           <input type="file" name="profile_picture" accept="image/*">
           @if ($user->Profile_Picture)
             <label style="display:flex; align-items:center; gap:8px; font-size:0.875rem;">
               <input type="checkbox" name="remove_profile_picture" value="1">
               Remove current picture
             </label>
           @endif
           @if (auth()->user()->Role === 'Admin' && auth()->user()->User_ID !== $user->User_ID)
           <select name="role" style="padding:8px;border-radius:6px;border:1px solid #E5E7EB;">
             <option value="Student" {{ $user->Role === 'Student' ? 'selected' : '' }}>Student</option>
             <option value="Faculty" {{ $user->Role === 'Faculty' ? 'selected' : '' }}>Faculty</option>
             <option value="Staff" {{ $user->Role === 'Staff' ? 'selected' : '' }}>Staff</option>
             <option value="Admin" {{ $user->Role === 'Admin' ? 'selected' : '' }}>Admin</option>
           </select>
           <select name="council" style="padding:8px;border-radius:6px;border:1px solid #E5E7EB;">
             <option value="" {{ !$user->Council ? 'selected' : '' }}>None</option>
             <option value="HBM" {{ $user->Council === 'HBM' ? 'selected' : '' }}>HBM</option>
             <option value="CSC" {{ $user->Council === 'CSC' ? 'selected' : '' }}>CSC</option>
             <option value="BIT" {{ $user->Council === 'BIT' ? 'selected' : '' }}>BIT</option>
             <option value="EDUC" {{ $user->Council === 'EDUC' ? 'selected' : '' }}>EDUC</option>
             <option value="Unaffiliated" {{ $user->Council === 'Unaffiliated' ? 'selected' : '' }}>Unaffiliated</option>
           </select>
           @endif
           <button type="submit" class="btn btn-success btn-sm">Save Changes</button>
           <button type="button" class="btn btn-secondary btn-sm" onclick="toggleEditForm()">Cancel</button>
         </form>
        </div>
        @endif
         @if (auth()->user()->User_ID !== $user->User_ID)
         <div style="margin-top:16px;">
           <button class="btn btn-danger btn-sm" onclick="openReportModal({{ $user->User_ID }})" title="Report User" style="padding:8px 10px;">
             <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
               <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
               <line x1="12" y1="9" x2="12" y2="13"></line>
               <line x1="12" y1="17" x2="12.01" y2="17"></line>
             </svg>
           </button>
         </div>
         @endif
    </div>
    <div class="card">
      <h3 style="margin-top:0">Skills</h3>
      <div class="profile-skills-container">
      @php
        $grouped = $userSkills->groupBy(function ($s) {
          return $s->pivot->Proficiency ?? 0;
        });
        $labels = [1 => 'Beginner', 2 => 'Advanced Beginner', 3 => 'Competent', 4 => 'Proficient', 5 => 'Expert'];
        $orderedIds = [5, 4, 3, 2, 1];
      @endphp
      @foreach ($orderedIds as $level)
        @if ($grouped->has($level))
          <div style="margin-bottom:14px;">
            <h4 style="margin:0 0 8px; font-size:0.9rem; color:var(--muted); text-transform:uppercase; letter-spacing:0.05em;">{{ $labels[$level] }}</h4>
            <div class="skills-chips">
              @foreach ($grouped[$level] as $s)
                <span>
                  {{ $s->Skill_Title }}
                  <form method="post" action="{{ route('profile.skill.remove') }}" style="display:inline;" onsubmit="return confirm('Remove this skill?');">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->User_ID }}">
                    <input type="hidden" name="skill_id" value="{{ $s->Skill_ID }}">
                    <button type="submit" style="border:none;background:none;color:var(--danger);cursor:pointer;font-weight:700;"> ×</button>
                  </form>
                </span>
              @endforeach
            </div>
          </div>
        @endif
      @endforeach
      @if ($grouped->has(0))
        <div style="margin-bottom:14px;">
          <h4 style="margin:0 0 8px; font-size:0.9rem; color:var(--muted); text-transform:uppercase; letter-spacing:0.05em;">Not rated</h4>
          <div class="skills-chips">
            @foreach ($grouped[0] as $s)
              <span>
                {{ $s->Skill_Title }}
                <form method="post" action="{{ route('profile.skill.remove') }}" style="display:inline;" onsubmit="return confirm('Remove this skill?');">
                  @csrf
                  <input type="hidden" name="user_id" value="{{ $user->User_ID }}">
                  <input type="hidden" name="skill_id" value="{{ $s->Skill_ID }}">
                  <button type="submit" style="border:none;background:none;color:var(--danger);cursor:pointer;font-weight:700;"> ×</button>
                </form>
              </span>
            @endforeach
          </div>
        </div>
      @endif
      </div>
    </div>
  </div>
</div>

<div id="report-modal-overlay" class="report-modal-overlay" onclick="if(event.target===this)closeReportModal()">
  <div class="report-modal">
    <div class="report-modal-header">
      <h3>Report User</h3>
      <button type="button" class="report-modal-close" onclick="closeReportModal()">&times;</button>
    </div>
    <form id="report-modal-form" method="POST" action="{{ route('reports.store') }}">
      @csrf
      <div class="report-modal-body">
        <input type="hidden" name="reported_user_id" id="report-modal-user-id" value="">
        <div class="form-group">
          <label>Reason</label>
          <textarea name="reason" rows="4" required class="form-control" style="width:100%;padding:10px;border:2px solid #E5E7EB;border-radius:8px;"></textarea>
        </div>
        <div class="form-group">
          <label>Proof (Optional)</label>
          <input type="text" name="proof" placeholder="Link or description of evidence" class="form-control" style="width:100%;padding:10px;border:2px solid #E5E7EB;border-radius:8px;">
        </div>
      </div>
      <div class="report-modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" onclick="closeReportModal()">Cancel</button>
        <button type="submit" class="btn btn-danger btn-sm">Submit Report</button>
      </div>
    </form>
  </div>
</div>

<script>
function toggleEditForm() {
  const c = document.getElementById('edit-form');
  c.style.display = c.style.display === 'none' ? 'block' : 'none';
}
function openReportModal(userId) {
  document.getElementById('report-modal-user-id').value = userId;
  document.getElementById('report-modal-overlay').classList.add('active');
}
function closeReportModal() {
  document.getElementById('report-modal-overlay').classList.remove('active');
}
</script>
@endsection
