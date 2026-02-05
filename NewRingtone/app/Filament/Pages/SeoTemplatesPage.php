<?php

namespace App\Filament\Pages;

use App\Models\SeoTemplate;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SeoTemplatesPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'SEO шаблоны';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 97;

    protected static ?string $title = 'SEO шаблоны разделов';

    protected static ?string $slug = 'seo-templates';

    protected string $view = 'filament.pages.seo-templates';

    public ?array $data = [];

    public function mount(): void
    {
        $templates = SeoTemplate::orderBy('slug')->get()->keyBy('slug');
        $data = [];
        foreach (['home', 'search', 'popular', 'best', 'articles_index', 'material', 'category', 'page'] as $slug) {
            $t = $templates->get($slug);
            $data[$slug] = [
                'title' => $t->title ?? '',
                'description' => $t->description ?? '',
                'h1' => $t->h1 ?? '',
            ];
        }
        $this->form->fill($data);
    }

    protected function getFormSchema(): array
    {
        $slugs = [
            'home' => 'Главная',
            'search' => 'Поиск (подстановка %query% — запрос пользователя)',
            'popular' => 'Популярные рингтоны',
            'best' => 'Лучшие / хиты',
            'articles_index' => 'Список статей',
            'material' => 'Страница материала (%item_name_lower% = как на старом сайте; %item_name%, %author%, %category%, %year%, %site_name%)',
            'category' => 'Страница категории (подстановки: %cat_name%, %year%, %site_name%)',
            'page' => 'Статическая страница (подстановки: %page_name%, %year%, %site_name%)',
        ];

        $components = [];
        foreach ($slugs as $slug => $label) {
            $components[] = Section::make($label)
                ->schema([
                    TextInput::make("{$slug}.title")
                        ->label('Title')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Textarea::make("{$slug}.description")
                        ->label('Description')
                        ->maxLength(500)
                        ->rows(2)
                        ->columnSpanFull(),
                    TextInput::make("{$slug}.h1")
                        ->label('H1 (если пусто — не выводится)')
                        ->maxLength(255)
                        ->columnSpanFull(),
                ])
                ->collapsible();
        }

        return $components;
    }

    public function save(): void
    {
        $data = $this->form->getState();
        foreach ($data as $slug => $row) {
            SeoTemplate::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $this->slugName($slug),
                    'title' => $row['title'] ?? null,
                    'description' => $row['description'] ?? null,
                    'h1' => $row['h1'] ?? null,
                ]
            );
        }

        Notification::make()
            ->success()
            ->title('Сохранено')
            ->body('SEO шаблоны разделов обновлены.')
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Сохранить')
                ->submit('save')
                ->color('primary'),
        ];
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    private function slugName(string $slug): string
    {
        return match ($slug) {
            'home' => 'Главная',
            'search' => 'Поиск',
            'popular' => 'Популярные',
            'best' => 'Лучшие (хиты)',
            'articles_index' => 'Раздел статей',
            'material' => 'Страница материала (рингтон)',
            'category' => 'Страница категории',
            'page' => 'Статическая страница',
            default => $slug,
        };
    }
}
