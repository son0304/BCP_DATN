<!-- resources/views/partials/notification.blade.php -->

<!-- 1. CSS RIÊNG CHO THÔNG BÁO -->
<style>
    /* Hiệu ứng rung */
    @keyframes pulse-ring {
        0% { transform: scale(0.8); box-shadow: 0 0 0 0 rgba(255, 59, 48, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(255, 59, 48, 0); }
        100% { transform: scale(0.8); box-shadow: 0 0 0 0 rgba(255, 59, 48, 0); }
    }
    .pulse-ring {
        display: none;
        position: absolute; top: 8px; right: 8px; width: 15px; height: 15px;
        border-radius: 50%; animation: pulse-ring 2s infinite; z-index: 0;
    }
    .has-urgent .pulse-ring { display: block; }

    /* Giao diện thẻ */
    .border-left-danger { border-left: 5px solid #ff3b30 !important; }
    .border-left-warning { border-left: 5px solid #ffcc00 !important; }
    .card-hover:hover { transform: translateY(-2px); transition: all 0.2s; box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
</style>

<!-- 2. MODAL HIỂN THỊ (Giao diện to) -->
<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bg-light">
            <div class="modal-header bg-white border-bottom py-3">
                <h4 class="modal-title d-flex align-items-center text-dark">
                    <i class="fe-bell text-warning mr-2" style="font-size: 1.5rem;"></i>
                    Trung tâm thông báo
                    <span class="badge badge-danger ml-2" id="modalCountBadge">0</span>
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body p-4">
                <!-- Khu vực render JS -->
                <h5 class="text-uppercase text-danger font-weight-bold mb-3"><i class="fe-alert-circle mr-1"></i> Cần xử lý ngay</h5>
                <div class="row" id="urgentList">
                    <div class="col-12 text-center py-5 text-muted">Đang tải dữ liệu...</div>
                </div>
            </div>
            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-light" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- 3. JAVASCRIPT XỬ LÝ (Realtime / API) -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Dữ liệu giả lập (Sau này thay bằng API gọi server)
        const mockData = [
            { id: 101, court: 'Sân 1', user: 'Nguyễn Văn A', time: 'Quá 5 phút', type: 'expired', price: '200.000đ', status: 'Chưa TT' },
            { id: 102, court: 'Sân VIP', user: 'Trần Thị B', time: 'Còn 10 phút', type: 'expiring', price: '350.000đ', status: 'Đang đá' },
            { id: 104, court: 'Sân 5', user: 'Phạm D', time: 'Còn 5 phút', type: 'expiring', price: '180.000đ', status: 'Đang đá' }
        ];

        // Gọi hàm render
        renderNotifications(mockData);

        function renderNotifications(data) {
            // Cập nhật số lượng Badge ở Navbar (thông qua ID)
            const count = data.length;

            // Cập nhật DOM ở Nav (Lưu ý: ID này nằm ở file Nav)
            const lblCount = document.getElementById('lblNotificationCount');
            const liElement = document.getElementById('notificationLi');
            const modalBadge = document.getElementById('modalCountBadge');

            if(lblCount) lblCount.innerText = count;
            if(modalBadge) modalBadge.innerText = count;

            if (count > 0 && liElement) {
                liElement.classList.add('has-urgent');
            } else if (liElement) {
                liElement.classList.remove('has-urgent');
            }

            // Render HTML
            let html = '';
            if (count === 0) {
                html = '<div class="col-12 text-center text-muted">Không có thông báo nào.</div>';
            } else {
                data.forEach(item => {
                    let isExpired = item.type === 'expired';
                    let borderClass = isExpired ? 'border-left-danger' : 'border-left-warning';
                    let badgeClass = isExpired ? 'badge-soft-danger' : 'badge-soft-warning';

                    // Nút bấm hành động
                    let btnAction = isExpired
                        ? `<button class="btn btn-danger btn-sm flex-grow-1" onclick="alert('Xử lý trả sân: ${item.court}')">Checkout</button>`
                        : `<button class="btn btn-warning text-white btn-sm flex-grow-1">Nhắc nhở</button>`;

                    html += `
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card shadow-sm h-100 card-hover ${borderClass}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="card-title font-weight-bold m-0 text-dark">${item.court}</h5>
                                    <span class="badge ${badgeClass} p-2">${item.time}</span>
                                </div>
                                <div class="media mb-3">
                                    <div class="media-body">
                                        <h6 class="mt-0 mb-1 font-weight-bold">${item.user}</h6>
                                        <p class="text-muted mb-0 font-13">${item.status}: <b class="text-danger">${item.price}</b></p>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">${btnAction}</div>
                            </div>
                        </div>
                    </div>`;
                });
            }
            const listContainer = document.getElementById('urgentList');
            if(listContainer) listContainer.innerHTML = html;
        }
    });
</script>
