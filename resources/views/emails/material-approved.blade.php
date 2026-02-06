<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Материал одобрен</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #4CAF50;">Ваш материал одобрен!</h2>
        
        <p>Здравствуйте, {{ $material->user->name ?? 'Пользователь' }}!</p>
        
        <p>Ваш материал <strong>{{ $material->name }}</strong> был одобрен администратором и теперь опубликован на сайте.</p>
        
        <p>
            <a href="{{ route('materials.show', $material->slug) }}" style="display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">
                Посмотреть материал
            </a>
        </p>
        
        <p style="margin-top: 30px; font-size: 12px; color: #999;">
            С уважением,<br>
            Команда {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
