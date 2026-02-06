@extends('layouts.app-old')

@section('title', $seo['title'] ?? meta_replace('Популярные рингтоны'))
@section('description', $seo['description'] ?? '')

@section('content')
    <div class="col-12 header_title">
        <h1>{{ $seo['h1'] ?? 'Популярные рингтоны' }}</h1>
    </div>

    @include('partials.filter-sort', ['context' => 'main', 'currentSort' => 'plays', 'slug' => null])

    @include('partials.ringtones-list-old', ['materials' => $materials])

    <div class="col-12 navpage">
        {{ $materials->links('components.pagination-old', ['sortContext' => 'main', 'sortType' => 'plays', 'slug' => null]) }}
    </div>

    <div class="col-12 text_ringtones">
        <p>Самые популярные по скачиваниям рингтоны и звонки — скачать бесплатно.</p>
    </div>
@endsection

@push('scripts')
<script src="/js/player-old.js"></script>
@endpush
