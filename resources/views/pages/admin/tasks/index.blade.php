@extends('layouts.admin')

@section('title', 'Управление заданиями')
@section('titleIcon', 'tasks')
@php
    $activeSidebar = 'admin.tasks';
@endphp

@section('header-actions')
    <a href="{{ route('admin.tasks.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Создать задание
    </a>
@endsection

@section('content')
<!-- Фильтры -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.tasks.index') }}" class="row g-3">
            <div class="col-md-5">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Поиск по названию или описанию..."
                       value="{{ request()->get('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Все статусы</option>
                    <option value="active" {{ request()->get('status') == 'active' ? 'selected' : '' }}>
                        Активные
                    </option>
                    <option value="inactive" {{ request()->get('status') == 'inactive' ? 'selected' : '' }}>
                        Неактивные
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="sort" class="form-select">
                    <option value="created_desc" {{ request()->get('sort') == 'created_desc' ? 'selected' : '' }}>
                        Новые сначала
                    </option>
                    <option value="created_asc" {{ request()->get('sort') == 'created_asc' ? 'selected' : '' }}>
                        Старые сначала
                    </option>
                    <option value="name_asc" {{ request()->get('sort') == 'name_asc' ? 'selected' : '' }}>
                        По названию (А-Я)
                    </option>
                    <option value="name_desc" {{ request()->get('sort') == 'name_desc' ? 'selected' : '' }}>
                        По названию (Я-А)
                    </option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-search me-1"></i> Найти
                </button>
                <a href="{{ route('admin.tasks.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Статистика -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Всего заданий</h6>
                        <h2 class="mb-0">{{ $tasks->total() }}</h2>
                    </div>
                    <i class="fas fa-tasks fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Активных</h6>
                        <h2 class="mb-0">{{ $tasks->where('is_active', true)->count() }}</h2>
                    </div>
                    <i class="fas fa-play-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Назначено команд</h6>
                        <h2 class="mb-0">{{ \App\Models\TeamTask::count() }}</h2>
                    </div>
                    <i class="fas fa-user-check fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Завершено</h6>
                        <h2 class="mb-0">{{ \App\Models\TeamTask::whereNotNull('completed_at')->count() }}</h2>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Таблица заданий -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40">#</th>
                        <th>Название задания</th>
                        <th>Целевая машина</th>
                        <th>Время</th>
                        <th>Баллы</th>
                        <th>Команд</th>
                        <th>Статус</th>
                        <th width="200">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr>
                            <td>{{ $loop->iteration + ($tasks->currentPage() - 1) * $tasks->perPage() }}</td>
                            <td>
                                <strong>{{ $task->name }}</strong>
                                @if($task->description)
                                    <br>
                                    <small class="text-muted">{{ Str::limit($task->description, 100) }}</small>
                                @endif
                            </td>
                            <td>
                                <code>{{ $task->target_ip_subnet }}</code>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $task->duration_minutes }} мин.</span>
                            </td>
                            <td>
                                <span class="badge bg-success">{{ $task->flag1_points }}</span>
                                <span class="badge bg-danger">{{ $task->flag2_points }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $task->teams_count ?? $task->teams()->count() }}</span>
                            </td>
                            <td>
                                @if($task->is_active)
                                    <span class="badge bg-success">Активно</span>
                                @else
                                    <span class="badge bg-secondary">Неактивно</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.tasks.assign.form', $task) }}" 
                                       class="btn btn-outline-primary" 
                                       title="Назначить командам">
                                        <i class="fas fa-user-plus"></i>
                                    </a>
                                    <a href="{{ route('admin.tasks.edit', $task) }}" 
                                       class="btn btn-outline-secondary" 
                                       title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-{{ $task->is_active ? 'warning' : 'success' }}"
                                            onclick="toggleTaskStatus({{ $task->id }}, {{ $task->is_active ? 'false' : 'true' }})"
                                            title="{{ $task->is_active ? 'Деактивировать' : 'Активировать' }}">
                                        <i class="fas fa-{{ $task->is_active ? 'ban' : 'check' }}"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-outline-danger"
                                            onclick="deleteTask({{ $task->id }}, '{{ $task->name }}')"
                                            title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Задания не найдены</p>
                                <a href="{{ route('admin.tasks.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Создать первое задание
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($tasks->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-center">
                {{ $tasks->links() }}
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function toggleTaskStatus(taskId, activate) {
    const action = activate ? 'активировать' : 'деактивировать';
    
    if (confirm(`Вы уверены, что хотите ${action} это задание?`)) {
        fetch(`/admin/tasks/${taskId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Ошибка при изменении статуса задания');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при изменении статуса задания');
        });
    }
}

function deleteTask(taskId, taskName) {
    if (confirm(`Вы уверены, что хотите удалить задание "${taskName}"?`)) {
        fetch(`/admin/tasks/${taskId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Ошибка при удалении задания');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при удалении задания');
        });
    }
}
</script>
@endpush