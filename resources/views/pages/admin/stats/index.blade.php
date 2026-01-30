@extends('layouts.admin')

@section('title', 'Статистика соревнований')
@section('titleIcon', 'chart-bar')
@php
    $activeSidebar = 'admin.stats';
@endphp

@section('header-actions')
    <div class="btn-group">
        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fas fa-download me-1"></i> Экспорт
        </button>
        <ul class="dropdown-menu">
            <li>
                <a class="dropdown-item" href="{{ route('admin.stats.export') }}?format=csv">
                    <i class="fas fa-file-csv me-2"></i> CSV
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('admin.stats.export') }}?format=json">
                    <i class="fas fa-file-code me-2"></i> JSON
                </a>
            </li>
        </ul>
    </div>
@endsection

@section('content')
<!-- Общая статистика -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Активных команд</h6>
                        <h2 class="mb-0">{{ $stats['global']['total_teams'] }}</h2>
                    </div>
                    <i class="fas fa-users fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Выполняют задание</h6>
                        <h2 class="mb-0">{{ $stats['global']['active_teams'] }}</h2>
                    </div>
                    <i class="fas fa-tasks fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Всего участников</h6>
                        <h2 class="mb-0">{{ $stats['global']['total_participants'] }}</h2>
                    </div>
                    <i class="fas fa-user-friends fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Точность попыток</h6>
                        <h2 class="mb-0">{{ $stats['global']['accuracy_rate'] }}%</h2>
                    </div>
                    <i class="fas fa-bullseye fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Лидерборд -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-trophy me-2"></i> Топ-10 команд
        </h6>
        <span class="badge bg-light text-primary">Обновлено: {{ now()->format('H:i:s') }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="60">Место</th>
                        <th>Команда</th>
                        <th>Капитан</th>
                        <th>Участников</th>
                        <th>Счет</th>
                        <th>Флагов</th>
                        <th>Ошибок</th>
                        <th>Активное задание</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['teams'] as $index => $teamData)
                        <tr>
                            <td>
                                <div class="text-center">
                                    @if($index == 0)
                                        <span class="badge bg-warning text-dark fs-6">🥇</span>
                                    @elseif($index == 1)
                                        <span class="badge bg-secondary fs-6">🥈</span>
                                    @elseif($index == 2)
                                        <span class="badge bg-danger fs-6">🥉</span>
                                    @else
                                        <span class="fw-bold text-muted">#{{ $index + 1 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <strong>{{ $teamData['team']->name }}</strong>
                            </td>
                            <td>
                                {{ $teamData['team']->captain->full_name ?? 'N/A' }}
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $teamData['team']->members->count() }}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary fs-5">{{ $teamData['team']->score }}</span>
                            </td>
                            <td>
                                <span class="badge bg-success">{{ $teamData['team']->flags_found }}</span>
                            </td>
                            <td>
                                <span class="badge bg-danger">{{ $teamData['team']->wrong_attempts }}</span>
                            </td>
                            <td>
                                @if($teamData['task'])
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                        <div>
                                            <small class="d-block">{{ $teamData['task']->name }}</small>
                                            <small class="text-muted">
                                                @if($teamData['progress']['flag1'] && $teamData['progress']['flag2'])
                                                    <span class="text-success">Оба флага</span>
                                                @elseif($teamData['progress']['flag1'])
                                                    <span class="text-warning">1/2 флагов</span>
                                                @else
                                                    <span class="text-danger">0/2 флагов</span>
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">Нет активных заданий</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Статистика заданий -->
<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-tasks me-2"></i> Статистика заданий
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Задание</th>
                                <th>Назначено</th>
                                <th>Завершили</th>
                                <th>Средний счет</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['tasks'] as $taskData)
                                <tr>
                                    <td>
                                        <strong>{{ $taskData['task']->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $taskData['teams_count'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ $taskData['completed_count'] }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ $taskData['average_score'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-warning text-white">
                <h6 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i> Распределение попыток
                </h6>
            </div>
            <div class="card-body">
                <canvas id="attemptsChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- График активности -->
<div class="card shadow-sm border-0 mt-4">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i class="fas fa-chart-line me-2"></i> Активность попыток по часам
        </h6>
    </div>
    <div class="card-body">
        <canvas id="activityChart" height="100"></canvas>
    </div>
</div>
@endsection

@push('scripts')
<script>
// График распределения попыток
const attemptsCtx = document.getElementById('attemptsChart').getContext('2d');
const attemptsChart = new Chart(attemptsCtx, {
    type: 'doughnut',
    data: {
        labels: ['Правильные попытки', 'Неправильные попытки'],
        datasets: [{
            data: [
                {{ $stats['global']['correct_attempts'] }},
                {{ $stats['global']['total_flag_attempts'] - $stats['global']['correct_attempts'] }}
            ],
            backgroundColor: [
                '#198754',
                '#dc3545'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.raw || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = Math.round((value / total) * 100);
                        return `${label}: ${value} (${percentage}%)`;
                    }
                }
            }
        }
    }
});

// График активности
@php
    $hourlyData = [];
    for ($i = 0; $i < 24; $i++) {
        $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
        $hourlyData[$hour] = 0;
    }
    
    foreach($flagStats as $date => $data) {
        foreach($data as $attempt) {
            if (isset($hourlyData[$attempt->hour])) {
                $hourlyData[$attempt->hour] += $attempt->count;
            }
        }
    }
@endphp

const activityCtx = document.getElementById('activityChart').getContext('2d');
const activityChart = new Chart(activityCtx, {
    type: 'bar',
    data: {
        labels: [
            @foreach($hourlyData as $hour => $count)
                '{{ $hour }}:00',
            @endforeach
        ],
        datasets: [{
            label: 'Количество попыток',
            data: [
                @foreach($hourlyData as $count)
                    {{ $count }},
                @endforeach
            ],
            backgroundColor: 'rgba(13, 110, 253, 0.5)',
            borderColor: '#0d6efd',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Попыток: ' + context.raw;
                    }
                }
            }
        }
    }
});

// Автообновление каждые 30 секунд
setTimeout(() => {
    location.reload();
}, 30000);
</script>
@endpush