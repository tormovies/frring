<?php

namespace App\Filament\Resources\AuthorModerations\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuthorModerationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('author.name')
                    ->label('Автор')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Пользователь')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('field_name')
                    ->label('Поле')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'img' => 'Фото',
                        'title' => 'SEO Title',
                        'description' => 'SEO Description',
                        'h1' => 'H1',
                        'long_description' => 'Описание',
                        'content' => 'Контент',
                        default => $state,
                    }),
                TextColumn::make('old_value')
                    ->label('Старое значение')
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('new_value')
                    ->label('Новое значение')
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'approved' => 'Разрешено',
                        'pending' => 'Модерация',
                        'rejected' => 'Отказано',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('rejection_reason')
                    ->label('Причина отклонения')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'Модерация',
                        'approved' => 'Разрешено',
                        'rejected' => 'Отказано',
                    ])
                    ->default('pending'),
                SelectFilter::make('field_name')
                    ->label('Поле')
                    ->options([
                        'img' => 'Фото',
                        'title' => 'SEO Title',
                        'description' => 'SEO Description',
                        'h1' => 'H1',
                        'long_description' => 'Описание',
                        'content' => 'Контент',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
