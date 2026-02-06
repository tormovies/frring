<?php

namespace App\Filament\Resources\AuthorModerations\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class AuthorModerationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Информация об изменении')->schema([
                    TextInput::make('author_name')
                        ->label('Автор')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('user_name')
                        ->label('Пользователь')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('user_email')
                        ->label('Email')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('field_name')
                        ->label('Изменяемое поле')
                        ->disabled()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'img' => 'Фото',
                            'title' => 'SEO Title',
                            'description' => 'SEO Description',
                            'h1' => 'H1',
                            'long_description' => 'Описание',
                            'content' => 'Контент',
                            default => $state,
                        }),
                    TextInput::make('status')
                        ->label('Статус')
                        ->disabled()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'approved' => 'Разрешено',
                            'pending' => 'Модерация',
                            'rejected' => 'Отказано',
                            default => $state,
                        }),
                ]),
                
                Section::make('Сравнение изменений')
                    ->visible(fn ($record) => $record && $record->field_name !== 'img' && $record->field_name !== 'content')
                    ->schema([
                        Textarea::make('old_value')
                            ->label('Старое значение')
                            ->disabled()
                            ->rows(5),
                        Textarea::make('new_value')
                            ->label('Новое значение')
                            ->disabled()
                            ->rows(5),
                    ])->columns(2),
                
                Section::make('Сравнение контента')
                    ->visible(fn ($record) => $record && $record->field_name === 'content')
                    ->schema([
                        ViewField::make('old_content')
                            ->label('Старое значение')
                            ->view('filament.components.quill-content-preview')
                            ->viewData(function ($record) {
                                static $oldCounter = 0;
                                $oldCounter++;
                                return [
                                    'content' => $record?->old_value ?? '',
                                    'uniqueId' => 'old-content-' . ($record?->id ?? '0') . '-' . $oldCounter
                                ];
                            })
                            ->dehydrated(false),
                        ViewField::make('new_content')
                            ->label('Новое значение')
                            ->view('filament.components.quill-content-preview')
                            ->viewData(function ($record) {
                                static $newCounter = 0;
                                $newCounter++;
                                return [
                                    'content' => $record?->new_value ?? '',
                                    'uniqueId' => 'new-content-' . ($record?->id ?? '0') . '-' . $newCounter
                                ];
                            })
                            ->dehydrated(false),
                    ]),
                
                Section::make('Сравнение изображений')
                    ->visible(fn ($record) => $record && $record->field_name === 'img')
                    ->schema([
                        ViewField::make('old_image')
                            ->label('Старое изображение')
                            ->view('filament.components.image-preview')
                            ->viewData(function ($record) {
                                return [
                                    'imagePath' => $record?->old_value ?? null,
                                    'label' => 'Старое изображение'
                                ];
                            })
                            ->dehydrated(false),
                        ViewField::make('new_image')
                            ->label('Новое изображение')
                            ->view('filament.components.image-preview')
                            ->viewData(function ($record) {
                                return [
                                    'imagePath' => $record?->new_value ?? null,
                                    'label' => 'Новое изображение'
                                ];
                            })
                            ->dehydrated(false),
                    ])->columns(2),
                
                Section::make('Дополнительная информация')->schema([
                    Textarea::make('rejection_reason')
                        ->label('Причина отклонения')
                        ->disabled()
                        ->rows(3)
                        ->visible(fn ($get) => $get('status') === 'rejected' && !empty($get('rejection_reason'))),
                ]),
            ]);
    }
}
