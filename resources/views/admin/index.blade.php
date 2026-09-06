@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Admin Panel</h2>

  <div class="stat-cards">
    <div class="stat-card"><div class="stat-value">{{ count($users) }}</div><div class="stat-label">Total Users</div></div>
    <div class="stat-card"><div class="stat-value">{{ count($pendingUsers) }}</div><div class="stat-label">Pending Approval</div></div>
  </div>

  <div class="tabs mobile-tabs-wrapper">
    <button class="tab active" data-tab="tab-pending" onclick="switchTab(event, 'tab-pending')">Pending Accounts</button>
    <button class="tab" data-tab="tab-users" onclick="switchTab(event, 'tab-users')">All Users</button>
    <button class="tab" data-tab="tab-reports" onclick="switchTab(event, 'tab-reports')">Reports</button>
    <button class="tab" data-tab="tab-skills" onclick="switchTab(event, 'tab-skills')">Skills</button>
    <button class="tab" data-tab="tab-logs" onclick="switchTab(event, 'tab-logs')">Logs</button>
  </div>

  <div id="tab-pending" class="tab-panel">
    <h3>Unverified Accounts</h3>
    @if ($pendingUsers->isEmpty())
      <div class="alert alert-success">No pending accounts.</div>
    @else
      <div class="cards-grid">
        @foreach ($pendingUsers as $u)
          <div class="card">
            <div class="card-title">{{ $u->Full_Name }}</div>
            <div class="card-subtitle">{{ $u->Email }} · {{ $u->Role }}</div>
            <form method="post" action="{{ route('admin.users.verify') }}" style="display:flex;flex-direction:column;gap:8px;margin-top:12px;">
              @csrf
              <input type="hidden" name="user_id" value="{{ $u->User_ID }}">
              <div style="display:flex;gap:8px;">
                <button type="submit" name="decision" value="approve" class="btn btn-success btn-sm">Approve</button>
                <button type="submit" name="decision" value="reject" class="btn btn-danger btn-sm">Reject</button>
              </div>
              <input type="text" name="rejection_reason" placeholder="Rejection reason (optional)" style="padding:6px;border-radius:6px;border:1px solid #ddd;">
            </form>
          </div>
        @endforeach
      </div>
    @endif
  </div>

   <div id="tab-users" class="tab-panel">
     <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm" style="margin-bottom:16px;">Add Student Manually</a>
     <div class="table-wrap"><table>
       <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Council</th><th>Rating</th><th>Completed</th><th>Status</th><th>Verified</th><th>Actions</th></tr></thead>
       <tbody>
         @foreach ($users as $u)
           <tr>
             <td>{{ $u->User_ID }}</td>
             <td>{{ $u->Full_Name }}</td>
             <td>{{ $u->Email }}</td>
              <td><span class="badge badge-{{ strtolower($u->Role) }}">{{ $u->Role }}</span></td>
              <td>{{ $u->Council ?? '-' }}</td>
              <td>{{ number_format((float)$u->Avg_Rating, 2) }}</td>
             <td>{{ $u->Total_Completed }}</td>
             <td><span class="badge badge-{{ strtolower($u->Account_Status) }}">{{ $u->Account_Status }}</span></td>
             <td>{{ !empty($u->Is_Verified) ? 'Yes' : 'No' }}</td>
              <td style="display:inline-flex;gap:6px;flex-wrap:wrap;">
               <a href="{{ route('profile.show', $u->User_ID) }}" class="btn btn-sm btn-secondary">Edit</a>
               <form method="post" action="{{ route('admin.users.delete') }}" style="display:inline;" onsubmit="return confirm('Delete this user?');">
                 @csrf
                 <input type="hidden" name="user_id" value="{{ $u->User_ID }}">
                 <button type="submit" class="btn btn-sm btn-danger">Delete</button>
               </form>
             </td>
           </tr>
         @endforeach
       </tbody>
     </table></div>
   </div>

  <div id="tab-reports" class="tab-panel" style="display:none;">
    <h3>Reports</h3>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Reporter</th><th>Reported</th><th>Violations</th><th>Account</th><th>Reason</th><th>Report</th><th>Actions</th></tr></thead>
        <tbody>
          @foreach ($reports as $r)
            <tr>
              <td>{{ $r->Reporter }}</td>
              <td>{{ $r->Reported }}</td>
              <td>{{ $r->Warning_Count }}</td>
              <td><span class="badge badge-{{ strtolower($r->Account_Status) }}">{{ $r->Account_Status }}</span></td>
              <td>{{ \Illuminate\Support\Str::limit($r->Reason, 60) }}</td>
              <td><span class="badge badge-{{ strtolower($r->Status) }}">{{ $r->Status }}</span></td>
              <td>
                @if ($r->Status === 'Pending')
                <form method="post" action="{{ route('admin.reports.resolve') }}" style="display:inline;">
                  @csrf
                  <input type="hidden" name="report_id" value="{{ $r->Report_ID }}">
                  <button type="submit" name="approve" value="Dismiss" class="btn btn-sm btn-secondary">Dismiss</button>
                  <button type="submit" name="approve" value="Action_Taken" class="btn btn-sm btn-danger">Escalate</button>
                </form>
                @else
                  <span style="color:var(--muted);">Resolved</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div id="tab-skills" class="tab-panel" style="display:none;">
    <h3>Skills</h3>
    <div class="card">
      <form method="post" action="{{ route('admin.skills') }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:end;">
        @csrf
        <div style="display:flex;flex-direction:column;gap:4px;">
          <input type="text" name="skill_title" placeholder="Skill" required>
        </div>
        <div style="display:flex;flex-direction:column;gap:4px;">
          <input type="text" name="category" placeholder="Category" value="General">
        </div>
        <div style="display:flex;flex-direction:column;gap:4px;">
          <input type="text" name="subcategory" placeholder="Subcategory (optional)">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Add</button>
      </form>
    </div>
    <div class="table-wrap"><table>
      <thead><tr><th>ID</th><th>Title</th><th>Category</th><th>Subcategory</th></tr></thead>
      <tbody>
        @foreach ($skills as $s)
          <tr>
            <td>{{ $s->Skill_ID }}</td>
            <td>{{ $s->Skill_Title }}</td>
            <td>{{ $s->Category ?? '-' }}</td>
            <td>{{ $s->Subcategory ?? '-' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table></div>
  </div>

  <div id="tab-logs" class="tab-panel" style="display:none;">
    <h3>Audit Logs</h3>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Admin</th><th>Action</th><th>Details</th><th>Time</th></tr></thead>
        <tbody>
          @foreach ($logs as $l)
            <tr>
              <td>{{ $l->Admin_ID }}</td>
              <td>{{ $l->Action }}</td>
              <td>{{ $l->Details }}</td>
              <td>{{ $l->Created_At }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
function switchTab(event, tabId) {
  document.querySelectorAll('.tab-panel').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
  document.getElementById(tabId).style.display = 'block';
  event.currentTarget.classList.add('active');

  if (window.innerWidth <= 640) {
    const wrapper = document.querySelector('.mobile-tabs-wrapper');
    if (!wrapper) return;
    const clickedTab = event.currentTarget;
    const siblings = Array.from(wrapper.querySelectorAll('.tab'));
    siblings.forEach(s => s.classList.remove('mobile-active'));
    clickedTab.classList.add('mobile-active');
    wrapper.appendChild(clickedTab);
  }
}

if (window.innerWidth <= 640) {
  document.querySelectorAll('.mobile-tabs-wrapper .tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.mobile-tabs-wrapper .tab').forEach(t => t.classList.remove('mobile-active'));
      tab.classList.add('mobile-active');
    });
  });
}
</script>
@endsection
