# Команды для локальной разработки

## Запуск локального сервера

```bash
# Вариант 1: Через встроенный PHP сервер на порту 3000
php -S localhost:3000 -t public

# Вариант 2: Через Laravel Artisan
php artisan serve --port=3000

# Вариант 3: Через батник (Windows)
run_server_3000.bat
```

После запуска открой в браузере: **http://localhost:3000**

## Composer (локально)

```bash
# Установка зависимостей
composer install

# Обновление зависимостей
composer update

# Добавить новый пакет
composer require vendor/package

# Обновить autoloader
composer dump-autoload
```

## Artisan команды (локально)

```bash
# Миграции
php artisan migrate
php artisan migrate:fresh --seed

# Очистка кешей
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Создание кешей
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Tinker (консоль)
php artisan tinker

# Список роутов
php artisan route:list
```

## Git (перед деплоем)

```bash
# Проверить изменения
git status
git diff

# Закоммитить изменения
git add .
git commit -m "Описание изменений"

# Отправить на GitHub
git push origin master
```

## Деплой на продакшен (freeringtones.ru)

**Кратко:**
```bash
ssh root@195.62.53.151
su - admin
cd /home/admin/domains/freeringtones.ru/laravel && git pull origin master && /usr/local/php83/bin/php /usr/local/bin/composer install --no-dev --optimize-autoloader && /usr/local/php83/bin/php artisan migrate --force && /usr/local/php83/bin/php artisan optimize:clear && /usr/local/php83/bin/php artisan optimize
```

**Подробно:** см. `DEPLOY_FREERINGTONES.txt` и `DEPLOY_PRODUCTION_STEPS.md`

⚠️ **ВАЖНО:** Деплой только от пользователя **admin**, не от root!
