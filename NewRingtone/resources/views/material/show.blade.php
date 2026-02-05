@extends('layouts.app-old')

@section('title', $seo['title'] ?? $material->name)
@section('description', $seo['description'] ?? '')

@push('head')
@php
    $audioUrl = $material->hasFile() ? $material->fileUrl() : '';
    $durationSec = (int) ($material->mp4_duration ?? 0);
    $isoDuration = $durationSec > 0 ? 'PT' . gmdate('G', $durationSec) . 'H' . gmdate('i', $durationSec) . 'M' . gmdate('s', $durationSec) . 'S' : null;
    if ($isoDuration && str_starts_with($isoDuration, 'PT0H')) {
        $isoDuration = 'PT' . gmdate('i', $durationSec) . 'M' . gmdate('s', $durationSec) . 'S';
    }
@endphp
<script type="application/ld+json">
@php
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'MusicRecording',
        'name' => $material->name,
        'description' => Str::limit(strip_tags($material->long_description ?? $material->description ?? ''), 500),
        'url' => route('materials.show', $material->slug),
        'datePublished' => $material->created_at?->format('c'),
        'inLanguage' => 'ru',
        'publisher' => ['@type' => 'Organization', 'name' => config('app.name'), 'url' => url('/')],
    ];
    if ($audioUrl !== '') {
        $jsonLd['contentUrl'] = $audioUrl;
    }
    if ($isoDuration !== null) {
        $jsonLd['duration'] = $isoDuration;
    }
    if ($material->authors->isNotEmpty()) {
        $jsonLd['byArtist'] = ['@type' => 'Person', 'name' => $material->authors->pluck('name')->implode(', ')];
    }
    echo json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp
</script>
@endpush

@section('content')
<article itemscope itemtype="https://schema.org/MusicRecording">
    <div class="col-12 header_title">
        <h1 itemprop="name">{{ ($seo['h1'] ?? '') !== '' ? $seo['h1'] : ($material->h1 ?? $material->name) }}</h1>
    </div>

    <div id="player">
        <audio src="" preload="none"></audio>
    </div>

    @php
        $durationStr = ($material->mp4_duration && (int) $material->mp4_duration > 0)
            ? gmdate('i:s', (int) $material->mp4_duration)
            : '0:00';
        $audioUrl = $material->hasFile() ? $material->fileUrl() : '';
        $size = (int) ($material->mp4_size ?? 0);
        if ($size === 0 && $material->hasFile() && $material->mp4) {
            try {
                $size = \Illuminate\Support\Facades\Storage::disk('mp4')->size(ltrim($material->mp4, '/'));
            } catch (Exception $e) {
                $size = 0;
            }
        }
        $formatted = $size
            ? ($size >= 1048576
                ? number_format($size / 1048576, 2) . ' MB'
                : number_format($size / 1024, 2) . ' KB')
            : '—';

        $m4rFile = null;
        $m4rSize = 0;
        if ($material->m4r30 && \Illuminate\Support\Facades\Storage::disk('m4r30')->exists(ltrim($material->m4r30, '/'))) {
            $m4rFile = 'm4r30';
            try {
                $m4rSize = \Illuminate\Support\Facades\Storage::disk('m4r30')->size(ltrim($material->m4r30, '/'));
            } catch (Exception $e) {}
        } elseif ($material->m4r40 && \Illuminate\Support\Facades\Storage::disk('m4r40')->exists(ltrim($material->m4r40, '/'))) {
            $m4rFile = 'm4r40';
            try {
                $m4rSize = \Illuminate\Support\Facades\Storage::disk('m4r40')->size(ltrim($material->m4r40, '/'));
            } catch (Exception $e) {}
        }
        if (!$m4rFile && $material->m4rFileUrl()) {
            $m4rFile = 'm4r30';
        }
        $m4rFormatted = $m4rSize
            ? ($m4rSize >= 1048576 ? number_format($m4rSize / 1048576, 2) . ' MB' : number_format($m4rSize / 1024, 2) . ' KB')
            : '—';
        $bitrate = $material->mp4_bitrate
            ? ($material->mp4_bitrate >= 1000 ? round($material->mp4_bitrate / 1000) : $material->mp4_bitrate) . ' kb/s'
            : '128 kb/s';
    @endphp

    <div class="col-xl-12">
        <div id="song_{{ $material->id }}" audio_url="{{ $audioUrl }}" class="aduio_player">
            <div class="play_l_btn">
                @if($material->hasFile())
                    <button type="button" class="play_audio" aria-label="Воспроизвести"><i class="far fa-play-circle"></i> <i class="far fa-pause-circle"></i></button>
                @else
                    <button type="button" class="play_audio disabled" disabled aria-label="Нет файла"><i class="far fa-play-circle"></i> <i class="far fa-pause-circle"></i></button>
                @endif
            </div>
            <div class="info_to_range">
                <span class="name">{{ $material->name }}</span>
                <span class="time"><i class="far fa-clock"></i> <time>{{ $durationStr }}</time></span>
                <span class="dwnld"><i class="fas fa-download"></i> {{ number_format($material->downloads ?? 0) }}</span>
            </div>
            <div class="like-container">
                <button type="button" class="like-btn {{ session()->has('liked_' . $material->id) ? 'liked' : '' }}"
                    data-like-url="{{ route('materials.like', $material->slug) }}"
                    data-dislike-url="{{ route('materials.dislike', $material->slug) }}"
                    title="{{ session()->has('liked_' . $material->id) ? 'Убрать лайк' : 'Нравится' }}"
                    aria-label="{{ session()->has('liked_' . $material->id) ? 'Убрать лайк' : 'Нравится' }}">{{ session()->has('liked_' . $material->id) ? '♥' : '♡' }}</button>
                <span class="like-count" id="vcount_{{ $material->id }}">{{ $material->likes ?? 0 }}</span>
            </div>
        </div>
    </div>

    <div class="col-12 material-two-tiles">
        <div class="material-tile material-tile-left">
            <dl class="info_ringtone">
                <dt>Исполнитель:</dt>
                <dd>
                    @if($material->authors->isNotEmpty())
                        @foreach($material->authors as $author)
                            <a href="{{ route('search', ['query' => $author->name]) }}">{{ $author->name }}</a>@if(!$loop->last), @endif
                        @endforeach
                    @else
                        —
                    @endif
                </dd>

                <dt>Категория:</dt>
                <dd>
                    @if($material->categories->isNotEmpty())
                        @foreach($material->categories as $cat)
                            <a href="{{ url('/category/' . $cat->slug . '.html') }}">{{ $cat->name }}</a>@if(!$loop->last)&nbsp;&nbsp;›&nbsp;&nbsp;@endif
                        @endforeach
                    @else
                        —
                    @endif
                </dd>

                @if(trim(strip_tags($material->description ?? '')) !== '' || trim(strip_tags($material->long_description ?? '')) !== '')
                    <dt class="des">Описание:</dt>
                    <dd class="des">
                        <span itemprop="description">
                            {!! nl2br(e(Str::limit(strip_tags($material->long_description ?? $material->description ?? ''), 500))) !!}
                        </span>
                    </dd>
                @endif
            </dl>
        </div>

        <div class="material-tile material-tile-right download_zone">
            @if($material->hasFile())
                <a href="{{ route('materials.download', [$material->slug, 'mp4']) }}" class="btn btn-downl" download>Скачать mp3</a>
            @endif
            @if($m4rFile)
                <a href="{{ route('materials.download', [$material->slug, $m4rFile]) }}" class="btn btn-downl-2" download>Скачать m4r</a>
            @endif
            <dl class="info_ringtone slim">
                <dt>Размер:</dt>
                <dd>{{ $formatted }}</dd>
                <dt>Битрейт:</dt>
                <dd>{{ $bitrate }}</dd>
                <dt>Добавлен:</dt>
                <dd>{{ $material->created_at->format('d.m.Y') }}</dd>
                <dt class="des">Послушали уже {{ number_format($material->downloads ?? 0) }} человек</dt>
            </dl>
        </div>
    </div>

    <div class="col-12 text_ringtones">
        @php
            $authorName = $material->authors->first()?->name;
            $compositionName = $authorName && str_contains($material->name, ' - ')
                ? trim(Str::after($material->name, ' - '))
                : $material->name;
        @endphp
        <p>Поздравляем ! Вы нашли отличный звонок на телефон. @if($authorName)Скачать рингтон от {{ $authorName }} из композиции {{ $compositionName }} можно@elseСкачать рингтон «{{ $material->name }}» можно@endif бесплатно, нажав на соответствующюю кнопку "Скачать", файл будет скачан в формате mp3 или m4r, в зависимости от вашего выбора. Если автоматического скачивания не началось после нажатия на кнопку, жмите над кнопкой — правой клавишей мышки, и выбирайте "Сохранить ссылку как ...". В мобильных устройствах надо нажать и держать палец на кнопке, после чего выбрать "Сохранить данные по ссылке".</p>
    </div>
</article>

    @if($related->isNotEmpty())
        @foreach($related as $rel)
            @php
                $relDuration = $rel->mp4_duration ? gmdate('i:s', $rel->mp4_duration) : '0:00';
                $relUrl = $rel->hasFile() ? $rel->fileUrl() : '';
            @endphp
            <div class="col-xl-6">
                <div id="song_rel_{{ $rel->id }}" audio_url="{{ $relUrl }}" class="aduio_player">
                    <div class="play_l_btn">
                        @if($rel->hasFile())
                            <button type="button" class="play_audio" aria-label="Воспроизвести"><i class="far fa-play-circle"></i> <i class="far fa-pause-circle"></i></button>
                        @else
                            <button type="button" class="play_audio disabled" disabled><i class="far fa-play-circle"></i> <i class="far fa-pause-circle"></i></button>
                        @endif
                    </div>
                    <div class="info_to_range">
                        <a href="{{ route('materials.show', $rel->slug) }}" class="name">{{ $rel->name }}</a>
                        <span class="time"><i class="far fa-clock"></i> <time>{{ $relDuration }}</time></span>
                        <span class="dwnld"><i class="fas fa-download"></i> {{ number_format($rel->downloads ?? 0) }}</span>
                    </div>
                    <div class="like-container">
                        <button type="button" class="like-btn {{ session()->has('liked_' . $rel->id) ? 'liked' : '' }}"
                            data-like-url="{{ route('materials.like', $rel->slug) }}"
                            data-dislike-url="{{ route('materials.dislike', $rel->slug) }}"
                            title="{{ session()->has('liked_' . $rel->id) ? 'Убрать лайк' : 'Нравится' }}"
                            aria-label="{{ session()->has('liked_' . $rel->id) ? 'Убрать лайк' : 'Нравится' }}">{{ session()->has('liked_' . $rel->id) ? '♥' : '♡' }}</button>
                        <span class="like-count" id="vcount_rel_{{ $rel->id }}">{{ $rel->likes ?? 0 }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@endsection

@push('scripts')
<script src="{{ asset('js/player-old.js') }}"></script>
@endpush
