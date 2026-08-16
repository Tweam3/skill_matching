@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Search</h2>
  <p style="color:var(--muted);margin-bottom:20px;">Find service requests and skill providers by keyword or category.</p>

  <form method="GET" action="{{ route('search.index') }}" style="margin-bottom:24px;">
    <div class="card" style="margin-bottom:16px;">
      <div style="display:flex; gap:24px; flex-wrap:wrap; align-items:center; margin-top:16px;">
        <div>
          <strong>Service Mode</strong>
          <div style="display:flex; gap:12px; margin-top:6px;">
            <label><input type="radio" name="service_mode" value="" {{ $serviceMode === '' ? 'checked' : '' }}> All</label>
            <label><input type="radio" name="service_mode" value="Remote" {{ $serviceMode === 'Remote' ? 'checked' : '' }}> Remote</label>
            <label><input type="radio" name="service_mode" value="Face-to-Face" {{ $serviceMode === 'Face-to-Face' ? 'checked' : '' }}> Face-to-Face</label>
            <label><input type="radio" name="service_mode" value="Hybrid" {{ $serviceMode === 'Hybrid' ? 'checked' : '' }}> Hybrid</label>
          </div>
        </div>
      </div>

      <div style="margin-top:16px;">
        <button type="button" id="toggle-categories" class="btn btn-secondary btn-sm">Show Categories</button>
      </div>

      <div id="categories-section" style="display:none; margin-top:16px;">
        <strong>Category Tags</strong>
        <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:6px;">
          <label><input type="checkbox" id="select-all-categories"> Select All</label>
          <label><input type="checkbox" id="clear-all-categories"> Clear All</label>
        </div>
        <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:6px;">
          @foreach ($allCategories as $cat)
            @php $checked = in_array($cat, $categories) ? 'checked' : ''; @endphp
            <label>
              <input type="checkbox" name="categories[]" value="{{ $cat }}" {{ $checked }}
                data-category="{{ $cat }}" class="category-checkbox">
              {{ $cat }}
              @if (isset($allSubcategories[$cat]) && ! empty($allSubcategories[$cat]))
                <span class="toggle-subcats" data-category="{{ $cat }}" style="cursor:pointer; font-size:0.7rem; margin-left:4px;">[+]</span>
                <span class="subcategory-list" data-category="{{ $cat }}" style="display:none; margin-left:16px; margin-top:4px;">
                  @foreach ($allSubcategories[$cat] as $sub)
                    @php $subChecked = in_array($sub, $subcategories) ? 'checked' : ''; @endphp
                    <div><label><input type="checkbox" name="subcategories[]" value="{{ $sub }}" {{ $subChecked }}>{{ $sub }}</label></div>
                  @endforeach
                </span>
              @endif
            </label>
          @endforeach
        </div>
      </div>

      @if (Auth::check() && Auth::user()->Role === 'Admin')
        <div style="margin-top:16px; padding-top:12px; border-top:1px solid #e0e0e0;">
          <strong>Add Category</strong>
          <div style="display:flex; gap:12px; margin-top:6px;">
            <input type="text" name="new_category" placeholder="New category" style="padding:6px;border-radius:4px;border:1px solid #ddd;">
            <input type="text" name="new_subcategory" placeholder="Subcategory (optional)" style="padding:6px;border-radius:4px;border:1px solid #ddd;">
            <button type="button" id="add-category-btn" class="btn btn-primary btn-sm" style="padding:6px 12px;">Add</button>
          </div>
        </div>
      @endif

      <div style="margin-top:16px;">
        <strong>Keyword</strong>
        <input type="text" name="keyword" value="{{ $keyword }}" placeholder="Search by title, description, skill, or name…" style="padding:8px;border-radius:6px;border:1px solid #ddd;width:100%;max-width:400px;">
      </div>

      <div style="margin-top:16px;">
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="{{ route('search.index') }}" class="btn btn-secondary">Reset</a>
      </div>
    </div>
  </form>

  @if ($results->isEmpty() && $providers->isEmpty())
    <div class="alert alert-info">Found Nothing Try Again Later</div>
  @else
    @if (! $results->isEmpty())
      <h3 style="margin-bottom:12px;">Service Requests</h3>
      <div class="cards-grid">
        @foreach ($results as $r)
          @php $allSkills = $r->skills->merge([$r->skill])->unique('Skill_ID')->values(); @endphp
          <div class="card">
            <div class="card-title">{{ $r->Title }}</div>
            <div class="card-subtitle">
              {{ $allSkills->pluck('Skill_Title')->join(', ') }}
              · <span class="badge badge-{{ strtolower(str_replace('-', '', str_replace(' ', '-', $r->Service_Mode ?? 'Remote'))) }}">
                {{ $r->Service_Mode ?? 'Remote' }}
              </span>
            </div>
            @if ($r->skill && $r->skill->Subcategory)
              <span class="badge badge-open" style="font-size:0.7rem; margin-top:4px; display:inline-block;">{{ $r->skill->Subcategory }}</span>
            @endif
            <p>{{ \Illuminate\Support\Str::limit($r->Description, 120) }}</p>
            <div style="margin-top:10px;">
              <span class="badge badge-{{ strtolower($r->Status) }}">{{ $r->Status }}</span>
              <span style="color:var(--muted);font-size:0.85rem;">by {{ $r->user->Full_Name ?? 'N/A' }}</span>
            </div>
            <div style="margin-top:14px;">
              <a href="{{ route('requests.show', $r->Request_ID) }}" class="btn btn-secondary btn-sm">View</a>
            </div>
          </div>
        @endforeach
      </div>
      {{ $results->links('pagination::bootstrap-4') }}
    @endif

    @if (! $providers->isEmpty())
      <h3 style="margin:24px 0 12px;">Skill Providers</h3>
      <div class="cards-grid">
        @foreach ($providers as $p)
          @php $userSkills = $p->skills; @endphp
          <div class="card">
            <div class="card-title">{{ $p->Full_Name }}</div>
            <div class="card-subtitle">
              @if ($userSkills->isNotEmpty())
                {{ $userSkills->pluck('Skill_Title')->join(', ') }}
              @else
                <span style="color:var(--muted);">No skills listed</span>
              @endif
            </div>
            <div style="margin:8px 0;">
              <div style="font-size:0.85rem; color:var(--muted);">
                Rating: {{ number_format((float)$p->Avg_Rating, 2) }}/5 ·
                Completed: {{ $p->Total_Completed }} ·
                {{ $p->Is_Verified ? 'Verified' : 'Unverified' }}
              </div>
              @if ($p->Is_Verified)
                <div style="display:flex;gap:4px;margin-top:4px;">
                  <span class="badge badge-accepted">Verified</span>
                </div>
              @endif
            </div>
            <div style="margin-top:8px;">
              @if ($userSkills->isNotEmpty())
                @foreach ($userSkills->take(4) as $s)
                  <span class="badge badge-open" style="font-size:0.7rem;">{{ $s->Skill_Title }}</span>
                @endforeach
                @if ($userSkills->count() > 4)
                  <span class="badge" style="font-size:0.7rem;">+{{ $userSkills->count() - 4 }} more</span>
                @endif
              @endif
            </div>
            <div style="margin-top:14px;">
              <a href="{{ route('profile.show', $p->User_ID) }}" class="btn btn-secondary btn-sm">View Profile</a>
              @if (Auth::check() && Auth::id() !== $p->User_ID)
                <a href="{{ route('messages.index', ['with' => $p->User_ID]) }}" class="btn btn-primary btn-sm">Message</a>
              @endif
            </div>
          </div>
        @endforeach
      </div>
      {{ $providers->links('pagination::bootstrap-4') }}
    @endif
  @endif
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var selectAll = document.getElementById('select-all-categories');
    var clearAll = document.getElementById('clear-all-categories');
    var checkboxes = document.querySelectorAll('.category-checkbox');
    var toggleBtn = document.getElementById('toggle-categories');
    var categoriesSection = document.getElementById('categories-section');

    if (toggleBtn && categoriesSection) {
      toggleBtn.addEventListener('click', function () {
        if (categoriesSection.style.display === 'none') {
          categoriesSection.style.display = 'block';
          toggleBtn.textContent = 'Hide Categories';
        } else {
          categoriesSection.style.display = 'none';
          toggleBtn.textContent = 'Show Categories';
        }
      });
    }

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
        selectAll.indeterminate = Array.from(checkboxes).some(function (c) { return c.checked; }) && !Array.from(checkboxes).every(function (c) { return c.checked; });
        clearAll.checked = false;
      });
    });

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

    var addBtn = document.getElementById('add-category-btn');
    if (addBtn) {
      addBtn.addEventListener('click', function () {
        var catInput = document.querySelector('input[name="new_category"]');
        var subInput = document.querySelector('input[name="new_subcategory"]');
        var cat = catInput.value.trim();
        var sub = subInput.value.trim();
        if (!cat) {
          alert('Please enter a category name.');
          return;
        }
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("search.categories.add") }}';
        var csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        var catField = document.createElement('input');
        catField.type = 'hidden';
        catField.name = 'category';
        catField.value = cat;
        var subField = document.createElement('input');
        subField.type = 'hidden';
        subField.name = 'subcategory';
        subField.value = sub;

        if (cat) form.appendChild(catField);
        if (sub) form.appendChild(subField);
        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
      });
    }
  });
</script>
@endpush
@endsection
