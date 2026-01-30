@extends('layouts.auth')

@section('title', 'Вход и регистрация')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white">
                <ul class="nav nav-tabs card-header-tabs" id="authTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ session('activeTab') !== 'register' ? 'active' : '' }} text-white" 
                                id="login-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#login" 
                                type="button" 
                                role="tab">
                            <i class="fas fa-sign-in-alt"></i> Вход
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ session('activeTab') === 'register' ? 'active' : '' }} text-white" 
                                id="register-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#register" 
                                type="button" 
                                role="tab">
                            <i class="fas fa-user-plus"></i> Регистрация
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body p-4">
                <div class="tab-content" id="authTabsContent">
                    <!-- Вкладка Вход -->
                    <div class="tab-pane fade {{ session('activeTab') !== 'register' ? 'show active' : '' }}" 
                         id="login" 
                         role="tabpanel">
                        
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="login-username" class="form-label">
                                    <i class="fas fa-user"></i> Имя пользователя
                                </label>
                                <input type="text" 
                                       class="form-control @error('username') is-invalid @enderror" 
                                       id="login-username" 
                                       name="username" 
                                       value="{{ old('username') }}" 
                                       required 
                                       autofocus>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="login-password" class="form-label">
                                    <i class="fas fa-lock"></i> Пароль
                                </label>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="login-password" 
                                       name="password" 
                                       required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="remember" 
                                       name="remember">
                                <label class="form-check-label" for="remember">
                                    Запомнить меня
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-sign-in-alt"></i> Войти
                            </button>
                        </form>
                    </div>
                    
                    <!-- Вкладка Регистрация -->
                    <div class="tab-pane fade {{ session('activeTab') === 'register' ? 'show active' : '' }}" 
                         id="register" 
                         role="tabpanel">
                         
                        <form method="POST" action="{{ route('register') }}" id="register-form">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="register-username" class="form-label">
                                        <i class="fas fa-user"></i> Имя пользователя *
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('username') is-invalid @enderror" 
                                           id="register-username" 
                                           name="username" 
                                           value="{{ old('username') }}" 
                                           required>
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="register-email" class="form-label">
                                        <i class="fas fa-envelope"></i> Email *
                                    </label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="register-email" 
                                           name="email" 
                                           value="{{ old('email') }}" 
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="register-surname" class="form-label">Фамилия *</label>
                                    <input type="text" 
                                           class="form-control @error('surname') is-invalid @enderror" 
                                           id="register-surname" 
                                           name="surname" 
                                           value="{{ old('surname') }}" 
                                           required>
                                    @error('surname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="register-name" class="form-label">Имя *</label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="register-name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="register-patronymic" class="form-label">Отчество</label>
                                    <input type="text" 
                                           class="form-control @error('patronymic') is-invalid @enderror" 
                                           id="register-patronymic" 
                                           name="patronymic" 
                                           value="{{ old('patronymic') }}">
                                    @error('patronymic')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="register-phone" class="form-label">
                                        <i class="fas fa-phone"></i> Телефон *
                                    </label>
                                    <input type="tel" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="register-phone" 
                                           name="phone" 
                                           value="{{ old('phone') }}" 
                                           required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="register-role" class="form-label">Роль *</label>
                                    <select class="form-select @error('role') is-invalid @enderror" 
                                            id="register-role" 
                                            name="role" 
                                            required>
                                        <option value="">Выберите роль</option>
                                        <option value="captain" {{ old('role') == 'captain' ? 'selected' : '' }}>
                                            Капитан (создать новую команду)
                                        </option>
                                        <option value="participant" {{ old('role') == 'participant' ? 'selected' : '' }}>
                                            Участник (присоединиться к команде)
                                        </option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="register-password" class="form-label">
                                        <i class="fas fa-lock"></i> Пароль *
                                    </label>
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="register-password" 
                                           name="password" 
                                           required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="register-password-confirm" class="form-label">
                                        <i class="fas fa-lock"></i> Подтверждение пароля *
                                    </label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="register-password-confirm" 
                                           name="password_confirmation" 
                                           required>
                                </div>
                            </div>
                            
                            <!-- Поля для капитана -->
                            <div id="captain-fields" style="display: none;">
                                <div class="mb-3">
                                    <label for="register-team-name" class="form-label">
                                        <i class="fas fa-users"></i> Название команды *
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('team_name') is-invalid @enderror" 
                                           id="register-team-name" 
                                           name="team_name" 
                                           value="{{ old('team_name') }}">
                                    @error('team_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Поля для участника -->
                            <div id="participant-fields" style="display: none;">
                                <div class="mb-3">
                                    <label for="register-invite-code" class="form-label">
                                        <i class="fas fa-ticket-alt"></i> Код приглашения *
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('invite_code') is-invalid @enderror" 
                                           id="register-invite-code" 
                                           name="invite_code" 
                                           value="{{ old('invite_code') }}"
                                           placeholder="Введите 8-значный код">
                                    @error('invite_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        Код приглашения должен быть предоставлен капитаном команды.
                                    </small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input @error('privacy_policy') is-invalid @enderror" 
                                           type="checkbox" 
                                           id="privacy-policy" 
                                           name="privacy_policy" 
                                           {{ old('privacy_policy') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="privacy-policy">
                                        Я согласен на обработку 
                                        <a href="{{ route('privacy') }}" target="_blank">персональных данных</a> *
                                    </label>
                                    @error('privacy_policy')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <button type="submit" 
                                    class="btn btn-success w-100" 
                                    id="register-button" 
                                    disabled>
                                <i class="fas fa-user-plus"></i> Зарегистрироваться
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <a href="{{ route('home') }}" class="text-decoration-none">
                <i class="fas fa-arrow-left"></i> Вернуться на главную
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('register-role');
    const captainFields = document.getElementById('captain-fields');
    const participantFields = document.getElementById('participant-fields');
    const privacyCheckbox = document.getElementById('privacy-policy');
    const registerButton = document.getElementById('register-button');
    const registerForm = document.getElementById('register-form');
    
    // Обработчик выбора роли
    roleSelect.addEventListener('change', function() {
        if (this.value === 'captain') {
            captainFields.style.display = 'block';
            participantFields.style.display = 'none';
        } else if (this.value === 'participant') {
            captainFields.style.display = 'none';
            participantFields.style.display = 'block';
        } else {
            captainFields.style.display = 'none';
            participantFields.style.display = 'none';
        }
        updateRegisterButton();
    });
    
    // Обработчик чекбокса политики конфиденциальности
    privacyCheckbox.addEventListener('change', updateRegisterButton);
    
    // Валидация формы регистрации
    registerForm.addEventListener('input', function() {
        updateRegisterButton();
    });
    
    function updateRegisterButton() {
        const isRoleSelected = roleSelect.value !== '';
        const isPrivacyAccepted = privacyCheckbox.checked;
        
        // Проверка полей в зависимости от роли
        let isFormValid = isRoleSelected && isPrivacyAccepted;
        
        if (isFormValid) {
            if (roleSelect.value === 'captain') {
                const teamName = document.getElementById('register-team-name').value;
                isFormValid = teamName.trim() !== '';
            } else if (roleSelect.value === 'participant') {
                const inviteCode = document.getElementById('register-invite-code').value;
                isFormValid = inviteCode.trim().length === 8;
            }
        }
        
        registerButton.disabled = !isFormValid;
    }
    
    // Инициализация при загрузке
    if (roleSelect.value) {
        roleSelect.dispatchEvent(new Event('change'));
    }
    updateRegisterButton();
    
    // Автоматический переход на нужную вкладку при ошибках
    @if(session('activeTab') === 'register' || $errors->has('team_name') || $errors->has('invite_code'))
        const registerTab = document.getElementById('register-tab');
        if (registerTab) {
            registerTab.click();
        }
    @endif
});
</script>

<style>
.nav-tabs .nav-link {
    border: none;
    color: rgba(255, 255, 255, 0.7);
    background: transparent;
}

.nav-tabs .nav-link.active {
    color: white;
    background: rgba(255, 255, 255, 0.1);
    border-bottom: 3px solid white;
}

.form-label i {
    margin-right: 8px;
    width: 20px;
}
</style>
@endpush