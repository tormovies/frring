@extends('layouts.app')

@section('title', $article->title ?? $article->name)
@section('description', Str::limit(strip_tags($article->description ?? ''), 150) ?: ($article->title ?? $article->name))
@section('og_title', $article->title ?? $article->name)
@section('og_description', Str::limit(strip_tags($article->description ?? ''), 150) ?: ($article->title ?? $article->name))
@section('og_image', $article->img ? asset('storage/articles/' . ltrim($article->img, '/')) : asset('img/logo.png'))
@section('og_type', 'article')

@push('json-ld')
<script type="application/ld+json">
@php
$jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $article->title ?? $article->name,
    'description' => Str::limit(strip_tags($article->description ?? ''), 500),
    'url' => route('articles.show', $article->slug),
    'datePublished' => $article->created_at?->format('c'),
    'dateModified' => $article->updated_at?->format('c'),
    'publisher' => ['@type' => 'Organization', 'name' => config('app.name', 'НейроЗвук'), 'url' => url('/')],
    'inLanguage' => 'ru',
];
if ($article->img) {
    $jsonLd['image'] = asset('storage/articles/' . ltrim($article->img, '/'));
}
echo json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp
</script>
@endpush

@section('content')
    <main class="main-cloud">
        <div class="container">
            <div class="article-detail-container">
                <div class="article-main-content">

                    <!-- Заголовок -->
                    <div class="article-header">
                        <div class="article-title-section">
                            <h1>{{ $article->h1 ?? $article->name }}</h1>

                            @if($article->tags->isNotEmpty())
                                <div class="article-meta-badges">
                                    @foreach($article->tags as $tag)
                                        <a href="{{ route('tags.show', $tag->slug) }}" class="article-badge">{{ $tag->name }}</a>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="article-stats-header">
                            <div class="article-author">
                                <div class="author-avatar">AI</div>
                                <div class="author-info">
                                    <h4>AudioCloud Team</h4>
                                    <p>{{ $article->created_at->format('d.m.Y') }}</p>
                                </div>
                            </div>

                            <div class="article-actions-header">
                                <div class="article-stat">
                                    <span>👁 {{ number_format($article->views) }}</span>
                                </div>

                                <div class="article-like-container">
                                    <button type="button"
                                       class="like-btn {{ session()->has('liked_article_'.$article->id) ? 'liked' : '' }}"
                                       data-like-url="{{ route('articles.like', $article->slug) }}"
                                       data-dislike-url="{{ route('articles.dislike', $article->slug) }}"
                                       title="{{ session()->has('liked_article_'.$article->id) ? 'Убрать лайк' : 'Поставить лайк' }}"
                                       aria-label="{{ session()->has('liked_article_'.$article->id) ? 'Убрать лайк' : 'Поставить лайк' }}">{{ session()->has('liked_article_'.$article->id) ? '♥' : '♡' }}</button>
                                    <span class="like-count">{{ number_format($article->likes) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Контент статьи -->
                    <div class="article-content">
                        {!! $article->content !!}
                    </div>

                    <!-- Теги -->
                    @if($article->tags->isNotEmpty())
                        <div class="article-tags">
                            <h3>Теги статьи</h3>
                            <div class="tags-container">
                                @foreach($article->tags as $tag)
                                    <a href="{{ route('tags.show', $tag->slug) }}" class="tag">{{ $tag->name }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Боковая панель -->
                <div class="article-sidebar">
                    <div class="article-stats-sidebar">
                        <h3>Статистика статьи</h3>
                        <div class="stats-grid">
                            <div class="stat-item-sidebar">
                                <div class="stat-info-sidebar">
                                    <div class="stat-icon-sidebar">👁</div>
                                    <div class="stat-text-sidebar">
                                        <h4>Просмотров</h4>
                                        <p>{{ number_format($article->views) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="stat-item-sidebar">
                                <div class="stat-info-sidebar">
                                    <div class="stat-icon-sidebar">♥</div>
                                    <div class="stat-text-sidebar">
                                        <h4>Лайков</h4>
                                        <p>{{ number_format($article->likes) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Похожие материалы -->
                    @if($related->isNotEmpty())
                        <div class="related-articles-sidebar">
                            <h3>Похожие материалы</h3>
                            <div class="related-list">
                                @foreach($related as $mat)
                                    <a href="{{ route('materials.show', $mat->slug) }}" class="related-item-sidebar">
                                        <div class="related-title-sidebar">{{ $mat->title }}</div>
                                        <div class="related-meta-sidebar">
                                            <span>⬇ {{ number_format($mat->downloads) }}</span>
                                            <span>♥ {{ number_format($mat->likes) }}</span>
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
