<div class="left-side-menu">

    <div class="slimscroll-menu">

        <!--- Sidemenu -->
        <div id="sidebar-menu">

            <ul class="metismenu" id="side-menu">

                <li class="menu-title">Menu điều khiển</li>

                {{-- Menu chung --}}
                @if (auth()->check())


                {{-- Admin menu --}}
                @if (auth()->user()->role->name === 'admin')
                <li>
                    <a href="{{ route('admin.statistics.index') }}" class="waves-effect">
                        <i class="remixicon-dashboard-line"></i>
                        <span> Thống kê Sàn </span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.users.index') }}" class="waves-effect">
                        <i class="remixicon-user-line"></i>
                        <span> Người dùng </span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.venues.index') }}" class="waves-effect">
                        <i class="remixicon-store-2-line"></i>
                        <span> Quản lý Thương hiệu </span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.reviews.index') }}" class="waves-effect">
                        <i class="remixicon-star-line"></i>
                        <span> Đánh giá </span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.promotions.index') }}" class="waves-effect">
                        <i class="ri-coupon-line"></i>
                        <span> Voucher </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.chats.index') }}" class="waves-effect">
                        <i class="remixicon-chat-3-line"></i>
                        <span> Message </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.transactions.index') }}" class="waves-effect">
                        {{-- Icon hóa đơn --}}
                        <i class="ri-file-list-3-line"></i>
                        <span> Quản lý giao dịch </span>
                    </a>
                </li>
                @endif

                {{-- Venue Owner menu --}}
                @if (auth()->user()->role->name === 'venue_owner')
                <li>
                    <a href="{{ route('owner.statistics.index') }}" class="waves-effect">
                        <i class="remixicon-dashboard-line"></i>
                        <span> Thống kê Sàn </span>
                    </a>
                </li>
                <li>

                    <a href="{{ route('owner.venues.index') }}" class="waves-effect">
                        <i class="remixicon-store-2-line"></i>
                        <span> Thương Hiệu </span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('owner.reviews.index') }}" class="waves-effect">
                        <i class="remixicon-star-line"></i>
                        <span> Đánh giá </span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('owner.bookings.index') }}" class="waves-effect">
                        <i class="ri-ticket-line"></i>
                        <span> Đơn đặt sân </span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('owner.chats.index') }}" class="waves-effect">
                        <i class="remixicon-chat-3-line"></i>
                        <span> Message </span>
                    </a>
                </li>
                @endif
                @endif

            </ul>

        </div>
        <!-- End Sidebar -->

        <div class="clearfix"></div>

    </div>
    <!-- Sidebar -left -->

</div>