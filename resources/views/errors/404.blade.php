@extends('layouts.app-old')

@section('title', '404 — Страница не найдена')
@section('description', 'Запрашиваемая страница не найдена. Перейдите на главную или воспользуйтесь поиском.')

@section('content')
    <div class="col-12">
        <div class="error-page">
            <p class="error-code">404</p>
            <h1 class="error-title">Страница не найдена</h1>
            <p class="error-text">К сожалению, такой страницы не существует. Возможно, вы перешли по устаревшей ссылке или указали неверный адрес.</p>
            <div class="error-actions">
                <a href="{{ url('/') }}" class="btn btn-error-primary"><i class="fas fa-home"></i> На главную</a>
                <a href="{{ route('search') }}" class="btn btn-error-secondary"><i class="fas fa-search"></i> Поиск</a>
                <a href="{{ url('/category/index-0-plays.html') }}" class="btn btn-error-secondary"><i class="fas fa-music"></i> Популярные рингтоны</a>
            </div>
        </div>
    </div>
@endsection
