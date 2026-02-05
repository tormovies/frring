# Заметки по развертыванию (Deployment Notes)

## Composer на продакшене

### Расположение Composer

- **Composer 2**: `/home/a/adminfeg/.local/bin/composer` (версия 2.2.25)
- **Composer 1**: `/usr/local/bin/composer-phar` (версия 1.10.26) - **НЕ ИСПОЛЬЗОВАТЬ** для Laravel 12
- **PHP 8.3 wrapper для composer**: `/usr/local/bin/composer-php8.3` - использует Composer 1, **НЕ ИСПОЛЬЗОВАТЬ**

### Правильные команды для продакшена

**Для обновления автозагрузчика:**
```bash
cd ~/neurozvuk.ru/laravel
php8.3 /home/a/adminfeg/.local/bin/composer dump-autoload --no-scripts
```

**Для установки зависимостей:**
```bash
cd ~/neurozvuk.ru/laravel
php8.3 /home/a/adminfeg/.local/bin/composer install --no-scripts
```

**Важно:** 
- Laravel 12 требует Composer 2 (composer-runtime-api ^2.2)
- Всегда использовать PHP 8.3: `php8.3`
- Использовать `--no-scripts` если возникают проблемы с post-autoload-dump скриптами

### Путь к PHP 8.3

- PHP 8.3 доступен через команду `php8.3`
- Проверка версии: `php8.3 -v`

## Очистка кеша после изменений

После обновления кода или автозагрузчика:
```bash
cd ~/neurozvuk.ru/laravel
php8.3 artisan optimize:clear
php8.3 artisan cache:clear
php8.3 artisan config:clear
php8.3 artisan view:clear
```

## Деплой на продакшен (Deploy to Production)

Команды для деплоя новой версии кода:

```bash
cd ~/neurozvuk.ru/laravel
git pull origin master
php8.3 /home/a/adminfeg/.local/bin/composer dump-autoload --no-scripts
php8.3 artisan optimize:clear
php8.3 artisan migrate --force
```

## Кеширование Laravel (оптимизация производительности)

После деплоя для ускорения работы приложения рекомендуется включить кеширование:

```bash
cd ~/neurozvuk.ru/laravel
php8.3 artisan config:cache
php8.3 artisan route:cache
php8.3 artisan view:cache
php8.3 artisan optimize
```

**Важно:** После включения кеширования:
- При изменении конфигурации нужно выполнять `php8.3 artisan config:clear` и `php8.3 artisan config:cache`
- При добавлении/изменении маршрутов нужно выполнять `php8.3 artisan route:clear` и `php8.3 artisan route:cache`
- При изменении Blade шаблонов нужно выполнять `php8.3 artisan view:clear` и `php8.3 artisan view:cache`
- Или просто `php8.3 artisan optimize:clear` для очистки всего, затем `php8.3 artisan optimize` для создания всех кешей

**Ожидаемый эффект:** Ускорение загрузки страниц на 30-50%

## Логи

Логи находятся в: `~/neurozvuk.ru/laravel/storage/logs/`
Формат файлов: `laravel-YYYY-MM-DD.log` (daily driver)

Просмотр последних ошибок:
```bash
tail -200 ~/neurozvuk.ru/laravel/storage/logs/laravel-$(date +%Y-%m-%d).log | grep -i "error\|exception\|fatal" -A 20
```
