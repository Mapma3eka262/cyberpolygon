@extends('layouts.app')

@section('title', 'Главная')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Соревнования по CTF</h3>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="text-primary">Что такое CTF?</h4>
                            <p class="lead">
                                Capture The Flag (CTF) — это соревнования по информационной безопасности, 
                                где команды решают задачи различной сложности для получения флагов.
                            </p>
                            
                            <div class="mt-4">
                                <h5>Формат соревнований:</h5>
                                <ul>
                                    <li>Командное участие (до 5 человек)</li>
                                    <li>Задачи различной категории (web, crypto, pwn, reversing)</li>
                                    <li>Два уровня флагов в каждой задаче</li>
                                    <li>Рейтинговая система с штрафами</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">До начала соревнований:</h5>
                                </div>
                                <div class="card-body text-center">
                                    <div id="countdown-timer" class="display-4 text-primary mb-3">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Загрузка...</span>
                                        </div>
                                    </div>
                                    
                                    @if(config('ctf.competition.registration_open'))
                                        <a href="{{ route('login') }}" class="btn btn-success btn-lg w-100">
                                            <i class="fas fa-user-plus"></i> Участвовать в соревнованиях
                                        </a>
                                    @else
                                        <button class="btn btn-secondary btn-lg w-100" disabled>
                                            Регистрация закрыта
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h5><i class="fas fa-info-circle"></i> Важная информация:</h5>
                                <ul class="mb-0">
                                    <li>Соревнования начнутся: {{ config('ctf.competition.start_date') }}</li>
                                    <li>Продолжительность: 8 часов</li>
                                    <li>Максимальный размер команды: {{ config('ctf.teams.max_members') }} человек</li>
                                    <li>Формат: Attack-Defense с элементами Jeopardy</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Партнеры -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Партнеры соревнования</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center mb-3">
                            <div class="partner-logo bg-white p-3 rounded shadow-sm">
                                <span class="text-muted">СКАНБИТ</span>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <p class="mb-0">
                                <strong>Контактная информация:</strong><br>
                                Компания "СКАНБИТ"<br>
                                Email: info@scanbit.ru<br>
                                Телефон: +7 (XXX) XXX-XX-XX
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDate = new Date("{{ config('ctf.competition.start_date') }}").getTime();
    
    function updateCountdown() {
        const now = new Date().getTime();
        const distance = startDate - now;
        
        if (distance < 0) {
            document.getElementById("countdown-timer").innerHTML = "Соревнования начались!";
            return;
        }
        
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        document.getElementById("countdown-timer").innerHTML = 
            `<div class="countdown-item">
                <span class="countdown-number">${days}</span>
                <span class="countdown-label">дней</span>
            </div>
            <div class="countdown-separator">:</div>
            <div class="countdown-item">
                <span class="countdown-number">${hours.toString().padStart(2, '0')}</span>
                <span class="countdown-label">часов</span>
            </div>
            <div class="countdown-separator">:</div>
            <div class="countdown-item">
                <span class="countdown-number">${minutes.toString().padStart(2, '0')}</span>
                <span class="countdown-label">минут</span>
            </div>
            <div class="countdown-separator">:</div>
            <div class="countdown-item">
                <span class="countdown-number">${seconds.toString().padStart(2, '0')}</span>
                <span class="countdown-label">секунд</span>
            </div>`;
    }
    
    updateCountdown();
    setInterval(updateCountdown, 1000);
});
</script>

<style>
#countdown-timer {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    font-family: 'Courier New', monospace;
}

.countdown-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 8px;
    min-width: 70px;
}

.countdown-number {
    font-size: 1.8rem;
    font-weight: bold;
    color: #0d6efd;
}

.countdown-label {
    font-size: 0.8rem;
    color: #6c757d;
    text-transform: uppercase;
    margin-top: 5px;
}

.countdown-separator {
    font-size: 1.5rem;
    color: #0d6efd;
    font-weight: bold;
}

.partner-logo {
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
}
</style>
@endpush