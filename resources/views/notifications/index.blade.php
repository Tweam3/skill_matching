@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Notifications</h2>
  @if ($notifications->isEmpty())
    <div class="alert alert-info">No notifications.</div>
  @else
    <div class="table-wrap"><table>
      <thead><tr><th></th><th>Type</th><th>Message</th><th>Status</th></tr></thead>
      <tbody>
        @foreach ($notifications as $n)
          <tr style="{{ $n->Status === 'Unread' ? 'font-weight:600; background:#f8fafc;' : '' }}">
            <td style="white-space:nowrap;">{{ $n->Created_At->format('M d, Y h:i A') }}</td>
            <td><span class="badge badge-{{ strtolower(str_replace(' ', '_', $n->Notif_Type)) }}">{{ $n->Notif_Type }}</span></td>
            <td>
              @if ($n->url)
                <a href="{{ $n->url }}" style="color:inherit; text-decoration:underline;">{{ $n->Message }}</a>
              @else
                {{ $n->Message }}
              @endif
            </td>
            <td><span class="badge badge-{{ $n->Status === 'Read' ? 'accepted' : 'open' }}">{{ $n->Status }}</span></td>
          </tr>
        @endforeach
      </tbody>
    </table></div>
  @endif
</div>
@endsection
