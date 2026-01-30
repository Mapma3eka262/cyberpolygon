@props([
    'task',
    'showDescription' => true,
    'showStats' => true,
    'showActions' => false,
    'assignedTeams' => null,
])

<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-tasks text-info me-2"></i>
            {{ $task->name }}
        </h6>
        
        <div>
            @if($task->is_active)
                <span class="badge bg-success">Активно</span>
            @else
                <span class="badge bg-secondary">Неактивно</span>
            @endif
        </div>
    </div>
    
    <div class="card-body">
        @if($showDescription && $task->description)
            <p class="mb-3 text-muted">
                {{ Str::limit($task->description, 150) }}
            </p>
        @endif
        
        <div class="row mb-2">
            <div class="col-md-6">
                <p class="mb-1">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        {{ $task->duration_minutes }} мин.
                    </small>
                </p>
                <p class="mb-1">
                    <small class="text-muted">
                        <i class="fas fa-flag me-1"></i>
                        Флаги: {{ $task->flag1_points }}/{{ $task->flag2_points }}
                    </small>
                </p>
            </div>
            <div class="col-md-6">
                <p class="mb-1">
                    <small class="text-muted">
                        <i class="fas fa-server me-1"></i>
                        {{ $task->target_ip_subnet }}
                    </small>
                </p>
                <p class="mb-1">
                    <small class="text-muted">
                        <i class="fas fa-calendar me-1"></i>
                        {{ $task->created_at->format('d.m.Y') }}
                    </small>
                </p>
            </div>
        </div>
        
        @if($showStats)
            <div class="row mt-2">
                <div class="col-4">
                    <div class="text-center p-1 border rounded">
                        <div class="fw-bold">{{ $task->teams_count ?? $task->teams()->count() }}</div>
                        <small class="text-muted">Команд</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="text-center p-1 border rounded">
                        <div class="fw-bold">{{ $task->completed_count ?? 0 }}</div>
                        <small class="text-muted">Завершили</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="text-center p-1 border rounded">
                        <div class="fw-bold">{{ $task->average_score ?? 0 }}</div>
                        <small class="text-muted">Сред. балл</small>
                    </div>
                </div>
            </div>
        @endif
        
        @if($assignedTeams && $assignedTeams->count() > 0)
            <div class="mt-3">
                <small class="text-muted d-block mb-1">Назначено командам:</small>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($assignedTeams as $team)
                        <span class="badge bg-info">{{ $team->name }}</span>
                    @endforeach
                </div>
            </div>
        @endif
        
        @if($showActions)
            <div class="mt-3">
                <a href="{{ route('admin.tasks.edit', $task) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-edit"></i> Редактировать
                </a>
                
                <a href="{{ route('admin.tasks.assign.form', $task) }}" class="btn btn-outline-primary btn-sm ms-1">
                    <i class="fas fa-user-plus"></i> Назначить
                </a>
            </div>
        @endif
    </div>
</div>