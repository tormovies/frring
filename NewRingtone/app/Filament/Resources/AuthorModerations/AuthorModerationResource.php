<?php

namespace App\Filament\Resources\AuthorModerations;

use App\Filament\Resources\AuthorModerations\Pages\ListAuthorModerations;
use App\Filament\Resources\AuthorModerations\Pages\ViewAuthorModeration;
use App\Filament\Resources\AuthorModerations\Schemas\AuthorModerationForm;
use App\Filament\Resources\AuthorModerations\Tables\AuthorModerationsTable;
use App\Models\AuthorModeration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AuthorModerationResource extends Resource
{
    protected static ?string $model = AuthorModeration::class;

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?string $navigationLabel = 'Модерация изменений авторов';

    public static function getNavigationGroup(): ?string
    {
        return 'Модерация';
    }

    protected static ?string $recordTitleAttribute = 'id';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['author', 'user']);
    }

    public static function form(Schema $schema): Schema
    {
        return AuthorModerationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuthorModerationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuthorModerations::route('/'),
            'view' => ViewAuthorModeration::route('/{record}'),
        ];
    }
}
