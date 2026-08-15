@extends('layouts.app')

@section('content')
<div class="container">
  <div class="page-header">
    <h2>Profile</h2>
    <div class="page-header-actions">
      <a href="{{ route('skills.index') }}" class="btn btn-secondary btn-sm">Add Skill</a>
    </div>
  </div>

  <div class="card" style="margin-bottom:20px;">
    <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
      <div style="flex-shrink:0;">
        @php
          $pic = $user->Profile_Picture ? asset('storage/'.$user->Profile_Picture) : asset('images/default-avatar.svg');
        @endphp
        <img src="{{ $pic }}" alt="Profile picture" onerror="this.src='{{ asset('images/default-avatar.svg') }}'" style="width:88px; height:88px; border-radius:50%; object-fit:cover; border:3px solid var(--primary); background:var(--bg);">
      </div>
      <div style="flex:1; min-width:220px;">
        <h3 style="margin:0 0 4px; font-size:1.25rem; color:var(--primary);">{{ $user->Full_Name }}</h3>
        <p style="margin:0; color:var(--muted); font-size:0.95rem;">{{ $user->Email }}</p>
        <div style="margin-top:8px; display:flex; gap:8px; flex-wrap:wrap;">
          <span class="badge badge-{{ strtolower(str_replace(' ', '_', $user->Role)) }}">{{ $user->Role }}</span>
          <span class="badge badge-pending">{{ $user->Council ?? 'Unaffiliated' }}</span>
          <span class="badge badge-open">Rating {{ number_format((float)$user->Avg_Rating, 2) }}</span>
          <span class="badge badge-assigned">{{ $user->Total_Completed }} completed</span>
        </div>
      </div>
      <div style="flex-shrink:0;">
        @if (auth()->user()->User_ID === $user->User_ID || auth()->user()->Role === 'Admin')
          <a href="{{ route('profile.edit', $user->User_ID) }}" class="btn btn-primary btn-sm">Edit Profile</a>
        @endif
      </div>
      </div>
    </div>
  </div>

  <div class="card" style="margin-bottom:20px;">
    <h3 style="margin-top:0;">About</h3>
    <p style="color:var(--muted); margin:0; white-space:pre-line;">
      {{ $user->Bio ?: 'No bio yet.' }}
    </p>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Skills</h3>
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
                @if (auth()->user()->User_ID === $user->User_ID || auth()->user()->Role === 'Admin')
                  <form method="post" action="{{ route('profile.skill.remove') }}" style="display:inline;" onsubmit="return confirm('Remove this skill?');">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->User_ID }}">
                    <input type="hidden" name="skill_id" value="{{ $s->Skill_ID }}">
                    <button type="submit" style="border:none;background:none;color:var(--danger);cursor:pointer;font-weight:700;"> ×</button>
                  </form>
                @endif
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
              @if (auth()->user()->User_ID === $user->User_ID || auth()->user()->Role === 'Admin')
                <form method="post" action="{{ route('profile.skill.remove') }}" style="display:inline;" onsubmit="return confirm('Remove this skill?');">
                  @csrf
                  <input type="hidden" name="user_id" value="{{ $user->User_ID }}">
                  <input type="hidden" name="skill_id" value="{{ $s->Skill_ID }}">
                  <button type="submit" style="border:none;background:none;color:var(--danger);cursor:pointer;font-weight:700;"> ×</button>
                </form>
              @endif
            </span>
          @endforeach
        </div>
      </div>
    @endif
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
