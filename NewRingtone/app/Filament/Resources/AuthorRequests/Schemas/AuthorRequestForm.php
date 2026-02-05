<?php

namespace App\Filament\Resources\AuthorRequests\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AuthorRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Информация о запросе')->schema([
                    TextInput::make('user_name')
                        ->label('Пользователь')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('user_email')
                        ->label('Email')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('author_name')
                        ->label('Имя автора')
                        ->disabled(),
                    TextInput::make('author_card_url')
                        ->label('Ссылка на карточку артиста')
                        ->url(fn ($state) => $state ?? '#')
                        ->disabled(),
                    TextInput::make('existing_author')
                        ->label('Существующий автор (если есть)')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('status')
                        ->label('Статус')
                        ->disabled()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'approved' => 'Разрешено',
                            'pending' => 'Модерация',
                            'rejected' => 'Отказано',
                            default => $state,
                        }),
                    Textarea::make('rejection_reason')
                        ->label('Причина отклонения')
                        ->disabled()
                        ->rows(3)
                        ->visible(fn ($get) => $get('status') === 'rejected' && !empty($get('rejection_reason'))),
                ])->columns(2),
            ]);
    }
}
