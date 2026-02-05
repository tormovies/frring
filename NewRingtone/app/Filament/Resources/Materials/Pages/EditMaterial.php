<?php

namespace App\Filament\Resources\Materials\Pages;

use App\Filament\Resources\Materials\MaterialResource;
use App\Mail\MaterialApproved;
use App\Mail\MaterialRejected;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditMaterial extends EditRecord
{
    protected static string $resource = MaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Одобрить')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn () => $this->record->moderation_status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Одобрить материал')
                ->modalDescription('Материал будет одобрен и станет активным. Пользователь получит уведомление на email.')
                ->action(function () {
                    $material = $this->record;
                    
                    $material->update([
                        'moderation_status' => 'approved',
                        'status' => true, // Делаем активным
                        'rejection_reason' => null,
                    ]);
                    
                    // Отправляем уведомление пользователю
                    if ($material->user) {
                        Mail::to($material->user->email)->send(new MaterialApproved($material));
                    }
                    
                    Notification::make()
                        ->success()
                        ->title('Материал одобрен')
                        ->body('Материал активирован. Уведомление отправлено пользователю.')
                        ->send();
                }),
                
            Action::make('reject')
                ->label('Отклонить')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(fn () => $this->record->moderation_status === 'pending')
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->label('Причина отклонения')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $material = $this->record;
                    
                    $material->update([
                        'moderation_status' => 'rejected',
                        'status' => false, // Делаем неактивным
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                    
                    // Отправляем уведомление пользователю
                    if ($material->user) {
                        Mail::to($material->user->email)->send(new MaterialRejected($material, $data['rejection_reason']));
                    }
                    
                    Notification::make()
                        ->success()
                        ->title('Материал отклонен')
                        ->body('Уведомление отправлено пользователю.')
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
