<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verify_email')
                ->label('Подтвердить Email')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Подтвердить Email пользователя')
                ->modalDescription('Это действие установит дату подтверждения email и изменит статус на "Email подтвержден".')
                ->action(function () {
                    $this->record->email_verified_at = now();
                    if ($this->record->status === 'not_verified') {
                        $this->record->status = 'email_verified';
                    }
                    $this->record->save();

                    Notification::make()
                        ->title('Email подтвержден')
                        ->success()
                        ->body('Email пользователя успешно подтвержден. Статус обновлен.')
                        ->send();
                    
                    $this->fillForm();
                })
                ->visible(fn () => is_null($this->record->email_verified_at)),
            Action::make('unverify_email')
                ->label('Отменить подтверждение Email')
                ->icon('heroicon-o-x-circle')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Отменить подтверждение Email')
                ->modalDescription('Это действие отменит подтверждение email пользователя.')
                ->action(function () {
                    $this->record->email_verified_at = null;
                    if ($this->record->status === 'email_verified') {
                        $this->record->status = 'not_verified';
                    }
                    $this->record->save();

                    Notification::make()
                        ->title('Подтверждение Email отменено')
                        ->warning()
                        ->body('Подтверждение email пользователя отменено.')
                        ->send();
                    
                    $this->fillForm();
                })
                ->visible(fn () => !is_null($this->record->email_verified_at)),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Автоматически обновляем статус при установке email_verified_at
        if (!empty($data['email_verified_at']) && $data['status'] === 'not_verified') {
            $data['status'] = 'email_verified';
        }
        
        // Автоматически сбрасываем статус при снятии email_verified_at
        if (empty($data['email_verified_at']) && $data['status'] === 'email_verified') {
            $data['status'] = 'not_verified';
        }

        return $data;
    }
}
