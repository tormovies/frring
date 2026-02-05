{{--
  Общий верхний блок: логотип, поиск, соцсети, кабинет.
  Сюда потом добавить счётчик (просмотров/звонков и т.п.) на всех страницах.
--}}
<div class="top-line row">
    <div class="col-md-3 col-sm-4">
        <a href="{{ url('/') }}" class="logo_text">{{ config('app.name') }}</a>
        {{-- Место для счётчика: @include('partials.counter') или виджет --}}
    </div>
    <div class="col-md-9 col-sm-8">
        <div class="row">
            <div class="col-xl-7 col-md-5 col-9 form_search">
                <form action="{{ route('search') }}" method="get" role="search">
                    <input type="text" name="query" class="search_field" placeholder="Какую песню вы ищите?" value="{{ request('query') }}" aria-label="Поиск">
                    <button type="submit" class="search_btn"><i class="fas fa-search" aria-hidden="true"></i></button>
                </form>
            </div>
            <div class="col-md-5 col-xl-3 col-4 social_net">
                @php $pageTitle = $seo['title'] ?? config('app.name'); @endphp
                <a rel="nofollow" href="https://vk.com/share.php?url={{ urlencode(url()->current()) }}&title={{ urlencode($pageTitle) }}" target="_blank" title="Поделиться ВКонтакте"><i class="fab fa-vk" aria-hidden="true"></i></a>
                <a rel="nofollow" href="https://t.me/share/url?url={{ urlencode(url()->current()) }}&text={{ urlencode($pageTitle) }}" target="_blank" title="Поделиться в Telegram"><i class="fab fa-telegram-plane" aria-hidden="true"></i></a>
            </div>
            @auth
                <div class="col-md-2 col-3 login_zona">
                    <a href="{{ route('account.materials.index') }}" class="login">Кабинет <i class="fas fa-user" aria-hidden="true"></i></a>
                </div>
            @endauth
        </div>
    </div>
</div>
