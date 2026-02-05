@extends('layouts.app-old')

@section('title', '503 — Сайт временно недоступен')
@section('description', 'Сайт на техническом обслуживании. Скоро вернёмся.')

@section('content')
    <div class="col-12">
        <div class="error-page">
            <p class="error-code">503</p>
            <h1 class="error-title">Сайт временно недоступен</h1>
            <p class="error-text">Идут плановые технические работы. Мы постараемся вернуться как можно скорее.</p>
            <div class="error-actions">
                <a href="{{ url('/') }}" class="btn btn-error-primary"><i class="fas fa-home"></i> На главную</a>
                <a href="{{ route('search') }}" class="btn btn-error-secondary"><i class="fas fa-search"></i> Поиск</a>
            </div>
        </div>
    </div>
@endsection
