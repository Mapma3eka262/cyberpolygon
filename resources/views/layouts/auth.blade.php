<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CTF Platform')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
    
    @stack('styles')
</head>
<body class="bg-light">
    
    <!-- Шапка -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-flag me-2"></i>CTF Platform
            </a>
            <div class="navbar-nav">
                <a class="nav-link text-white" href="{{ route('home') }}">
                    <i class="fas fa-home me-1"></i> На главную
                </a>
            </div>
        </div>
    </nav>

    <!-- Основной контент -->
    <main class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <!-- Уведомления -->
                    @include('components.alerts.success')
                    @include('components.alerts.error')
                    
                    <!-- Контент страницы -->
                    @yield('content')
                </div>
            </div>
        </div>
    </main>

    <!-- Подвал -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <a href="{{ route('privacy') }}" class="text-white text-decoration-none">
                        <i class="fas fa-shield-alt me-1"></i> Политика конфиденциальности
                    </a>
                </div>
                <div class="col-md-6 text-end">
                    © Компания СКАНБИТ. Все права защищены
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    @stack('scripts')
</body>
</html>