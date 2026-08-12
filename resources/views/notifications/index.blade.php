@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Notifications</h2>
  @if ($notifications->isEmpty())
    <div class="alert alert-info">No notifications.</div>
  @else
    <div class="table-wrap"><table>
      <thead><tr><th>Time</th><th>Type</th><th>Message</th><th>Status</th></tr></thead>
      <tbody>
        @foreach ($notifications as $n)
          <tr>
            <td>{{ $n->Created_At }}</td>
            <td>{{ $n->Notif_Type }}</td>
            <td>{{ $n->Message }}</td>
            <td><span class="badge badge-{{ $n->Status === 'Read' ? 'accepted' : 'open' }}">{{ $n->Status }}</span></td>
          </tr>
        @endforeach
      </tbody>
    </table></div>
  @endif
</div>
@endsection
