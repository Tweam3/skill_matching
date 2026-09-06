# HTML Code Reference

Unique HTML structural patterns used across Blade templates, organized by category. No duplicates.

---

## HTML BLOCKS

### Layout Structures

```html
<!-- Base layout wrapper -->
<div class="container">
  <h2>Admin Panel</h2>
  <div class="stat-cards">
    <div class="stat-card"><div class="stat-value">{count}</div><div class="stat-label">Total Users</div></div>
    <div class="stat-card"><div class="stat-value">{count}</div><div class="stat-label">Pending Approval</div></div>
  </div>
</div>
```

```html
<!-- Tab navigation -->
<div class="tabs mobile-tabs-wrapper">
  <button class="tab active" data-tab="tab-pending" onclick="switchTab(event, 'tab-pending')">Pending Accounts</button>
  <button class="tab" data-tab="tab-users" onclick="switchTab(event, 'tab-users')">All Users</button>
  <button class="tab" data-tab="tab-reports" onclick="switchTab(event, 'tab-reports')">Reports</button>
  <button class="tab" data-tab="tab-skills" onclick="switchTab(event, 'tab-skills')">Skills</button>
  <button class="tab" data-tab="tab-logs" onclick="switchTab(event, 'tab-logs')">Logs</button>
</div>
```

```html
<!-- Tab panel -->
<div id="tab-users" class="tab-panel">
  <h3>All Users</h3>
  <a href="{route}" class="btn btn-primary btn-sm" style="margin-bottom:16px;">Add Student Manually</a>
  <div class="table-wrap">
    <table>
      <thead><tr>...</tr></thead>
      <tbody>...</tbody>
    </table>
  </div>
</div>
```

```html
<!-- Cards grid layout -->
<div class="cards-grid">
  @foreach ($items as $item)
    <div class="card">
      <div class="card-title">{{ $item->Title }}</div>
      <div class="card-subtitle">{{ $item->Description }}</div>
      <p>{{ Str::limit($item->Content, 120) }}</p>
      <div style="margin-top:10px;">
        <span class="badge badge-{status}">{status}</span>
      </div>
    </div>
  @endforeach
</div>
```

```html
<!-- User card with badge and actions -->
<div class="card">
  <div class="card-title">{{ $p->Full_Name }}</div>
  <div class="card-subtitle">{{ $skills }}</div>
  <div style="margin:8px 0;">
    <div style="font-size:0.85rem; color:var(--muted);">
      Rating: {rating}/5 · Completed: {count} · {verified}
    </div>
  </div>
  <div style="margin-top:14px;">
    <a href="{route}" class="btn btn-secondary btn-sm">View Profile</a>
    <a href="{route}" class="btn btn-primary btn-sm">Message</a>
  </div>
</div>
```

```html
<!-- Skill card -->
<div class="skill-card">
  <div class="skill-name">{{ $s->Skill_Title }}</div>
  <button type="button" class="btn btn-secondary btn-sm btn-added" disabled>Added</button>
  <button type="button" onclick="openProficiencyModal(...)" class="btn btn-success btn-sm skill-btn">Add</button>
</div>
```

```html
<!-- Provider profile card -->
<div class="card" style="max-width:600px;">
  <div style="display:flex; align-items:center; gap:16px;">
    <img src="{avatar}" alt="{name}" style="width:80px; height:80px; border-radius:50%;">
    <div>
      <h3 style="margin:0 0 4px; text-align:center; color:var(--primary);">{name}</h3>
      <h3 style="margin-top:0;">Name & Nickname</h3>
    </div>
  </div>
</div>
```

### Forms

```html
<!-- Search form with filters -->
<form method="GET" action="{route}" style="margin-bottom:24px;">
  <div class="card" style="margin-bottom:16px;">
    <div style="display:flex; gap:24px; flex-wrap:wrap; align-items:center; margin-top:16px;">
      <div>
        <strong>Service Mode</strong>
        <div style="display:flex; gap:12px; margin-top:6px;">
          <label><input type="radio" name="service_mode" value=""> All</label>
          <label><input type="radio" name="service_mode" value="Remote"> Remote</label>
          <label><input type="radio" name="service_mode" value="Face-to-Face"> Face-to-Face</label>
          <label><input type="radio" name="service_mode" value="Hybrid"> Hybrid</label>
        </div>
      </div>
    </div>
    <input type="text" name="keyword" placeholder="Search by title, description, skill, or name…">
    <button type="submit" class="btn btn-primary">Search</button>
    <a href="{route}" class="btn btn-secondary">Reset</a>
  </div>
</form>
```

```html
<!-- Request creation form -->
<form method="POST" action="{route}" enctype="multipart/form-data">
  @csrf
  <input type="text" name="title" placeholder="Title" required>
  <textarea name="description" placeholder="Description" required></textarea>
  <select name="skill_ids[]" multiple required>
    @foreach ($skills as $s)
      <option value="{{ $s->Skill_ID }}">{{ $s->Skill_Title }}</option>
    @endforeach
  </select>
  <label><input type="radio" name="service_mode" value="Remote" checked> Remote</label>
  <label><input type="radio" name="service_mode" value="Face-to-Face"> Face-to-Face</label>
  <label><input type="radio" name="service_mode" value="Hybrid"> Hybrid</label>
  <button type="submit" class="btn btn-primary">Post Request</button>
</form>
```

```html
<!-- Login form -->
<form method="POST" action="{route}">
  @csrf
  <div class="form-group">
    <input type="email" name="email" placeholder="Email" required autofocus>
  </div>
  <div class="form-group password-field">
    <input type="password" name="password" placeholder="Password" required>
    <button type="button" class="password-toggle">👁</button>
  </div>
  <button type="submit" class="btn btn-primary">Log In</button>
</form>
```

```html
<!-- Registration form -->
<form method="POST" action="{route}">
  @csrf
  <input type="text" name="name" placeholder="Full Name" required>
  <input type="email" name="email" placeholder="Email" required>
  <div class="password-field">
    <input type="password" name="password" placeholder="Password" required>
    <button type="button" class="password-toggle">👁</button>
  </div>
  <div class="password-field">
    <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
    <button type="button" class="password-toggle">👁</button>
  </div>
  <select name="role" required>
    <option value="Student">Student</option>
    <option value="Faculty">Faculty</option>
    <option value="Staff">Staff</option>
  </select>
  <select name="council" required>
    <option value="HBM">HBM</option>
    <option value="CSC">CSC</option>
    <option value="BIT">BIT</option>
    <option value="EDUC">EDUC</option>
    <option value="Unaffiliated">Unaffiliated</option>
  </select>
  <button type="submit" class="btn btn-primary">Sign Up</button>
</form>
```

```html
<!-- Profile edit form -->
<form method="POST" action="{route}" enctype="multipart/form-data">
  @csrf
  <div style="display:flex; gap:16px; align-items:center;">
    <img src="{avatar}" style="width:80px; height:80px; border-radius:50%;">
    <input type="file" name="profile_picture" accept="image/*">
  </div>
  <input type="text" name="Full_Name" value="{name}" required>
  <textarea name="Bio">{bio}</textarea>
  <input type="text" name="Headline" value="{headline}" placeholder="e.g. Full-stack Laravel Developer">
  <input type="number" name="Hourly_Rate" value="{rate}" placeholder="e.g. 1000">
  <select name="Service_Modes[]" multiple>
    @foreach ($serviceModes as $mode)
      <option value="{{ $mode }}" {{ in_array($mode, $user->Service_Modes ?? []) ? 'selected' : '' }}>{{ $mode }}</option>
    @endforeach
  </select>
  <button type="submit" class="btn btn-primary">Save Changes</button>
</form>
```

```html
<!-- Skill proficiency rating -->
<form method="POST" action="{route}">
  @csrf
  <input type="hidden" name="skill_id" value="{id}">
  <h3>Rate your skill level</h3>
  <div class="proficiency-options">
    <label><input type="radio" name="level" value="Beginner" required> Beginner</label>
    <label><input type="radio" name="level" value="Intermediate"> Intermediate</label>
    <label><input type="radio" name="level" value="Advanced"> Advanced</label>
  </div>
  <button type="submit" class="btn btn-primary">Save</button>
</form>
```

```html
<!-- Review form -->
<form method="POST" action="{route}">
  @csrf
  <h2>Write a Review</h2>
  <div class="proficiency-options">
    <h3>Rating</h3>
    @for ($i = 1; $i <= 5; $i++)
      <label><input type="radio" name="rating" value="{{ $i }}" required> {{ $i }} Star{{ $i > 1 ? 's' : '' }}</label>
    @endfor
  </div>
  <textarea name="comment" placeholder="Your review (optional)"></textarea>
  <button type="submit" class="btn btn-primary">Submit Review</button>
</form>
```

```html
<!-- Report user form -->
<form method="POST" action="{route}">
  @csrf
  <input type="hidden" name="reported_user_id" value="{id}">
  <textarea name="reason" placeholder="What happened?" required></textarea>
  <button type="submit" class="btn btn-danger">Report</button>
</form>
```

```html
<!-- Admin verification form -->
<form method="post" action="{route}" style="display:flex;flex-direction:column;gap:8px;margin-top:12px;">
  @csrf
  <input type="hidden" name="user_id" value="{id}">
  <div style="display:flex;gap:8px;">
    <button type="submit" name="decision" value="approve" class="btn btn-success btn-sm">Approve</button>
    <button type="submit" name="decision" value="reject" class="btn btn-danger btn-sm">Reject</button>
  </div>
  <input type="text" name="rejection_reason" placeholder="Rejection reason (optional)">
</form>
```

```html
<!-- Admin skill add form -->
<form method="post" action="{route}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:end;">
  @csrf
  <input type="text" name="skill_title" placeholder="Skill" required>
  <input type="text" name="category" placeholder="Category" value="General">
  <input type="text" name="subcategory" placeholder="Subcategory (optional)">
  <button type="submit" class="btn btn-primary btn-sm">Add</button>
</form>
```

### Tables

```html
<!-- User table -->
<table>
  <thead>
    <tr>
      <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Council</th>
      <th>Rating</th><th>Completed</th><th>Status</th><th>Verified</th><th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>{id}</td>
      <td>{name}</td>
      <td>{email}</td>
      <td><span class="badge badge-{role}">{role}</span></td>
      <td>{council}</td>
      <td>{rating}</td>
      <td><td><span class="badge badge-{status}">{status}</span></td>
      <td>{verified}</td>
      <td style="display:inline-flex;gap:6px;flex-wrap:wrap;">
        <a href="{route}" class="btn btn-sm btn-secondary">Edit</a>
        <form method="post" action="{route}" style="display:inline;" onsubmit="return confirm('Delete this user?');">
          <button type="submit" class="btn btn-sm btn-danger">Delete</button>
        </form>
      </td>
    </tr>
  </tbody>
</table>
```

```html
<!-- Reports table -->
<table>
  <thead>
    <tr>
      <th>Reporter</th><th>Reported</th><th>Violations</th>
      <th>Account</th><th>Reason</th><th>Report</th><th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>{reporter}</td>
      <td>{reported}</td>
      <td>{count}</td>
      <td><span class="badge badge-{status}">{status}</span></td>
      <td>{reason}</td>
      <td><span class="badge badge-{status}">{status}</span></td>
      <td>
        <form method="post" action="{route}" style="display:inline;">
          <button type="submit" name="approve" value="Dismiss" class="btn btn-sm btn-secondary">Dismiss</button>
          <button type="submit" name="approve" value="Action_Taken" class="btn btn-sm btn-danger">Escalate</button>
        </form>
      </td>
    </tr>
  </tbody>
</table>
```

```html
<!-- Skills table -->
<table>
  <thead><tr><th>ID</th><th>Title</th><th>Category</th><th>Subcategory</th></tr></thead>
  <tbody>
    <tr>
      <td>{id}</td>
      <td>{title}</td>
      <td>{category}</td>
      <td>{subcategory}</td>
    </tr>
  </tbody>
</table>
```

```html
<!-- Audit logs table -->
<table>
  <thead><tr><th>Admin</th><th>Action</th><th>Details</th><th>Time</th></tr></thead>
  <tbody>
    <tr>
      <td>{admin_id}</td>
      <td>{action}</td>
      <td>{details}</td>
      <td>{time}</td>
    </tr>
  </tbody>
</table>
```

```html
<!-- Matches table -->
<table>
  <thead><tr><th>Date</th><th>Request</th><th>Provider</th><th>Status</th><th>Actions</th></tr></thead>
  <tbody>
    <tr>
      <td>{date}</td>
      <td>{request_title}</td>
      <td>{provider_name}</td>
      <td><span class="badge badge-{status}">{status}</span></td>
      <td><a href="{route}" class="btn btn-sm btn-secondary">View</a></td>
    </tr>
  </tbody>
</table>
```

### Notifications & Alerts

```html
<!-- Success alert -->
<div class="alert alert-success">{message}</div>
```

```html
<!-- Error alert -->
<div class="alert alert-danger">{message}</div>
```

```html
<!-- Info alert -->
<div class="alert alert-info">{message}</div>
```

```html
<!-- Empty state -->
<div class="alert alert-info">Found Nothing Try Again Later</div>
<div class="alert alert-info">No pending accounts.</div>
<div class="alert alert-info">No conversations yet.</div>
```

### Badges

```html
<!-- Status badges -->
<span class="badge badge-open">{status}</span>
<span class="badge badge-accepted">{status}</span>
<span class="badge badge-pending">{status}</span>
<span class="badge badge-rejected">{status}</span>
<span class="badge badge-banned">{status}</span>
<span class="badge badge-active">{status}</span>
<span class="badge badge-suspended">{status}</span>
```

```html
<!-- Role badges -->
<span class="badge badge-student">{role}</span>
<span class="badge badge-faculty">{role}</span>
<span class="badge badge-staff">{role}</span>
<span class="badge badge-admin">{role}</span>
```

```html
<!-- Verification badge -->
<span class="badge badge-accepted">Verified</span>
```

```html
<!-- Service mode badges -->
<span class="badge badge-remote">Remote</span>
<span class="badge badge-faceto-face">Face-to-Face</span>
<span class="badge badge-hybrid">Hybrid</span>
```

### Buttons

```html
<!-- Action buttons -->
<a href="{route}" class="btn btn-primary">Search</a>
<a href="{route}" class="btn btn-secondary">Reset</a>
<button type="submit" class="btn btn-primary">Post Request</button>
<button type="submit" class="btn btn-primary">Submit Review</button>
<button type="submit" class="btn btn-danger">Report</button>
<button type="submit" class="btn btn-success btn-sm">Approve</button>
<button type="submit" class="btn btn-danger btn-sm">Reject</button>
<button type="submit" class="btn btn-primary btn-sm">Add</button>
<a href="{route}" class="btn btn-secondary btn-sm">View Profile</a>
<a href="{route}" class="btn btn-primary btn-sm">Message</a>
<a href="{route}" class="btn btn-secondary btn-sm">Edit</a>
<button type="submit" class="btn btn-sm btn-danger">Delete</button>
```

### Headings

```html
<h1>Welcome Back</h1>
<h1>Create Account</h1>
<h1 style="font-size:2.6rem; margin:0 0 16px; line-height:1.2;">Skill Matching System</h1>
<h1 style="margin:0 0 8px;">Welcome back, User</h1>
```

```html
<h2>Search</h2>
<h2>Skills</h2>
<h2>Settings</h2>
<h2>Skill Requests</h2>
<h2>Edit Request</h2>
<h2>Profile</h2>
<h2>Edit Profile</h2>
<h2>Student Dashboard</h2>
<h2>Notifications</h2>
<h2>Messages</h2>
<h2>Analytics Dashboard</h2>
<h2>Admin Panel</h2>
<h2>Match Details</h2>
<h2>Matches</h2>
<h2>Add Student Manually</h2>
<h2>Write a Review</h2>
<h2>Reviews & Reports</h2>
```

```html
<h3>All Users</h3>
<h3>Unverified Accounts</h3>
<h3>Reports</h3>
<h3>Skills</h3>
<h3>Audit Logs</h3>
<h3>Available Requests</h3>
<h3 style="margin-bottom:12px;">Search Requests</h3>
<h3 style="margin:24px 0 12px;">Skill Providers</h3>
<h3>Reviews About Me</h3>
<h3>Rate your skill level</h3>
<h3>Recent Requests</h3>
<h3>Quick Links</h3>
<h3>For You</h3>
```

### Pagination

```html
<div class="pagination">
  {links}
</div>
<!-- Uses: $results->links('pagination::bootstrap-4') -->
```

### SVG Icons

```html
<!-- Password toggle eye icon -->
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s8-8 11-8 11 8 11 8-8 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>

<!-- Password toggle eye-off icon -->
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A4.07 4.07 0 0112 19c-3.35 0-6-2.65-6-6 0-1.06.39-2.04 1.06-2.81L5.59 7.59A7.93 7.93 0 004 12c0 1.66.78 3.14 2.05 4.06L4 18.17"></path><path d="M9.86 9.86A2.25 2.25 0 1012 9.86"></path><path d="M1 1l22 22"></path></svg>
```

### Other Elements

```html
<!-- Avatar image -->
<img src="{avatar}" alt="{name}" style="width:80px; height:80px; border-radius:50%;">

<!-- Stat card -->
<div class="stat-card"><div class="stat-value">{value}</div><div class="stat-label">{label}</div></div>

<!-- File upload -->
<input type="file" name="profile_picture" accept="image/*">

<!-- Textarea -->
<textarea name="description" placeholder="Description" required></textarea>
<textarea name="Bio">{bio}</textarea>
<textarea name="Headline" placeholder="e.g. Full-stack Laravel Developer"></textarea>
<textarea name="reason" placeholder="What happened?" required></textarea>
<textarea name="comment" placeholder="Your review (optional)"></textarea>
<textarea name="rejection_reason" placeholder="Rejection reason (optional)"></textarea>

<!-- Hidden inputs -->
<input type="hidden" name="user_id" value="{id}">
<input type="hidden" name="report_id" value="{id}">
<input type="hidden" name="skill_id" value="{id}">
<input type="hidden" name="reported_user_id" value="{id}">
<input type="hidden" name="role" value="Student">
```
