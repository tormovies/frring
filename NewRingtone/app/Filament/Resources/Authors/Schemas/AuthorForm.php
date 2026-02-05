<?php

namespace App\Filament\Resources\Authors\Schemas;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AuthorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основное')->schema([
                    TextInput::make('name')
                        ->placeholder('Author Name')
                        ->required()
                        ->columnSpan(1),

                    TextInput::make('slug')
                        ->label('Slug')
                        ->unique()
                        ->suffixAction(
                            Action::make('generateSlug')
                                ->label('Generate')
                                ->icon('heroicon-m-arrow-path')
                                ->action(function (callable $get, callable $set) {
                                    $name = $get('name');

                                    if (!$name) {
                                        return;
                                    }

                                    $set('slug', Str::slug($name));
                                })
                        )
                        ->columnSpan(1),

                    FileUpload::make('img')
                        ->label('Photo')
                        ->image()
                        ->imageEditor()
                        ->previewable()
                        ->openable()
                        ->disk('authors') // storage/app/public/authors
                        ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                            $fileName = Str::uuid() . '.webp';
                            $path = Storage::disk('authors')->path($fileName);
                            Image::read($file->getRealPath())->toWebp()->save($path, 90);
                            return $fileName;
                        })
                        ->columnSpanFull(),

                ])->columns(2),

                Section::make('SEO')->schema([
                    TextInput::make('title')
                        ->label('Title')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label('Description')
                        ->maxLength(500)
                        ->rows(3)
                        ->placeholder('Short SEO description (up to 500 characters)')
                        ->required()
                        ->columnSpanFull(),
                ]),

                Section::make('Content')->schema([

                    TextInput::make('h1')
                        ->label('H1')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    RichEditor::make('long_description')
                        ->label('Long Description')
                        ->extraAttributes(['style' => 'min-height: 200px;'])
                        ->columnSpanFull(),

                    TinyEditor::make('content')
                        ->label('Content')
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsVisibility('public')
                        ->fileAttachmentsDirectory('uploads')
                        ->profile('full')
                        ->resize('both')
                        ->columnSpanFull()
                        ->nullable(),

                    Toggle::make('status')
                        ->default(true)
                        ->columnSpanFull(),
                ]),
            ])
            ->columns(1);
    }
}
