@extends('layouts.app')

@section('content')
<div class="container">
  <div class="page-header">
    <h2>Skill Requests</h2>
    <button class="btn btn-primary" onclick="toggleForm()">+ New Request</button>
  </div>
  <div id="form-card" class="card" style="display:none;margin-bottom:20px;">
    <form method="POST" action="{{ route('requests.store') }}">
      @csrf
      <div class="form-group">
        <label>Skills</label>
        <div class="skill-picker">
          <input type="text" class="skill-search" placeholder="Search skills…" autocomplete="off">
          <div class="skill-groups">
            @php
              $allSkills = \App\Models\Skill::orderBy('Category')->orderBy('Subcategory')->orderBy('Skill_Title')->get();
              $groupedSkills = $allSkills->groupBy(function ($s) {
                  return $s->Subcategory
                    ? ($s->Category . ' / ' . $s->Subcategory)
                    : ($s->Category ?: 'General');
              });
            @endphp
            @foreach ($groupedSkills as $groupLabel => $groupSkills)
              <div class="skill-group" data-group="{{ strtolower($groupLabel) }}">
                <div class="skill-group-title">{{ $groupLabel }}</div>
                @foreach ($groupSkills as $sk)
                  <label class="skill-option">
                    <input type="checkbox" name="skill_ids[]" value="{{ $sk->Skill_ID }}">
                    <span>{{ $sk->Skill_Title }}</span>
                  </label>
                @endforeach
              </div>
            @endforeach
            <div class="skill-empty" style="display:none;">No skills match your search.</div>
          </div>
          <div class="selected-chips" id="selected-chips"></div>
        </div>
        <p class="skill-picker-hint">Tap skills to select multiple. Use the search bar to filter.</p>
      </div>
      <div class="form-group">
        <label>Title</label>
        <input type="text" name="title" required>
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea name="description" rows="3" required></textarea>
      </div>
      <div class="form-group">
        <label>Service Mode</label>
        <div style="display:flex; gap:12px;">
          <label><input type="radio" name="service_mode" value="Remote" checked> Remote</label>
          <label><input type="radio" name="service_mode" value="Face-to-Face"> Face-to-Face</label>
          <label><input type="radio" name="service_mode" value="Hybrid"> Hybrid</label>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Create Request</button>
      <button type="button" class="btn btn-secondary" onclick="toggleForm()">Cancel</button>
    </form>
  </div>
  @if ($requests->isEmpty())
    <div class="alert alert-info">You haven't posted any requests yet.</div>
  @else
    <div class="cards-grid">
      @foreach ($requests as $r)
        @php
          $allSkills = $r->skills->merge([$r->skill])->unique('Skill_ID')->values();
        @endphp
        <div class="card">
          <span class="card-title">{{ $r->Title }}</span>
          <div class="card-subtitle">{{ $allSkills->pluck('Skill_Title')->join(', ') }}</div>
          <p>{{ $r->Description }}</p>
          <div style="margin-top:10px;"><span class="badge badge-{{ strtolower($r->Status) }}">{{ $r->Status }}</span></div>
          <div style="margin-top:10px; display:flex; gap:8px;">
            <a href="{{ route('requests.show', $r->Request_ID) }}" class="btn btn-secondary btn-sm">View</a>
            <a href="{{ route('requests.edit', $r->Request_ID) }}" class="btn btn-primary btn-sm">Edit</a>
            <form method="POST" action="{{ route('requests.destroy', $r->Request_ID) }}" style="display:inline;" onsubmit="return confirm('Delete this request?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
          </div>
        </div>
      @endforeach
    </div>
  @endif

  @if (isset($appliedRequests) && $appliedRequests->isNotEmpty())
    <h3 style="margin-top:32px;">Requests You Applied</h3>
    <div class="cards-grid">
      @foreach ($appliedRequests as $r)
        @php $assignment = $r->assignments->first(); @endphp
        @php $allSkills = $r->skills->merge([$r->skill])->unique('Skill_ID')->values(); @endphp
        <div class="card">
          <span class="card-title">{{ $r->Title }}</span>
          <div class="card-subtitle">{{ $allSkills->pluck('Skill_Title')->join(', ') }} · by {{ $r->user->Full_Name }}</div>
          <p>{{ $r->Description }}</p>
          <div style="margin-top:10px;">
            <span class="badge badge-{{ strtolower($assignment->Status ?? 'open') }}">Application: {{ $assignment->Status ?? 'N/A' }}</span>
          </div>
          <div style="margin-top:10px;">
            <a href="{{ route('requests.show', $r->Request_ID) }}" class="btn btn-secondary btn-sm">View</a>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>
<script>
function toggleForm() {
  const c = document.getElementById('form-card');
  c.style.display = c.style.display === 'none' ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {
  const picker = document.querySelector('.skill-picker');
  if (!picker) return;

  const searchInput = picker.querySelector('.skill-search');
  const skillOptions = picker.querySelectorAll('.skill-option');
  const skillGroups = picker.querySelectorAll('.skill-group');
  const emptyState = picker.querySelector('.skill-empty');
  const chipsContainer = picker.querySelector('.selected-chips');

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

  function filterSkills() {
    const term = searchInput.value.trim().toLowerCase();
    let anyVisible = false;

    skillGroups.forEach(function (group) {
      let groupHasVisible = false;
      const options = group.querySelectorAll('.skill-option');
      options.forEach(function (opt) {
        const text = opt.textContent.toLowerCase();
        const match = text.indexOf(term) !== -1;
        opt.style.display = match ? 'flex' : 'none';
        if (match) groupHasVisible = true;
      });
      group.style.display = groupHasVisible ? 'block' : 'none';
      if (groupHasVisible) anyVisible = true;
    });

    emptyState.style.display = anyVisible ? 'none' : 'block';
  }

  picker.addEventListener('change', function (e) {
    if (e.target.name === 'skill_ids[]') {
      updateChips();
    }
  });

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

  searchInput.addEventListener('input', filterSkills);

  updateChips();
});
</script>
@endsection
