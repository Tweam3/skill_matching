@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card">
    <h2>Feedback for Failed Request</h2>
    <p style="color:var(--muted); margin:0 0 20px;">
      The request <strong>{{ $request->Title }}</strong> was marked as failed by the requester, <strong>{{ $requester->Full_Name }}</strong>.
      You can leave a review or submit a report about the requester.
    </p>

    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px;">
      <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('provider-review-form').style.display='block';">Rate Requester</button>
      <button type="button" class="btn btn-danger btn-sm" onclick="document.getElementById('provider-report-form').style.display='block';">Report Requester</button>
    </div>

    <form id="provider-review-form" method="POST" action="{{ route('requests.submit-provider-feedback', $request->Request_ID) }}" style="display:none; margin-bottom:20px;">
      @csrf
      <input type="hidden" name="feedback_type" value="rate">
      <div class="form-group">
        <label>Rating (1-5)</label>
        <select name="rating" required>
          @for ($i=1; $i<=5; $i++)
            <option value="{{ $i }}">{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
          @endfor
        </select>
      </div>
      <div class="form-group">
        <label>Comment</label>
        <textarea name="comment" rows="3" required></textarea>
      </div>
      <button type="submit" class="btn btn-primary btn-sm">Submit Review</button>
    </form>

    <form id="provider-report-form" method="POST" action="{{ route('requests.submit-provider-feedback', $request->Request_ID) }}" style="display:none;">
      @csrf
      <input type="hidden" name="feedback_type" value="report">
      <div class="form-group">
        <label>Reason</label>
        <textarea name="reason" rows="4" required class="form-control" style="width:100%;padding:10px;border:2px solid #E5E7EB;border-radius:8px;"></textarea>
      </div>
      <div class="form-group">
        <label>Proof (Optional)</label>
        <input type="text" name="proof" placeholder="Link or description of evidence" class="form-control" style="width:100%;padding:10px;border:2px solid #E5E7EB;border-radius:8px;">
      </div>
      <button type="submit" class="btn btn-danger btn-sm">Submit Report</button>
    </form>
  </div>
</div>
@endsection
