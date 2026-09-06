# JavaScript Code Reference

Unique JS patterns used across Blade templates, organized by category. No duplicates.

---

## DOMContentLoaded PATTERNS

```javascript
// Standard page load with hash routing
document.addEventListener('DOMContentLoaded', function () {
  var hash = window.location.hash.replace('#', '');
  if (hash) {
    switchSettingsTab(new Event('click'), hash);
    var link = document.querySelector('.settings-sidebar-item[href="#' + hash + '"]');
    if (link) link.classList.add('active');
  }
});
```

```javascript
// Page load with element guard
document.addEventListener('DOMContentLoaded', function () {
  const picker = document.querySelector('.skill-picker');
  if (!picker) return;
  // ... skill picker logic
});
```

```javascript
// Page load with mobile class manipulation
document.addEventListener('DOMContentLoaded', function () {
  var selectAll = document.getElementById('select-all-categories');
  var clearAll = document.getElementById('clear-all-categories');
  var checkboxes = document.querySelectorAll('.category-checkbox');
  // ... category filter logic
});
```

## MODAL PATTERNS

```javascript
// Proficiency modal open/close
function openProficiencyModal(skillId, skillName, btn) {
  proficiencyModalSkillId = skillId;
  proficiencyModalBtn = btn;
  btn.setAttribute('data-skill-name', skillName);
  document.getElementById('proficiency-modal-skill-id').value = skillId;
  document.getElementById('proficiency-modal-skill-name').textContent = skillName;
  document.getElementById('proficiency-modal-overlay').classList.add('active');
  updateStarDisplay(3);
}

function closeProficiencyModal() {
  document.getElementById('proficiency-modal-overlay').classList.remove('active');
  proficiencyModalSkillId = null;
  proficiencyModalBtn = null;
}
```

```javascript
// Report modal open/close (profile)
function openReportModal(userId) {
  document.getElementById('report-modal-user-id').value = userId;
  document.getElementById('report-modal-overlay').classList.add('active');
}

function closeReportModal() {
  document.getElementById('report-modal-overlay').classList.remove('active');
}
```

```javascript
// Request report modal open/close
function openRequestReportModal(userId, requestId) {
  document.getElementById('request-report-modal-user-id').value = userId;
  document.getElementById('request-report-modal-request-id').value = requestId;
  document.getElementById('request-report-modal-overlay').classList.add('active');
}

function closeRequestReportModal() {
  document.getElementById('request-report-modal-overlay').classList.remove('active');
}
```

```javascript
// Overlay click-to-close pattern
onclick="if(event.target===this)closeProficiencyModal()"
onclick="if(event.target===this)closeReportModal()"
onclick="if(event.target===this)closeRequestReportModal()"
```

## STAR RATING PATTERNS

```javascript
// Star display update
function setProficiency(value) {
  document.getElementById('proficiency-modal-value').value = value;
  updateStarDisplay(value);
}

function updateStarDisplay(value) {
  var stars = document.querySelectorAll('#proficiency-modal-stars .star');
  stars.forEach(function(star, index) {
    if (index < value) {
      star.classList.add('active');
    } else {
      star.classList.remove('active');
    }
  });
}
```

```html
<!-- Star buttons for rating -->
<button type="button" class="star" onclick="setProficiency(1)" title="1 - Beginner">★</button>
<button type="button" class="star" onclick="setProficiency(2)" title="2 - Advanced Beginner">★</button>
<button type="button" class="star" onclick="setProficiency(3)" title="3 - Competent">★</button>
<button type="button" class="star" onclick="setProficiency(4)" title="4 - Proficient">★</button>
<button type="button" class="star" onclick="setProficiency(5)" title="5 - Expert">★</button>
```

## TOGGLE PATTERNS

```javascript
// Toggle form visibility (create/edit form)
function toggleForm() {
  const c = document.getElementById('form-card');
  c.style.display = c.style.display === 'none' ? 'block' : 'none';
}
```

```javascript
// Toggle skill form visibility
function toggleSkillForm() {
  var c = document.getElementById('add-skill-card');
  c.style.display = c.style.display === 'none' ? 'block' : 'none';
}
```

```javascript
// Toggle edit form visibility (profile)
function toggleEditForm() {
  const c = document.getElementById('edit-form');
  c.style.display = c.style.display === 'none' ? 'block' : 'none';
}
```

```javascript
// Category expand/collapse with accordion behavior
function toggleCategory(slug) {
  var card = document.getElementById('card-' + slug);
  var content = document.getElementById('content-' + slug);
  var icon = card.querySelector('.toggle-icon');

  if (card.classList.contains('expanded')) {
    card.classList.remove('expanded');
    content.classList.remove('expanded');
    icon.textContent = '▸';
    expandedCard = null;
  } else {
    if (expandedCard) {
      // collapse previously expanded
      var prevCard = document.getElementById('card-' + expandedCard);
      var prevContent = document.getElementById('content-' + expandedCard);
      var prevIcon = prevCard.querySelector('.toggle-icon');
      prevCard.classList.remove('expanded');
      prevContent.classList.remove('expanded');
      prevIcon.textContent = '▸';
    }
    card.classList.add('expanded');
    content.classList.add('expanded');
    icon.textContent = '▾';
    expandedCard = slug;
  }
}
```

```javascript
// Tab switching with class manipulation
function switchTab(event, tabId) {
  document.querySelectorAll('.tab-panel').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
  document.getElementById(tabId).style.display = 'block';
  event.currentTarget.classList.add('active');
}
```

```javascript
// Settings tab switching
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
```

## FETCH / AJAX PATTERNS

```javascript
// Fetch with CSRF token and JSON headers
fetch('{{ route('profile.skill.remove') }}', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': '{{ csrf_token() }}',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
  body: JSON.stringify({ skill_id: skillId, user_id: authUserId }),
})
.then(r => r.ok ? r.json() : Promise.reject('Remove failed'))
.then(data => { ... })
.catch(err => { ... });
```

```javascript
// Fetch with FormData (form submission)
fetch(form.action, {
  method: 'POST',
  body: formData,
  headers: {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  }
})
.then(r => {
  if (!r.ok) return r.text().then(t => { throw new Error(t || 'Submit failed'); });
  return r.json();
})
.then(data => { ... })
.catch(err => { ... });
```

```javascript
// Polling for new messages
function pollMessages() {
  fetch('/messages?with=' + withId + '&since=' + (lastMessageId || 0) + '&ajax=1')
    .then(r => r.ok ? r.json() : Promise.reject('Network error'))
    .then(data => { ... })
    .catch(err => console.error('Poll error:', err));
}
setInterval(pollMessages, 3000);
```

```javascript
// Message send via fetch
function sendMessage(event) {
  event.preventDefault();
  const form = event.target;
  const input = document.getElementById('message-input');
  const message = input.value.trim();
  if (!message) return false;

  const formData = new FormData(form);
  fetch(form.action, {
    method: 'POST',
    body: formData,
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    }
  })
  .then(r => r.ok ? r.json() : Promise.reject('Send failed'))
  .then(data => { ... })
  .catch(err => console.error('Send error:', err));
  return false;
}
document.getElementById('message-form').addEventListener('submit', sendMessage);
```

## EVENT LISTENER PATTERNS

```javascript
// Password toggle
document.addEventListener('click', function(e) {
  const btn = e.target.closest('.password-toggle');
  if (!btn) return;
  const input = btn.closest('.password-field').querySelector('input');
  const isPassword = input.type === 'password';
  input.type = isPassword ? 'text' : 'password';
  btn.innerHTML = isPassword ? '{eye-open-svg}' : '{eye-closed-svg}';
});
```

```javascript
// Mobile navbar toggle
const toggle = document.getElementById('navbar-toggle');
const links = document.getElementById('navbar-links');
if (toggle && links) {
  toggle.addEventListener('click', () => {
    links.classList.toggle('is-open');
  });
}
```

```javascript
// Mobile tab active state
if (window.innerWidth <= 640) {
  document.querySelectorAll('.mobile-tabs-wrapper .tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.mobile-tabs-wrapper .tab').forEach(t => t.classList.remove('mobile-active'));
      tab.classList.add('mobile-active');
    });
  });
}
```

```javascript
// Category checkbox handlers
selectAll.addEventListener('change', function (e) {
  checkboxes.forEach(function (cb) {
    cb.checked = e.target.checked;
  });
});

clearAll.addEventListener('change', function (e) {
  if (e.target.checked) {
    checkboxes.forEach(function (cb) {
      cb.checked = false;
    });
    clearAll.checked = false;
  }
});

checkboxes.forEach(function (cb) {
  cb.addEventListener('change', function () {
    selectAll.checked = false;
    selectAll.indeterminate = Array.from(checkboxes).some(c => c.checked)
      && !Array.from(checkboxes).every(c => c.checked);
    clearAll.checked = false;
  });
});
```

```javascript
// Search filter toggle button
toggleBtn.addEventListener('click', function () {
  if (categoriesSection.style.display === 'none') {
    categoriesSection.style.display = 'block';
    toggleBtn.textContent = 'Hide Categories';
  } else {
    categoriesSection.style.display = 'none';
    toggleBtn.textContent = 'Show Categories';
  }
});
```

```javascript
// Subcategory expand/collapse
var toggles = document.querySelectorAll('.toggle-subcats');
toggles.forEach(function (el) {
  el.addEventListener('click', function (e) {
    e.stopPropagation();
    var cat = el.getAttribute('data-category');
    var list = document.querySelector('.subcategory-list[data-category="' + cat + '"]');
    if (list.style.display === 'none') {
      list.style.display = 'block';
      el.textContent = '[-]';
    } else {
      list.style.display = 'none';
      el.textContent = '[+]';
    }
  });
});
```

```javascript
// Add category button (admin)
var addBtn = document.getElementById('add-category-btn');
if (addBtn) {
  addBtn.addEventListener('click', function () {
    var cat = document.querySelector('input[name="new_category"]').value.trim();
    var sub = document.querySelector('input[name="new_subcategory"]').value.trim();
    if (!cat) { alert('Please enter a category name.'); return; }
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '{route}';
    // ... append CSRF and hidden fields, submit
  });
}
```

```javascript
// Skill picker change handler
picker.addEventListener('change', function (e) {
  if (e.target.name === 'skill_ids[]') {
    updateChips();
  }
});
```

```javascript
// Skill chip removal
chipsContainer.addEventListener('click', function (e) {
  if (e.target.tagName === 'BUTTON') {
    const id = e.target.getAttribute('data-id');
    const cb = picker.querySelector('input[name="skill_ids[]"][value="' + id + '"]');
    if (cb) {
      cb.checked = false;
      updateChips();
    }
  }
});
```

```javascript
// Search input filter
searchInput.addEventListener('input', filterSkills);
```

```javascript
// Form submit with preventDefault
document.getElementById('proficiency-modal-form').addEventListener('submit', function(e) {
  e.preventDefault();
  // ... fetch logic
});

document.getElementById('add-skill-form').addEventListener('submit', function(e) {
  e.preventDefault();
  // ... fetch logic
});
```

## IMAGE / FILE MANIPULATION

```javascript
// Profile picture zoom + pan
let currentFile = null;
let scale = 1;
let panX = 0;
let panY = 0;
let isDragging = false;

const fileInput = document.getElementById('picture-input');
const previewArea = document.getElementById('picture-preview-area');
const previewImg = document.getElementById('preview-img');
const zoomRange = document.getElementById('zoom-range');
const saveBtn = document.getElementById('save-picture-btn');
const cancelBtn = document.getElementById('cancel-picture-btn');

fileInput.addEventListener('change', (e) => {
  const file = e.target.files[0];
  if (!file) return;
  currentFile = file;
  const reader = new FileReader();
  reader.onload = (ev) => {
    previewImg.src = ev.target.result;
    previewArea.style.display = 'block';
    previewImg.onload = () => { scale = 1; panX = 0; panY = 0; zoomRange.value = 1; fitPreview(); };
  };
  reader.readAsDataURL(file);
});

window.addEventListener('mousemove', (e) => {
  if (!isDragging) return;
  panX = e.clientX - startX;
  panY = e.clientY - startY;
  clampPan();
  updateTransform();
});
```

```javascript
// Touch events for mobile image manipulation
previewBox.addEventListener('touchstart', (e) => {
  if (e.touches.length === 1) {
    e.preventDefault();
    isDragging = true;
    startX = e.touches[0].clientX - panX;
    startY = e.touches[0].clientY - panY;
  }
}, { passive: false });

window.addEventListener('touchmove', (e) => {
  if (!isDragging) return;
  e.preventDefault();
  panX = e.touches[0].clientX - startX;
  panY = e.touches[0].clientY - startY;
  clampPan();
  updateTransform();
}, { passive: false });

window.addEventListener('touchend', () => {
  isDragging = false;
});
```

```javascript
// Canvas crop for saved image
saveBtn.addEventListener('click', () => {
  const canvas = document.createElement('canvas');
  canvas.width = 400;
  canvas.height = 400;
  const ctx = canvas.getContext('2d');
  ctx.drawImage(previewImg, ...);
  canvas.toBlob((blob) => {
    const dt = new DataTransfer();
    dt.items.add(new File([blob], 'adjusted.png', { type: 'image/png' }));
    input.files = dt.files;
    form.submit();
  }, 'image/png');
});
```

## DARK MODE

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

## MESSAGE RENDERING

```javascript
function appendMessage(message) {
  if (!chatContainer) return;
  const isSent = message.Sender_ID == authId;
  const row = document.createElement('div');
  row.className = 'message-row ' + (isSent ? 'sent' : '');
  row.setAttribute('data-message-id', message.Message_ID);

  const time = new Date(message.Sent_At).toLocaleTimeString('en-US', {
    hour: 'numeric', minute: '2-digit', hour12: true
  });

  row.innerHTML =
    '<div class="message-bubble">' +
      '<div class="message-meta">' +
        '<span>' + (isSent ? 'You' : partnerName) + '</span>' +
        '<span class="message-timestamp">' + time + '</span>' +
        (isSent ? (message.read_at ? '<span class="read-receipt read">Seen</span>' : '<span class="read-receipt">Sent</span>') : '') +
      '</div>' +
      '<div class="message-text">' + message.Message_Text + '</div>' +
    '</div>';
  chatContainer.appendChild(row);
  scrollToBottom();
}
```

## CONVERSATION NAVIGATION

```javascript
function openConversation(userId, element) {
  window.location.href = '/messages?with=' + userId;
}

function closeConversation() {
  if (window.innerWidth <= 640) {
    window.location.href = '/messages';
  }
}
```

## CHIP RENDERING

```javascript
function updateChips() {
  const checked = picker.querySelectorAll('input[name="skill_ids[]"]:checked');
  chipsContainer.innerHTML = '';
  checked.forEach(function (cb) {
    const label = cb.closest('.skill-option');
    const name = label ? label.querySelector('span').textContent : cb.value;
    const chip = document.createElement('span');
    chip.className = 'skill-chip';
    chip.innerHTML = name + ' <button type="button" data-id="' + cb.value + '">×</button>';
    chipsContainer.appendChild(chip);
  });
}
```

## SKILL FILTERING

```javascript
function filterSkills() {
  const term = searchInput.value.trim().toLowerCase();
  let anyVisible = false;

  skillGroups.forEach(function (group) {
    let groupHasVisible = false;
    const options = group.querySelectorAll('.skill-option');
    options.forEach(function (opt) {
      const match = opt.textContent.toLowerCase().indexOf(term) !== -1;
      opt.style.display = match ? 'flex' : 'none';
      if (match) groupHasVisible = true;
    });
    group.style.display = groupHasVisible ? 'block' : 'none';
    if (groupHasVisible) anyVisible = true;
  });

  emptyState.style.display = anyVisible ? 'none' : 'block';
}
```

## READ RECEIPT UPDATE

```javascript
function updateReadReceipt(messageId, isRead) {
  const row = document.querySelector('.message-row[data-message-id="' + messageId + '"]');
  if (!row) return;
  const existing = row.querySelector('.read-receipt');
  if (existing) {
    existing.className = 'read-receipt ' + (isRead ? 'read' : '');
    existing.title = isRead ? 'Seen' : 'Sent';
    existing.textContent = isRead ? 'Seen' : 'Sent';
  }
}
```
