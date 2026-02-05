@extends('layouts.app')

@section('title', meta_replace($author->title ?? $author->name ?? 'Автор'))
@section('description', meta_replace(
    Str::limit(strip_tags($author->description ?? ('Автор ' . $author->name)), 250)
))
@section('og_title', meta_replace($author->title ?? $author->name ?? 'Автор'))
@section('og_description', meta_replace(Str::limit(strip_tags($author->description ?? ('Автор ' . $author->name)), 250)))
@section('og_image', $author->img ? \Illuminate\Support\Facades\Storage::disk('authors')->url($author->img) : asset('img/logo.png'))

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1 class="page-title">
                    {{ meta_replace($author->h1 ?? $author->name ?? 'Автор') }}
                </h1>
                <p class="page-subtitle">
                    {{ meta_replace(strip_tags($author->long_description ?? ('Автор ' . $author->name))) }}
                </p>
            </div>
        </div>
    </div>

    <!-- Основной контент -->
    <main class="main-cloud">
        <div class="container">
            <div class="article-detail-container">
                <!-- Левая колонка - основная информация об авторе -->
                <div class="article-main-content">
                    <!-- Заголовок и фото автора -->
                    <div class="author-header">
                        <div>
                            <div class="author-title-section">
                                <h2>{{ $author->name }}</h2>

                                <div class="article-meta-badges">
                                    @forelse($topTags as $tag)
                                        <a href="{{ route('tags.show', $tag->slug) }}"
                                           class="article-badge">{{ $tag->name }}</a>
                                    @empty
                                        <span class="article-badge">Музыка</span>
                                    @endforelse
                                </div>
                            </div>

                            <div class="author-stats-header">
                                <div class="author-stat">
                                    <span>🎵</span>
                                    <span>{{ number_format($stats['tracks']) }} треков</span>
                                </div>
                                <div class="author-stat">
                                    <span>👁</span>
                                    <span>{{ number_format($stats['views']) }} прослушиваний</span>
                                </div>
                                <div class="author-like-container">
                                    <span class="like-btn liked" aria-hidden="true">♥</span>
                                    <span class="like-count">{{ number_format($stats['likes']) }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            @if($author->img)
                                <img src="{{ asset('storage/authors/' . ltrim($author->img)) }}"
                                     alt="{{ $author->title ?? $author->name }}"
                                     width="200" height="200"
                                     loading="lazy"
                                     class="author-image-main">
                            @endif
                            <p class="text-tertiary author-caption-block">
                                {{ $author->caption ?? ($author->title ?? $author->name) }}
                            </p>
                        </div>
                    </div>

                    <!-- Контент об авторе -->
                    @if(!empty($author->content))
                        <div class="article-content author-content-section">
                            {!! $author->content !!}
                        </div>
                    @endif

                    <!-- Навыки / Теги автора -->
                    @if($topTags->isNotEmpty())
                        <div class="article-tags">
                            <h3>Навыки и специализация</h3>
                            <div class="tags-container">
                                @foreach($topTags as $t)
                                    <a href="{{ route('tags.show', $t->slug) }}" class="tag">{{ $t->name }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Панель управления видом и сортировкой материалов автора -->
                    <div class="view-controls mt-2">
                        <div class="view-controls-group">
                            <div class="sort-controls">
                                <span class="text-secondary">Сортировка:</span>
                                <select class="sort-select" id="sort-select">
                                    <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>По
                                        популярности
                                    </option>
                                    <option value="new" {{ $sort === 'new' ? 'selected' : '' }}>По новизне</option>
                                    <option value="alpha" {{ $sort === 'alpha' ? 'selected' : '' }}>По алфавиту</option>
                                    <option value="duration" {{ $sort === 'duration' ? 'selected' : '' }}>По
                                        длительности
                                    </option>
                                    <option value="views" {{ $sort === 'views' ? 'selected' : '' }}>По просмотрам
                                    </option>
                                </select>
                            </div>

                            <div class="view-toggle">
                                <button
                                    class="view-btn {{ ($view ?? request('view','list')) === 'list' ? 'active' : '' }}"
                                    data-view="list" title="Вид списка">≡
                                </button>
                                <button class="view-btn {{ ($view ?? request('view')) === 'grid' ? 'active' : '' }}"
                                        data-view="grid" title="Вид плитки">⧉
                                </button>
                            </div>
                        </div>
                    </div>

                    @php $currentView = $view ?? request('view', 'list'); @endphp
                    <div class="view-container {{ $currentView === 'grid' ? 'view-grid' : 'view-list' }}">
                    <!-- Список работ автора — вид списка -->
                    <div class="audio-list-view" id="list-view">
                        @forelse($materials as $material)
                            <div class="audio-item-list">
                                @if($material->hasFile())
                                    <button class="play-btn-list"
                                            data-audio-url="{{ $material->fileUrl() }}"
                                            data-title="{{ $material->name }}"
                                            data-author="{{ optional($material->authors->first())->name ?? 'AI' }}"
                                            data-type="{{ $material->type->name ?? '' }}">▶
                                    </button>
                                @else
                                    <button class="no-play-btn-list disabled" title="Файл отсутствует">🚫</button>
                                @endif

                                <div class="audio-info-list">
                                    <div class="audio-title-list">
                                        <a href="{{ route('materials.show', $material->slug) }}">{{ $material->name }}</a>
                                    </div>
                                    <div class="audio-meta-list">
                                        @if($material->authors->isNotEmpty())
                                            <a href="{{ route('search', ['query' => $material->authors->first()->name]) }}">
                                                {{ $material->authors->first()->name }}
                                            </a>
                                        @else
                                            AI
                                        @endif
                                        <span class="audio-bitrate"> • {{ $material->mp4_bitrate ?? 128 }}kbps</span> •
                                        {{ $material->mp4_duration ? gmdate('i:s', $material->mp4_duration) : '—' }} •
                                        <span class="audio-downloads">⬇️ {{ number_format($material->downloads ?? 0) }}</span>
                                    </div>
                                </div>
                                <div class="audio-actions-list">
                                    @if($material->hasFile())
                                        <a href="{{ route('materials.download', [$material->slug, 'mp4']) }}"
                                           class="btn btn-secondary btn-download-icon" title="Скачать" download>⬇</a>
                                    @else
                                        <button class="btn btn-secondary btn-download-icon" disabled title="Нет файла">⬇</button>
                                    @endif

                                    <div class="like-container">
                                        <button type="button"
                                           class="like-btn {{ session()->has('liked_'.$material->id) ? 'liked' : '' }}"
                                           data-like-url="{{ route('materials.like', $material->slug) }}"
                                           data-dislike-url="{{ route('materials.dislike', $material->slug) }}"
                                           title="{{ session()->has('liked_'.$material->id) ? 'Убрать лайк' : 'Поставить лайк' }}"
                                           aria-label="{{ session()->has('liked_'.$material->id) ? 'Убрать лайк' : 'Поставить лайк' }}">{{ session()->has('liked_'.$material->id) ? '♥' : '♡' }}</button>
                                        <span class="like-count">{{ $material->likes ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-secondary list-empty-msg">У автора пока нет опубликованных
                                материалов</p>
                        @endforelse
                    </div>

                    <!-- Список работ автора — вид плитки -->
                    <div class="audio-grid-view" id="grid-view">
                        @foreach($materials as $material)
                            <div class="audio-item-grid">
                                @if($material->hasFile())
                                    <button class="play-btn-grid"
                                            data-audio-url="{{ $material->fileUrl() }}"
                                            data-title="{{ $material->name }}"
                                            data-author="{{ optional($material->authors->first())->name ?? 'AI' }}"
                                            data-type="{{ $material->type->name ?? '' }}">▶
                                    </button>
                                @else
                                    <button class="no-play-btn-grid disabled" title="Файл отсутствует">🚫</button>
                                @endif

                                <div class="audio-header-grid">
                                    <div class="audio-type-grid">{{ $material->type->name ?? '' }}</div>
                                </div>

                                <div class="audio-content-grid">
                                    <div class="audio-title-grid">
                                        <a href="{{ route('materials.show', $material->slug) }}">{{ $material->name }}</a>
                                    </div>
                                    <div class="audio-description-grid">
                                        {{ Str::limit($material->description, 60) }}
                                    </div>
                                    <div class="audio-wave-grid">
                                        <div class="wave-grid"></div>
                                        <div class="wave-grid"></div>
                                        <div class="wave-grid"></div>
                                        <div class="wave-grid"></div>
                                        <div class="wave-grid"></div>
                                    </div>
                                </div>

                                <div class="audio-footer-grid">
                                    <div class="audio-stats-grid">
                                        <span class="duration-icon">⏱</span> {{ $material->mp4_duration ? gmdate('i:s', $material->mp4_duration) : '—' }}
                                        • <span class="download-icon">⬇</span> {{ number_format($material->downloads ?? 0) }}
                                    </div>

                                    <div class="audio-actions-grid">
                                        <div class="audio-likes-grid">
                                            <button type="button"
                                               class="like-btn {{ session()->has('liked_'.$material->id) ? 'liked' : '' }}"
                                               data-like-url="{{ route('materials.like', $material->slug) }}"
                                               data-dislike-url="{{ route('materials.dislike', $material->slug) }}"
                                               title="{{ session()->has('liked_'.$material->id) ? 'Убрать лайк' : 'Поставить лайк' }}"
                                               aria-label="{{ session()->has('liked_'.$material->id) ? 'Убрать лайк' : 'Поставить лайк' }}">{{ session()->has('liked_'.$material->id) ? '♥' : '♡' }}</button>
                                            <span class="like-count-grid">{{ $material->likes ?? 0 }}</span>
                                        </div>

                                        @if($material->hasFile())
                                            <a href="{{ route('materials.download', [$material->slug, 'mp4']) }}"
                                               class="btn-download-grid" title="Скачать" download>⬇</a>
                                        @else
                                            <button class="btn-download-grid disabled" disabled title="Нет файла">⬇</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    </div>

                    <!-- Пагинация -->
                    <div class="pagination">
                        {{ $materials->appends([
                            'sort' => $sort ?? 'popular',
                            'view' => $view ?? request('view','list'),
                        ])->links('components.pagination') }}
                    </div>
                </div>

                <!-- Правая колонка - сайдбар -->
                <div class="article-sidebar">
                    <!-- Статистика автора -->
                    <div class="article-stats-sidebar">
                        <h3>Статистика автора</h3>
                        <div class="stats-grid">
                            <div class="stat-item-sidebar">
                                <div class="stat-info-sidebar">
                                    <div class="stat-icon-sidebar">🎵</div>
                                    <div class="stat-text-sidebar">
                                        <h4>Треков</h4>
                                        <p>{{ number_format($stats['tracks']) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="stat-item-sidebar">
                                <div class="stat-info-sidebar">
                                    <div class="stat-icon-sidebar">👁</div>
                                    <div class="stat-text-sidebar">
                                        <h4>Прослушиваний</h4>
                                        <p>{{ number_format($stats['views']) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="stat-item-sidebar">
                                <div class="stat-info-sidebar">
                                    <div class="stat-icon-sidebar">♥</div>
                                    <div class="stat-text-sidebar">
                                        <h4>Лайков</h4>
                                        <p>{{ number_format($stats['likes']) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="stat-item-sidebar">
                                <div class="stat-info-sidebar">
                                    <div class="stat-icon-sidebar">📅</div>
                                    <div class="stat-text-sidebar">
                                        <h4>На платформе</h4>
                                        <p>{{ $stats['tenure'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Популярные треки автора -->
                    @if($topPopular->isNotEmpty())
                        <div class="related-articles-sidebar">
                            <h3>Популярные треки</h3>
                            <div class="related-list">
                                @foreach($topPopular as $item)
                                    <a href="{{ route('materials.show', $item->slug) }}" class="related-item-sidebar">
                                        <div class="related-title-sidebar">{{ $item->name }}</div>
                                        <div class="related-meta-sidebar">
                                            <span>👁 {{ number_format($item->views) }}</span>
                                            <span>♥ {{ number_format($item->likes) }}</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
@endsection

