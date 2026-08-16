@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Matches</h2>

  <h3 style="margin-top:24px;">For You</h3>
  @if ($matched->isEmpty())
    <div class="alert alert-info">No matches yet. Create a request with a skill to get matched.</div>
  @endif
  <div class="cards-grid">
    @foreach ($matched as $m)
      @php $allSkills = $m->request->skills->merge([$m->request->skill])->unique('Skill_ID')->values(); @endphp
      <div class="card">
        <div class="card-title">{{ $m->request->Title }}</div>
        <div class="card-subtitle">Requested by {{ $m->request->user->Full_Name }} · {{ $allSkills->pluck('Skill_Title')->join(', ') }} · <span class="badge badge-{{ strtolower(str_replace('-', '', str_replace(' ', '-', $m->request->Service_Mode ?? 'Remote'))) }}">{{ $m->request->Service_Mode ?? 'Remote' }}</span></div>
        <p>Match Score: <strong>{{ $m->Match_Score }}%</strong></p>
        <div style="margin-top:14px;">
          <a href="{{ route('requests.show', $m->request->Request_ID) }}" class="btn btn-primary btn-sm">View Request</a>
          <a href="{{ route('messages.index', ['with' => $m->request->user->User_ID]) }}" class="btn btn-secondary btn-sm">Contact</a>
        </div>
      </div>
    @endforeach
  </div>

  <h3 style="margin-top:32px;">Available Requests</h3>
  @if ($available->isEmpty())
    <div class="alert alert-info">No open requests available right now.</div>
  @endif
  <div class="cards-grid">
    @foreach ($available as $a)
      @php $allSkills = $a->skills->merge([$a->skill])->unique('Skill_ID')->values(); @endphp
      <div class="card">
        <div class="card-title">{{ $a->Title }}</div>
        <div class="card-subtitle">{{ $allSkills->pluck('Skill_Title')->join(', ') }} · by {{ $a->user->Full_Name }} · <span class="badge badge-{{ strtolower(str_replace('-', '', str_replace(' ', '-', $a->Service_Mode ?? 'Remote'))) }}">{{ $a->Service_Mode ?? 'Remote' }}</span></div>
        <div style="margin-top:14px;">
          <a href="{{ route('requests.show', $a->Request_ID) }}" class="btn btn-primary btn-sm">View Request</a>
        </div>
      </div>
    @endforeach
  </div>
</div>
@endsection
