<!-- Шапка -->
<header class="header-cloud">
    <div class="container">
        <div class="header-grid">
            <div class="logo-cloud"><a href="{{ route('home') }}">НейроЗвук</a></div>

            @php
                $route     = request()->route();
                $routeName = $route ? $route->getName() : null;
                $slug      = $route ? $route->parameter('slug') : null;
            @endphp

            <nav class="nav-cloud">
                <a href="{{ route('types.show', 'ringtony') }}"
                   class="nav-item-cloud {{ $routeName === 'types.show' && $slug === 'ringtony' ? 'active' : '' }}">
                    Рингтоны
                </a>

                <a href="{{ route('types.show', 'melodii') }}"
                   class="nav-item-cloud {{ $routeName === 'types.show' && $slug === 'melodii' ? 'active' : '' }}">
                    Мелодии
                </a>

                <a href="{{ route('types.show', 'pesni') }}"
                   class="nav-item-cloud {{ $routeName === 'types.show' && $slug === 'pesni' ? 'active' : '' }}">
                    Песни
                </a>

                @auth
                    <a href="{{ route('dashboard') }}"
                       class="nav-item-cloud {{ ($routeName === 'dashboard' || ($routeName && \Illuminate\Support\Str::startsWith($routeName, 'account.'))) ? 'active' : '' }}">
                        Личный кабинет
                    </a>
                @endauth
            </nav>

            <form action="{{ route('search') }}" method="post">
                @csrf

                <input type="text" class="search-cloud" name="q" value="{{ request('q') }}" placeholder="Поиск...">
            </form>

            <div class="theme-switcher">
                <div class="theme-btn active" data-theme="light" title="Светлая тема"></div>
                <div class="theme-btn" data-theme="dark" title="Темная тема"></div>
                <div class="theme-btn" data-theme="green" title="Зеленая тема"></div>
                <div class="theme-btn" data-theme="blue" title="Голубая тема"></div>
            </div>

            <!-- Кнопка гамбургера -->
            <button class="hamburger-btn">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </div>
</header>
