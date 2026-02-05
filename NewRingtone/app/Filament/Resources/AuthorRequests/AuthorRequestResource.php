<?php

namespace App\Filament\Resources\AuthorRequests;

use App\Filament\Resources\AuthorRequests\Pages\ListAuthorRequests;
use App\Filament\Resources\AuthorRequests\Pages\ViewAuthorRequest;
use App\Filament\Resources\AuthorRequests\Schemas\AuthorRequestForm;
use App\Filament\Resources\AuthorRequests\Tables\AuthorRequestsTable;
use App\Models\AuthorRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AuthorRequestResource extends Resource
{
    protected static ?string $model = AuthorRequest::class;

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Запросы на авторов';

    public static function getNavigationGroup(): ?string
    {
        return 'Модерация';
    }

    protected static ?string $recordTitleAttribute = 'author_name';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['user', 'author']);
    }

    public static function form(Schema $schema): Schema
    {
        return AuthorRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuthorRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuthorRequests::route('/'),
            'view' => ViewAuthorRequest::route('/{record}'),
        ];
    }
}
