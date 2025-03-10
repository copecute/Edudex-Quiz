<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Trang chủ')</title>
    <!-- Bootstrap 5 CSS -->
    <link href="{{ asset('bootstrap-5.3.3/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="{{ asset('font-awesome-6-pro/css/all.min.css') }}" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <script src="{{ asset('js/theme.js') }}"></script>
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    @yield('styles')
</head>
<body>
    @include('layouts.header')
    
    <div class="d-flex flex-column min-vh-100" style="margin-top: 80px;">
        <main class="flex-grow-1">
            @yield('content')
        </main>
        @include('layouts.footer')
    </div>

    @include('components.toast')

    <!-- Bootstrap 5 JS -->
    <script src="{{ asset('bootstrap-5.3.3/js/bootstrap.bundle.min.js') }}"></script>
    
    <!-- Toast Init Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toastElList = [].slice.call(document.querySelectorAll('.toast'));
            var toastList = toastElList.map(function(toastEl) {
                var toast = new bootstrap.Toast(toastEl, {
                    autohide: true,
                    delay: 3000
                });
                toast.show();
                return toast;
            });
        });
    </script>

    @stack('scripts')

    <!-- Thêm vào trước đóng thẻ body -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3">
        <!-- Toasts sẽ được thêm vào đây -->
    </div>
</body>
</html> 