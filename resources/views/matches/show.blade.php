@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Match Details</h2>

  <div class="card">
    <div class="card-title">{{ $request->Title }}</div>
    <div class="card-subtitle">
      Requested by {{ $request->user->Full_Name }} ·
      @php $allSkills = $request->skills->merge([$request->skill])->unique('Skill_ID')->values(); @endphp
      {{ $allSkills->pluck('Skill_Title')->join(', ') }}
    </div>
    <p>{{ $request->Description }}</p>
    <p>Status: <span class="badge badge-{{ strtolower($request->Status) }}">{{ $request->Status }}</span></p>

    @if ($myScore)
      <div style="margin-top:16px; padding:12px; background:#f8f9fa; border-radius:6px;">
        <h4 style="margin:0 0 8px;">Your Match Score: <strong>{{ $myScore['score'] }}%</strong></h4>
        <div style="display:flex; gap:12px; flex-wrap:wrap;">
          <div><strong>Skill Overlap:</strong> {{ round($myScore['breakdown']['skill_overlap'] * 100, 1) }}%</div>
          <div><strong>Category Coverage:</strong> {{ round($myScore['breakdown']['category_coverage'] * 100, 1) }}%</div>
          <div><strong>Rating:</strong> {{ round($myScore['breakdown']['rating'] * 100, 1) }}%</div>
          <div><strong>Profile Quality:</strong> {{ round($myScore['breakdown']['profile_quality'] * 100, 1) }}%</div>
        </div>
      </div>
    @elseif ($isMatched)
      <p style="margin-top:16px; color:#6c757d;">You are matched to this request. Open it to apply.</p>
    @else
      <p style="margin-top:16px; color:#6c757d;">You are not matched to this request.</p>
    @endif
  </div>

  @if ($ranking && $ranking->isNotEmpty())
    <h3 style="margin-top:32px;">Top Recommended Providers</h3>
    <div class="cards-grid">
      @foreach ($ranking as $entry)
        <div class="card">
          <div class="card-title">{{ $entry['user']->Full_Name }}</div>
          <div class="card-subtitle">
            Rating: {{ $entry['user']->Avg_Rating }} ·
            Completed: {{ $entry['user']->Total_Completed }} ·
            @if ($entry['user']->Is_Verified)
              <span style="color:#28a745;">&#10003; Verified</span>
            @else
              <span style="color:#dc3545;">&#10007; Unverified</span>
            @endif
          </div>
          <p>Match Score: <strong>{{ $entry['score'] }}%</strong></p>
          <div style="margin-top:8px;">
            <small>Skill: {{ round($entry['breakdown']['skill_overlap'] * 100, 1) }}% ·
              Category: {{ round($entry['breakdown']['category_coverage'] * 100, 1) }}% ·
              Rating: {{ round($entry['breakdown']['rating'] * 100, 1) }}% ·
              Profile: {{ round($entry['breakdown']['profile_quality'] * 100, 1) }}%</small>
          </div>
          @if (Auth::id() == $request->User_ID)
            <div style="margin-top:12px;">
              <form method="POST" action="{{ route('assignments.apply') }}" style="display:inline;">
                @csrf
                <input type="hidden" name="request_id" value="{{ $request->Request_ID }}">
                <button type="submit" class="btn btn-primary btn-sm">View Profile</button>
              </form>
            </div>
          @endif
        </div>
      @endforeach
    </div>
  @endif
</div>
@endsection
