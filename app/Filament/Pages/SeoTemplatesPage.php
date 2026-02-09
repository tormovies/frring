<?php

namespace App\Filament\Pages;

use App\Models\SeoTemplate;
use App\Models\SiteSetting;
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
        $data['head_script'] = SiteSetting::get('head_script') ?? $this->defaultHeadScript();
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
                ->collapsible()
                ->collapsed();
        }

        $components[] = Section::make('Код счётчика')
            ->description('Вставляется в шапку сайта перед </head>. Счётчик Яндекс.Метрики или другой JS/HTML.')
            ->schema([
                Textarea::make('head_script')
                    ->label('Код счётчика / скрипты для <head>')
                    ->rows(12)
                    ->columnSpanFull()
                    ->helperText('HTML и JavaScript. Сохраняется как есть.'),
            ])
            ->collapsible()
            ->collapsed();

        return $components;
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $templateSlugs = ['home', 'search', 'popular', 'best', 'articles_index', 'material', 'category', 'page'];

        foreach ($data as $slug => $row) {
            if ($slug === 'head_script') {
                SiteSetting::set('head_script', is_string($row) ? $row : '');
                continue;
            }
            if (! in_array($slug, $templateSlugs, true) || ! is_array($row)) {
                continue;
            }
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
            ->body('SEO шаблоны и код счётчика обновлены.')
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

    private function defaultHeadScript(): string
    {
        return '<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
    })(window, document,\'script\',\'https://mc.yandex.ru/metrika/tag.js\', \'ym\');
    ym(61077613, \'init\', {webvisor:true, clickmap:true, referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/61077613" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->';
    }
}
