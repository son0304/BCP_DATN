<!-- ========================================== -->
<!-- 1. CSS (Giữ nguyên hiệu ứng rung & màu)    -->
<!-- ========================================== -->
<style>
    /* Hiệu ứng rung chuông */
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

    /* Màu nền & Animation */
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
        <div class="modal-content">
            <div class="modal-header border-bottom py-3 bg-light">
                <h5 class="modal-title d-flex align-items-center text-dark font-weight-bold">
                    <i class="fe-bell text-primary mr-2"></i> Thông báo
                    <span id="modalBadgeCount" class="badge badge-danger ml-2 shadow-sm">{{ $unreadCount ?? 0 }}</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body p-0">
                <div id="notificationList" class="list-group list-group-flush">
                    @forelse($notifications ?? [] as $item)
                        <!-- Link được lấy trực tiếp từ controller -->
                        <a href="{{ $item->link }}"
                            class="list-group-item list-group-item-action {{ $item->style->bg }} py-3">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 font-weight-bold text-dark">
                                    <i class="{{ $item->style->icon }} {{ $item->style->text }} mr-1"></i>
                                    {{ $item->title }}
                                </h6>
                                <small class="text-muted" style="font-size: 0.75rem">{{ $item->time }}</small>
                            </div>
                            <p class="mb-0 text-secondary small pl-4">{{ $item->message }}</p>
                        </a>
                    @empty
                        <div class="text-center py-5 empty-state">
                            <i class="fe-bell-off text-muted opacity-50" style="font-size: 3rem;"></i>
                            <p class="mt-3 text-muted">Hiện tại không có thông báo nào.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-light text-muted" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 3. JAVASCRIPT REALTIME                     -->
<!-- ========================================== -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const elements = {
            navBadge: document.getElementById('lblNotificationCount'),
            modalBadge: document.getElementById('modalBadgeCount'),
            navIconLi: document.getElementById('notificationLi'),
            list: document.getElementById('notificationList')
        };
        const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');

        function handleNotification(eventData) {
            // 1. Lấy dữ liệu
            const noti = eventData.data; // Đây là model Notification
            console.log('🔔 New Notification:', noti);

            // Phát âm thanh
            audio.play().catch(() => {});

            // 2. Parse dữ liệu cột 'data' (JSON)
            let extraData = {};
            if (typeof noti.data === 'string') {
                try {
                    extraData = JSON.parse(noti.data);
                } catch (e) {}
            } else {
                extraData = noti.data || {};
            }

            // 3. Cập nhật Badge số lượng
            let currentCount = parseInt(elements.navBadge?.innerText || '0');
            let newCount = currentCount + 1;
            if (elements.navBadge) {
                elements.navBadge.innerText = newCount;
                elements.navBadge.style.display = 'inline-block';
            }
            if (elements.modalBadge) elements.modalBadge.innerText = newCount;
            if (elements.navIconLi) elements.navIconLi.classList.add('has-urgent');

            // 4. Xác định Link & Style (Logic JS tương tự PHP để đồng bộ)
            const link = extraData.link || '#';
            const title = noti.title || 'Thông báo mới';
            const message = noti.message || '';

            // Map màu sắc theo type
            let bgClass = 'bg-soft-info';
            let iconClass = 'fe-bell text-primary';

            switch (noti.type) {
                case 'danger':
                    bgClass = 'bg-soft-danger';
                    iconClass = 'fe-alert-circle text-danger';
                    break;
                case 'warning':
                    bgClass = 'bg-soft-warning';
                    iconClass = 'fe-alert-triangle text-warning';
                    break;
                case 'success':
                    bgClass = 'bg-soft-success';
                    iconClass = 'fe-check-circle text-success';
                    break;
            }

            // 5. Tạo HTML
            const htmlItem = `
                <a href="${link}" class="list-group-item list-group-item-action ${bgClass} new-noti-item py-3">
                    <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                        <h6 class="mb-0 font-weight-bold text-dark">
                            <i class="${iconClass} mr-1"></i> ${title}
                        </h6>
                        <small class="text-success font-weight-bold" style="font-size: 0.75rem">Vừa xong</small>
                    </div>
                    <p class="mb-0 text-secondary small pl-4">${message}</p>
                </a>
            `;

            // 6. Chèn vào danh sách
            if (elements.list) {
                const emptyState = elements.list.querySelector('.empty-state');
                if (emptyState) emptyState.remove();
                elements.list.insertAdjacentHTML('afterbegin', htmlItem);
            }
        }

        // --- SOCKET LISTENER ---
        if (typeof Echo !== 'undefined') {
            Echo.channel('notification')
                .listen('.notification.created', (e) => handleNotification(e));
        }
    });
</script>
