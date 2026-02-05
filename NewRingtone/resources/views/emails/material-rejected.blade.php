<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Материал отклонен</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #f44336;">Материал отклонен</h2>
        
        <p>Здравствуйте, {{ $material->user->name ?? 'Пользователь' }}!</p>
        
        <p>К сожалению, ваш материал <strong>{{ $material->name }}</strong> был отклонен администратором.</p>
        
        @if($rejectionReason)
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
                <strong>Причина отклонения:</strong><br>
                {{ $rejectionReason }}
            </div>
        @endif
        
        <p>Вы можете отредактировать материал и отправить его на повторную модерацию.</p>
        
        <p>
            <a href="{{ route('account.materials.edit', $material) }}" style="display: inline-block; padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px;">
                Редактировать материал
            </a>
        </p>
        
        <p style="margin-top: 30px; font-size: 12px; color: #999;">
            С уважением,<br>
            Команда {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
