@extends('layouts.app')

@section('title', 'Арена')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <!-- Информация о команде -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-users"></i> Команда: {{ $team->name }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Капитан:</strong> {{ $team->captain->full_name ?? 'Не назначен' }}
                            </p>
                            <p class="mb-2">
                                <strong>Участников:</strong> {{ $team->members->count() }}
                            </p>
                            <p class="mb-2">
                                <strong>Общий счет:</strong> 
                                <span class="badge bg-success">{{ $team->score }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Найдено флагов:</strong> 
                                <span class="badge bg-info">{{ $team->flags_found }}</span>
                            </p>
                            <p class="mb-2">
                                <strong>Неправильных попыток:</strong> 
                                <span class="badge bg-danger">{{ $team->wrong_attempts }}</span>
                            </p>
                            <p class="mb-0">
                                <strong>Статус:</strong> 
                                @if($team->is_active)
                                    <span class="badge bg-success">Активна</span>
                                @else
                                    <span class="badge bg-danger">Неактивна</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Активное задание -->
            @if($activeTask)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-tasks"></i> Активное задание: {{ $activeTask->name }}
                        </h5>
                        <div id="task-timer" class="fs-4 fw-bold"></div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Целевая машина:</strong>
                                <code class="bg-light p-1 rounded">{{ $team->target_ip ?? $activeTask->target_ip_subnet }}</code>
                            </p>
                            <p class="mb-2">
                                <strong>Продолжительность:</strong> {{ $activeTask->duration_minutes }} минут
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Начато:</strong> {{ $activeTask->pivot->started_at->format('H:i:s') }}
                            </p>
                            <p class="mb-2">
                                <strong>Прогресс:</strong>
                                <div class="progress" style="height: 20px;">
                                    @php
                                        $progress = 0;
                                        if ($activeTask->pivot->flag1_found) $progress += 50;
                                        if ($activeTask->pivot->flag2_found) $progress += 50;
                                    @endphp
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ $progress }}%">
                                        {{ $progress }}%
                                    </div>
                                </div>
                            </p>
                        </div>
                    </div>

                    @if($activeTask->description)
                    <div class="alert alert-light border">
                        <h6><i class="fas fa-file-alt"></i> Описание задания:</h6>
                        <p class="mb-0">{{ $activeTask->description }}</p>
                    </div>
                    @endif

                    <!-- Форма отправки флагов -->
                    <div class="mt-4">
                        <form method="POST" action="{{ route('arena.submit') }}" id="flag-form">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="flag1" class="form-label">
                                        <i class="fas fa-flag text-success"></i> Первый флаг ({{ $activeTask->flag1_points }} баллов)
                                    </label>
                                    <div class="input-group">
                                        <input type="text" 
                                               class="form-control" 
                                               id="flag1" 
                                               name="flag" 
                                               placeholder="Введите первый флаг"
                                               {{ $activeTask->pivot->flag1_found ? 'disabled' : '' }}>
                                        <input type="hidden" name="flag_type" value="flag1">
                                        <button type="submit" 
                                                class="btn {{ $activeTask->pivot->flag1_found ? 'btn-success' : 'btn-outline-success' }}" 
                                                {{ $activeTask->pivot->flag1_found ? 'disabled' : '' }}>
                                            @if($activeTask->pivot->flag1_found)
                                                <i class="fas fa-check"></i> Найден
                                            @else
                                                <i class="fas fa-paper-plane"></i> Отправить
                                            @endif
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="flag2" class="form-label">
                                        <i class="fas fa-flag text-danger"></i> Второй флаг ({{ $activeTask->flag2_points }} баллов)
                                    </label>
                                    <div class="input-group">
                                        <input type="text" 
                                               class="form-control" 
                                               id="flag2" 
                                               name="flag" 
                                               placeholder="Введите второй флаг"
                                               {{ $activeTask->pivot->flag2_found ? 'disabled' : '' }}>
                                        <input type="hidden" name="flag_type" value="flag2">
                                        <button type="submit" 
                                                class="btn {{ $activeTask->pivot->flag2_found ? 'btn-success' : 'btn-outline-danger' }}" 
                                                {{ $activeTask->pivot->flag2_found ? 'disabled' : '' }}>
                                            @if($activeTask->pivot->flag2_found)
                                                <i class="fas fa-check"></i> Найден
                                            @else
                                                <i class="fas fa-paper-plane"></i> Отправить
                                            @endif
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <div class="alert alert-warning mt-3">
                            <small>
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Внимание:</strong> За каждую неправильную попытку команда теряет 
                                {{ config('ctf.scoring.wrong_attempt_penalty') }} балла.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center py-5">
                    <i class="fas fa-hourglass-half fa-3x text-muted mb-3"></i>
                    <h4>Нет активных заданий</h4>
                    <p class="text-muted">Ожидайте, пока администратор назначит задание вашей команде.</p>
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- История попыток -->
            @if($team->flagAttempts()->count() > 0)
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> История попыток
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Время</th>
                                    <th>Пользователь</th>
                                    <th>Тип флага</th>
                                    <th>Результат</th>
                                    <th>Баллы</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($team->flagAttempts()->latest()->limit(10)->get() as $attempt)
                                <tr>
                                    <td>{{ $attempt->created_at->format('H:i:s') }}</td>
                                    <td>{{ $attempt->user->username }}</td>
                                    <td>
                                        @if($attempt->flag_type === 'flag1')
                                            <span class="badge bg-success">Флаг 1</span>
                                        @else
                                            <span class="badge bg-danger">Флаг 2</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attempt->is_correct)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Успешно
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times"></i> Неверно
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attempt->is_correct)
                                            <span class="text-success fw-bold">
                                                +{{ $attempt->flag_type === 'flag1' ? $activeTask->flag1_points : $activeTask->flag2_points }}
                                            </span>
                                        @else
                                            <span class="text-danger fw-bold">
                                                -{{ config('ctf.scoring.wrong_attempt_penalty') }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Боковая панель -->
        <div class="col-lg-4">
            <!-- Состав команды -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-friends"></i> Состав команды
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($team->members as $member)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">
                                        {{ $member->full_name }}
                                        @if($member->isCaptain())
                                            <span class="badge bg-primary">Капитан</span>
                                        @endif
                                    </h6>
                                    <small class="text-muted">
                                        {{ $member->username }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    <small class="d-block">{{ $member->email }}</small>
                                    <small class="d-block">{{ $member->phone }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Статистика задания -->
            @if($activeTask)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Статистика задания</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded bg-success bg-opacity-10">
                                <div class="fs-4 fw-bold text-success">
                                    {{ $activeTask->pivot->score }}
                                </div>
                                <small class="text-muted">Баллов</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded bg-danger bg-opacity-10">
                                <div class="fs-4 fw-bold text-danger">
                                    {{ $activeTask->pivot->wrong_attempts }}
                                </div>
                                <small class="text-muted">Штрафов</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="progress mb-2" style="height: 10px;">
                        @php
                            $flag1Progress = $activeTask->pivot->flag1_found ? 100 : 0;
                            $flag2Progress = $activeTask->pivot->flag2_found ? 100 : 0;
                        @endphp
                        <div class="progress-bar bg-success" style="width: {{ $flag1Progress }}%"></div>
                        <div class="progress-bar bg-danger" style="width: {{ $flag2Progress }}%"></div>
                    </div>
                    <div class="small text-muted">
                        <span class="d-inline-block me-3">
                            <span class="badge bg-success">●</span> Флаг 1
                        </span>
                        <span class="d-inline-block">
                            <span class="badge bg-danger">●</span> Флаг 2
                        </span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Код приглашения -->
            @if(auth()->user()->isCaptain())
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">
                        <i class="fas fa-ticket-alt"></i> Код приглашения
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <code class="bg-dark text-white p-2 rounded fs-4 d-block">
                            {{ $team->invite_code }}
                        </code>
                        <small class="text-muted">
                            Поделитесь этим кодом с участниками для приглашения в команду
                        </small>
                    </div>
                    <button class="btn btn-outline-warning btn-sm" 
                            onclick="navigator.clipboard.writeText('{{ $team->invite_code }}')">
                        <i class="fas fa-copy"></i> Копировать код
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($activeTask && $remainingTime > 0)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const timerElement = document.getElementById('task-timer');
    let remainingTime = {{ $remainingTime }};
    
    function updateTimer() {
        if (remainingTime <= 0) {
            timerElement.innerHTML = '<span class="text-danger">Время вышло!</span>';
            // Перезагружаем страницу
            setTimeout(() => location.reload(), 5000);
            return;
        }
        
        const hours = Math.floor(remainingTime / 3600);
        const minutes = Math.floor((remainingTime % 3600) / 60);
        const seconds = remainingTime % 60;
        
        timerElement.innerHTML = `
            <span class="${remainingTime < 300 ? 'text-danger' : 'text-info'}">
                ${hours.toString().padStart(2, '0')}:
                ${minutes.toString().padStart(2, '0')}:
                ${seconds.toString().padStart(2, '0')}
            </span>
        `;
        
        remainingTime--;
    }
    
    updateTimer();
    const timerInterval = setInterval(updateTimer, 1000);
});
</script>
@endif

<script>
// Отправка флагов через AJAX с HTMX
document.addEventListener('DOMContentLoaded', function() {
    const flagForm = document.getElementById('flag-form');
    
    if (flagForm) {
        flagForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            
            // Сохраняем оригинальный текст кнопки
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Отправка...';
            submitButton.disabled = true;
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    // Обновляем страницу через 2 секунды
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert('danger', data.message);
                    submitButton.innerHTML = originalText;
                    submitButton.disabled = false;
                }
            })
            .catch(error => {
                showAlert('danger', 'Ошибка при отправке флага');
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
            });
        });
    }
    
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.container');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Автоматически скрыть через 5 секунд
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }, 5000);
    }
});
</script>

<style>
#task-timer {
    font-family: 'Courier New', monospace;
    background: rgba(0, 0, 0, 0.1);
    padding: 5px 15px;
    border-radius: 5px;
}

.progress {
    border-radius: 10px;
    overflow: hidden;
}

.list-group-item:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.input-group .btn {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}
</style>
@endpush