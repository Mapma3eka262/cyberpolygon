@props([
    'team',
    'showCaptain' => true,
    'showStats' => true,
    'showActions' => false,
])

<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-users text-primary me-2"></i>
            {{ $team->name }}
        </h6>
        
        @if($team->is_active)
            <span class="badge bg-success">Активна</span>
        @else
            <span class="badge bg-danger">Неактивна</span>
        @endif
    </div>
    
    <div class="card-body">
        @if($showCaptain && $team->captain)
            <p class="mb-2">
                <strong>Капитан:</strong>
                <span class="ms-2">{{ $team->captain->full_name }}</span>
            </p>
        @endif
        
        <p class="mb-2">
            <strong>Участников:</strong>
            <span class="badge bg-info ms-2">{{ $team->members_count ?? $team->members()->count() }}</span>
        </p>
        
        @if($showStats)
            <div class="row mt-3">
                <div class="col-6">
                    <div class="text-center p-2 border rounded bg-success bg-opacity-10">
                        <div class="fs-5 fw-bold text-success">{{ $team->score }}</div>
                        <small class="text-muted">Баллов</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-center p-2 border rounded bg-primary bg-opacity-10">
                        <div class="fs-5 fw-bold text-primary">{{ $team->flags_found }}</div>
                        <small class="text-muted">Флагов</small>
                    </div>
                </div>
            </div>
            
            <div class="mt-2">
                <small class="text-muted">
                    <i class="fas fa-history me-1"></i>
                    Обновлено: {{ $team->updated_at->diffForHumans() }}
                </small>
            </div>
        @endif
        
        @if($showActions)
            <div class="mt-3">
                <a href="{{ route('admin.teams.members', $team) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-user-friends"></i> Участники
                </a>
                
                <a href="{{ route('admin.teams.edit', $team) }}" class="btn btn-outline-secondary btn-sm ms-1">
                    <i class="fas fa-edit"></i> Редактировать
                </a>
            </div>
        @endif
    </div>
</div>