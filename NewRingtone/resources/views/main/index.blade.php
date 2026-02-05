@extends('layouts.app-old')

@section('title', $seo['title'] ?? meta_replace('Рингтоны на телефон — новинки, популярные, лучшие'))
@section('description', $seo['description'] ?? '')

@push('head')
@php
    $listItems = $materials->items();
    $basePosition = ($materials->currentPage() - 1) * $materials->perPage();
@endphp
@if(count($listItems) > 0)
<script type="application/ld+json">
@php
    $itemList = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'url' => $materials->url($materials->currentPage()),
        'numberOfItems' => $materials->total(),
        'itemListElement' => [],
    ];
    foreach ($listItems as $i => $mat) {
        $itemList['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => $basePosition + $i + 1,
            'item' => [
                '@type' => 'MusicRecording',
                'name' => $mat->name,
                'url' => route('materials.show', $mat->slug),
            ],
        ];
    }
    echo json_encode($itemList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp
</script>
@endif
@endpush

@section('content')
    <div class="col-12 header_title">
        <h1>{{ $seo['h1'] ?? 'Рингтоны на телефон' }}</h1>
    </div>

    @include('partials.filter-sort', ['context' => 'main', 'currentSort' => 'new', 'slug' => null])

    @include('partials.ringtones-list-old', ['materials' => $materials])

    <div class="col-12 navpage">
        {{ $materials->links('components.pagination-old') }}
    </div>

    <div class="col-12 text_ringtones">
        <p>Рингтон – это мелодия, музыкальная композиция, звуки, воспроизводимые на мобильном телефоне, оповещающие о входящем звонке. Скачать бесплатно рингтоны и звонки можно в любом разделе портала. Разрешено предварительное прослушивание, затем любой рингтон можно скачать бесплатно.</p>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/player-old.js') }}"></script>
@endpush
