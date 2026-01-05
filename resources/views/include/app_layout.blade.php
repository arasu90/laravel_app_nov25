<!DOCTYPE html>
<html lang="en">
  @include('include.head')
  <body class="app sidebar-mini rtl">
    @include('include.header')
    <!-- Sidebar menu-->
    @include('include.sidebar')
    <main class="app-content">
        @yield('content')
    </main>
    @include('include.footer')
  </body>
</html>
