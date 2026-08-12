@if ($skills->isNotEmpty())
  <div class="skill-grid-inline" style="display:flex;flex-wrap:wrap;gap:10px;">
    @foreach ($skills as $s)
      <div class="card" style="flex:1 1 calc(50% - 10px);min-width:140px;display:flex;flex-direction:column;background:#F9FAFB;border-radius:8px;padding:10px;border:1px solid #F3F4F6;">
        <div class="card-title" style="font-weight:600;font-size:0.9rem;margin:0 0 4px;">{{ $s->Skill_Title }}</div>
        <div class="card-subtitle" style="color:var(--muted);font-size:0.7rem;margin:0 0 6px;display:flex;gap:4px;flex-wrap:wrap;align-items:center;">
          @if ($s->Subcategory)
            <span class="badge" style="display:inline-block;padding:2px 8px;border-radius:20px;font-size:0.65rem;font-weight:600;background:#E0F2FE;color:#0369A1;">{{ $s->Subcategory }}</span>
          @endif
        </div>
        <form method="POST" action="{{ route('skills.addToMe') }}" style="margin-top:auto;">
          @csrf
          <input type="hidden" name="skill_id" value="{{ $s->Skill_ID }}">
          <button type="submit" class="btn btn-success btn-sm" style="padding:4px 8px;font-size:0.75rem;">Add</button>
        </form>
      </div>
    @endforeach
  </div>
@endif
