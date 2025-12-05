// resources/js/app.js
import './bootstrap';
import axios from 'axios';
import Pusher from 'pusher-js';
import Echo from 'laravel-echo';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.Pusher = Pusher;

// <CHANGE> Khởi tạo Echo sau khi DOM sẵn sàng
function initializeEcho() {
    if (!window.Echo) {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: import.meta.env.VITE_PUSHER_APP_KEY || '389d067ddc747f8f8e9a',
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'ap1',
            forceTLS: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }
        });
        console.log("[v0] Echo initialized successfully");
    }
}

// Chạy ngay khi DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeEcho);
} else {
    initializeEcho();
}