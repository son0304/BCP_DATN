@extends('app')

@section('content')

<div class="mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 pb-0">
            <h1 class="h3 mb-0 fw-bold"><i class="fas fa-comments me-2"></i></i> Hộp thư đến</h1>
        </div>

        @if (session('success'))
            <div class="alert alert-success mx-4 mt-3" role="alert">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger mx-4 mt-3" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="card-body">
            <div class="row g-4">

                <div class="col-12 col-lg-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <h2 class="h5 mb-0"><i class="fas fa-users me-2"></i></i> Liên hệ Admin</h2>
                        </div>
                        <div class="card-body p-0" style="max-height: 70vh; overflow-y: auto;">
                            @if (isset($adminUsers) && $adminUsers->isNotEmpty())
                                <div class="list-group list-group-flush">
                                    @foreach ($adminUsers as $adminUser)
                                        <a href="{{ route('owner.chats.show', $adminUser->id) }}" class="list-group-item list-group-item-action d-flex align-items-center py-3">
                                            <img class="rounded-circle me-3" src="{{ $adminUser->avt ?? 'https://placehold.co/40x40/1d4ed8/ffffff?text=AD' }}" alt="{{ $adminUser->name }}" style="width: 40px; height: 40px; object-fit: cover;">
                                            <div>
                                                <p class="mb-0 fw-semibold text-dark">{{ $adminUser->name }} <span class="badge bg-primary">Admin</span></p>
                                                <p class="mb-0 small text-muted">Trợ giúp & Hỗ trợ</p>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted p-3">Hiện không tìm thấy Admin nào để liên hệ.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-8">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header bg-success text-white">
                            <h2 class="h5 mb-0"><i class="fas fa-history me-2"></i> Các Cuộc Hội Thoại Gần Đây</h2>
                        </div>
                        <div class="card-body p-0" style="max-height: 70vh; overflow-y: auto;">

                            @if ($conversations->isEmpty())
                                <p class="text-muted p-3">Bạn chưa có cuộc hội thoại nào gần đây.</p>
                            @else
                                <div class="list-group list-group-flush">
                                    @foreach ($conversations as $conversation)
                                        @php
                                            // Tìm người đối diện trong cuộc hội thoại
                                            $currentUserId = Auth::id();
                                            $otherUser = ($conversation->user_one_id === $currentUserId) ? $conversation->userTwo : $conversation->userOne;
                                            // Lấy tin nhắn cuối cùng (nếu có)
                                            $latestMessage = $conversation->messages()->latest()->first();
                                        @endphp
                                        <a href="{{ route('owner.chats.show', $otherUser->id) }}" class="list-group-item list-group-item-action d-flex align-items-start py-3">
                                            <img class="rounded-circle me-3 flex-shrink-0" src="{{ $otherUser->avt ?? 'https://placehold.co/48x48/1e40af/ffffff?text=U' }}" alt="{{ $otherUser->name }}" style="width: 48px; height: 48px; object-fit: cover;">
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <p class="mb-1 fw-bold text-lg text-gray-900">{{ $otherUser->name }}</p>
                                                    <span class="text-sm text-muted ms-2 flex-shrink-0">
                                                        {{ $conversation->updated_at->diffForHumans() }}
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-600 mb-0 text-truncate" style="max-width: 90%;">
                                                    {{ $latestMessage ? \Illuminate\Support\Str::limit($latestMessage->message, 50) : 'Chưa có tin nhắn nào.' }}
                                                </p>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
