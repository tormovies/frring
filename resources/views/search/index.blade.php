@extends('layouts.app-old')

@section('title', $seo['title'] ?? ($term ? "Результаты поиска: {$term}" : 'Поиск по сайту'))
@section('description', $seo['description'] ?? ($term ? "Результаты поиска по запросу «{$term}»" : 'Поиск рингтонов на сайте'))

@section('content')
    <div class="col-12 header_title">
        <h1>
            @if(!empty($seo['h1']))
                {{ $seo['h1'] }}
            @elseif($term)
                Результаты поиска: «{{ $term }}»
            @else
                Поиск
            @endif
        </h1>
    </div>

    @if($term)
        <div class="col-12 filter">
            <span>Найдено: {{ $materials->total() }}</span>
        </div>

        @include('partials.ringtones-list-old', ['materials' => $materials])

        <div class="col-12 navpage">
            {{ $materials->links('components.pagination-old') }}
        </div>

        <div class="col-12 text_ringtones">
            <p>По запросу «{{ $term }}» найдено рингтонов и мелодий: {{ $materials->total() }}. Скачать бесплатно можно после прослушивания.</p>
        </div>
    @else
        <div class="col-12 text_ringtones">
            <p>Введите запрос в строку поиска выше, чтобы найти нужный рингтон или мелодию.</p>
        </div>
    @endif
@endsection

@push('scripts')
@if($term && $materials->isNotEmpty())
<script src="/js/player-old.js"></script>
@endif
@endpush
