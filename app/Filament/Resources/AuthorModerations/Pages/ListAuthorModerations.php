<?php

namespace App\Filament\Resources\AuthorModerations\Pages;

use App\Filament\Resources\AuthorModerations\AuthorModerationResource;
use Filament\Resources\Pages\ListRecords;

class ListAuthorModerations extends ListRecords
{
    protected static string $resource = AuthorModerationResource::class;
}
