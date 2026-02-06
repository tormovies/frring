<?php

namespace App\Filament\Pages;

use App\Services\SitemapService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SeoPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected string $view = 'filament.pages.seo-page';

    protected static ?string $navigationLabel = 'Sitemap';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 98;

    protected static ?string $title = 'Sitemap';

    protected static ?string $slug = 'sitemap';

    public string $sitemapUrl = '';
    public array $counts = [];
    public ?string $cachedAt = null;
    public int $totalCount = 0;

    public function mount(SitemapService $sitemap): void
    {
        $this->sitemapUrl = url('/sitemap.xml');
        $this->counts = $this->getCountsForDisplay($sitemap->getCounts());
        $this->totalCount = $sitemap->getTotalCount();
        $at = $sitemap->getCachedAt();
        $this->cachedAt = $at ? $at->format('d.m.Y H:i:s') : null;
    }

    protected function getCountsForDisplay(array $raw): array
    {
        $labels = [
            'main' => 'Главная',
            'static' => 'Статические страницы (Популярные, Лучшие, Статьи)',
            'materials' => 'Материалы (аудио)',
            'categories' => 'Категории',
            'tags' => 'Теги',
            'articles' => 'Статьи',
            'pages' => 'Страницы',
        ];
        $result = [];
        foreach ($raw as $key => $value) {
            $result[] = [
                'type' => $labels[$key] ?? $key,
                'count' => $value,
            ];
        }
        return $result;
    }

    public function refreshSitemapCache(): void
    {
        $sitemap = app(SitemapService::class);
        $sitemap->refreshCache();
        $this->cachedAt = now()->format('d.m.Y H:i:s');
        $this->counts = $this->getCountsForDisplay($sitemap->getCounts());
        $this->totalCount = $sitemap->getTotalCount();

        Notification::make()
            ->success()
            ->title('Кеш sitemap обновлён')
            ->body('Sitemap пересобран и закеширован.')
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refreshCache')
                ->label('Обновить кеш sitemap')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action(fn () => $this->refreshSitemapCache()),
        ];
    }
}
