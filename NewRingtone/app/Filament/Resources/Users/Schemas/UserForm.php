<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at')
                    ->label('Email подтвержден (дата/время)')
                    ->helperText('Установите дату, чтобы подтвердить email вручную. Или используйте кнопку "Подтвердить Email" ниже.')
                    ->displayFormat('d.m.Y H:i')
                    ->seconds(false),
                Select::make('status')
                    ->label('Статус')
                    ->options([
                        'not_verified' => 'Не подтвержден',
                        'email_verified' => 'Email подтвержден',
                        'active' => 'Активен',
                        'blocked' => 'Заблокирован',
                        'inactive' => 'Неактивен',
                    ])
                    ->required()
                    ->default('not_verified')
                    ->helperText('При подтверждении email статус автоматически изменится на "Email подтвержден"'),
                TextInput::make('password')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn ($operation) => $operation === 'create'),
            ]);
    }
}
