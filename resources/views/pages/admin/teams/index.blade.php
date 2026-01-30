@extends('layouts.admin')

@section('title', 'Управление командами')
@section('titleIcon', 'users-cog')
@php
    $activeSidebar = 'admin.teams';
@endphp

@section('header-actions')
    <a href="{{ route('admin.teams.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Создать команду
    </a>
@endsection

@section('content')
<!-- Фильтры -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.teams.index') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Поиск по названию или капитану..."
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
                    <option value="score_desc" {{ request()->get('sort') == 'score_desc' ? 'selected' : '' }}>
                        По убыванию счета
                    </option>
                    <option value="score_asc" {{ request()->get('sort') == 'score_asc' ? 'selected' : '' }}>
                        По возрастанию счета
                    </option>
                    <option value="name_asc" {{ request()->get('sort') == 'name_asc' ? 'selected' : '' }}>
                        По названию (А-Я)
                    </option>
                    <option value="name_desc" {{ request()->get('sort') == 'name_desc' ? 'selected' : '' }}>
                        По названию (Я-А)
                    </option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-search me-1"></i> Найти
                </button>
                <a href="{{ route('admin.teams.index') }}" class="btn btn-outline-secondary">
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
                        <h6 class="card-title mb-0">Всего команд</h6>
                        <h2 class="mb-0">{{ $teams->total() }}</h2>
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
                        <h6 class="card-title mb-0">Активных</h6>
                        <h2 class="mb-0">{{ $teams->where('is_active', true)->count() }}</h2>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Участников</h6>
                        <h2 class="mb-0">{{ \App\Models\User::whereNotNull('team_id')->count() }}</h2>
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
                        <h6 class="card-title mb-0">Средний счет</h6>
                        <h2 class="mb-0">{{ round($teams->avg('score') ?? 0) }}</h2>
                    </div>
                    <i class="fas fa-chart-line fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Таблица команд -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40">#</th>
                        <th>Название команды</th>
                        <th>Капитан</th>
                        <th>Участников</th>
                        <th>Счет</th>
                        <th>Флагов</th>
                        <th>Статус</th>
                        <th width="150">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teams as $team)
                        <tr>
                            <td>{{ $loop->iteration + ($teams->currentPage() - 1) * $teams->perPage() }}</td>
                            <td>
                                <strong>{{ $team->name }}</strong>
                                @if($team->invite_code)
                                    <br>
                                    <small class="text-muted">
                                        Код: <code>{{ $team->invite_code }}</code>
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($team->captain)
                                    <div>{{ $team->captain->full_name }}</div>
                                    <small class="text-muted">{{ $team->captain->email }}</small>
                                @else
                                    <span class="text-muted">Не назначен</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $team->members->count() }}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary">{{ $team->score }}</span>
                            </td>
                            <td>
                                <span class="badge bg-success">{{ $team->flags_found }}</span>
                            </td>
                            <td>
                                @if($team->is_active)
                                    <span class="badge bg-success">Активна</span>
                                @else
                                    <span class="badge bg-danger">Неактивна</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.teams.members', $team) }}" 
                                       class="btn btn-outline-primary" 
                                       title="Участники">
                                        <i class="fas fa-user-friends"></i>
                                    </a>
                                    <a href="{{ route('admin.teams.edit', $team) }}" 
                                       class="btn btn-outline-secondary" 
                                       title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-{{ $team->is_active ? 'warning' : 'success' }}"
                                            onclick="toggleTeamStatus({{ $team->id }}, {{ $team->is_active ? 'false' : 'true' }})"
                                            title="{{ $team->is_active ? 'Деактивировать' : 'Активировать' }}">
                                        <i class="fas fa-{{ $team->is_active ? 'ban' : 'check' }}"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Команды не найдены</p>
                                <a href="{{ route('admin.teams.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Создать первую команду
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($teams->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-center">
                {{ $teams->links() }}
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function toggleTeamStatus(teamId, activate) {
    const action = activate ? 'активировать' : 'деактивировать';
    
    if (confirm(`Вы уверены, что хотите ${action} эту команду?`)) {
        fetch(`/admin/teams/${teamId}/toggle-status`, {
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
                alert(data.message || 'Ошибка при изменении статуса команды');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при изменении статуса команды');
        });
    }
}
</script>
@endpush