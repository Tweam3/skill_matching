@if ($skills->isNotEmpty())
  <div class="skill-grid">
    @foreach ($skills as $s)
      <div class="card">
        <div class="card-title">{{ $s->Skill_Title }}</div>
        <div class="card-subtitle">
          @if ($showCategory ?? true)
            <span class="badge badge-open">{{ $s->Category }}</span>
          @endif
          @if ($s->Subcategory)
            <span class="badge" style="background:#E0F2FE; color:#0369A1;">{{ $s->Subcategory }}</span>
          @endif
        </div>
        <form method="POST" action="{{ route('skills.addToMe') }}">
          @csrf
          <input type="hidden" name="skill_id" value="{{ $s->Skill_ID }}">
          <button type="submit" class="btn btn-success btn-sm">Add to My Skills</button>
        </form>
      </div>
    @endforeach
  </div>
@endif

<style>
.skill-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 16px;
  margin-bottom: 16px;
}
.skill-grid .card {
  display: flex;
  flex-direction: column;
  background: var(--surface);
  border-radius: 12px;
  padding: 16px;
  border: 1px solid #E5E7EB;
  transition: box-shadow 0.2s;
}
.skill-grid .card:hover {
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}
.skill-grid .card-title {
  font-weight: 600;
  font-size: 1rem;
  margin: 0 0 6px;
}
.skill-grid .card-subtitle {
  color: var(--muted);
  font-size: 0.8rem;
  margin: 0 0 10px;
  display: flex;
  gap: 6px;
  align-items: center;
  flex-wrap: wrap;
}
.skill-grid .badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 600;
}
.skill-grid form {
  margin-top: auto;
}
@media (max-width: 640px) {
  .skill-grid {
    grid-template-columns: 1fr;
  }
}
@media (min-width: 641px) and (max-width: 1024px) {
  .skill-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (min-width: 1025px) {
  .skill-grid {
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  }
}
</style>
