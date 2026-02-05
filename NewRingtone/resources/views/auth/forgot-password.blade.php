@extends('layouts.app')

@section('title', 'Восстановление пароля')

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
<div class="page-header">
    <div class="container">
        <div class="page-header-content">
            <h1 class="page-title">Восстановление пароля</h1>
            <p class="page-subtitle" style="color: var(--text-tertiary); font-size: 0.9rem;">
                Доступно только для зарегистрированных пользователей
            </p>
        </div>
    </div>
</div>

<main class="main-cloud">
    <div class="container">
        <div style="max-width: 500px; margin: 2rem auto;">
            @if (session('status'))
                <div style="background: var(--bg-success); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; color: var(--text-success);">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" style="background: var(--bg-secondary); padding: 2rem; border-radius: 12px;">
                @csrf

                <div style="margin-bottom: 1.5rem;">
                    <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                    @error('email')
                        <div style="color: var(--text-danger); font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" style="width: 100%; padding: 0.75rem; background: var(--accent-primary); color: white; border: none; border-radius: 6px; font-weight: 500; cursor: pointer; margin-bottom: 1rem;">
                    Отправить ссылку для восстановления
                </button>
            </form>

            <div style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                <a href="{{ route('login') }}" style="color: var(--accent-primary); text-decoration: none; font-weight: 500;">
                    ← Вернуться к входу
                </a>
            </div>
        </div>
    </div>
</main>
@endsection
