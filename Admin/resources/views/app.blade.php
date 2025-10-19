<!DOCTYPE html>
<html lang="en">
  @include('layout.head')
  <body>
    <div class="container-scroller">
      @include('layout.nav')

      <div class="container-fluid page-body-wrapper">
        @include('layout.sidebar')

        <div class="main-panel">
          {{-- Content --}}
          <div class="content-wrapper mb-4">
            @yield('content')
          </div>
          {{-- EndContent --}}
          
          @include('layout.footer')
        </div>
      </div>
    </div>

    @include('layout.scrip')
    @stack('scripts')
  </body>
</html>
