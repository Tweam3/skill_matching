@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card">
    <h2>{{ $request->Title }}</h2>
    <p><strong>Skills:</strong> 
      @php $allSkills = $request->skills->merge([$request->skill])->unique('Skill_ID')->values(); @endphp
      {{ $allSkills->pluck('Skill_Title')->join(', ') }}
    </p>
    <p><strong>Description:</strong> {{ $request->Description }}</p>
    <p><strong>Status:</strong> <span class="badge badge-{{ strtolower($request->Status) }}">{{ $request->Status }}</span></p>
    <p><strong>Service Mode:</strong> <span class="badge badge-{{ strtolower(str_replace('-', '', str_replace(' ', '-', $request->Service_Mode ?? 'Remote'))) }}">{{ $request->Service_Mode ?? 'Remote' }}</span></p>
    <p><strong>Requested by:</strong> {{ $request->user->Full_Name ?? 'N/A' }}</p>

    @if (auth()->id() == $request->User_ID && $request->Status === 'Pending')
      <div style="margin-top:16px; padding:16px; background:#F0FDF4; border-radius:8px; border:1px solid #BBF7D0;">
        <strong>Work in Progress</strong>
        <p style="color:var(--muted); margin:8px 0 12px;">Once the work is done, mark this request as completed. If the request failed or wasn't fulfilled, mark it as failed.</p>
        <form method="POST" action="{{ route('requests.complete', $request->Request_ID) }}" style="display:inline;">
          @csrf
          <button type="submit" class="btn btn-success">Mark as Completed</button>
        </form>
        <form method="POST" action="{{ route('requests.fail', $request->Request_ID) }}" style="display:inline; margin-left:10px;" onsubmit="return confirm('Mark this request as failed? This will notify the assigned user.');">
          @csrf
          <button type="submit" class="btn btn-danger">Mark as Failed</button>
        </form>
      </div>
    @endif

    @if (session('success') && str_contains(session('success'), 'failed'))
      @php
        $failedAssignment = $request->assignments->firstWhere('Status', 'Failed');
        $provider = $failedAssignment->user ?? null;
        $hasReviewed = $provider ? \App\Models\Review::where('Request_ID', $request->Request_ID)->where('Reviewer_ID', auth()->id())->exists() : false;
      @endphp
      @if ($provider && ! $hasReviewed)
        <div style="margin-top:20px; padding:16px; background:#FFF5F5; border-radius:8px; border:1px solid #FECACA;">
          <h3>Feedback for {{ $provider->Full_Name }}</h3>
          <p style="color:var(--muted); margin:8px 0 12px;">This request was marked as failed. You can report this user or leave a review.</p>
          <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <button type="button" class="btn btn-danger btn-sm" onclick="document.getElementById('failed-report-form').style.display='block';">Report User</button>
            <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('failed-review-form').style.display='block';">Leave Review</button>
          </div>

          <form id="failed-report-form" method="POST" action="{{ route('reports.store') }}" style="margin-top:16px; display:none;">
            @csrf
            <input type="hidden" name="reported_user_id" value="{{ $provider->User_ID }}">
            <input type="hidden" name="request_id" value="{{ $request->Request_ID }}">
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

          <form id="failed-review-form" method="POST" action="{{ route('assignments.review', $failedAssignment->Assignment_ID) }}" style="margin-top:16px; display:none;">
            @csrf
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
        </div>
      @endif
    @endif

    @if (auth()->id() !== $request->User_ID)
      <div style="margin-top:20px; padding:16px; background:#F0FDF4; border-radius:8px; border:1px solid #BBF7D0;">
        <h3 style="margin-top:0; color:#065F46;">Actions</h3>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
          <form method="post" action="{{ route('assignments.apply') }}" style="display:inline;">
            @csrf
            <input type="hidden" name="request_id" value="{{ $request->Request_ID }}">
            <button type="submit" class="btn btn-success btn-sm">Apply</button>
          </form>
          <a href="{{ route('messages.index', ['with' => $request->User_ID]) }}" class="btn btn-primary btn-sm">Message</a>
          <button class="btn btn-danger btn-sm" onclick="openRequestReportModal({{ $request->User_ID }}, {{ $request->Request_ID }})" title="Report This Request" style="padding:8px 10px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
              <line x1="12" y1="9" x2="12" y2="13"></line>
              <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
          </button>
        </div>
      </div>
    @endif

    @if (auth()->id() == $request->User_ID)
      <h3 style="margin-top:24px;">Applicants</h3>
      @if ($applicants->isEmpty())
        <div class="alert alert-info">No applicants yet.</div>
      @else
        <div class="table-wrap">
          <table>
            <thead><tr><th>Name</th><th>Email</th><th>Rating</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              @foreach ($applicants as $a)
                <tr>
                  <td>{{ $a->user->Full_Name ?? 'N/A' }}</td>
                  <td>{{ $a->user->Email ?? 'N/A' }}</td>
                  <td>
                    {{ number_format((float)($a->user->Avg_Rating ?? 0), 2) }} / 5
                    <span style="color:var(--muted);font-size:0.82rem;">({{ $a->user->Total_Completed ?? 0 }} completed)</span>
                  </td>
                  <td><span class="badge badge-{{ strtolower($a->Status) }}">{{ $a->Status }}</span></td>
                  <td style="display:flex;gap:6px;">
                    @if ($a->Status === 'Pending')
                      <form method="post" action="{{ route('assignments.accept', $a->Assignment_ID) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">Accept</button>
                      </form>
                      <form method="post" action="{{ route('assignments.reject', $a->Assignment_ID) }}" style="display:inline;" onsubmit="return confirm('Reject this applicant?');">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                      </form>
                    @else
                      <span style="color:var(--muted);">Accepted</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    @endif

    <div style="margin-top:20px;">
      <a href="{{ route('requests.index') }}" class="btn btn-secondary">Back to Requests</a>
    </div>
  </div>
</div>

<div id="request-report-modal-overlay" class="report-modal-overlay" onclick="if(event.target===this)closeRequestReportModal()">
  <div class="report-modal">
    <div class="report-modal-header">
      <h3>Report This Request</h3>
      <button type="button" class="report-modal-close" onclick="closeRequestReportModal()">&times;</button>
    </div>
    <form id="request-report-modal-form" method="POST" action="{{ route('reports.store') }}">
      @csrf
      <div class="report-modal-body">
        <input type="hidden" name="reported_user_id" id="request-report-modal-user-id" value="">
        <input type="hidden" name="request_id" id="request-report-modal-request-id" value="">
        <div class="form-group">
          <label>Reason</label>
          <textarea name="reason" rows="4" required class="form-control" style="width:100%;padding:10px;border:2px solid #E5E7EB;border-radius:8px;"></textarea>
        </div>
        <div class="form-group">
          <label>Proof (Optional)</label>
          <input type="text" name="proof" placeholder="Link or description of evidence" class="form-control" style="width:100%;padding:10px;border:2px solid #E5E7EB;border-radius:8px;">
        </div>
      </div>
      <div class="report-modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" onclick="closeRequestReportModal()">Cancel</button>
        <button type="submit" class="btn btn-danger btn-sm">Submit Report</button>
      </div>
    </form>
  </div>
</div>

<script>
function openRequestReportModal(userId, requestId) {
  document.getElementById('request-report-modal-user-id').value = userId;
  document.getElementById('request-report-modal-request-id').value = requestId;
  document.getElementById('request-report-modal-overlay').classList.add('active');
}
function closeRequestReportModal() {
  document.getElementById('request-report-modal-overlay').classList.remove('active');
}
</script>
@endsection