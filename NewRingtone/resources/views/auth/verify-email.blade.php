@extends('layouts.app')

@section('title', 'Подтверждение Email')

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
<div class="page-header">
    <div class="container">
        <div class="page-header-content">
            <h1 class="page-title">Подтверждение Email</h1>
        </div>
    </div>
</div>

<main class="main-cloud">
    <div class="container">
        <div style="max-width: 600px; margin: 2rem auto; background: var(--bg-secondary); padding: 2rem; border-radius: 12px;">
            @if (session('status'))
                <div style="background: var(--bg-success); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; color: var(--text-success);">
                    {{ session('status') }}
                </div>
            @endif

            <p>Спасибо за регистрацию! Прежде чем продолжить, пожалуйста, подтвердите ваш email адрес.</p>
            
            <p>Мы отправили ссылку для подтверждения на ваш email. Если вы не получили письмо, мы можем отправить его снова.</p>

            @if (session('message'))
                <div style="background: var(--bg-tertiary); padding: 1rem; border-radius: 8px; margin: 1rem 0; color: var(--text-primary);">
                    {{ session('message') }}
                </div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}" style="margin-top: 1.5rem;">
                @csrf
                <button type="submit" style="padding: 0.75rem 1.5rem; background: var(--accent-primary); color: white; border: none; border-radius: 6px; font-weight: 500; cursor: pointer;">
                    Отправить письмо повторно
                </button>
            </form>
        </div>
    </div>
</main>
@endsection
