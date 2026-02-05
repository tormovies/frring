@extends('layouts.app-old')

@section('title', $seo['title'] ?? meta_replace('Лучшие рингтоны'))
@section('description', $seo['description'] ?? '')

@section('content')
    <div class="col-12 header_title">
        <h1>{{ $seo['h1'] ?? 'Лучшие рингтоны' }}</h1>
    </div>

    @include('partials.filter-sort', ['context' => 'main', 'currentSort' => 'rating', 'slug' => null])

    @include('partials.ringtones-list-old', ['materials' => $materials])

    <div class="col-12 navpage">
        {{ $materials->links('components.pagination-old', ['sortContext' => 'main', 'sortType' => 'rating', 'slug' => null]) }}
    </div>

    <div class="col-12 text_ringtones">
        <p>Рингтоны и звонки, за которые отдали больше всего голосов на нашем портале.</p>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/player-old.js') }}"></script>
@endpush
