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
         @yield('content')

         {{-- EndCotent --}}
         
          @include('layout.footer')
        </div>
      </div>
    </div>
    @include('layout.scrip')
  </body>
</html>