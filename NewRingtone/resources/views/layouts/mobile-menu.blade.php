<!-- Мобильное меню -->
<div class="mobile-menu">
    <div class="mobile-menu-header">
        <div class="logo-cloud"><a href="{{ route('home') }}">НейроЗвук</a></div>
        <button class="close-menu">✕</button>
    </div>

    <form action="{{ route('search') }}" method="post">
        @csrf

        <input type="text" class="search-cloud mobile-search" name="q" value="{{ request('q') }}" placeholder="Поиск...">
    </form>

    @php
        $route     = request()->route();
        $routeName = $route ? $route->getName() : null;
        $slug      = $route ? $route->parameter('slug') : null;
    @endphp

    <nav class="mobile-nav">
        <a href="{{ route('types.show', 'ringtony') }}"
           class="mobile-nav-item {{ $routeName === 'types.show' && $slug === 'ringtony' ? 'active' : '' }}">
            Рингтоны
        </a>

        <a href="{{ route('types.show', 'melodii') }}"
           class="mobile-nav-item {{ $routeName === 'types.show' && $slug === 'melodii' ? 'active' : '' }}">
            Мелодии
        </a>

        <a href="{{ route('types.show', 'pesni') }}"
           class="mobile-nav-item {{ $routeName === 'types.show' && $slug === 'pesni' ? 'active' : '' }}">
            Песни
        </a>

        @auth
            @if($routeName === 'dashboard' || ($routeName && \Illuminate\Support\Str::startsWith($routeName, 'account.')))
                <a href="{{ route('dashboard') }}"
                   class="mobile-nav-item {{ $routeName === 'dashboard' ? 'active' : '' }}">
                    Кабинет
                </a>
                <a href="{{ route('account.materials.index') }}"
                   class="mobile-nav-item {{ request()->routeIs('account.materials.*') ? 'active' : '' }}">
                    Материалы
                </a>
                <a href="{{ route('account.authors.index') }}"
                   class="mobile-nav-item {{ request()->routeIs('account.authors.*') ? 'active' : '' }}">
                    Авторы
                </a>
            @else
                <a href="{{ route('dashboard') }}"
                   class="mobile-nav-item">
                    Личный кабинет
                </a>
            @endif
        @endauth
    </nav>

    @auth
        @if($routeName === 'dashboard' || ($routeName && \Illuminate\Support\Str::startsWith($routeName, 'account.')))
            <form action="{{ route('logout') }}" method="POST" style="margin: 0; margin-bottom: 2rem;">
                @csrf
                <button type="submit" style="width: 100%; padding: 1rem; background: var(--bg-tertiary); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: var(--radius, 6px); cursor: pointer; font-size: 1.1rem; font-weight: 500;">
                    Выйти
                </button>
            </form>
        @endif
    @endauth

    <div class="mobile-theme-switcher">
        <div class="theme-btn active" data-theme="light" title="Светлая тема"></div>
        <div class="theme-btn" data-theme="dark" title="Темная тема"></div>
        <div class="theme-btn" data-theme="green" title="Зеленая тема"></div>
        <div class="theme-btn" data-theme="blue" title="Голубая тема"></div>
    </div>
</div>
