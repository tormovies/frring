<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Личный кабинет') - {{ config('app.name', 'NeuroZvuk') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <style>
        /* Адаптивность для мобильных устройств */
        @media (max-width: 768px) {
            body {
                overflow-x: hidden;
            }
            .container {
                padding-left: 1rem;
                padding-right: 1rem;
                max-width: 100%;
                width: 100%;
            }
            .page-header-content {
                padding: 1rem 0;
            }
            /* Уменьшаем padding в таблицах */
            table th, table td {
                padding: 0.5rem !important;
                font-size: 0.875rem !important;
            }
            /* Адаптивные формы и контейнеры */
            [style*="max-width: 800px"] {
                max-width: 100% !important;
                margin: 1rem auto !important;
            }
            [style*="max-width: 500px"] {
                max-width: 100% !important;
                width: 100% !important;
            }
            /* Адаптивные кнопки и формы */
            [style*="display: flex"] {
                flex-wrap: wrap !important;
            }
            [style*="justify-content: space-between"] {
                flex-direction: column !important;
                gap: 1rem !important;
            }
            /* Уменьшаем отступы в формах */
            [style*="padding: 2rem"] {
                padding: 1rem !important;
            }
            [style*="padding: 1.5rem"] {
                padding: 1rem !important;
            }
            /* Адаптивные кнопки */
            .mobile-stack {
                flex-direction: column !important;
            }
            .mobile-stack > * {
                width: 100% !important;
                margin-bottom: 0.5rem;
            }
            /* Адаптивные заголовки */
            h1, h2 {
                font-size: 1.25rem !important;
            }
            /* Адаптивные статистические карточки - в 4 колонки на мобильных */
            .stats-grid {
                grid-template-columns: repeat(4, 1fr) !important;
                gap: 0.5rem !important;
            }
            .stats-grid > div,
            .stats-grid > a > div {
                padding: 1rem 0.75rem !important;
            }
            .stats-grid > div > div:first-child,
            .stats-grid > a > div > div:first-child {
                font-size: 1.5rem !important;
                margin-bottom: 0.25rem !important;
                line-height: 1.2 !important;
            }
            .stats-grid > div > div:last-child,
            .stats-grid > a > div > div:last-child {
                font-size: 0.75rem !important;
                line-height: 1.4 !important;
            }
            .stats-grid > a {
                font-size: inherit !important;
                text-decoration: none !important;
                color: inherit !important;
            }
            /* Скрываем блок Быстрые действия на мобильных */
            .quick-actions-block {
                display: none !important;
            }
            /* Скрываем account-nav на мобильных (меню будет в основном гамбургере) */
            .account-nav {
                display: none !important;
            }
            /* Выравнивание контента в блоках на странице авторов */
            .account-authors-block {
                padding: 1rem !important;
            }
            .account-authors-form-grid {
                grid-template-columns: 1fr !important;
            }
            .account-author-card {
                padding: 1rem !important;
            }
            .account-author-card-header {
                flex-direction: column !important;
                gap: 1rem !important;
                align-items: flex-start !important;
            }
            .account-author-actions {
                width: 100% !important;
                flex-wrap: wrap !important;
            }
            .account-author-actions > * {
                flex: 1 1 auto !important;
                min-width: 120px !important;
            }
            /* Кнопки на странице материалов */
            .materials-title {
                display: none !important;
            }
            .dashboard-button {
                display: inline-block !important;
            }
            .materials-header {
                justify-content: flex-end !important;
            }
            .materials-buttons {
                width: 100% !important;
                justify-content: space-between !important;
            }
            .materials-buttons > a {
                flex: 1 1 auto !important;
                text-align: center !important;
            }
        }
        @media (max-width: 480px) {
            .container {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
            table th, table td {
                padding: 0.375rem !important;
                font-size: 0.75rem !important;
            }
            [style*="padding: 1rem"] {
                padding: 0.75rem !important;
            }
            h1, h2 {
                font-size: 1.1rem !important;
            }
            .stats-grid {
                gap: 0.375rem !important;
            }
            .stats-grid > div,
            .stats-grid > a > div {
                padding: 0.75rem 0.5rem !important;
            }
            .stats-grid > div > div:first-child,
            .stats-grid > a > div > div:first-child {
                font-size: 1.25rem !important;
                line-height: 1.2 !important;
            }
            .stats-grid > div > div:last-child,
            .stats-grid > a > div > div:last-child {
                font-size: 0.7rem !important;
                line-height: 1.4 !important;
            }
        }
    </style>
    @stack('head')
</head>
<body>

@include('layouts.header')

@include('layouts.mobile-menu')

@include('components.account-nav')

@yield('content')

@include('layouts.footer')

@include('layouts.scripts')
@stack('scripts')
</body>
</html>
