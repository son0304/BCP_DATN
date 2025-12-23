<!DOCTYPE html>
<html lang="en">

@include('layout.head')

<body>
    <div id="wrapper">
        @include('layout.nav')
        @include('layout.sidebar')
        <div class="content-page">
            <div class="py-4">
                <div class="container-fluid">
                    @yield('content')
                </div>

            </div>
            @include('layout.footer')
        </div>
    </div>
    @include('layout.right-bar')
    <div class="rightbar-overlay"></div>

    <script src="https://js.pusher.com/8.0/pusher.min.js"></script>

    <script src="{{ asset('js/app.js') }}"></script>

    @include('layout.scrip')

    @stack('scripts')
</body>

</html>
