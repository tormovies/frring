<?php

namespace App\Filament\Pages;

use App\Models\MailSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class MailSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected string $view = 'filament.pages.mail-settings';

    protected static ?string $navigationLabel = 'Настройки почты';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 99;

    public ?array $data = [];

    public function mount(): void
    {
        try {
            $settings = MailSetting::instance();
            $this->form->fill([
                'mailer' => $settings->mailer ?? env('MAIL_MAILER', 'log'),
                'host' => $settings->host ?? env('MAIL_HOST', ''),
                'port' => $settings->port ?? (int)env('MAIL_PORT', 2525),
                'encryption' => $settings->encryption ?? env('MAIL_ENCRYPTION'),
                'username' => $settings->username ?? env('MAIL_USERNAME', ''),
                'password' => $settings->password ?? env('MAIL_PASSWORD', ''),
                'from_address' => $settings->from_address ?? env('MAIL_FROM_ADDRESS', ''),
                'from_name' => $settings->from_name ?? env('MAIL_FROM_NAME', ''),
            ]);
        } catch (\Exception $e) {
            // Если таблица не существует, используем значения из .env
            $this->form->fill([
                'mailer' => env('MAIL_MAILER', 'log'),
                'host' => env('MAIL_HOST', ''),
                'port' => (int)env('MAIL_PORT', 2525),
                'encryption' => env('MAIL_ENCRYPTION'),
                'username' => env('MAIL_USERNAME', ''),
                'password' => env('MAIL_PASSWORD', ''),
                'from_address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'from_name' => env('MAIL_FROM_NAME', 'Example'),
            ]);
        }
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('mailer')
                ->label('Драйвер почты')
                ->options([
                    'smtp' => 'SMTP',
                    'log' => 'Лог (для разработки)',
                    'sendmail' => 'Sendmail',
                ])
                ->default('smtp')
                ->required()
                ->reactive()
                ->columnSpanFull(),

            TextInput::make('host')
                ->label('SMTP сервер')
                ->placeholder('smtp.example.com')
                ->visible(fn ($get) => $get('mailer') === 'smtp')
                ->required(fn ($get) => $get('mailer') === 'smtp'),

            TextInput::make('port')
                ->label('Порт')
                ->numeric()
                ->default(2525)
                ->visible(fn ($get) => $get('mailer') === 'smtp')
                ->required(fn ($get) => $get('mailer') === 'smtp'),

            Select::make('encryption')
                ->label('Шифрование')
                ->options([
                    null => 'Без шифрования',
                    'ssl' => 'SSL',
                    'tls' => 'TLS',
                ])
                ->visible(fn ($get) => $get('mailer') === 'smtp'),

            TextInput::make('username')
                ->label('Логин (Email)')
                ->email()
                ->placeholder('admin@example.com')
                ->visible(fn ($get) => $get('mailer') === 'smtp')
                ->required(fn ($get) => $get('mailer') === 'smtp'),

            TextInput::make('password')
                ->label('Пароль')
                ->password()
                ->visible(fn ($get) => $get('mailer') === 'smtp')
                ->required(fn ($get) => $get('mailer') === 'smtp')
                ->dehydrated(true),

            TextInput::make('from_address')
                ->label('Email отправителя')
                ->email()
                ->required()
                ->placeholder('noreply@example.com'),

            TextInput::make('from_name')
                ->label('Имя отправителя')
                ->required()
                ->placeholder('НейроЗвук'),
        ];
    }
    
    protected function getFormColumns(): int | array
    {
        return 2;
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            
            $settings = MailSetting::instance();
            
            // Проверяем, существует ли запись в БД (если таблица существует)
            if ($settings->exists) {
                $settings->update($data);
            } else {
                // Если таблицы нет, просто показываем сообщение об ошибке
                Notification::make()
                    ->warning()
                    ->title('Таблица не найдена')
                    ->body('Таблица mail_settings не существует. Пожалуйста, выполните миграцию: php artisan migrate')
                    ->send();
                return;
            }

            // Очищаем кеш настроек почты
            \App\Services\MailConfigService::clearCache();
            
            // Очищаем кеш конфигурации
            \Artisan::call('config:clear');

            Notification::make()
                ->success()
                ->title('Настройки сохранены')
                ->body('Настройки почты успешно обновлены.')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Ошибка сохранения')
                ->body('Не удалось сохранить настройки. Убедитесь, что миграция выполнена: php artisan migrate')
                ->send();
        }
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
}
