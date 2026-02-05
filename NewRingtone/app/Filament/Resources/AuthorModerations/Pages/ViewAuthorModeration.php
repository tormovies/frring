<?php

namespace App\Filament\Resources\AuthorModerations\Pages;

use App\Filament\Resources\AuthorModerations\AuthorModerationResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewAuthorModeration extends ViewRecord
{
    protected static string $resource = AuthorModerationResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['author_name'] = $this->record->author->name ?? 'Не указан';
        $data['user_name'] = $this->record->user->name ?? 'Не указан';
        $data['user_email'] = $this->record->user->email ?? 'Не указан';
        
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Одобрить')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn () => $this->record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Одобрить изменение')
                ->modalDescription('Изменение будет применено к автору.')
                ->action(function () {
                    $moderation = $this->record;
                    $author = $moderation->author;
                    
                    // Применяем изменение к автору
                    $fieldName = $moderation->field_name;
                    $newValue = $moderation->new_value;
                    $oldValue = $moderation->old_value;
                    
                    // Если это изображение и старое изображение существует и отличается, удаляем старое
                    if ($fieldName === 'img' && !empty($oldValue) && $oldValue !== $newValue) {
                        if (Storage::disk('authors')->exists($oldValue)) {
                            Storage::disk('authors')->delete($oldValue);
                        }
                    }
                    
                    // Применяем изменение
                    $author->update([$fieldName => $newValue]);
                    
                    // Обновляем статус модерации
                    $moderation->update(['status' => 'approved']);
                    
                    Notification::make()
                        ->success()
                        ->title('Изменение одобрено')
                        ->body('Изменение применено к автору.')
                        ->send();
                    
                    return redirect(static::getResource()::getUrl('index'));
                }),
                
            Action::make('reject')
                ->label('Отклонить')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(fn () => $this->record->status === 'pending')
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->label('Причина отклонения')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $moderation = $this->record;
                    
                    $moderation->update([
                        'status' => 'rejected',
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                    
                    // Если это было изображение, удаляем загруженный файл
                    if ($moderation->field_name === 'img' && $moderation->new_value) {
                        $newImagePath = $moderation->new_value;
                        // Проверяем, что это новое изображение (не текущее изображение автора)
                        $author = $moderation->author;
                        if ($author->img !== $newImagePath && Storage::disk('authors')->exists($newImagePath)) {
                            Storage::disk('authors')->delete($newImagePath);
                        }
                    }
                    
                    Notification::make()
                        ->success()
                        ->title('Изменение отклонено')
                        ->body('Загруженный файл удален (если это было изображение).')
                        ->send();
                    
                    return redirect(static::getResource()::getUrl('index'));
                }),
        ];
    }
}
