@props(['active' => 'dashboard'])

<div class="list-group list-group-flush">
    <a href="{{ route('dashboard') }}" 
       class="list-group-item list-group-item-action py-3 @if($active == 'dashboard') active @endif">
        <i class="fas fa-tachometer-alt me-2"></i> Обзор
    </a>
    
    <a href="{{ route('profile') }}" 
       class="list-group-item list-group-item-action py-3 @if($active == 'profile') active @endif">
        <i class="fas fa-user me-2"></i> Профиль
    </a>
    
    <a href="{{ route('arena') }}" 
       class="list-group-item list-group-item-action py-3 @if($active == 'arena') active @endif">
        <i class="fas fa-gamepad me-2"></i> Арена
    </a>
    
    @if(auth()->user()->isCaptain())
        <a href="{{ route('dashboard') }}?tab=team" 
           class="list-group-item list-group-item-action py-3 @if($active == 'team') active @endif">
            <i class="fas fa-users me-2"></i> Управление командой
        </a>
    @endif
    
    @if(auth()->user()->isAdmin())
        <div class="list-group-item py-2 bg-light">
            <small class="text-muted">АДМИНИСТРИРОВАНИЕ</small>
        </div>
        
        <a href="{{ route('admin.dashboard') }}" 
           class="list-group-item list-group-item-action py-3 @if($active == 'admin.dashboard') active @endif">
            <i class="fas fa-cogs me-2"></i> Панель управления
        </a>
        
        <a href="{{ route('admin.teams.index') }}" 
           class="list-group-item list-group-item-action py-3 @if($active == 'admin.teams') active @endif">
            <i class="fas fa-users-cog me-2"></i> Команды
        </a>
        
        <a href="{{ route('admin.tasks.index') }}" 
           class="list-group-item list-group-item-action py-3 @if($active == 'admin.tasks') active @endif">
            <i class="fas fa-tasks me-2"></i> Задания
        </a>
        
        <a href="{{ route('admin.analytics.index') }}" 
           class="list-group-item list-group-item-action py-3 @if($active == 'admin.analytics') active @endif">
            <i class="fas fa-chart-line me-2"></i> Аналитика
        </a>
        
        <a href="{{ route('admin.stats.index') }}" 
           class="list-group-item list-group-item-action py-3 @if($active == 'admin.stats') active @endif">
            <i class="fas fa-chart-bar me-2"></i> Статистика
        </a>
    @endif
</div>