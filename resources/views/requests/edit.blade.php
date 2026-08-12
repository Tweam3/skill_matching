@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card">
    <h2>Edit Request</h2>
    <form method="POST" action="{{ route('requests.update', $request->Request_ID) }}">
      @csrf
      @method('PUT')
      <div class="form-group">
        <label>Skills</label>
        <div class="skill-picker">
          <input type="text" class="skill-search" placeholder="Search skills…" autocomplete="off">
          <div class="skill-groups">
            @php
              $groupedSkills = $skills->groupBy(function ($s) {
                  return $s->Subcategory
                    ? ($s->Category . ' / ' . $s->Subcategory)
                    : ($s->Category ?: 'General');
              });
              $selectedIds = old('skill_ids', $request->skills->pluck('Skill_ID')->toArray());
            @endphp
            @foreach ($groupedSkills as $groupLabel => $groupSkills)
              <div class="skill-group" data-group="{{ strtolower($groupLabel) }}">
                <div class="skill-group-title">{{ $groupLabel }}</div>
                @foreach ($groupSkills as $sk)
                  <label class="skill-option">
                    <input type="checkbox" name="skill_ids[]" value="{{ $sk->Skill_ID }}" {{ in_array($sk->Skill_ID, $selectedIds) ? 'checked' : '' }}>
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
        <input type="text" name="title" value="{{ $request->Title }}" required>
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea name="description" rows="3" required>{{ $request->Description }}</textarea>
      </div>
      <div class="form-group">
        <label>Service Mode</label>
        <div style="display:flex; gap:12px;">
          <label><input type="radio" name="service_mode" value="Remote" {{ ($request->Service_Mode ?? 'Remote') === 'Remote' ? 'checked' : '' }}> Remote</label>
          <label><input type="radio" name="service_mode" value="Face-to-Face" {{ ($request->Service_Mode ?? '') === 'Face-to-Face' ? 'checked' : '' }}> Face-to-Face</label>
          <label><input type="radio" name="service_mode" value="Hybrid" {{ ($request->Service_Mode ?? '') === 'Hybrid' ? 'checked' : '' }}> Hybrid</label>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Update Request</button>
      <a href="{{ route('requests.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</div>
<script>
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
