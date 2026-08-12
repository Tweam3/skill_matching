@extends('layouts.app')

@section('content')
<div class="container">
  <h2 style="margin-bottom:4px;">Messages</h2>
  <p style="color:var(--muted);margin-bottom:20px;">Chat with providers and requesters.</p>

  <div class="message-layout">
    {{-- Conversation List --}}
    <div class="conversation-sidebar">
      <h4 style="margin:0 0 8px; padding:0 14px; font-size:0.9rem; color:var(--muted);">Conversations</h4>
      @if ($users->isEmpty())
        <div class="conversation-empty">
          <h4>No conversations yet</h4>
          <p style="font-size:0.85rem;">Start a conversation by messaging a matched provider.</p>
        </div>
      @else
        <div class="conversation-list">
          @foreach ($users as $p)
            @php
              $isActive = $p->User_ID == $withId;
              $initial = strtoupper(substr($p->Full_Name ?? 'U', 0, 1));
            @endphp
            <a href="{{ route('messages.index', ['with' => $p->User_ID]) }}"
               class="conversation-item {{ $isActive ? 'active' : '' }}">
              <div class="convo-avatar">{{ $initial }}</div>
              <div class="conversation-info">
                <div class="conversation-name">{{ $p->Full_Name }}</div>
                <div class="conversation-preview">Click to view conversation</div>
              </div>
              @if ($isActive)
                <span class="chat-user-online" title="Active conversation"></span>
              @endif
              <span class="conversation-menu-btn" onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('profile.show', $p->User_ID) }}'" title="View Profile">
                <span></span><span></span><span></span>
              </span>
            </a>
          @endforeach
        </div>
      @endif
    </div>

    {{-- Chat Panel --}}
    <div class="chat-container">
      @if (!$withId)
        <div class="chat-empty">
          <div style="font-size:3rem; margin-bottom:16px;">&#128483;</div>
          <h3>Select a conversation</h3>
          <p>Choose a conversation from the left to start chatting.</p>
        </div>
      @else
        @php
          $partner = $users->firstWhere('User_ID', $withId);
          $partnerName = $partner?->Full_Name ?? 'User';
          $partnerInitial = $partnerName ? strtoupper(substr($partnerName, 0, 1)) : 'U';
        @endphp
        <div class="chat-header">
          <div class="chat-avatar">{{ $partnerInitial }}</div>
          <div style="flex:1;">
            <div class="chat-header-name">{{ $partnerName }}</div>
            <div class="chat-header-sub">Online</div>
          </div>
        </div>

        <div id="chat-messages" class="chat-messages">
          @if ($messages->isEmpty())
            <div style="text-align:center;padding:40px 20px;color:var(--muted);">
              <div style="font-size:2rem;margin-bottom:12px;">⚡</div>
              <p>No messages yet. Start the conversation!</p>
            </div>
          @else
            @foreach ($messages as $m)
              @php $isSent = $m->Sender_ID == auth()->id(); @endphp
              <div class="message-row {{ $isSent ? 'sent' : '' }}" data-message-id="{{ $m->Message_ID }}">
                <div class="message-bubble">
                  <div class="message-meta">
                    <span>{{ $isSent ? 'You' : $partnerName }}</span>
                    <span class="message-timestamp">{{ \Carbon\Carbon::parse($m->Sent_At)->format('g:i A') }}</span>
                  </div>
                  <div class="message-text">{{ $m->Message_Text }}</div>
                </div>
              </div>
            @endforeach
          @endif
        </div>

        <form id="message-form" method="POST" action="{{ route('messages.store') }}" class="chat-input">
          @csrf
          <div class="chat-input-form">
            <input type="text" name="message" id="message-input" required autofocus>
            <input type="hidden" name="to_id" value="{{ $withId }}">
            <button type="submit" class="btn btn-primary">Send</button>
          </div>
        </form>
      @endif
    </div>
  </div>
</div>

@if ($withId)
<script>
const lastMessageId = {{ $messages->last()?->Message_ID ?? 0 }};
const seenMessageIds = new Set();
const chatContainer = document.getElementById('chat-messages');
const withId = {{ $withId }};
const authId = {{ auth()->id() }};
const partnerName = @json($partnerName ?? 'User');

document.querySelectorAll('[data-message-id]').forEach(el => {
  seenMessageIds.add(parseInt(el.getAttribute('data-message-id')));
});

function scrollToBottom() {
  if (chatContainer) chatContainer.scrollTop = chatContainer.scrollHeight;
}

function appendMessage(message) {
  if (!chatContainer) return;
  const isSent = message.Sender_ID == authId;
  const row = document.createElement('div');
  row.className = 'message-row ' + (isSent ? 'sent' : '');
  row.setAttribute('data-message-id', message.Message_ID);

  const time = new Date(message.Sent_At).toLocaleTimeString('en-US', {
    hour: 'numeric', minute: '2-digit', hour12: true
  });

  row.innerHTML =
    '<div class="message-bubble">' +
      '<div class="message-meta">' +
        '<span>' + (isSent ? 'You' : partnerName) + '</span>' +
        '<span class="message-timestamp">' + time + '</span>' +
      '</div>' +
      '<div class="message-text">' + message.Message_Text + '</div>' +
    '</div>';
  chatContainer.appendChild(row);
  seenMessageIds.add(parseInt(message.Message_ID));
  scrollToBottom();
}

function pollMessages() {
  fetch('/messages?with=' + withId + '&since=' + (lastMessageId || 0) + '&ajax=1')
    .then(r => r.ok ? r.json() : Promise.reject('Network error'))
    .then(data => {
      if (data.messages && data.messages.length > 0) {
        data.messages.forEach(msg => {
          const id = parseInt(msg.Message_ID);
          if (!seenMessageIds.has(id)) {
            appendMessage(msg);
          }
        });
        lastMessageId = data.last_id;
      }
    })
    .catch(err => console.error('Poll error:', err));
}

function sendMessage(event) {
  event.preventDefault();
  const form = event.target;
  const input = document.getElementById('message-input');
  const message = input.value.trim();
  if (!message) return false;

  const formData = new FormData(form);
  fetch(form.action, {
    method: 'POST',
    body: formData,
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    }
  })
  .then(r => r.ok ? r.json() : Promise.reject('Send failed'))
  .then(data => {
    if (data.message) {
      const id = parseInt(data.message.Message_ID);
      if (!seenMessageIds.has(id)) {
        appendMessage(data.message);
      }
      input.value = '';
      input.focus();
    }
  })
  .catch(err => console.error('Send error:', err));
  return false;
}

scrollToBottom();
setInterval(pollMessages, 3000);
document.getElementById('message-form').addEventListener('submit', sendMessage);
</script>
@endif
@endsection
