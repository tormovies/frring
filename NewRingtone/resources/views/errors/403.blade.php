@extends('layouts.app-old')

@section('title', '403 — Доступ запрещён')
@section('description', 'У вас нет прав для просмотра этой страницы.')

@section('content')
    <div class="col-12">
        <div class="error-page">
            <p class="error-code">403</p>
            <h1 class="error-title">Доступ запрещён</h1>
            <p class="error-text">У вас нет прав для просмотра этой страницы.</p>
            <div class="error-actions">
                <a href="{{ url('/') }}" class="btn btn-error-primary"><i class="fas fa-home"></i> На главную</a>
                <a href="{{ route('search') }}" class="btn btn-error-secondary"><i class="fas fa-search"></i> Поиск</a>
            </div>
        </div>
    </div>
@endsection
