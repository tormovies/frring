<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="theme-color" content="#2a436a">
    <title>@yield('title', $seo['title'] ?? config('app.name'))</title>
    <meta name="description" content="@yield('description', $seo['description'] ?? '')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    {{-- Критический CSS: первый экран (шапка, top-bar, отступ контента) — меньше FOUC до загрузки основных стилей --}}
    <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{background:#fff;font-size:16px;margin:0;padding:0;overflow-x:hidden;color:#627798}
    .section_content{padding-top:120px}
    header.header{position:fixed;left:0;top:0;bottom:0;width:80px;background:#2a436a;z-index:21}
    .top-line{background:#ebeff1;height:70px;position:fixed;top:0;left:80px;right:0;z-index:20}
    .container-fluid{padding-left:110px;padding-right:380px;position:relative}
    @media (max-width:991px){.container-fluid{padding-right:295px}}
    @media (max-width:767px){.container-fluid{padding-right:30px}}
    @media (max-width:450px){.container-fluid{padding-left:15px;padding-right:15px}.top-line{left:0}.section_content{padding-top:60px}header.header{width:60px}}
    </style>
    <link rel="stylesheet" href="{{ asset('css/grid.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style-old-site.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fonts-local.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome/all.min.css') }}">
    @stack('head')
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function(m,e,t,r,i,k,a){
            m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
            m[i].l=1*new Date();
            for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
            k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
        })(window, document,'script','https://mc.yandex.ru/metrika/tag.js', 'ym');
        ym(61077613, 'init', {clickmap:true, referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
    </script>
    <noscript><div class="noscript-placeholder"><img src="https://mc.yandex.ru/watch/61077613" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->
</head>
<body>
    {{-- Левый фиксированный блок: логотип, бургер, категории, наверх --}}
    <header class="header">
        <a href="{{ url('/') }}" class="logo">
            <img src="{{ asset('img/logo.jpg') }}" alt="{{ config('app.name') }}" width="180" height="50" onerror="this.style.display='none';">
        </a>
        <a href="#navig" class="nav_burger btn-open" rel="navig" aria-label="Меню"><span></span></a>
        <a href="#category" class="category-nav btn-open" rel="category"><i class="fas fa-music"></i> <span>Категории</span></a>
        <a href="#ScrolTop" class="scroll_to_top" title="Наверх"><i class="fas fa-arrow-up"></i></a>
    </header>

    {{-- Верхняя строка: название, поиск, соцсети (общий partial — сюда добавить счётчик) --}}
    @include('partials.top-bar')

    <div class="section_content">
        <div class="container-fluid">
            <div class="row content_music">
                @yield('content')
            </div>
        </div>
        @include('partials.sidebar-categories')
    </div>

    {{-- Левое выезжающее меню --}}
    <div class="navigation popup" id="navig">
        <nav>
            <ul>
                <li><h3>Разделы</h3></li>
                <li><a href="{{ url('/') }}"><i class="fas fa-star"></i> Новые Рингтоны</a></li>
                <li><a href="{{ url('/category/index-0-plays.html') }}"><i class="fas fa-fire"></i> Горячие</a></li>
                <li><a href="{{ url('/category/index-0-rating.html') }}"><i class="fas fa-music"></i> Хиты</a></li>
                <li><a href="{{ route('pages.show', 'programma-dlja-sozdanija-ringtonov') }}" title="Программы"><i class="fas fa-laptop"></i> Программы</a></li>
            </ul>
        </nav>
        <footer>
            <p class="copy">© {{ date('Y') }} {{ config('app.name') }} — рингтоны для вашего телефона.<br>
                <a href="{{ url('/sitemap.xml') }}">Карта сайта</a>
            </p>
        </footer>
    </div>

    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <script>
    document.createElement('header');
    document.createElement('nav');
    document.createElement('section');
    document.createElement('article');
    document.createElement('aside');
    document.createElement('footer');
    </script>
    <script>
    $(function() {
        // Бургер: открыть/закрыть левое меню
        $('a[href="#navig"], .nav_burger').on('click', function(e) {
            e.preventDefault();
            $('.navigation').toggleClass('view_popup');
            $('.nav_burger').toggleClass('closes');
        });
        // Кнопка «Категории»: открыть правый сайдбар (на мобиле)
        $('a[href="#category"], .category-nav').on('click', function(e) {
            e.preventDefault();
            $('.aside_right').toggleClass('view_popup');
        });
        // Закрыть попапы по клику вне
        $('body').on('click', function(e) {
            if (!$(e.target).closest('.header').length && !$(e.target).closest('.navigation').length && !$(e.target).closest('.aside_right').length) {
                $('.navigation').removeClass('view_popup');
                $('.nav_burger').removeClass('closes');
                if ($(window).width() < 768) $('.aside_right').removeClass('view_popup');
            }
        });
    });
    </script>
    <script>
    $(function() {
        $(window).on('scroll', function() {
            $('.scroll_to_top').toggle($(this).scrollTop() > 400);
        });
        $('a[href="#ScrolTop"]').on('click', function(e) {
            e.preventDefault();
            $('html, body').animate({ scrollTop: 0 }, 300);
        });
    });
    </script>
    <script src="{{ asset('js/script.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
