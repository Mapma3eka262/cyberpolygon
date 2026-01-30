@extends('layouts.admin')

@section('title', 'Аналитика системы')
@section('titleIcon', 'chart-line')
@php
    $activeSidebar = 'admin.analytics';
@endphp

@section('content')
<div class="row">
    <!-- Статистика системы -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="fas fa-microchip me-2"></i> Использование ресурсов
                </h6>
            </div>
            <div class="card-body">
                <!-- CPU -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold">Процессор (CPU)</span>
                        <span class="fw-bold text-primary">{{ round($metrics['cpu']['usage'] * 100, 1) }}%</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-primary" 
                             role="progressbar" 
                             style="width: {{ $metrics['cpu']['usage'] * 100 }}%">
                        </div>
                    </div>
                    <div class="small text-muted mt-1">
                        <span class="me-3">1 мин: {{ round($metrics['cpu']['load_1'] * 100, 1) }}%</span>
                        <span class="me-3">5 мин: {{ round($metrics['cpu']['load_5'] * 100, 1) }}%</span>
                        <span>15 мин: {{ round($metrics['cpu']['load_15'] * 100, 1) }}%</span>
                    </div>
                </div>
                
                <!-- Память -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold">Память (RAM)</span>
                        <span class="fw-bold text-success">{{ round($metrics['memory']['percentage'], 1) }}%</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-success" 
                             role="progressbar" 
                             style="width: {{ $metrics['memory']['percentage'] }}%">
                        </div>
                    </div>
                    <div class="small text-muted mt-1">
                        Используется: {{ round($metrics['memory']['used'], 2) }} MB /
                        Всего: {{ round($metrics['memory']['total'], 2) }} MB
                    </div>
                </div>
                
                <!-- Диск -->
                <div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold">Дисковое пространство</span>
                        <span class="fw-bold text-warning">{{ round($metrics['disk']['percentage'] ?? 0, 1) }}%</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-warning" 
                             role="progressbar" 
                             style="width: {{ $metrics['disk']['percentage'] ?? 0 }}%">
                        </div>
                    </div>
                    <div class="small text-muted mt-1">
                        Используется: {{ round($metrics['disk']['used'] ?? 0, 2) }} GB /
                        Всего: {{ round($metrics['disk']['total'] ?? 0, 2) }} GB
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Пользовательская статистика -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-users me-2"></i> Активность пользователей
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center mb-4">
                    <div class="col-4">
                        <div class="p-3 border rounded bg-light">
                            <div class="fs-3 fw-bold text-primary">{{ $metrics['users']['total'] }}</div>
                            <small class="text-muted">Всего пользователей</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 border rounded bg-light">
                            <div class="fs-3 fw-bold text-success">{{ $metrics['users']['active'] }}</div>
                            <small class="text-muted">Активных сейчас</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 border rounded bg-light">
                            <div class="fs-3 fw-bold text-warning">{{ $metrics['users']['online'] }}</div>
                            <small class="text-muted">Онлайн</small>
                        </div>
                    </div>
                </div>
                
                <div class="row text-center">
                    <div class="col-4">
                        <div class="p-2 border rounded">
                            <div class="fw-bold text-primary">{{ $metrics['users']['active_teams'] }}</div>
                            <small class="text-muted">Активных команд</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded">
                            <div class="fw-bold text-success">{{ $metrics['users']['total_attempts'] }}</div>
                            <small class="text-muted">Попыток/час</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded">
                            <div class="fw-bold text-danger">{{ $metrics['database']['connections'] }}</div>
                            <small class="text-muted">Соединений БД</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Графики -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fas fa-chart-area me-2"></i> Использование CPU за 24 часа
                </h6>
            </div>
            <div class="card-body">
                <canvas id="cpuChart" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fas fa-chart-area me-2"></i> Использование памяти за 24 часа
                </h6>
            </div>
            <div class="card-body">
                <canvas id="memoryChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- История логов -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-history me-2"></i> История системных логов
        </h6>
        <button class="btn btn-sm btn-outline-primary" onclick="refreshMetrics()">
            <i class="fas fa-sync-alt me-1"></i> Обновить
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Время</th>
                        <th>CPU %</th>
                        <th>Память %</th>
                        <th>Активных пользователей</th>
                        <th>Активных команд</th>
                        <th>Попыток</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $hour => $log)
                        <tr>
                            <td>{{ $hour }}:00</td>
                            <td>
                                <span class="fw-bold {{ $log['cpu_avg'] > 80 ? 'text-danger' : ($log['cpu_avg'] > 60 ? 'text-warning' : 'text-success') }}">
                                    {{ round($log['cpu_avg'] ?? 0, 1) }}%
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold {{ $log['memory_avg'] > 80 ? 'text-danger' : ($log['memory_avg'] > 60 ? 'text-warning' : 'text-success') }}">
                                    {{ round($log['memory_avg'] ?? 0, 1) }}%
                                </span>
                            </td>
                            <td>{{ round($log['active_users_avg'] ?? 0) }}</td>
                            <td>{{ round($log['active_teams_avg'] ?? 0) }}</td>
                            <td>{{ round($log['total_attempts_avg'] ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Нет данных для отображения
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// График CPU
const cpuCtx = document.getElementById('cpuChart').getContext('2d');
const cpuChart = new Chart(cpuCtx, {
    type: 'line',
    data: {
        labels: [
            @foreach($logs as $hour => $log)
                '{{ $hour }}',
            @endforeach
        ],
        datasets: [{
            label: 'Использование CPU (%)',
            data: [
                @foreach($logs as $log)
                    {{ round($log['cpu_avg'] ?? 0, 1) }},
                @endforeach
            ],
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'CPU: ' + context.raw + '%';
                    }
                }
            }
        }
    }
});

// График памяти
const memoryCtx = document.getElementById('memoryChart').getContext('2d');
const memoryChart = new Chart(memoryCtx, {
    type: 'line',
    data: {
        labels: [
            @foreach($logs as $hour => $log)
                '{{ $hour }}',
            @endforeach
        ],
        datasets: [{
            label: 'Использование памяти (%)',
            data: [
                @foreach($logs as $log)
                    {{ round($log['memory_avg'] ?? 0, 1) }},
                @endforeach
            ],
            borderColor: '#198754',
            backgroundColor: 'rgba(25, 135, 84, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Память: ' + context.raw + '%';
                    }
                }
            }
        }
    }
});

function refreshMetrics() {
    fetch('/admin/analytics/system-metrics')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ошибка при обновлении метрик');
        });
}

// Автообновление каждые 30 секунд
setTimeout(() => {
    refreshMetrics();
}, 30000);
</script>
@endpush