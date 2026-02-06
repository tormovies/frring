@extends('layouts.app')

@section('title', 'Регистрация')

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
<div class="page-header">
    <div class="container">
        <div class="page-header-content">
            <h1 class="page-title">Регистрация</h1>
            <p class="page-subtitle" style="color: var(--text-tertiary); font-size: 0.9rem;">
                Доступ только по специальной ссылке
            </p>
        </div>
    </div>
</div>

<main class="main-cloud">
    <div class="container">
        <div style="max-width: 600px; margin: 2rem auto;">
            @if ($errors->any())
                <div style="background: var(--bg-tertiary); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <ul style="margin: 0; padding-left: 1.5rem; color: var(--text-danger);">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register', $secretKey) }}" style="background: var(--bg-secondary); padding: 2rem; border-radius: 12px;">
                @csrf

                <div style="margin-bottom: 1.5rem;">
                    <label for="name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Имя <span style="color: var(--text-danger);">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                    @error('name')
                        <div style="color: var(--text-danger); font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Email <span style="color: var(--text-danger);">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                    @error('email')
                        <div style="color: var(--text-danger); font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Пароль <span style="color: var(--text-danger);">*</span></label>
                    <input type="password" id="password" name="password" required
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                    <small style="color: var(--text-tertiary); font-size: 0.85rem;">Минимум 8 символов</small>
                    @error('password')
                        <div style="color: var(--text-danger); font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label for="password_confirmation" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Подтвердите пароль <span style="color: var(--text-danger);">*</span></label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                    @error('password_confirmation')
                        <div style="color: var(--text-danger); font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label for="author_name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Имя Автора <span style="color: var(--text-danger);">*</span></label>
                    <input type="text" id="author_name" name="author_name" value="{{ old('author_name') }}" required
                           placeholder="Например: Имя Артиста"
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                    <small style="color: var(--text-tertiary); font-size: 0.85rem;">Имя автора, к которому вы запрашиваете доступ</small>
                    @error('author_name')
                        <div style="color: var(--text-danger); font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label for="author_card_url" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Ссылка на карточку артиста <span style="color: var(--text-danger);">*</span></label>
                    <input type="url" id="author_card_url" name="author_card_url" value="{{ old('author_card_url') }}" required
                           placeholder="https://vk.com/artist или https://soundcloud.com/artist"
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                    <small style="color: var(--text-tertiary); font-size: 0.85rem;">URL страницы артиста (ВК, SoundCloud и т.д.)</small>
                    @error('author_card_url')
                        <div style="color: var(--text-danger); font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" style="width: 100%; padding: 0.75rem; background: var(--accent-primary); color: white; border: none; border-radius: 6px; font-weight: 500; cursor: pointer; margin-bottom: 1rem;">
                    Зарегистрироваться
                </button>
            </form>

            <div style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                <p style="color: var(--text-secondary); margin-bottom: 0.5rem;">Уже есть аккаунт?</p>
                <a href="{{ route('login') }}" style="color: var(--accent-primary); text-decoration: none; font-weight: 500;">
                    Войти в личный кабинет
                </a>
            </div>
        </div>
    </div>
</main>
@endsection
