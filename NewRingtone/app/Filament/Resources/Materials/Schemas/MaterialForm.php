<?php

namespace App\Filament\Resources\Materials\Schemas;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use getID3;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MaterialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основное')->schema([
                    TextInput::make('name')
                        ->label('Name')
                        ->unique()
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
                        ->disk('materials') // storage/app/public/materials
                        ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                            $fileName = Str::uuid() . '.webp';
                            $path = Storage::disk('materials')->path($fileName);
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

                    RichEditor::make('copyright')
                        ->label('Copyright')
                        ->extraAttributes(['style' => 'min-height: 200px;'])
                        ->columnSpanFull(),

                    TinyEditor::make('content')
                        ->label('Content')
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsVisibility('public')
                        ->fileAttachmentsDirectory('uploads')
                        ->minHeight(500)
                        ->profile('full')
                        ->language('ru')
                        ->setConvertUrls(false)
                        ->showMenuBar()
                        ->live()
                        ->columnSpanFull(),
                ]),

                Section::make('Связи')->schema([

                    Select::make('type_id')
                        ->label('Type')
                        ->relationship(
                            name: 'type',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn ($query, $record) => $query->where('status', true)
                                ->orWhere('id', $record?->type_id)
                        )
                        ->required()
                        ->preload()
                        ->searchable()
                        ->columnSpanFull(),

                    Select::make('authors')
                        ->label('Authors')
                        ->multiple()
                        ->relationship('authors', 'name')
                        ->preload()
                        ->searchable()
                        ->columnSpanFull(),

                    Select::make('categories')
                        ->label('Categories')
                        ->multiple()
                        ->relationship('categories', 'name')
                        ->preload()
                        ->searchable()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label('Name')
                                ->required(),

                            TextInput::make('title')
                                ->label('Title')
                                ->required(),

                            Textarea::make('description')
                                ->label('Description'),
                        ])
                        ->createOptionUsing(function (array $data) {
                            // Функция для преобразования первой буквы в заглавную (работает с русским и английским)
                            $ucfirst = function($string) {
                                if (empty($string)) return $string;
                                return mb_strtoupper(mb_substr($string, 0, 1, 'UTF-8'), 'UTF-8') . mb_strtolower(mb_substr($string, 1, null, 'UTF-8'), 'UTF-8');
                            };
                            
                            return \App\Models\Category::create([
                                'name' => $ucfirst(trim($data['name'])),
                                'slug' => Str::slug($data['name']),
                                'title' => $ucfirst(trim($data['title'])),
                                'h1' => $ucfirst(trim($data['title'])), // H1 = Title
                                'description' => $data['description'] ?? null,
                                'content' => null,
                                'status' => true, // Всегда активная
                            ])->id;
                        })
                        ->createOptionAction(
                            fn(Action $action) => $action->label('Create Category')
                        )
                        ->columnSpanFull(),
                    Select::make('tags')
                        ->label('Tags')
                        ->multiple()
                        ->relationship('tags', 'name')
                        ->preload()
                        ->searchable()
                        ->allowHtml() // если понадобится
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label('Name')
                                ->required(),

                            TextInput::make('title')
                                ->label('Title')
                                ->required(),

                            Textarea::make('description')
                                ->label('Description'),

                            Toggle::make('status')
                                ->label('Active')
                                ->default(true),
                        ])
                        ->createOptionUsing(function (array $data) {
                            // Функция для преобразования первой буквы в заглавную (работает с русским и английским)
                            $ucfirst = function($string) {
                                if (empty($string)) return $string;
                                return mb_strtoupper(mb_substr($string, 0, 1, 'UTF-8'), 'UTF-8') . mb_strtolower(mb_substr($string, 1, null, 'UTF-8'), 'UTF-8');
                            };
                            
                            return \App\Models\Tag::create([
                                'name' => $ucfirst(trim($data['name'])),
                                'slug' => Str::slug($data['name']),
                                'title' => $ucfirst(trim($data['title'])),
                                'description' => $data['description'] ?? null,
                                'content' => null,
                                'status' => $data['status'] ?? true,
                            ])->id;
                        })
                        ->createOptionAction(
                            fn(Action $action) => $action->label('Create Tag')
                        )
                        ->columnSpanFull()
                ])->columns(2),

                Section::make('Аудио файлы')->schema([

                    FileUpload::make('mp4')
                        ->label('MP4 File')
                        ->disk('mp4')
                        ->acceptedFileTypes([
                            'audio/mp4',
                            'audio/mpeg',
                            'audio/x-m4a',
                            'audio/mp3',
                            'audio/x-mp3',
                        ])
                        ->saveUploadedFileUsing(function (TemporaryUploadedFile $file) {
                            $filename = Str::uuid() . '.' . strtolower($file->getClientOriginalExtension());
                            $file->storeAs('', $filename, 'mp4');
                            return $filename;
                        })
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (!$state instanceof TemporaryUploadedFile) {
                                return;
                            }

                            // Временный путь, пока файл не сохранён на диск
                            $tmpPath = $state->getRealPath();

                            if (!$tmpPath || !file_exists($tmpPath)) {
                                return;
                            }

                            // Анализируем аудио прямо из временного файла
                            $analyzer = new getID3();
                            $info = $analyzer->analyze($tmpPath);

                            $bitrate = $info['bitrate'] ?? null;
                            $duration = $info['playtime_seconds'] ?? null;

                            if ($bitrate !== null) {
                                $set('mp4_bitrate', (int)round($bitrate / 1000)); // kbps
                            }

                            if ($duration !== null) {
                                $set('mp4_duration', (int)round($duration)); // сек
                            }
                        })
                        ->columnSpan(1),

                    TextInput::make('mp4_bitrate')
                        ->label('MP4 bitrate, kbps')
                        ->live()
                        ->numeric(),
                    TextInput::make('mp4_duration')
                        ->label('MP4 duration, sec')
                        ->live()
                        ->numeric(),

                    FileUpload::make('m4r30')
                        ->label('M4R 30s File')
                        ->disk('m4r30')
                        ->acceptedFileTypes(['audio/mp4', 'audio/mpeg', 'audio/x-m4a'])
                        ->saveUploadedFileUsing(function (TemporaryUploadedFile $file) {
                            $filename = Str::uuid() . '.' . strtolower($file->getClientOriginalExtension());
                            $file->storeAs('', $filename, 'm4r30');
                            return $filename;
                        })
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (!$state instanceof TemporaryUploadedFile) {
                                return;
                            }

                            // Временный путь, пока файл не сохранён на диск
                            $tmpPath = $state->getRealPath();

                            if (!$tmpPath || !file_exists($tmpPath)) {
                                return;
                            }

                            // Анализируем аудио прямо из временного файла
                            $analyzer = new getID3();
                            $info = $analyzer->analyze($tmpPath);

                            $bitrate = $info['bitrate'] ?? null;
                            $duration = $info['playtime_seconds'] ?? null;

                            if ($bitrate !== null) {
                                $set('m4r30_bitrate', (int)round($bitrate / 1000)); // kbps
                            }

                            if ($duration !== null) {
                                $set('m4r30_duration', (int)round($duration)); // сек
                            }
                        })
                        ->columnSpan(1),
                    TextInput::make('m4r30_bitrate')
                        ->label('M4R30 bitrate, kbps')
                        ->numeric(),
                    TextInput::make('m4r30_duration')
                        ->label('M4R30 duration, sec')
                        ->numeric(),

                    FileUpload::make('m4r40')
                        ->label('M4R 40s File')
                        ->disk('m4r40')
                        ->acceptedFileTypes(['audio/mp4', 'audio/mpeg', 'audio/x-m4a'])
                        ->saveUploadedFileUsing(function (TemporaryUploadedFile $file) {
                            $filename = Str::uuid() . '.' . strtolower($file->getClientOriginalExtension());
                            $file->storeAs('', $filename, 'm4r40');
                            return $filename;
                        })
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (!$state instanceof TemporaryUploadedFile) {
                                return;
                            }

                            // Временный путь, пока файл не сохранён на диск
                            $tmpPath = $state->getRealPath();

                            if (!$tmpPath || !file_exists($tmpPath)) {
                                return;
                            }

                            // Анализируем аудио прямо из временного файла
                            $analyzer = new getID3();
                            $info = $analyzer->analyze($tmpPath);

                            $bitrate = $info['bitrate'] ?? null;
                            $duration = $info['playtime_seconds'] ?? null;

                            if ($bitrate !== null) {
                                $set('m4r40_bitrate', (int)round($bitrate / 1000)); // kbps
                            }

                            if ($duration !== null) {
                                $set('m4r40_duration', (int)round($duration)); // сек
                            }
                        })
                        ->columnSpan(1),
                    TextInput::make('m4r40_bitrate')
                        ->label('M4R40 bitrate, kbps')
                        ->numeric(),
                    TextInput::make('m4r40_duration')
                        ->label('M4R40 duration, sec')
                        ->numeric(),
                ])->columns(3),

                Section::make('Метрики и статус')->schema([
                    TextInput::make('views')
                        ->numeric()
                        ->default(0),
                    TextInput::make('likes')
                        ->numeric()
                        ->default(0),
                    TextInput::make('downloads')
                        ->numeric()
                        ->default(0),
                    Toggle::make('status')
                        ->default(true)
                        ->label('Активен'),
                    \Filament\Forms\Components\Select::make('moderation_status')
                        ->label('Статус модерации')
                        ->options([
                            'pending' => 'На модерации',
                            'approved' => 'Одобрено',
                            'rejected' => 'Отклонено',
                        ])
                        ->nullable(),
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->label('Причина отклонения')
                        ->rows(3)
                        ->visible(fn ($get) => $get('moderation_status') === 'rejected'),
                ])->columns(3),
            ])
            ->columns(1);
    }
}
