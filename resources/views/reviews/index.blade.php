@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Write a Review</h2>
  @if ($completed->isEmpty())
    <div class="alert alert-info">No completed requests to review yet.</div>
  @else
    @foreach ($completed as $a)
      <div class="card" style="margin-bottom:16px;">
        <div class="card-title">{{ $a->request->Title }}</div>
        <div class="card-subtitle">{{ $a->request->skill->Skill_Title ?? '' }}</div>
        <form method="POST" action="{{ route('reviews.store') }}">
          @csrf
          <input type="hidden" name="request_id" value="{{ $a->Request_ID }}">
          <input type="hidden" name="rated_user_id" value="{{ $a->request->User_ID }}">
          <div class="form-group">
            <label>Rating (1-5)</label>
            <select name="rating" required>
              @for ($i=1;$i<=5;$i++)
                <option value="{{ $i }}">{{ $i }}</option>
              @endfor
            </select>
          </div>
          <div class="form-group">
            <label>Comment</label>
            <textarea name="comment" rows="3" required></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Submit Review</button>
        </form>
      </div>
    @endforeach
  @endif
</div>
@endsection
