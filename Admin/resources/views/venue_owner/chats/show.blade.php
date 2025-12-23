@extends('app')

@section('content')

<div class="container-fluid py-4" style="height: 90vh;">

    <div class="card shadow-lg border-0 h-100" style="display:flex; flex-direction:column;">

        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center py-3">
            <a href="{{ route('owner.chats.index') }}" class="text-white text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i> Quay lại
            </a>

            <div class="d-flex align-items-center gap-3">
                <img class="rounded-circle object-fit-cover" width="45" height="45"
                    src="{{ $otherUser->avt ?? 'https://placehold.co/45x45/4f46e5/ffffff?text=AD' }}"
                    alt="{{ $otherUser->name }}">
                <h5 class="mb-0 fw-semibold">{{ $otherUser->name }}</h5>
            </div>

            <span></span>
        </div>

        <div id="chat-box"
            class="card-body overflow-auto bg-light d-flex flex-column gap-3"
            style="flex-grow:1; min-height: 0;">

            @forelse ($messages as $message)
                @php
                    $isSender = $message->sender_id === Auth::id();
                    $bgColor = $isSender ? '#4f46e5' : '#e5e7eb';
                    $textColor = $isSender ? 'white' : '#111';
                    $alignment = $isSender ? 'justify-content-end' : 'justify-content-start';
                    $borderRadius = "border-radius: 20px; word-wrap: break-word;";
                    $borderRadius .= $isSender
                        ? " border-top-right-radius: 5px; border-bottom-right-radius: 5px;"
                        : " border-top-left-radius: 5px; border-bottom-left-radius: 5px;";
                @endphp

                <div class="d-flex w-100 {{ $alignment }}">
                    <div style="max-width: 70%;" class="{{ $isSender ? 'ms-auto' : 'me-auto' }}">
                        <div class="p-3 shadow-sm"
                             style="max-width:100%; background: {{ $bgColor }}; color: {{ $textColor }};
                                     margin-bottom: 2px; {{ $borderRadius }}">
                            {{ $message->message }}
                        </div>
                        <div class="text-muted small mt-1 {{ $isSender ? 'text-end' : 'text-start' }}"
                             style="font-size: 0.75rem;">
                            {{ $message->created_at->format('H:i | d/m') }}
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center text-muted mt-5" id="no-message-text">
                    Chưa có tin nhắn nào. Bắt đầu cuộc trò chuyện ngay!
                </p>
            @endforelse

        </div>

        <div class="card-footer bg-white border-top py-3">
            <form id="message-form" action="{{ route('owner.chats.send', $otherUser->id) }}" method="POST" class="d-flex">
                @csrf
                <input type="text" name="message" id="message-input"
                        class="form-control form-control-lg me-2"
                        placeholder="Nhập tin nhắn..." required autofocus>
                <button type="submit" class="btn btn-primary btn-lg px-4 d-flex align-items-center">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
const conversationId = {{ $conversation->id ?? 0 }};
const authUserId = {{ Auth::id() }};
const chatBox = document.getElementById('chat-box');
let noMessageText = document.getElementById('no-message-text');

function scrollToBottom() {
    chatBox.scrollTop = chatBox.scrollHeight;
}

function createMessageHtml(messageText, timeString, isSender) {
    const bgColor = isSender ? '#4f46e5' : '#e5e7eb';
    const textColor = isSender ? 'white' : '#111';
    const alignment = isSender ? 'justify-content-end' : 'justify-content-start';
    let borderRadius = "border-radius: 20px; word-wrap: break-word;";
    borderRadius += isSender
        ? " border-top-right-radius: 5px; border-bottom-right-radius: 5px;"
        : " border-top-left-radius: 5px; border-bottom-left-radius: 5px;";

    return `
    <div class="d-flex w-100 ${alignment}">
        <div style="max-width: 70%;" class="${isSender ? 'ms-auto' : 'me-auto'}">
            <div class="p-3 shadow-sm" style="max-width:100%; background: ${bgColor}; color: ${textColor};
                         margin-bottom: 2px; ${borderRadius}">
                ${messageText}
            </div>
            <div class="text-muted small mt-1 ${isSender ? 'text-end' : 'text-start'}"
                 style="font-size: 0.75rem;">
                ${timeString}
            </div>
        </div>
    </div>`;
}

function initializeEchoListener() {
    if (typeof window.Echo === 'undefined' || conversationId <= 0) {
        return false;
    }

    window.Echo.private(`chat.${conversationId}`)
        .listen('.new-message', (e) => {
            if (noMessageText) {
                noMessageText.remove();
                noMessageText = null;
            }
            
            // Hiển thị tin nhắn từ cả hai người (không filter)
            chatBox.insertAdjacentHTML('beforeend', createMessageHtml(e.message, e.created_at, e.sender_id === authUserId));
            scrollToBottom();
        });
    
    console.log("[v0] Echo listener initialized for chat." + conversationId);
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    scrollToBottom();
    const inputField = document.getElementById('message-input');
    const messageForm = document.getElementById('message-form');

    setTimeout(() => inputField.focus(), 100);

    messageForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const messageText = inputField.value.trim();
        if (!messageText) return;

        const csrfToken = document.querySelector('input[name="_token"]').value;

        if (noMessageText) {
            noMessageText.remove();
            noMessageText = null;
        }

        const now = new Date();
        const timeString = `${now.getHours().toString().padStart(2,'0')}:${now.getMinutes().toString().padStart(2,'0')} | Đang gửi...`;
        
        const messageElement = document.createElement('div');
        messageElement.innerHTML = createMessageHtml(messageText, timeString, true);
        messageElement.setAttribute('data-pending', 'true');
        chatBox.appendChild(messageElement);
        scrollToBottom();

        fetch(messageForm.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({message: messageText, _token: csrfToken})
        })
        .then(r => {
            if (!r.ok) throw new Error(`HTTP ${r.status}`);
            return r.json();
        })
        .then(data => {
            const lastMsg = chatBox.lastElementChild;
            if (lastMsg && lastMsg.hasAttribute('data-pending')) {
                lastMsg.querySelector('.text-muted.small').textContent = data.created_at;
                lastMsg.removeAttribute('data-pending');
            }
            inputField.value = '';
            inputField.focus();
        }).catch(err => {
            console.error("[v0] Send failed:", err);
            const lastMsg = chatBox.lastElementChild;
            if (lastMsg && lastMsg.hasAttribute('data-pending')) {
                lastMsg.querySelector('.text-muted.small').textContent = 'Gửi thất bại ⚠️';
                lastMsg.querySelector('.p-3').style.opacity = '0.6';
            }
        });
    });

    let retryCount = 0;
    const retryInterval = setInterval(() => {
        if (initializeEchoListener()) {
            clearInterval(retryInterval);
        } else if (retryCount++ > 20) {
            clearInterval(retryInterval);
            console.error("[v0] Could not initialize Echo listener after 20 retries");
        }
    }, 250);
});
</script>

@endpush
