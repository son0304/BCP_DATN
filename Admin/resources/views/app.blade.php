<!DOCTYPE html>
<html lang="en">

@include('layout.head')

<body>

    <!-- Begin page -->
    <div id="wrapper">
        @include('layout.nav')
        @include('layout.sidebar')
        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>

            </div>
            <!-- Footer Start -->
            @include('layout.footer')
            <!-- end Footer -->
        </div>
    </div>
   @include('layout.right-bar')
    <div class="rightbar-overlay"></div>

    @include('layout.scrip')

</body>

</html>
