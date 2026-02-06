@extends('layouts.app-old')

@section('title', '500 — Ошибка сервера')
@section('description', 'Внутренняя ошибка сервера. Попробуйте позже или перейдите на главную.')

@section('content')
    <div class="col-12">
        <div class="error-page">
            <p class="error-code">500</p>
            <h1 class="error-title">Ошибка сервера</h1>
            <p class="error-text">Что-то пошло не так на нашей стороне. Мы уже знаем о проблеме и работаем над её устранением. Попробуйте обновить страницу через несколько минут.</p>
            <div class="error-actions">
                <a href="{{ url('/') }}" class="btn btn-error-primary"><i class="fas fa-home"></i> На главную</a>
                <a href="{{ route('search') }}" class="btn btn-error-secondary"><i class="fas fa-search"></i> Поиск</a>
            </div>
        </div>
    </div>
@endsection
