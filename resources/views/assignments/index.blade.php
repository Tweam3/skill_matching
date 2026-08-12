@extends('layouts.app')

@section('content')
<div class="container">
  <h2>My Applications</h2>

  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  @if ($assignments->isEmpty())
    <div class="alert alert-info">You haven't applied to any requests yet.</div>
  @else
    <div class="cards-grid">
      @foreach ($assignments as $a)
        <div class="card">
          <div class="card-title">{{ $a->request->Title }}</div>
          <div class="card-subtitle">{{ $a->request->skill->Skill_Title ?? '' }} · by {{ $a->request->user->Full_Name }}</div>
          <div style="margin-top:8px;"><span class="badge badge-{{ strtolower($a->Status) }}">{{ $a->Status }}</span></div>

          @if (in_array($a->Status, ['Accepted', 'Active']))
            <div style="margin-top:14px; display:flex; flex-direction: column; gap: 8px;">
              <form method="post" action="{{ route('assignments.complete', $a->Assignment_ID) }}" style="display:inline;">
                @csrf
                <input type="hidden" name="completed" value="1">
                <button type="submit" class="btn btn-success btn-sm">Mark Completed</button>
              </form>
              <form method="post" action="{{ route('assignments.complete', $a->Assignment_ID) }}" style="display:inline;">
                @csrf
                <input type="hidden" name="completed" value="0">
                <button type="submit" class="btn btn-danger btn-sm">Mark Failed</button>
              </form>
            </div>
          @endif

          @if ($a->Status === 'Completed')
            <div style="margin-top: 10px; border-top: 1px solid #eee; padding-top: 8px;">
              <strong>Rate this work</strong>
              <form method="post" action="{{ route('assignments.review', $a->Assignment_ID) }}" style="margin-top:8px;">
                @csrf
                <select name="rating" required style="padding:6px;border-radius:6px;">
                  @for ($i=1; $i<=5; $i++)
                    <option value="{{ $i }}">{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                  @endfor
                </select>
                <textarea name="comment" rows="2" placeholder="Write a review..." required class="form-control" style="width:100%;margin-top:6px;padding:8px;border:2px solid #E5E7EB;border-radius:8px;"></textarea>
                <button type="submit" class="btn btn-primary btn-sm" style="margin-top:8px;">Submit Review</button>
              </form>
            </div>
          @endif
        </div>
      @endforeach
    </div>
  @endif
</div>
@endsection
