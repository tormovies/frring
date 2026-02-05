@extends('layouts.app')

@section('title', $seo['title'] ?? meta_replace('Статьи о звуке и аудио %year% %page%'))
@section('description', $seo['description'] ?? meta_replace(
    Str::limit(
        strip_tags('Познавательные материалы о создании звуков, музыкальной теории, аудиотехнике и многом другом'),
        250
    )
))

@section('content')

    <!-- Заголовок страницы -->
    <div class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1 class="page-title">
                    {{ $seo['h1'] ?? meta_replace('Статьи о звуке и аудио %year% %page%') }}
                </h1>
                <p class="page-subtitle">
                    {{ $seo['description'] ?? meta_replace(
                        strip_tags('Познавательные материалы о создании звуков, музыкальной теории, аудиотехнике и многом другом')
                    ) }}
                </p>
            </div>
        </div>
    </div>

    <!-- Основной контент -->
    <main class="main-cloud">
        <div class="container">
            <!-- Панель управления видом отображения -->
            <div class="view-controls">
                <div class="view-controls-group">
                    <div class="sort-controls">
                        <span class="text-secondary">Сортировка:</span>
                        <select class="sort-select" id="sort-select">
                            <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>По популярности</option>
                            <option value="new"      {{ $sort  === 'new' ? 'selected' : '' }}>По новизне</option>
                            <option value="alpha"    {{ $sort === 'alpha' ? 'selected' : '' }}>По алфавиту</option>
                            <option value="views"    {{ $sort  === 'views' ? 'selected' : '' }}>По просмотрам</option>
                        </select>
                    </div>
                    <div class="view-toggle">
                        <button class="view-btn {{ ($view ?? request('view','list')) === 'list' ? 'active' : '' }}"
                                data-view="list" title="Вид списка">≡</button>
                        <button class="view-btn {{ ($view ?? request('view')) === 'grid' ? 'active' : '' }}"
                                data-view="grid" title="Вид плитки">⧉</button>
                    </div>
                </div>
            </div>

            @php $currentView = $view ?? request('view', 'list'); @endphp
            <div class="view-container {{ $currentView === 'grid' ? 'view-grid' : 'view-list' }}">
            <!-- Список статей - вид списка -->
            <div class="audio-list-view" id="list-view">
                @forelse($articles as $article)
                    <div class="audio-item-list">
                        <div class="article-image-list">
                            @if(!empty($article->img))
                                <img src="{{ asset('storage/articles/' . ltrim($article->img)) }}"
                                     alt="{{ $article->title ?? $article->name }}"
                                     class="article-thumb"
                                     width="300" height="200"
                                     loading="lazy">
                            @endif
                        </div>

                        <div class="audio-info-list">
                            <div class="audio-title-list">
                                <a href="{{ route('articles.show', $article->slug) }}">
                                    {{ $article->title ?? $article->name }}
                                </a>
                            </div>
                            <div class="audio-meta-list">
                                {{ $article->created_at->format('d.m.Y') }} •
                                👁 {{ number_format($article->views) }} •
                                ♥ {{ number_format($article->likes) }}
                            </div>
                            <div class="article-excerpt">
                                {{ Str::limit(strip_tags($article->description ?? ''), 160) }}
                            </div>

                            @if($article->tags->isNotEmpty())
                                <div class="article-tags-small">
                                    @foreach($article->tags as $tag)
                                        <a href="{{ route('tags.show', $tag->slug) }}" class="tag">{{ $tag->name }}</a>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="audio-actions-list">
                            <a class="btn btn-secondary" href="{{ route('articles.show', $article->slug) }}">Читать</a>

                            <div class="like-container">
                                <button type="button"
                                   class="like-btn {{ session()->has('liked_article_'.$article->id) ? 'liked' : '' }}"
                                   data-like-url="{{ route('articles.like', $article->slug) }}"
                                   data-dislike-url="{{ route('articles.dislike', $article->slug) }}"
                                   title="{{ session()->has('liked_article_'.$article->id) ? 'Убрать лайк' : 'Поставить лайк' }}"
                                   aria-label="{{ session()->has('liked_article_'.$article->id) ? 'Убрать лайк' : 'Поставить лайк' }}">{{ session()->has('liked_article_'.$article->id) ? '♥' : '♡' }}</button>
                                <span class="like-count">{{ $article->likes }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-secondary list-empty-msg">Пока нет опубликованных статей</p>
                @endforelse
            </div>

            <!-- Сетка статей - вид плитки -->
            <div class="audio-grid-view" id="grid-view">
                @foreach($articles as $article)
                    <div class="audio-item-grid article-item-grid">
                        <div class="article-image-grid">
                            @if(!empty($article->img))
                                <a href="{{ route('articles.show', $article->slug) }}">
                                    <img src="{{ asset('storage/articles/' . ltrim($article->img)) }}"
                                         alt="{{ $article->title ?? $article->name }}"
                                         class="article-thumb-grid"
                                         width="300" height="200"
                                         loading="lazy">
                                </a>
                            @endif
                        </div>

                        <div class="audio-header-grid">
                            <div class="audio-type-grid">Статья</div>
                            <div class="article-read-time"></div>
                        </div>

                        <div class="audio-content-grid">
                            <div class="audio-title-grid">
                                <a href="{{ route('articles.show', $article->slug) }}">
                                    {{ $article->title ?? $article->name }}
                                </a>
                            </div>
                            <div class="audio-description-grid">
                                {{ Str::limit(strip_tags($article->description ?? ''), 100) }}
                            </div>
                        </div>

                        <div class="article-meta-grid">
                            <div class="article-date">{{ $article->created_at->format('d.m.Y') }}</div>
                        </div>

                        <div class="audio-footer-grid">
                            <div class="audio-stats-grid">
                                👁 {{ number_format($article->views) }} • ♥ {{ number_format($article->likes) }}
                            </div>
                            <div class="audio-likes-grid">
                                <button type="button"
                                   class="like-btn {{ session()->has('liked_article_'.$article->id) ? 'liked' : '' }}"
                                   data-like-url="{{ route('articles.like', $article->slug) }}"
                                   data-dislike-url="{{ route('articles.dislike', $article->slug) }}"
                                   title="{{ session()->has('liked_article_'.$article->id) ? 'Убрать лайк' : 'Поставить лайк' }}"
                                   aria-label="{{ session()->has('liked_article_'.$article->id) ? 'Убрать лайк' : 'Поставить лайк' }}">{{ session()->has('liked_article_'.$article->id) ? '♥' : '♡' }}</button>
                                <span class="like-count-grid">{{ $article->likes }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            </div>

            <!-- Пагинация -->
            <div class="pagination">
                {{ $articles->links('components.pagination') }}
            </div>
        </div>
    </main>
@endsection
