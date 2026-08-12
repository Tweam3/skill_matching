@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Reviews & Reports</h2>
  <h3>Reviews About Me</h3>
  @if ($reviewsReceived->isEmpty())
    <div class="alert alert-info">No reviews yet.</div>
  @else
    <div class="cards-grid">
      @foreach ($reviewsReceived as $r)
        <div class="card">
          <div class="card-title">{{ $r->reviewer->Full_Name }}</div>
          <div class="rating">{{ str_repeat('★', $r->Rating) . str_repeat('☆', 5 - $r->Rating) }}</div>
          <div class="card-subtitle">{{ $r->Comment }}</div>
          <div style="font-size:0.82rem;color:var(--muted);">{{ $r->Created_At }}</div>
        </div>
      @endforeach
    </div>
  @endif

  <h3 style="margin-top:28px;">Reviews I Wrote</h3>
  @if ($reviewsGiven->isEmpty())
    <div class="alert alert-info">No reviews submitted yet.</div>
  @else
    <div class="cards-grid">
      @foreach ($reviewsGiven as $r)
        <div class="card">
          <div class="card-title">{{ $r->reviewedUser->Full_Name }}</div>
          <div class="rating">{{ str_repeat('★', $r->Rating) . str_repeat('☆', 5 - $r->Rating) }}</div>
          <div class="card-subtitle">{{ $r->Comment }}</div>
          <div style="font-size:0.82rem;color:var(--muted);">{{ $r->Created_At }}</div>
        </div>
      @endforeach
    </div>
  @endif

  <h3 style="margin-top:28px;">Report a User</h3>
  <form method="POST" action="{{ route('reports.store') }}" class="card" style="max-width:600px;">
    @csrf
    <div class="form-group">
      <label>Reported User ID</label>
      <input type="number" name="reported_user_id" required>
    </div>
    <div class="form-group">
      <label>Reason</label>
      <textarea name="reason" rows="3" required></textarea>
    </div>
    <button type="submit" class="btn btn-danger">Submit Report</button>
  </form>
</div>
@endsection
