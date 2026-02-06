<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Material;
use App\Models\Tag;
use App\Models\Type;
use App\Observers\ArticleObserver;
use App\Observers\AuthorObserver;
use App\Observers\CategoryObserver;
use App\Observers\MaterialObserver;
use App\Observers\TagObserver;
use App\Observers\TypeObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Author::observe(AuthorObserver::class);
        Category::observe(CategoryObserver::class);
        Tag::observe(TagObserver::class);
        Type::observe(TypeObserver::class);
        Article::observe(ArticleObserver::class);
        Material::observe(MaterialObserver::class);
        
        // Переопределяем настройки почты из базы данных (если они есть)
        try {
            $mailSettings = \App\Services\MailConfigService::getSettings();
            
            if (!empty($mailSettings)) {
                config([
                    'mail.default' => $mailSettings['mailer'] ?? config('mail.default'),
                    'mail.mailers.smtp.host' => $mailSettings['host'] ?? config('mail.mailers.smtp.host'),
                    'mail.mailers.smtp.port' => $mailSettings['port'] ?? config('mail.mailers.smtp.port'),
                    'mail.mailers.smtp.encryption' => $mailSettings['encryption'] ?? config('mail.mailers.smtp.encryption'),
                    'mail.mailers.smtp.username' => $mailSettings['username'] ?? config('mail.mailers.smtp.username'),
                    'mail.mailers.smtp.password' => $mailSettings['password'] ?? config('mail.mailers.smtp.password'),
                    'mail.from.address' => $mailSettings['from_address'] ?? config('mail.from.address'),
                    'mail.from.name' => $mailSettings['from_name'] ?? config('mail.from.name'),
                ]);
            }
        } catch (\Exception $e) {
            // Если таблица еще не создана или ошибка, используем значения из .env
            // Не логируем, чтобы не засорять логи при первой установке
        }
    }
}
