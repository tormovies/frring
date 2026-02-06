@extends('layouts.app')

@section('title', 'Вход')

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
<div class="page-header">
    <div class="container">
        <div class="page-header-content">
            <h1 class="page-title">Вход в личный кабинет</h1>
            <p class="page-subtitle" style="color: var(--text-tertiary); font-size: 0.9rem;">
                Доступно только для зарегистрированных пользователей
            </p>
        </div>
    </div>
</div>

<main class="main-cloud">
    <div class="container">
        <div style="max-width: 500px; margin: 2rem auto;">
            @if ($errors->any())
                <div style="background: var(--bg-tertiary); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <ul style="margin: 0; padding-left: 1.5rem; color: var(--text-danger);">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status'))
                <div style="background: var(--bg-success); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; color: var(--text-success);">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" style="background: var(--bg-secondary); padding: 2rem; border-radius: 12px;">
                @csrf

                <div style="margin-bottom: 1.5rem;">
                    <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Пароль</label>
                    <input type="password" id="password" name="password" required
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="remember" style="width: auto;">
                        <span>Запомнить меня</span>
                    </label>
                </div>

                <button type="submit" style="width: 100%; padding: 0.75rem; background: var(--accent-primary); color: white; border: none; border-radius: 6px; font-weight: 500; cursor: pointer; margin-bottom: 1rem;">
                    Войти
                </button>
            </form>

            <div style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                <p style="color: var(--text-secondary); margin-bottom: 0.5rem;">
                    <a href="{{ route('password.request') }}" style="color: var(--accent-primary); text-decoration: none;">
                        Забыли пароль?
                    </a>
                </p>
                <p style="color: var(--text-secondary); margin-bottom: 0.5rem;">Нет аккаунта?</p>
                <a href="{{ route('register', config('app.registration_secret_key')) }}" style="color: var(--accent-primary); text-decoration: none; font-weight: 500;">
                    Зарегистрироваться
                </a>
            </div>
        </div>
    </div>
</main>
@endsection
