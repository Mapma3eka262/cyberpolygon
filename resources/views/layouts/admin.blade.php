<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель | @yield('title', 'CTF Platform')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    
    @stack('styles')
</head>
<body class="bg-light">
    
    <!-- Шапка -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                <i class="fas fa-cogs me-2"></i> Админ-панель
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i> Дашборд
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.teams.index') }}">
                            <i class="fas fa-users-cog"></i> Команды
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.tasks.index') }}">
                            <i class="fas fa-tasks"></i> Задания
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.analytics.index') }}">
                            <i class="fas fa-chart-line"></i> Аналитика
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.stats.index') }}">
                            <i class="fas fa-chart-bar"></i> Статистика
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}" title="На сайт">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}" title="Личный кабинет">
                            <i class="fas fa-user"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm" title="Выход">
                                <i class="fas fa-sign-out-alt"></i>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Контент -->
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Боковая панель -->
            <div class="col-md-3 col-lg-2 d-none d-md-block">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        @include('components.navigation.sidebar', ['active' => $activeSidebar ?? 'admin.dashboard'])
                    </div>
                </div>
                
                <!-- Статистика -->
                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Быстрая статистика</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $stats = \App\Services\Analytics\SystemMetricsService::collectMetrics();
                        @endphp
                        <div class="small">
                            <div class="d-flex justify-content-between mb-1">
                                <span>CPU:</span>
                                <span class="fw-bold">{{ round($stats['cpu']['usage'] * 100, 1) }}%</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>RAM:</span>
                                <span class="fw-bold">{{ round($stats['memory']['percentage'], 1) }}%</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Активных команд:</span>
                                <span class="fw-bold">{{ $stats['users']['active_teams'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Попыток/час:</span>
                                <span class="fw-bold">{{ $stats['users']['total_attempts'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Основной контент -->
            <div class="col-md-9 col-lg-10">
                <!-- Хлебные крошки -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb bg-white shadow-sm py-2 px-3 rounded">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Главная</a></li>
                        @if(isset($breadcrumbs))
                            @foreach($breadcrumbs as $breadcrumb)
                                @if(isset($breadcrumb['url']))
                                    <li class="breadcrumb-item"><a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a></li>
                                @else
                                    <li class="breadcrumb-item active">{{ $breadcrumb['title'] }}</li>
                                @endif
                            @endforeach
                        @else
                            <li class="breadcrumb-item active">@yield('title')</li>
                        @endif
                    </ol>
                </nav>
                
                <!-- Уведомления -->
                @include('components.alerts.success')
                @include('components.alerts.error')
                
                <!-- Заголовок страницы -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">
                        <i class="fas fa-{{ $titleIcon ?? 'cog' }} text-primary me-2"></i>
                        @yield('title')
                    </h2>
                    
                    @hasSection('header-actions')
                        <div>
                            @yield('header-actions')
                        </div>
                    @endif
                </div>
                
                <!-- Контент страницы -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@1.9.6"></script>
    
    <!-- Custom JS -->
    <script src="{{ asset('js/admin.js') }}"></script>
    
    @stack('scripts')
</body>
</html>