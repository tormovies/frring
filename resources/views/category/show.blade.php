@extends('layouts.app-old')

@section('title', $seo['title'] ?? $category->name)
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
        <h1>{{ ($seo['h1'] ?? '') !== '' ? $seo['h1'] : ($category->h1 ?? $category->name) }}</h1>
    </div>

    @include('partials.filter-sort', [
        'context' => 'category',
        'currentSort' => in_array($sort ?? 'new', ['plays', 'rating'], true) ? $sort : 'new',
        'slug' => $category->slug,
    ])

    @include('partials.ringtones-list-old', ['materials' => $materials])

    <div class="col-12 navpage">
        {{ $materials->links('components.pagination-old', ['sortContext' => 'category', 'sortType' => in_array($sort ?? 'new', ['plays', 'rating'], true) ? $sort : 'new', 'slug' => $category->slug]) }}
    </div>

    <div class="col-12 text_ringtones">
        <p>{{ meta_replace(strip_tags($category->long_description ?? $category->description ?? 'Все материалы категории ' . $category->name)) }}</p>
    </div>
@endsection

@push('scripts')
<script src="/js/player-old.js"></script>
@endpush
