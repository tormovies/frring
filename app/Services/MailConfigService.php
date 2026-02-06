<?php

namespace App\Services;

use App\Models\MailSetting;
use Illuminate\Support\Facades\Cache;

class MailConfigService
{
    /**
     * Получить настройки почты из базы данных
     * Используется кеширование для оптимизации
     */
    public static function getSettings(): array
    {
        return Cache::remember('mail_settings', 3600, function () {
            try {
                $settings = MailSetting::instance();
                
                return [
                    'mailer' => $settings->mailer ?? env('MAIL_MAILER', 'log'),
                    'host' => $settings->host ?? env('MAIL_HOST', '127.0.0.1'),
                    'port' => $settings->port ?? (int)env('MAIL_PORT', 2525),
                    'encryption' => $settings->encryption ?? env('MAIL_ENCRYPTION'),
                    'username' => $settings->username ?? env('MAIL_USERNAME'),
                    'password' => $settings->password ?? env('MAIL_PASSWORD'),
                    'from_address' => $settings->from_address ?? env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                    'from_name' => $settings->from_name ?? env('MAIL_FROM_NAME', 'Example'),
                ];
            } catch (\Exception $e) {
                // Если таблица еще не создана или ошибка, используем значения из .env
                return [
                    'mailer' => env('MAIL_MAILER', 'log'),
                    'host' => env('MAIL_HOST', '127.0.0.1'),
                    'port' => (int)env('MAIL_PORT', 2525),
                    'encryption' => env('MAIL_ENCRYPTION'),
                    'username' => env('MAIL_USERNAME'),
                    'password' => env('MAIL_PASSWORD'),
                    'from_address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                    'from_name' => env('MAIL_FROM_NAME', 'Example'),
                ];
            }
        });
    }

    /**
     * Очистить кеш настроек почты
     */
    public static function clearCache(): void
    {
        Cache::forget('mail_settings');
    }
}
