@extends('layouts.app')

@section('title', 'Личный кабинет')

@php
    $activeTab = request()->get('tab', 'overview');
@endphp

@section('content')
<div class="row">
    <!-- Боковая панель -->
    <div class="col-lg-3 mb-4">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                @include('components.navigation.sidebar', ['active' => $activeTab])
            </div>
        </div>
        
        <!-- Информация о команде -->
        @if($user->team)
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-users me-2"></i>Ваша команда</h6>
                </div>
                <div class="card-body">
                    <h5 class="card-title">{{ $user->team->name }}</h5>
                    
                    <div class="row text-center mt-3">
                        <div class="col-6">
                            <div class="p-2 border rounded">
                                <div class="fs-4 fw-bold text-primary">{{ $user->team->score }}</div>
                                <small class="text-muted">Баллов</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 border rounded">
                                <div class="fs-4 fw-bold text-success">{{ $user->team->flags_found }}</div>
                                <small class="text-muted">Флагов</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="{{ route('arena') }}" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-gamepad me-1"></i> Перейти на арену
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Основной контент -->
    <div class="col-lg-9">
        <!-- Вкладки -->
        <ul class="nav nav-tabs mb-4" id="dashboardTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'overview' ? 'active' : '' }}" 
                        id="overview-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#overview" 
                        type="button">
                    <i class="fas fa-tachometer-alt me-1"></i> Обзор
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'profile' ? 'active' : '' }}" 
                        id="profile-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#profile" 
                        type="button">
                    <i class="fas fa-user me-1"></i> Профиль
                </button>
            </li>
            @if($user->team)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab == 'team' ? 'active' : '' }}" 
                            id="team-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#team" 
                            type="button">
                        <i class="fas fa-users me-1"></i> Команда
                    </button>
                </li>
            @endif
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'activity' ? 'active' : '' }}" 
                        id="activity-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#activity" 
                        type="button">
                    <i class="fas fa-history me-1"></i> Активность
                </button>
            </li>
        </ul>
        
        <!-- Контент вкладок -->
        <div class="tab-content" id="dashboardTabsContent">
            <!-- Вкладка: Обзор -->
            <div class="tab-pane fade {{ $activeTab == 'overview' ? 'show active' : '' }}" 
                 id="overview" 
                 role="tabpanel">
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-user-circle me-2"></i> Ваши данные
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Имя пользователя:</strong>
                                    <div class="text-muted">{{ $user->username }}</div>
                                </div>
                                <div class="mb-3">
                                    <strong>ФИО:</strong>
                                    <div class="text-muted">{{ $user->full_name }}</div>
                                </div>
                                <div class="mb-3">
                                    <strong>Email:</strong>
                                    <div class="text-muted">{{ $user->email }}</div>
                                </div>
                                <div class="mb-3">
                                    <strong>Телефон:</strong>
                                    <div class="text-muted">{{ $user->phone }}</div>
                                </div>
                                <div class="mb-0">
                                    <strong>Роль:</strong>
                                    <div>
                                        @if($user->isCaptain())
                                            <span class="badge bg-primary">Капитан</span>
                                        @elseif($user->isAdmin())
                                            <span class="badge bg-danger">Администратор</span>
                                        @else
                                            <span class="badge bg-secondary">Участник</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-line me-2"></i> Статистика
                                </h6>
                            </div>
                            <div class="card-body">
                                @if($user->team)
                                    <div class="row text-center mb-3">
                                        <div class="col-6">
                                            <div class="p-3 border rounded bg-light">
                                                <div class="fs-3 fw-bold text-primary">{{ $user->team->score }}</div>
                                                <small class="text-muted">Общий счет</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-3 border rounded bg-light">
                                                <div class="fs-3 fw-bold text-success">{{ $user->team->flags_found }}</div>
                                                <small class="text-muted">Найдено флагов</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Точность:</strong>
                                        @php
                                            $accuracy = $user->team->flags_found + $user->team->wrong_attempts > 0 
                                                ? round(($user->team->flags_found / ($user->team->flags_found + $user->team->wrong_attempts)) * 100, 1)
                                                : 0;
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" 
                                                 role="progressbar" 
                                                 style="width: {{ $accuracy }}%">
                                                {{ $accuracy }}%
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <strong>Неправильных попыток:</strong>
                                        <div class="text-danger fw-bold">{{ $user->team->wrong_attempts }}</div>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Вы не состоите в команде</p>
                                        <a href="{{ route('login') }}" class="btn btn-primary">
                                            Присоединиться к команде
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Активное задание -->
                @if($user->team && $user->team->activeTask())
                    @php
                        $activeTask = $user->team->activeTask();
                        $teamTask = $activeTask->pivot;
                    @endphp
                    
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-warning">
                            <h6 class="mb-0">
                                <i class="fas fa-tasks me-2"></i> Активное задание
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5>{{ $activeTask->name }}</h5>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $activeTask->duration_minutes }} минут
                                    </p>
                                    <div class="progress" style="width: 300px; height: 10px;">
                                        @php
                                            $progress = 0;
                                            if ($teamTask->flag1_found) $progress += 50;
                                            if ($teamTask->flag2_found) $progress += 50;
                                        @endphp
                                        <div class="progress-bar bg-success" 
                                             role="progressbar" 
                                             style="width: {{ $progress }}%">
                                        </div>
                                    </div>
                                    <small class="text-muted">Прогресс: {{ $progress }}%</small>
                                </div>
                                <div>
                                    <a href="{{ route('arena') }}" class="btn btn-primary">
                                        <i class="fas fa-gamepad me-1"></i> Перейти к заданию
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Недавняя активность -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-history me-2"></i> Недавняя активность
                        </h6>
                    </div>
                    <div class="card-body">
                        @php
                            $activities = $user->flagAttempts()
                                ->with(['teamTask.task'])
                                ->orderBy('created_at', 'desc')
                                ->limit(5)
                                ->get();
                        @endphp
                        
                        @if($activities->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($activities as $activity)
                                    <div class="list-group-item border-0 px-0">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong>{{ $activity->teamTask->task->name }}</strong>
                                                <div class="text-muted small">
                                                    {{ $activity->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                            <div>
                                                @if($activity->is_correct)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check"></i> Успех
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times"></i> Ошибка
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted text-center mb-0">Активность отсутствует</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Вкладка: Профиль -->
            <div class="tab-pane fade {{ $activeTab == 'profile' ? 'show active' : '' }}" 
                 id="profile" 
                 role="tabpanel">
                 
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Редактирование профиля</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('dashboard.profile.update') }}">
                                    @csrf
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            @include('components.forms.input', [
                                                'name' => 'email',
                                                'label' => 'Email',
                                                'type' => 'email',
                                                'value' => $user->email,
                                                'required' => true,
                                                'icon' => 'envelope'
                                            ])
                                        </div>
                                        <div class="col-md-6">
                                            @include('components.forms.input', [
                                                'name' => 'phone',
                                                'label' => 'Телефон',
                                                'type' => 'tel',
                                                'value' => $user->phone,
                                                'required' => true,
                                                'icon' => 'phone'
                                            ])
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <h6 class="mb-3">Смена пароля</h6>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            @include('components.forms.input', [
                                                'name' => 'current_password',
                                                'label' => 'Текущий пароль',
                                                'type' => 'password',
                                                'icon' => 'lock'
                                            ])
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            @include('components.forms.input', [
                                                'name' => 'new_password',
                                                'label' => 'Новый пароль',
                                                'type' => 'password',
                                                'icon' => 'key'
                                            ])
                                        </div>
                                        <div class="col-md-6">
                                            @include('components.forms.input', [
                                                'name' => 'new_password_confirmation',
                                                'label' => 'Подтверждение пароля',
                                                'type' => 'password',
                                                'icon' => 'key'
                                            ])
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Сохранить изменения
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Информация об учетной записи</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Дата регистрации:</strong>
                                    <div class="text-muted">{{ $user->created_at->format('d.m.Y H:i') }}</div>
                                </div>
                                <div class="mb-3">
                                    <strong>Последний вход:</strong>
                                    <div class="text-muted">
                                        {{ $user->updated_at->diffForHumans() }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <strong>Имя пользователя:</strong>
                                    <div class="text-muted">{{ $user->username }}</div>
                                </div>
                                <div class="mb-0">
                                    <strong>Роль:</strong>
                                    <div>
                                        @if($user->isCaptain())
                                            <span class="badge bg-primary">Капитан команды</span>
                                        @elseif($user->isAdmin())
                                            <span class="badge bg-danger">Администратор</span>
                                        @else
                                            <span class="badge bg-secondary">Участник</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if($user->team)
                            <div class="card shadow-sm border-0 mt-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Информация о команде</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <strong>Название команды:</strong>
                                        <div class="text-muted">{{ $user->team->name }}</div>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Код приглашения:</strong>
                                        <div>
                                            <code class="bg-dark text-white px-2 py-1 rounded">
                                                {{ $user->team->invite_code }}
                                            </code>
                                        </div>
                                    </div>
                                    <div class="mb-0">
                                        <strong>Статус команды:</strong>
                                        <div>
                                            @if($user->team->is_active)
                                                <span class="badge bg-success">Активна</span>
                                            @else
                                                <span class="badge bg-danger">Неактивна</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Вкладка: Команда (только для капитанов) -->
            @if($user->team)
                <div class="tab-pane fade {{ $activeTab == 'team' ? 'show active' : '' }}" 
                     id="team" 
                     role="tabpanel">
                     
                    @if($user->isCaptain())
                        <div class="row">
                            <div class="col-lg-8">
                                <!-- Добавление участника -->
                                <div class="card shadow-sm border-0 mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-user-plus me-2"></i> Добавить участника
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="{{ route('dashboard.team.add-member') }}">
                                            @csrf
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    @include('components.forms.input', [
                                                        'name' => 'username',
                                                        'label' => 'Имя пользователя',
                                                        'required' => true,
                                                        'icon' => 'user'
                                                    ])
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    @include('components.forms.input', [
                                                        'name' => 'email',
                                                        'label' => 'Email',
                                                        'type' => 'email',
                                                        'required' => true,
                                                        'icon' => 'envelope'
                                                    ])
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    @include('components.forms.input', [
                                                        'name' => 'surname',
                                                        'label' => 'Фамилия',
                                                        'required' => true
                                                    ])
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    @include('components.forms.input', [
                                                        'name' => 'name',
                                                        'label' => 'Имя',
                                                        'required' => true
                                                    ])
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    @include('components.forms.input', [
                                                        'name' => 'patronymic',
                                                        'label' => 'Отчество'
                                                    ])
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    @include('components.forms.input', [
                                                        'name' => 'phone',
                                                        'label' => 'Телефон',
                                                        'required' => true,
                                                        'icon' => 'phone'
                                                    ])
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    @include('components.forms.input', [
                                                        'name' => 'password',
                                                        'label' => 'Пароль',
                                                        'type' => 'password',
                                                        'required' => true,
                                                        'icon' => 'lock'
                                                    ])
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-end">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-user-plus me-1"></i> Добавить участника
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                
                                <!-- Код приглашения -->
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-ticket-alt me-2"></i> Код приглашения
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted">
                                            Поделитесь этим кодом с участниками, чтобы они могли присоединиться к вашей команде.
                                        </p>
                                        
                                        <div class="mb-3">
                                            <div class="input-group">
                                                <input type="text" 
                                                       class="form-control form-control-lg text-center fw-bold" 
                                                       value="{{ $user->team->invite_code }}" 
                                                       readonly
                                                       id="inviteCode">
                                                <button class="btn btn-outline-secondary" 
                                                        type="button"
                                                        onclick="copyInviteCode()">
                                                    <i class="fas fa-copy"></i> Копировать
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="alert alert-warning">
                                            <small>
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Код приглашения действует до момента присоединения участника.
                                                Максимальное количество участников в команде: {{ config('ctf.teams.max_members', 5) }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4">
                                <!-- Состав команды -->
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="fas fa-user-friends me-2"></i> Состав команды
                                            <span class="badge bg-primary ms-2">{{ $user->team->members()->count() }}</span>
                                        </h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="list-group list-group-flush">
                                            @foreach($user->team->members as $member)
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
                                                                {{ $member->email }}
                                                            </small>
                                                        </div>
                                                        @if(!$member->isCaptain())
                                                            <button class="btn btn-outline-danger btn-sm"
                                                                    onclick="removeMember({{ $member->id }}, '{{ $member->full_name }}')">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Статистика команды -->
                                <div class="card shadow-sm border-0 mt-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="fas fa-chart-bar me-2"></i> Статистика команды
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <div class="display-6 fw-bold text-primary">
                                                {{ $user->team->score }}
                                            </div>
                                            <small class="text-muted">Общий счет</small>
                                        </div>
                                        
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="p-2 border rounded">
                                                    <div class="fw-bold text-success">{{ $user->team->flags_found }}</div>
                                                    <small class="text-muted">Флагов</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="p-2 border rounded">
                                                    <div class="fw-bold text-danger">{{ $user->team->wrong_attempts }}</div>
                                                    <small class="text-muted">Ошибок</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <i class="fas fa-history me-1"></i>
                                                Последняя активность: {{ $user->team->updated_at->diffForHumans() }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle me-2"></i> Информация</h5>
                            <p class="mb-0">
                                Управление командой доступно только капитану.
                                Ваш капитан: <strong>{{ $user->team->captain->full_name ?? 'Не назначен' }}</strong>
                            </p>
                        </div>
                    @endif
                </div>
            @endif
            
            <!-- Вкладка: Активность -->
            <div class="tab-pane fade {{ $activeTab == 'activity' ? 'show active' : '' }}" 
                 id="activity" 
                 role="tabpanel">
                 
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-history me-2"></i> История активности
                        </h6>
                    </div>
                    <div class="card-body">
                        @php
                            $activities = $user->flagAttempts()
                                ->with(['teamTask.task', 'teamTask.team'])
                                ->orderBy('created_at', 'desc')
                                ->paginate(20);
                        @endphp
                        
                        @if($activities->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Дата и время</th>
                                            <th>Задание</th>
                                            <th>Тип флага</th>
                                            <th>Результат</th>
                                            <th>Баллы</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activities as $activity)
                                            <tr>
                                                <td>{{ $activity->created_at->format('d.m.Y H:i:s') }}</td>
                                                <td>{{ $activity->teamTask->task->name }}</td>
                                                <td>
                                                    @if($activity->flag_type === 'flag1')
                                                        <span class="badge bg-success">Флаг 1</span>
                                                    @else
                                                        <span class="badge bg-danger">Флаг 2</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($activity->is_correct)
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
                                                    @if($activity->is_correct)
                                                        <span class="text-success fw-bold">
                                                            +{{ $activity->flag_type === 'flag1' 
                                                                ? $activity->teamTask->task->flag1_points 
                                                                : $activity->teamTask->task->flag2_points }}
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
                            
                            <div class="d-flex justify-content-center mt-3">
                                {{ $activities->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Активность отсутствует</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyInviteCode() {
    const inviteCode = document.getElementById('inviteCode');
    inviteCode.select();
    inviteCode.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(inviteCode.value);
    
    // Показать уведомление
    alert('Код приглашения скопирован в буфер обмена!');
}

function removeMember(memberId, memberName) {
    if (confirm(`Вы уверены, что хотите удалить участника "${memberName}" из команды?`)) {
        // Отправить AJAX запрос на удаление
        fetch(`/dashboard/team/remove-member/${memberId}`, {
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
                alert(data.message || 'Ошибка при удалении участника');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при удалении участника');
        });
    }
}
</script>
@endpush