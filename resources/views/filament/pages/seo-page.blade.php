<x-filament-panels::page>
    <style>
        .seo-page-url-box { background: #f3f4f6; padding: 1rem 1.25rem; border-radius: 12px; border: 1px solid #e5e7eb; }
        .dark .seo-page-url-box { background: rgba(31,41,55,0.8); border-color: #374151; }
        .seo-page-cache-badge { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem; border-radius: 8px; background: #f3f4f6; border: 1px solid #e5e7eb; }
        .dark .seo-page-cache-badge { background: rgba(31,41,55,0.8); border-color: #374151; }
        .seo-page-table-wrap { overflow: hidden; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .dark .seo-page-table-wrap { border-color: #374151; }
        .seo-page-table { width: 100%; border-collapse: collapse; }
        .seo-page-table th { text-align: left; padding: 14px 20px; font-weight: 600; font-size: 0.875rem; background: rgba(245,158,11,0.15); border-bottom: 1px solid #e5e7eb; }
        .dark .seo-page-table th { border-color: #374151; color: #e5e7eb; }
        .seo-page-table td { padding: 12px 20px; font-size: 0.875rem; border-bottom: 1px solid #f3f4f6; }
        .dark .seo-page-table td { border-color: #1f2937; color: #d1d5db; }
        .seo-page-table tbody tr:nth-child(even) { background: #f9fafb; }
        .dark .seo-page-table tbody tr:nth-child(even) { background: rgba(31,41,55,0.5); }
        .seo-page-table tbody tr:nth-child(odd) { background: #fff; }
        .dark .seo-page-table tbody tr:nth-child(odd) { background: rgba(17,24,39,0.5); }
        .seo-page-table tfoot td { padding: 16px 20px; font-weight: 600; background: rgba(245,158,11,0.2); border-top: 2px solid #e5e7eb; }
        .dark .seo-page-table tfoot td { border-color: #4b5563; color: #fbbf24; }
        .seo-page-table .seo-page-num { text-align: right; font-variant-numeric: tabular-nums; }
    </style>
    <div style="display: flex; flex-direction: column; gap: 2rem;">
        {{-- URL Sitemap --}}
        <x-filament::section>
            <x-slot name="heading">URL Sitemap</x-slot>
            <p style="margin-bottom: 0.75rem; font-size: 0.875rem; color: #6b7280;">Ссылка на sitemap для поисковиков и файла robots.txt:</p>
            <div class="seo-page-url-box">
                <a href="{{ $sitemapUrl }}" target="_blank" rel="noopener" style=" color: #d97706; font-family: ui-monospace, monospace; font-size: 0.875rem; word-break: break-all; font-weight: 500; text-decoration: none;">
                    {{ $sitemapUrl }}
                </a>
            </div>
        </x-filament::section>

        {{-- Кеширование --}}
        <x-filament::section>
            <x-slot name="heading">Кеширование sitemap</x-slot>
            <p style="margin-bottom: 1rem; font-size: 0.875rem; color: #6b7280;">Sitemap кешируется на 24 часа. После обновления контента нажмите «Обновить кеш», чтобы поисковики получили актуальный список страниц.</p>
            <div class="seo-page-cache-badge">
                <span style="font-size: 0.875rem; color: #6b7280;">Последнее обновление:</span>
                <span style="font-weight: 600; {{ $cachedAt ? 'color: #059669;' : 'color: #d97706;' }}">{{ $cachedAt ?? 'Не кешировано' }}</span>
            </div>
        </x-filament::section>

        {{-- Таблица --}}
        <x-filament::section>
            <x-slot name="heading">Количество URL по типам</x-slot>
            <p style="margin-bottom: 1rem; font-size: 0.875rem; color: #6b7280;">Сколько страниц каждого типа включено в sitemap (активные записи).</p>
            <div class="seo-page-table-wrap">
                <table class="seo-page-table">
                    <thead>
                        <tr>
                            <th>Тип страницы</th>
                            <th style="text-align: right; width: 120px;">Количество</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($counts as $row)
                            <tr>
                                <td>{{ $row['type'] }}</td>
                                <td class="seo-page-num">{{ number_format($row['count']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Всего URL в sitemap</td>
                            <td class="seo-page-num">{{ number_format($totalCount) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
