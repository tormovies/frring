@extends('layouts.app')

@section('title', meta_replace($type->title ?? $type->name))
@section('description', meta_replace(Str::limit(strip_tags($type->description ?? 'Все материалы типа ' . $type->name), 250)))
@section('og_title', meta_replace($type->title ?? $type->name))
@section('og_description', meta_replace(Str::limit(strip_tags($type->description ?? 'Все материалы типа ' . $type->name), 250)))

@section('content')

    <!-- Заголовок страницы -->
    <div class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1 class="page-title">{{ meta_replace($type->h1 ?? $type->name) }}</h1>
                <p class="page-subtitle">{{ meta_replace(strip_tags($type->long_description ?? 'Все материалы типа ' . $type->name)) }}</p>
            </div>
        </div>
    </div>

    <main class="main-cloud">
        <div class="container">

            <!-- Панель управления видом -->
            <div class="view-controls">
                <div class="view-controls-group">
                    <div class="sort-controls">
                        <span class="text-secondary">Сортировка:</span>
                        <select class="sort-select" id="sort-select">
                            <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>По популярности</option>
                            <option value="new" {{ $sort === 'new' ? 'selected' : '' }}>По новизне</option>
                            <option value="alpha" {{ $sort === 'alpha' ? 'selected' : '' }}>По алфавиту</option>
                            <option value="duration" {{ $sort === 'duration' ? 'selected' : '' }}>По длительности
                            </option>
                        </select>
                    </div>
                    <div class="view-toggle">
                        <button class="view-btn {{ request('view', 'list') === 'list' ? 'active' : '' }}"
                                data-view="list" title="Вид списка">≡
                        </button>
                        <button class="view-btn {{ request('view') === 'grid' ? 'active' : '' }}"
                                data-view="grid" title="Вид плитки">⧉
                        </button>
                    </div>
                </div>
            </div>

            @php $currentView = request('view', 'list'); @endphp
            <div class="view-container {{ $currentView === 'grid' ? 'view-grid' : 'view-list' }}">
            <!-- Список -->
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
                                <a href="{{ route('materials.show', $material->slug) }}">
                                    {{ $material->name }}
                                </a>
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
                    <p class="text-secondary list-empty-msg">Нет материалов данного типа</p>
                @endforelse
            </div>

            <!-- Вид плитки -->
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
                {{ $materials->links('components.pagination') }}
            </div>

        </div>
    </main>
@endsection
