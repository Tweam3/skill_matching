# Code Reference — Skill Matching System

Unique code patterns organized by language. Each block includes what it does. No duplicates.

---

## HTML

### Layout Structures

```html
<div class="container">
  <h2>Admin Panel</h2>
  <div class="stat-cards">
    <div class="stat-card"><div class="stat-value">{count}</div><div class="stat-label">Total Users</div></div>
    <div class="stat-card"><div class="stat-value">{count}</div><div class="stat-label">Pending Approval</div></div>
  </div>
</div>
```
**Purpose:** Main page wrapper with stat cards at the top showing key metrics (user counts). Used on dashboard/admin pages for at-a-glance overview.

```html
<div class="tabs mobile-tabs-wrapper">
  <button class="tab active" data-tab="tab-pending" onclick="switchTab(event, 'tab-pending')">Pending Accounts</button>
  <button class="tab" data-tab="tab-users" onclick="switchTab(event, 'tab-users')">All Users</button>
  ...
</div>
<div id="tab-users" class="tab-panel">...</div>
```
**Purpose:** Tabbed navigation interface for switching between different admin sections. `switchTab()` hides all panels and shows the selected one. Mobile-responsive with active tab tracking.

```html
<div class="cards-grid">
  @foreach ($items as $item)
    <div class="card">
      <div class="card-title">{{ $item->Title }}</div>
      <div class="card-subtitle">{{ $item->Description }}</div>
      <p>{{ Str::limit($item->Content, 120) }}</p>
    </div>
  @endforeach
</div>
```
**Purpose:** CSS grid layout for displaying items as cards. Auto-fits columns from 280px, 20px gap, used for search results, providers, and skills.

```html
<div class="card">
  <div class="card-title">{{ $p->Full_Name }}</div>
  <div class="card-subtitle">{{ $skills }}</div>
  <div style="margin:8px 0;">
    <div style="font-size:0.85rem; color:var(--muted);">
      Rating: {rating}/5 · Completed: {count} · {verified}
    </div>
  </div>
</div>
```
**Purpose:** Provider card displaying name, skills, rating, completion count, and verification status. Uses badge system for visual status indicators.

### Forms

```html
<form method="POST" action="{route}" enctype="multipart/form-data">
  @csrf
  @method('PUT')
  <input type="text" name="field" value="{{ old('field', $model->field) }}" required>
  <button type="submit" class="btn btn-primary">Save</button>
</form>
```
**Purpose:** Standard form pattern with CSRF protection, method spoofing (for PUT/PATCH/DELETE), old input retention, and validation. Used for all create/update operations.

```html
<form method="POST" action="{route}" style="display:inline;" onsubmit="return confirm('Delete this user?');">
  @csrf
  <input type="hidden" name="user_id" value="{id}">
  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
</form>
```
**Purpose:** Inline destructive action form with JavaScript confirmation dialog. Red button signals danger.

```html
<form method="POST" action="{route}">
  @csrf
  <input type="hidden" name="user_id" value="{id}">
  <button type="submit" name="decision" value="approve" class="btn btn-success btn-sm">Approve</button>
  <button type="submit" name="decision" value="reject" class="btn btn-danger btn-sm">Reject</button>
</form>
```
**Purpose:** Dual-action form for content moderation. Single form with two submit buttons that POST different values to the same endpoint.

### Tables

```html
<div class="table-wrap">
  <table>
    <thead><tr><th>ID</th><th>Name</th><th>Email</th>...</tr></thead>
    <tbody>
      @foreach ($users as $u)
        <tr>
          <td>{{ $u->User_ID }}</td>
          <td>{{ $u->Full_Name }}</td>
          <td><span class="badge badge-{role}">{role}</span></td>
          ...
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
```
**Purpose:** Responsive table wrapped in `.table-wrap` for horizontal overflow on mobile. Uses badge classes for status/role columns. Standard pattern for admin data tables.

### Badges

```html
<span class="badge badge-open">{status}</span>
<span class="badge badge-accepted">{status}</span>
<span class="badge badge-pending">{status}</span>
<span class="badge badge-rejected">{status}</span>
```
**Purpose:** Color-coded status indicators using CSS class-based color scheme. Maps status values to visual colors (green=success, red=failure, yellow=pending, blue=info).

```html
<span class="badge badge-student">{role}</span>
<span class="badge badge-faculty">{role}</span>
<span class="badge badge-admin">{role}</span>
```
**Purpose:** Role-based badge styling for user roles. Different colors per role type.

### Buttons

```html
<a href="{route}" class="btn btn-primary">Primary Action</a>
<a href="{route}" class="btn btn-secondary">Secondary Action</a>
<button type="submit" class="btn btn-danger">Delete</button>
```
**Purpose:** Consistent button styling across all variants. `btn-primary` for main actions, `btn-secondary` for alternatives, `btn-danger` for destructive actions.

### Headings

```html
<h1>Welcome Back</h1>
<h2>Admin Panel</h2>
<h3>All Users</h3>
<h4>Rate your skill level</h4>
```
**Purpose:** Hierarchical heading structure. `h1` for page-level, `h2` for section headers, `h3` for sub-sections, `h4` for labels within cards/modals.

---

## JavaScript

### DOMContentLoaded Patterns

```javascript
document.addEventListener('DOMContentLoaded', function () {
  const picker = document.querySelector('.skill-picker');
  if (!picker) return;
  // ... skill picker logic
});
```
**Purpose:** Page load event listener with null guard. Ensures DOM is fully loaded before manipulating elements, and skips execution if target element doesn't exist.

```javascript
document.addEventListener('DOMContentLoaded', function () {
  var hash = window.location.hash.replace('#', '');
  if (hash) {
    switchSettingsTab(new Event('click'), hash);
  }
});
```
**Purpose:** Tab routing via URL hash. Opens the correct settings tab based on `#section` in the URL on page load.

### Modal Patterns

```javascript
function openProficiencyModal(skillId, skillName, btn) {
  proficiencyModalSkillId = skillId;
  proficiencyModalBtn = btn;
  document.getElementById('proficiency-modal-skill-id').value = skillId;
  document.getElementById('proficiency-modal-skill-name').textContent = skillName;
  document.getElementById('proficiency-modal-overlay').classList.add('active');
  updateStarDisplay(3);
}

function closeProficiencyModal() {
  document.getElementById('proficiency-modal-overlay').classList.remove('active');
}
```
**Purpose:** Modal lifecycle management. Opens/closes the skill proficiency modal, sets hidden form values, and initializes the star rating to "Competent" (level 3).

```html
onclick="if(event.target===this)closeProficiencyModal()"
```
**Purpose:** Overlay click-to-close. Only closes if the user clicks the overlay itself (not the modal content), using event target comparison.

### Star Rating

```javascript
function setProficiency(value) {
  document.getElementById('proficiency-modal-value').value = value;
  updateStarDisplay(value);
}

function updateStarDisplay(value) {
  var stars = document.querySelectorAll('#proficiency-modal-stars .star');
  stars.forEach(function(star, index) {
    star.classList.toggle('active', index < value);
  });
}
```
**Purpose:** Interactive star rating system. Sets hidden input value and visually highlights stars up to the selected level.

### Toggle Patterns

```javascript
function toggleForm() {
  const c = document.getElementById('form-card');
  c.style.display = c.style.display === 'none' ? 'block' : 'none';
}
```
**Purpose:** Simple show/hide toggle for form cards (e.g., "Add New Request" form).

```javascript
function toggleCategory(slug) {
  var card = document.getElementById('card-' + slug);
  if (card.classList.contains('expanded')) {
    card.classList.remove('expanded');
  } else {
    card.classList.add('expanded');
  }
}
```
**Purpose:** Accordion-style toggle for skill category groups. Expands/collapses skill lists under category headers.

```javascript
function switchTab(event, tabId) {
  document.querySelectorAll('.tab-panel').forEach(el => el.style.display = 'none');
  document.getElementById(tabId).style.display = 'block';
  event.currentTarget.classList.add('active');
}
```
**Purpose:** Tab navigation. Hides all panels, shows selected one, and updates active state on tab buttons.

### Fetch / AJAX

```javascript
fetch('{{ route('profile.skill.remove') }}', {
  method: 'POST',
  headers: {
    'X-CSRF-TOKEN': '{{ csrf_token() }}',
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json',
  },
  body: JSON.stringify({ skill_id: skillId, user_id: authUserId }),
})
.then(r => r.ok ? r.json() : Promise.reject('Remove failed'))
.then(data => { /* update UI */ })
.catch(err => { /* handle error */ });
```
**Purpose:** AJAX skill removal. Sends POST with CSRF token as JSON, handles success (updates button state) and errors (alerts user).

```javascript
var formData = new FormData(form);
fetch(form.action, {
  method: 'POST',
  body: formData,
  headers: { 'X-Requested-With': 'XMLHttpRequest' }
})
.then(r => r.ok ? r.json() : r.text().then(t => { throw new Error(t); }))
.then(data => { /* update UI */ })
```
**Purpose:** AJAX form submission using FormData for file uploads. No explicit Content-Type header so the browser sets multipart/form-data automatically.

```javascript
function pollMessages() {
  fetch('/messages?with=' + withId + '&since=' + lastMessageId + '&ajax=1')
    .then(r => r.ok ? r.json() : Promise.reject('Network error'))
    .then(data => { /* append new messages */ })
}
setInterval(pollMessages, 3000);
```
**Purpose:** Long-polling pattern for real-time messages. Fetches new messages every 3 seconds since the last message ID.

### Event Listeners

```javascript
document.addEventListener('click', function(e) {
  const btn = e.target.closest('.password-toggle');
  if (!btn) return;
  const input = btn.closest('.password-field').querySelector('input');
  input.type = input.type === 'password' ? 'text' : 'password';
});
```
**Purpose:** Global password visibility toggle. Uses event delegation to handle dynamically added password fields. Toggles input type between password and text.

```javascript
toggleBtn.addEventListener('click', function () {
  categoriesSection.style.display = categoriesSection.style.display === 'none' ? 'block' : 'none';
  toggleBtn.textContent = toggleBtn.textContent === 'Show Categories' ? 'Hide Categories' : 'Show Categories';
});
```
**Purpose:** Expand/collapse category filters in search. Toggles visibility and button text.

### Image Manipulation (Profile Picture)

```javascript
const fileInput = document.getElementById('picture-input');
fileInput.addEventListener('change', (e) => {
  const file = e.target.files[0];
  const reader = new FileReader();
  reader.onload = (ev) => {
    previewImg.src = ev.target.result;
    // Reset zoom/pan state
  };
  reader.readAsDataURL(file);
});
```
**Purpose:** Client-side image preview. Reads uploaded file as data URL for instant preview without server round-trip.

```javascript
previewBox.addEventListener('mousedown', (e) => {
  isDragging = true;
  startX = e.clientX - panX;
  startY = e.clientY - panY;
});
window.addEventListener('mousemove', (e) => {
  if (!isDragging) return;
  panX = e.clientX - startX;
  panY = e.clientY - startY;
  updateTransform();
});
window.addEventListener('mouseup', () => { isDragging = false; });
```
**Purpose:** Mouse-based drag-to-pan for image cropping. Tracks mouse position to translate the image within the preview area.

```javascript
previewBox.addEventListener('touchstart', (e) => {
  e.preventDefault();
  isDragging = true;
  startX = e.touches[0].clientX - panX;
}, { passive: false });
window.addEventListener('touchmove', (e) => {
  if (!isDragging) return;
  e.preventDefault();
  panX = e.touches[0].clientX - startX;
  updateTransform();
}, { passive: false });
```
**Purpose:** Mobile touch support for image drag-to-pan. Mirrors mouse behavior but uses touch events. `passive: false` allows `preventDefault()` to prevent scrolling.

```javascript
const size = 400;
const canvas = document.createElement('canvas');
canvas.width = size;
canvas.height = size;
const ctx = canvas.getContext('2d');
ctx.drawImage(previewImg, panX * ratio, panY * ratio, previewImg.naturalWidth * scale * ratio, previewImg.naturalHeight * scale * ratio);
canvas.toBlob((blob) => {
  const dt = new DataTransfer();
  dt.items.add(new File([blob], 'adjusted.png', { type: 'image/png' }));
  input.files = dt.files;
  form.submit();
}, 'image/png');
```
**Purpose:** Crop and resize profile picture on client side. Draws the visible portion of the image onto a 400x400 canvas, converts to blob, replaces the file input value, and submits the form.

### Dark Mode

```javascript
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
```
**Purpose:** Theme toggle with persistence. Sets `data-theme="dark"` attribute on root element, triggers CSS theme switch, and saves preference to localStorage.

### Message Rendering

```javascript
function appendMessage(message) {
  const isSent = message.Sender_ID == authId;
  const row = document.createElement('div');
  row.className = 'message-row ' + (isSent ? 'sent' : '');
  row.setAttribute('data-message-id', message.Message_ID);
  row.innerHTML = '<div class="message-bubble">...{message.Message_Text}...</div>';
  chatContainer.appendChild(row);
  seenMessageIds.add(parseInt(message.Message_ID));
  scrollToBottom();
}
```
**Purpose:** Dynamically creates and appends a new message to the chat view. Different styling for sent vs received messages (right-aligned for sent).

### Skill Picker

```javascript
function updateChips() {
  const checked = picker.querySelectorAll('input[name="skill_ids[]"]:checked');
  chipsContainer.innerHTML = '';
  checked.forEach(function (cb) {
    const chip = document.createElement('span');
    chip.className = 'skill-chip';
    chip.innerHTML = name + ' <button type="button" data-id="' + cb.value + '">×</button>';
    chipsContainer.appendChild(chip);
  });
}
```
**Purpose:** Updates the selected skills display (chips) when checkbox selection changes. Each chip shows skill name and a remove button.

```javascript
function filterSkills() {
  const term = searchInput.value.trim().toLowerCase();
  skillGroups.forEach(function (group) {
    let groupHasVisible = false;
    group.querySelectorAll('.skill-option').forEach(function (opt) {
      const match = opt.textContent.toLowerCase().includes(term);
      opt.style.display = match ? 'flex' : 'none';
    });
    group.style.display = groupHasVisible ? 'block' : 'none';
  });
}
```
**Purpose:** Client-side search filtering within the skill picker. Hides non-matching options and empty groups, shows empty state if nothing matches.

---

## CSS

### Variables

```css
:root {
  --primary: #002147;
  --gold: #FFC72C;
  --bg: #F0F4F8;
  --surface: #FFFFFF;
  --text: #1E293B;
  --muted: #64748B;
  --danger: #DC2626;
  --success: #16A34A;
  --warning: #F59E0B;
}
```
**Purpose:** Design token system. All color values are CSS custom properties for consistent theming. Dark mode overrides these same variables.

```css
[data-theme="dark"] {
  --bg: #1b1e24;
  --surface: #23262e;
  --text: #d4d4d8;
  --primary: #6cb6ff;
}
```
**Purpose:** Dark theme override. Activates when `data-theme="dark"` is set on the root element by the JS toggle function.

### Layout

```css
.container { flex:1; max-width:1200px; margin:0 auto; padding:24px; }
.cards-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:20px; }
.stat-cards { display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:16px; }
```
**Purpose:** Responsive grid layouts. `auto-fit` with `minmax` creates fluid columns that collapse to single column on mobile.

### Buttons

```css
.btn {
  display:inline-flex; align-items:center; justify-content:center; gap:8px;
  padding:10px 20px; border:none; border-radius:8px; font-size:0.95rem;
  font-weight:600; cursor:pointer; text-decoration:none; transition:background 0.2s;
}
.btn-primary { background:var(--primary); color:#fff; }
.btn-primary:hover { background:var(--primary-hover); }
```
**Purpose:** Base button styling with hover transitions. All button variants share the base `.btn` class and override only the background color.

### Badges

```css
.badge { display:inline-block; padding:4px 10px; border-radius:20px; font-size:0.78rem; font-weight:600; }
.badge-open { background:var(--blue-light); color:var(--primary); }
.badge-accepted { background:#D1FAE5; color:#065F46; }
.badge-pending { background:#F3F4F6; color:#374151; }
.badge-rejected { background:#FEE2E2; color:#991B1B; }
```
**Purpose:** Pill-shaped status indicators. Each status maps to a specific color palette (green=success, red=error, gray=pending).

### Forms

```css
.form-group input, .form-group select, .form-group textarea {
  width:100%; padding:10px 12px; border:2px solid #E5E7EB; border-radius:8px;
  font-size:0.95rem; transition: border-color 0.2s; background:#fff;
}
.form-group input:focus { outline:none; border-color:var(--primary); }
```
**Purpose:** Consistent form field styling with focus states. Blue border on focus provides accessibility feedback.

### Messages

```css
.message-row { display:flex; gap:12px; margin-bottom:16px; }
.message-row.sent { flex-direction:row-reverse; }
.message-text { padding:12px 18px; border-radius:18px; font-size:0.92rem; }
.message-row:not(.sent) .message-text { background:var(--surface); border:1px solid #E5E7EB; border-bottom-left-radius:5px; }
.message-row.sent .message-text { background:var(--primary); color:#fff; border-bottom-right-radius:5px; }
```
**Purpose:** Chat message bubble styling. Sent messages are right-aligned with primary color background. Received messages are left-aligned with surface background.

### Modals

```css
.report-modal-overlay {
  display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55);
  z-index:200; align-items:center; justify-content:center; padding:20px;
}
.report-modal-overlay.active { display:flex; }
```
**Purpose:** Full-screen overlay for modal dialogs. Hidden by default, becomes centered flexbox when `.active` class is added.

### Star Rating

```css
.star-rating .star {
  font-size:2rem; color:#D1D5DB; cursor:pointer;
  transition:color 0.2s, transform 0.2s;
}
.star-rating .star:hover,
.star-rating .star.active {
  color:#F59E0B; transform:scale(1.1);
}
```
**Purpose:** Interactive star rating hover/active states. Stars glow amber and grow slightly on hover/active.

### Responsive Breakpoints

```css
@media (max-width:640px) {
  .navbar-inner { height:auto; padding:8px 0; flex-wrap:wrap; }
  .navbar-toggle { display:block; }
  .navbar-links { display:none; }
  .navbar-links.is-open { display:flex; }
  .cards-grid { grid-template-columns:1fr; }
}
```
**Purpose:** Mobile-first responsive design. At 640px, navbar becomes a hamburger menu, grids collapse to single column, and padding is reduced.

```css
@media (max-width:480px) {
  .stat-cards { grid-template-columns:repeat(2,1fr); }
  .navbar-links a { padding:6px 8px; font-size:0.78rem; }
}
```
**Purpose:** Ultra-mobile optimization. At 480px, stat cards use 2-column layout and navbar links get more compact padding.

---

## PHP

### Controller: Basic Index with Data Loading

```php
public function index()
{
    $users = User::all();
    $pendingUsers = User::where('Is_Verified', false)->get();
    $skills = Skill::orderBy('Category')->orderBy('Skill_Title')->get();

    return view('admin.index', compact('users', 'pendingUsers', 'skills'));
}
```
**Purpose:** Loads all data needed for the admin panel view. Fetches users, pending verifications, and skills. Passes everything to the Blade template via `compact()`.

### Controller: Validation + Create with try/catch

```php
public function registerUser(Request $request)
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,Email'],
        'password' => ['required', 'string', 'min:8', 'confirmed',
            Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised()],
        'role' => ['required', 'in:Student,Faculty,Staff,Admin'],
        'council' => ['nullable', 'string', 'in:HBM,CSC,BIT,EDUC,Unaffiliated',
            'required_if:role,Student,Faculty'],
    ]);
    try {
        User::create([...]);
        $this->logAction('register_user', '...');
    } catch (\Exception $e) {
        Log::error('Admin register user failed', [...]);
        return back()->with('error', '...');
    }
    return back()->with('success', '...');
}
```
**Purpose:** Admin user registration with comprehensive validation. Password must meet complexity rules (8+ chars, mixed case, numbers, symbols, not compromised). Council required only for Student/Faculty roles. Try/catch prevents 500 errors and logs failures.

### Controller: DB Transaction for Related Deletes

```php
DB::transaction(function () use ($uid) {
    UserSkill::where('User_ID', $uid)->delete();
    Assignment::where('User_ID', $uid)->delete();
    Message::where('Sender_ID', $uid)->orWhere('Receiver_ID', $uid)->delete();
    // ... cascade deletes from all related tables
    User::where('User_ID', $uid)->delete();
});
```
**Purpose:** Atomic user deletion. Ensures all related records are removed in a single transaction — if any delete fails, the entire operation rolls back. This is data integrity protection.

### Controller: Login with Suspension Check

```php
$status = strtolower($user->Account_Status ?? 'active');
if ($status === 'suspended') {
    if ($user->Suspended_At && $user->Suspended_At->copy()->addDays(3)->isPast()) {
        $user->update(['Account_Status' => 'Active', 'Suspended_At' => null]);
    } else {
        return back()->withErrors(['email' => 'Account suspended.']);
    }
}
```
**Purpose:** 3-day suspension enforcement. Automatically re-activates accounts after 3 days by checking `Suspended_At + 3 days` against current time. Prevents suspended users from logging in before the period expires.

### Controller: CSV Export

```php
public function report(AnalyticsService $analytics)
{
    $metrics = $analytics->allMetrics();
    $csv = [];
    $csv[] = ['Summary', 'Total Service Requests', $metrics['total_requests']];
    // ... build CSV rows

    $headers = [
        'Content-type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="analytics-report.csv"',
    ];

    $callback = function () use ($csv) {
        $file = fopen('php://output', 'w');
        foreach ($csv as $row) { fputcsv($file, $row); }
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
```
**Purpose:** Downloads analytics data as a CSV file. Uses Laravel's `response()->stream()` to stream CSV directly to the browser without creating a temporary file.

### Controller: Search with Dynamic Query Building

```php
$searchIn = $request->input('search_in', 'requests');
$query = SkillRequest::where('Status', 'Open')->with(['skill', 'skills', 'user']);

if ($serviceMode) { $query->where('Service_Mode', $serviceMode); }
if (! empty($categories)) {
    $query->where(function ($q) use ($categories) {
        $q->whereIn('Skill_ID', Skill::whereIn('Category', $categories)->pluck('Skill_ID'))
          ->orWhereHas('skills', fn ($sq) => $sq->whereIn('Category', $categories));
    });
}

$results = $query->orderBy('Created_At', 'desc')->paginate(20)->withQueryString();
```
**Purpose:** Dynamic search query builder. Filters requests by service mode, categories, and keywords. Uses `whereHas` for relationship-based filtering. `withQueryString()` preserves filter state in pagination links.

### Model: Eloquent Relationships

```php
class User extends Authenticatable
{
    protected $fillable = ['Full_Name', 'Email', 'Password_Hash', 'Role', 'Service_Modes', ...];
    protected $casts = ['Service_Modes' => 'array', 'Is_Verified' => 'boolean', ...];

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'user_skills', 'User_ID', 'Skill_ID')
            ->withPivot('Proficiency');
    }
}
```
**Purpose:** Eloquent model with fillable fields (mass-assignment whitelist), casts (automatic type conversion), and relationships. `Service_Modes` is stored as JSON and cast to array automatically.

### Model: Custom Primary Key and Hidden Fields

```php
class User extends Authenticatable
{
    protected $primaryKey = 'User_ID';
    public $timestamps = false;
    protected $hidden = ['Password_Hash', 'remember_token'];
    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return $this->Full_Name;
    }
}
```
**Purpose:** Custom Eloquent configuration. Uses `User_ID` as primary key (not `id`), disables Laravel's automatic timestamps, hides sensitive fields from serialization, and adds a computed `name` accessor.

### Service: Recommender with Weighted Scoring

```php
protected function weightedScore(float $skill, float $category, float $serviceMode,
    float $profileTags, float $rating, float $profile): float
{
    $weights = $this->weights;
    $total = array_sum([
        $weights[self::WEIGHT_SKILL] ?? 0,      // 0.30
        $weights[self::WEIGHT_CATEGORY] ?? 0,   // 0.20
        $weights[self::WEIGHT_SERVICE_MODE] ?? 0, // 0.15
        $weights[self::WEIGHT_PROFILE_TAGS] ?? 0, // 0.15
        $weights[self::WEIGHT_RATING] ?? 0,     // 0.12
        $weights[self::WEIGHT_PROFILE] ?? 0,    // 0.08
    ]);

    return (($skill * 0.30) + ($category * 0.20) + ...) / $total;
}
```
**Purpose:** Composite match score calculation. Combines 6 weighted factors (skill overlap, category coverage, service mode, profile tags, rating, profile quality) into a single 0-1 score. Weights configured in `config/matching.php`.

### Service: Cold-Start Rule

```php
protected function ratingScore(User $provider): float
{
    $hasRatings = (int) $provider->Total_Completed > 0 && (float) $provider->Avg_Rating > 0;

    if (! $hasRatings) {
        $prior = config('matching.cold_start_prior_rating', 3.0);
        $normalized = $prior / $this->maxRating;
        return $normalized * 0.5;
    }
    // ... full rating score with confidence factor
}
```
**Purpose:** Cold-start problem handling. Unrated providers (0 completed) receive a neutral prior rating of 3.0 (configurable), which is penalized by 50% so they rank lower than proven providers but aren't completely excluded.

### Service: Service Mode Compatibility

```php
protected function serviceModeScore(SkillRequest $request, User $provider): float
{
    $providerModes = $provider->Service_Modes;
    if (empty($providerModes)) return 0.5;
    if (in_array($requestMode, $providerModes, true)) return 1.0;
    if ($requestMode === 'Hybrid' && (has Remote || has Face-to-Face)) return 1.0;
    return 0.0;
}
```
**Purpose:** Matches request service mode against provider's available modes. Exact match = 1.0, no provider modes set = 0.5 (neutral), Hybrid request is compatible with either Remote or Face-to-Face providers.

### Service: Evaluation Metrics

```php
public function evaluate(array $testCases, int $k = 5): array
{
    // For each test case, rank providers and compute:
    // Precision@K = relevant items in top K / K
    // Recall@K = relevant items in top K / total relevant
    // MRR = 1/rank of first relevant item
    // MAP = mean of average precision across all queries
}
```
**Purpose:** Recommender evaluation using standard IR metrics. Compares predicted rankings against ground-truth matches. Target: Precision@5 >= 0.80.

### Service: Analytics with Database Driver Detection

```php
$driver = DB::connection()->getDriverName();
$monthExpr = match ($driver) {
    'sqlite' => "strftime('%Y-%m', {$column})",
    'pgsql' => "to_char(\"{$column}\", 'YYYY-MM')",
    default => "DATE_FORMAT({$column}, '%Y-%m')",
};
```
**Purpose:** Database-agnostic date aggregation. PostgreSQL uses `to_char()` with quoted column names (case-sensitive), MySQL uses `DATE_FORMAT()`, SQLite uses `strftime()`. The `match` expression handles all three.

### Migration: Idempotent Index Creation

```php
private const INDEXES = [
    'user_skills' => ['User_ID', 'Skill_ID'],
    'matches' => ['Request_ID', 'Matched_User_ID'],
    ...
    'admin_action_logs' => ['Admin_ID'],
];

foreach ($INDEXES as $table => $columns) {
    if (! $this->indexExists($table, $indexName)) {
        Schema::table($table, fn (Blueprint $t) => $t->index($column, $indexName));
    }
}
```
**Purpose:** Creates database indexes safely. Checks if each index exists before creating it, preventing errors on partial migration runs or re-executions. Fixed `admin_action_logs` to use `Admin_ID` (not `User_ID`) to match the table schema.

### Migration: Table with Foreign Keys

```php
Schema::create('skill_requests', function (Blueprint $table) {
    $table->id('Request_ID');
    $table->foreignId('User_ID')->constrained('users', 'User_ID');
    $table->foreignId('Skill_ID')->constrained('skills', 'Skill_ID');
    $table->string('Status')->default('Open');
    $table->string('Service_Mode')->default('Remote');
    $table->timestamp('Created_At')->useCurrent();
});
```
**Purpose:** Creates table with UUID-style primary key (`id()` creates an auto-incrementing bigint), foreign key constraints to other tables, and default column values. Uses `useCurrent()` for automatic timestamp on creation.

### Middleware: Authorization Guard

```php
public function handle($request, Closure $next)
{
    if (!Auth::check()) return redirect()->route('login');

    $user = $request->user();
    $status = strtolower($user->Account_Status ?? 'active');

    if ($status === 'suspended') {
        if ($user->Suspended_At && $user->Suspended_At->copy()->addDays(3)->isPast()) {
            $user->update(['Account_Status' => 'Active', 'Suspended_At' => null]);
        } else {
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'Account suspended.']);
        }
    }

    if ($status === 'banned') {
        Auth::logout();
        return redirect()->route('login')->withErrors(['email' => 'Account banned.']);
    }

    return $next($request);
}
```
**Purpose:** Ensures only active, non-banned users can access protected routes. Enforces the 3-day suspension auto-recovery policy. Banned users are permanently blocked.

---

## Dockerfile

```dockerfile
FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev libpq-dev \
    zip unzip git
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql pdo_pgsql gd mbstring exif pcntl bcmath sockets

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . /var/www/html/
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
EXPOSE 8080
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
```
**Purpose:** Laravel application container image. Installs PHP 8.2 with required extensions (PostgreSQL, MySQL, GD image processing, mbstring). Uses multi-stage composer copy for dependency installation. Entry point script handles environment setup before serving.

## Shell Scripts

### Docker Entrypoint (`docker-entrypoint.sh`)

```bash
#!/bin/bash
set -e

if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Override DB config from environment
[ -n "$DB_CONNECTION" ] && sed -i "s/^DB_CONNECTION=.*/DB_CONNECTION=$DB_CONNECTION/" .env
[ -n "$DB_HOST" ] && sed -i "s/^DB_HOST=.*/DB_HOST=$DB_HOST/" .env
# ...

php artisan key:generate --force
php artisan config:clear
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
exec "$@"
```
**Purpose:** Container startup orchestration. Generates .env, applies environment variable overrides, runs Laravel setup commands (key generation, config caching, migrations, seeding, storage symlink), then hands control to the CMD process.

### Local Dev Script (`start.sh`)

```bash
#!/bin/bash
export PATH="/e/XAMPP/php:$PATH"
export PATH="/e/XAMPP/mysql/bin:$PATH"

start_mysql() {
    if ! netstat -ano | grep -q ':3306'; then
        mysqld --console --datadir="/e/XAMPP/mysql/data" &
        sleep 5
    fi
}

echo "1. Start MySQL + Laravel Server"
echo "2. Start MySQL only"
echo "4. Run migrations"
read -p "Choose: " choice

case $choice in
    1) start_mysql; php artisan serve --host=0.0.0.0 --port=8000 ;;
    4) php artisan migrate --force ;;
esac
```
**Purpose:** Local development convenience script for Git Bash on Windows. Manages MySQL startup, path configuration, and provides an interactive menu for common Laravel commands.

---

## Render Configuration (`render.yaml`)

```yaml
services:
  - type: web
    name: skill-matching
    env: docker
    dockerfilePath: ./Dockerfile
    disk:
      name: skill-matching-storage
      mountPath: /var/www/html/storage
      size: 1
    envVars:
      APP_ENV: production
      APP_DEBUG: false
      CACHE_DRIVER: redis
      SESSION_DRIVER: redis
      DB_CONNECTION: pgsql
      DB_HOST: dpg-d9vtio5bedkc739dbuqg-a
      DB_PORT: "5432"

databases:
  - name: skill-matching-db
    plan: free
    databaseName: skill_matching_system_t808
    user: skill_matching_user
```
**Purpose:** Infrastructure-as-code for Render.com deployment. Defines a Docker-based web service with persistent storage for uploads, PostgreSQL database, Redis for caching/sessions, and environment variable configuration.
