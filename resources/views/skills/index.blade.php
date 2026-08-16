@extends('layouts.app')

@section('content')
  <div style="margin-bottom:16px;">
    <a href="{{ route('profile.show', auth()->id()) }}" class="btn btn-secondary btn-sm">← Back to Profile</a>
  </div>
  <div class="page-header">
    <h2>Skills</h2>
    @if (auth()->user()->Role === 'Admin')
      <button class="btn btn-primary btn-sm" onclick="toggleSkillForm()">+ Add Skill</button>
    @endif
  </div>
  <div id="add-skill-card" class="card" style="display:none;margin-bottom:20px;">
    <form id="add-skill-form" method="POST" action="{{ route('skills.store') }}">
      @csrf
      <div class="form-group">
        <label>Title</label>
        <input type="text" name="skill_title" required>
      </div>
      <div class="form-group">
        <label>Category</label>
        <input type="text" name="category" value="General">
      </div>
      <div class="form-group">
        <label>Subcategory</label>
        <input type="text" name="subcategory" placeholder="e.g., Backend, Frontend, Data...">
      </div>
      <button type="submit" class="btn btn-primary">Add</button>
      <button type="button" class="btn btn-secondary" onclick="toggleSkillForm()">Cancel</button>
    </form>
    <div id="add-skill-success" style="display:none; margin-top:12px; padding:10px; background:#D1FAE5; color:#065F46; border-radius:8px;">
      Skill added successfully.
    </div>
  </div>

  @if ($groupedSkills->isEmpty())
    <div class="alert alert-info">No skills available yet.</div>
  @else
    <div class="category-grid">
      @foreach ($groupedSkills as $category => $subcategories)
        @php $slug = Illuminate\Support\Str::slug($category); @endphp
        <div class="category-card" id="card-{{ $slug }}">
          <div class="card-header" onclick="toggleCategory('{{ $slug }}')">
            <h3 class="category-title">{{ $category }}</h3>
            <span class="toggle-icon">▸</span>
          </div>
          <div class="skill-content" id="content-{{ $slug }}">
            <div style="padding:0 16px 16px;">
              @foreach ($subcategories as $subcategory => $skills)
                @if ($subcategory)
                  <div style="margin-bottom:16px;">
                    <div class="subcategory-label">{{ $subcategory }}</div>
                    <div class="skill-subgrid">
                      @foreach ($skills as $s)
                        <div class="skill-card">
                          <div class="skill-name">{{ $s->Skill_Title }}</div>
                          @if (in_array($s->Skill_ID, $userSkills))
                            <button type="button" class="btn btn-secondary btn-sm btn-added" disabled>Added</button>
                          @else
                            <button type="button" onclick="openProficiencyModal({{ $s->Skill_ID }}, '{{ addslashes($s->Skill_Title) }}', this)" class="btn btn-success btn-sm skill-btn">Add</button>
                          @endif
                        </div>
                      @endforeach
                    </div>
                  </div>
                @else
                  <div class="skill-subgrid">
                    @foreach ($skills as $s)
                      <div class="skill-card">
                        <div class="skill-name">{{ $s->Skill_Title }}</div>
                        @if (in_array($s->Skill_ID, $userSkills))
                          <button type="button" class="btn btn-secondary btn-sm btn-added" disabled>Added</button>
                        @else
                            <button type="button" onclick="openProficiencyModal({{ $s->Skill_ID }}, '{{ addslashes($s->Skill_Title) }}', this)" class="btn btn-success btn-sm skill-btn">Add</button>
                        @endif
                      </div>
                    @endforeach
                  </div>
                @endif
              @endforeach
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif

  <div id="proficiency-modal-overlay" class="proficiency-modal-overlay" onclick="if(event.target===this)closeProficiencyModal()">
    <div class="proficiency-modal">
      <div class="proficiency-modal-header">
        <h3>Rate your skill level</h3>
        <button type="button" class="report-modal-close" onclick="closeProficiencyModal()">&times;</button>
      </div>
      <form id="proficiency-modal-form" method="POST" action="{{ route('skills.addToMe') }}">
        @csrf
        <div class="proficiency-modal-body">
          <input type="hidden" name="skill_id" id="proficiency-modal-skill-id" value="">
          <input type="hidden" name="proficiency" id="proficiency-modal-value" value="3">
          <p style="text-align:center; color:var(--muted); margin:0 0 4px;">How well do you know <strong id="proficiency-modal-skill-name" style="color:var(--primary);"></strong>?</p>
          <div id="proficiency-modal-stars" class="star-rating">
            <button type="button" class="star" onclick="setProficiency(1)" title="1 - Beginner">★</button>
            <button type="button" class="star" onclick="setProficiency(2)" title="2 - Advanced Beginner">★</button>
            <button type="button" class="star" onclick="setProficiency(3)" title="3 - Competent">★</button>
            <button type="button" class="star" onclick="setProficiency(4)" title="4 - Proficient">★</button>
            <button type="button" class="star" onclick="setProficiency(5)" title="5 - Expert">★</button>
          </div>
          <div class="star-labels">
            <span>Beginner</span>
            <span>Expert</span>
          </div>
        </div>
        <div class="proficiency-modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" onclick="closeProficiencyModal()">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm">Add Skill</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
<script>
function toggleSkillForm() {
  var c = document.getElementById('add-skill-card');
  c.style.display = c.style.display === 'none' ? 'block' : 'none';
}

var expandedCard = null;
var authUserId = {{ auth()->id() }};

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
      var prevCard = document.getElementById('card-' + expandedCard);
      var prevContent = document.getElementById('content-' + expandedCard);
      var prevIcon = prevCard.querySelector('.toggle-icon');
      prevCard.classList.remove('expanded');
      prevContent.classList.remove('expanded');
      prevIcon.textContent = '▸';
      expandedCard = null;
    }
    card.classList.add('expanded');
    content.classList.add('expanded');
    icon.textContent = '▾';
    expandedCard = slug;
  }
}

var proficiencyModalSkillId = null;
var proficiencyModalBtn = null;

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

function removeSkill(skillId, btn) {
  btn.disabled = true;
  btn.textContent = 'Removing...';
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
  .then(data => {
    btn.textContent = 'Add';
    btn.classList.remove('btn-danger', 'btn-added');
    btn.classList.add('btn-success', 'skill-btn');
    btn.disabled = false;
    var skillName = btn.getAttribute('data-skill-name') || '';
    btn.setAttribute('onclick', 'openProficiencyModal(' + skillId + ', \'' + skillName.replace(/'/g, "\\'") + '\', this)');
  })
  .catch(err => {
    btn.disabled = false;
    btn.textContent = 'Remove skill';
    console.error(err);
  });
}

document.getElementById('proficiency-modal-form').addEventListener('submit', function(e) {
  e.preventDefault();
  var form = e.target;
  var formData = new FormData(form);
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
  .then(data => {
    if (data.success) {
      if (proficiencyModalBtn) {
        proficiencyModalBtn.textContent = 'Remove skill';
        proficiencyModalBtn.classList.remove('btn-success');
        proficiencyModalBtn.classList.add('btn-danger', 'skill-btn');
        proficiencyModalBtn.disabled = false;
        proficiencyModalBtn.setAttribute('onclick', 'removeSkill(' + proficiencyModalSkillId + ', this)');
      }
      closeProficiencyModal();
    }
  })
  .catch(err => {
    console.error(err);
    alert('Failed to add skill: ' + err.message);
  });
});

document.getElementById('add-skill-form').addEventListener('submit', function(e) {
  e.preventDefault();
  var form = e.target;
  var successDiv = document.getElementById('add-skill-success');
  var formData = new FormData(form);
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
  .then(data => {
    if (data.success) {
      form.reset();
      successDiv.style.display = 'block';
      setTimeout(function() { successDiv.style.display = 'none'; }, 3000);
    }
  })
  .catch(err => {
    console.error(err);
    alert('Failed to add skill: ' + err.message);
  });
});
</script>
@endpush
