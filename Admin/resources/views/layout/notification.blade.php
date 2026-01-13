<!-- ========================================== -->
<!-- 1. CSS (Hiệu ứng & Màu sắc)                -->
<!-- ========================================== -->
<style>
    @keyframes pulse-ring {
        0% {
            transform: scale(0.8);
            box-shadow: 0 0 0 0 rgba(255, 59, 48, 0.7);
        }

        70% {
            transform: scale(1);
            box-shadow: 0 0 0 10px rgba(255, 59, 48, 0);
        }

        100% {
            transform: scale(0.8);
            box-shadow: 0 0 0 0 rgba(255, 59, 48, 0);
        }
    }

    .pulse-ring {
        display: none;
        position: absolute;
        top: 8px;
        right: 8px;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        animation: pulse-ring 2s infinite;
        z-index: 0;
    }

    .has-urgent .pulse-ring {
        display: block;
    }

    .bg-soft-danger {
        background-color: rgba(255, 59, 48, 0.1);
        border-left: 4px solid #ff3b30;
    }

    .bg-soft-warning {
        background-color: rgba(255, 204, 0, 0.1);
        border-left: 4px solid #ffcc00;
    }

    .bg-soft-info {
        background-color: rgba(58, 87, 232, 0.1);
        border-left: 4px solid #3a57e8;
    }

    .bg-soft-success {
        background-color: rgba(40, 167, 69, 0.1);
        border-left: 4px solid #28a745;
    }

    .new-noti-item {
        animation: slideIn 0.5s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<!-- ========================================== -->
<!-- 2. HTML MODAL                              -->
<!-- ========================================== -->
<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header border-bottom py-3 bg-light">
                <h5 class="modal-title d-flex align-items-center text-dark font-weight-bold">
                    <i class="fe-bell text-primary mr-2"></i> Thông báo
                    <span id="modalBadgeCount"
                        class="badge badge-danger ml-2 shadow-sm {{ ($unreadCount ?? 0) > 0 ? '' : 'd-none' }}">
                        {{ $unreadCount ?? 0 }}
                    </span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body p-0">
                <div id="notificationList" class="list-group list-group-flush">
                    @forelse($notifications ?? [] as $item)
                        @php $p = $item->presentation; @endphp
                        {{-- Đã sửa lỗi cú pháp thẻ a ở đây --}}
                        <a href="{{ route('notifications.read', $item->id) }}"
                            class="list-group-item list-group-item-action {{ $p->style->bg }} py-3 border-bottom">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 font-weight-bold text-dark">
                                    <i class="{{ $p->style->icon }} {{ $p->style->text }} mr-1"></i>
                                    {{ $item->title }}
                                </h6>
                                <small class="text-muted" style="font-size: 0.75rem">{{ $p->time }}</small>
                            </div>
                            <p class="mb-0 text-secondary small pl-4">{{ $item->message }}</p>
                        </a>
                    @empty
                        <div id="emptyNotiState" class="text-center py-5">
                            <i class="fe-bell-off text-muted opacity-50" style="font-size: 3rem;"></i>
                            <p class="mt-3 text-muted">Hiện tại không có thông báo nào.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="modal-footer bg-light py-2 text-center">
                <small class="text-muted w-100">Hệ thống thông báo thời gian thực</small>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 3. JAVASCRIPT LOGIC                        -->
<!-- ========================================== -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const elements = {
            navBadge: document.getElementById('lblNotificationCount'),
            modalBadge: document.getElementById('modalBadgeCount'),
            navIconLi: document.getElementById('notificationLi'),
            list: document.getElementById('notificationList'),
            emptyState: document.getElementById('emptyNotiState')
        };

        const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');

        // Hàm xử lý khi có thông báo mới (Realtime)
        function handleNotification(eventData) {
            const noti = eventData.notification;
            audio.play().catch(() => {});

            // Cập nhật Badge số lượng
            let currentCount = parseInt(elements.modalBadge?.innerText || '0');
            let newCount = currentCount + 1;

            [elements.navBadge, elements.modalBadge].forEach(el => {
                if (el) {
                    el.innerText = newCount;
                    el.classList.remove('d-none');
                }
            });
            if (elements.navIconLi) elements.navIconLi.classList.add('has-urgent');

            // Render Item mới
            const styles = {
                danger: {
                    bg: 'bg-soft-danger',
                    icon: 'fe-alert-circle',
                    text: 'text-danger'
                },
                warning: {
                    bg: 'bg-soft-warning',
                    icon: 'fe-alert-triangle',
                    text: 'text-warning'
                },
                success: {
                    bg: 'bg-soft-success',
                    icon: 'fe-check-circle',
                    text: 'text-success'
                },
                default: {
                    bg: 'bg-soft-info',
                    icon: 'fe-bell',
                    text: 'text-primary'
                }
            };

            const s = styles[noti.type] || styles.default;
            // Link cho realtime: Nên dẫn qua route read để tự động đánh dấu đã đọc khi click
            const readRoute = `/notifications/${noti.id}/read`;

            const htmlItem = `
                <a href="${readRoute}" class="list-group-item list-group-item-action ${s.bg} new-noti-item py-3 border-bottom">
                    <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                        <h6 class="mb-0 font-weight-bold text-dark">
                            <i class="${s.icon} ${s.text} mr-1"></i> ${noti.title || 'Thông báo'}
                        </h6>
                        <small class="text-success font-weight-bold" style="font-size: 0.75rem">Vừa xong</small>
                    </div>
                    <p class="mb-0 text-secondary small pl-4">${noti.message}</p>
                </a>
            `;

            if (elements.list) {
                if (elements.emptyState) elements.emptyState.remove();
                elements.list.insertAdjacentHTML('afterbegin', htmlItem);
            }
        }

        // Lắng nghe sự kiện mở Modal để đánh dấu đã đọc tất cả
        $('#notificationModal').on('show.bs.modal', function() {
            // Kiểm tra nếu đang có thông báo chưa đọc mới gửi request
            const currentCount = parseInt(elements.modalBadge?.innerText || '0');
            if (currentCount <= 0) return;

            fetch('/notifications/mark-all-as-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).then(response => {
                if (response.ok) {
                    // Xóa badge sau khi server đã xử lý
                    if (elements.navBadge) elements.navBadge.classList.add('d-none');
                    if (elements.modalBadge) elements.modalBadge.classList.add('d-none');
                    if (elements.navIconLi) elements.navIconLi.classList.remove('has-urgent');
                }
            }).catch(err => console.error('Lỗi khi đánh dấu đã đọc:', err));
        });

        // --- SOCKET LISTENER (Laravel Echo) ---
        if (typeof Echo !== 'undefined') {
            Echo.private(`App.Models.User.${window.userId}`)
                .notification((notification) => {
                    handleNotification({
                        notification
                    });
                });
        }
    });
</script>
