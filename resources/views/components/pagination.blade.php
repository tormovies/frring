@if ($paginator->hasPages())
    <div class="pagination">

        {{-- Кнопка "назад" --}}
        @if ($paginator->onFirstPage())
            <button class="page-btn disabled" disabled title="Назад">←</button>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="page-btn" title="Назад">←</a>
        @endif

        {{-- Список страниц --}}
        @foreach ($elements as $element)
            {{-- Троеточие --}}
            @if (is_string($element))
                <span class="page-btn dots" aria-disabled="true">{{ $element }}</span>
            @endif

            {{-- Ссылки на страницы --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page === $paginator->currentPage())
                        <button class="page-btn active" aria-current="page">{{ $page }}</button>
                    @else
                        <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Кнопка "вперёд" --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="page-btn" title="Вперёд">→</a>
        @else
            <button class="page-btn disabled" disabled title="Вперёд">→</button>
        @endif

    </div>
@endif
