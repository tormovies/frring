@php
    $useSortUrls = isset($sortContext) && $sortContext !== null && isset($sortType) && $sortType !== null;
    $slug = $slug ?? null;
@endphp
@if ($paginator->hasPages())
<ul>
    @if ($paginator->onFirstPage())
        <li><span>←</span></li>
    @else
        <li><a href="{{ $useSortUrls ? sort_page_url($sortContext, $sortType, $slug, $paginator->currentPage() - 1) : $paginator->previousPageUrl() }}">←</a></li>
    @endif

    @foreach ($elements ?? [] as $element)
        @if (is_string($element))
            <li><span>{{ $element }}</span></li>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page === $paginator->currentPage())
                    <li><span>{{ $page }}</span></li>
                @else
                    <li><a href="{{ $useSortUrls ? sort_page_url($sortContext, $sortType, $slug, $page) : $url }}">{{ $page }}</a></li>
                @endif
            @endforeach
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <li><a href="{{ $useSortUrls ? sort_page_url($sortContext, $sortType, $slug, $paginator->currentPage() + 1) : $paginator->nextPageUrl() }}">→</a></li>
    @else
        <li><span>→</span></li>
    @endif
</ul>
@endif
