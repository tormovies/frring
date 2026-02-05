# Инструкция по запуску проекта Neurozvuk

## Шаги для запуска проекта на локальном сервере:

### 1. Установка зависимостей PHP
```bash
composer install
```

### 2. Создание .env файла
✅ Файл `.env` уже создан. Если нужно изменить настройки базы данных, отредактируйте его.

### 3. Генерация ключа приложения
```bash
php artisan key:generate
```

### 4. Настройка базы данных

**Если используете MySQL** (рекомендуется для этого проекта):
- См. раздел "Настройка MySQL базы данных" ниже
- После настройки MySQL в `.env` и импорта дампа, проверьте миграции

**Если используете SQLite** (только для тестирования):
```bash
# Создайте файл базы данных
New-Item -ItemType File -Path "database\database.sqlite" -Force
```
Или вручную создайте файл `database/database.sqlite`

### 5. Запуск миграций базы данных
```bash
php artisan migrate
```

### 6. Установка зависимостей Node.js
```bash
npm install
```

### 7. Сборка frontend ресурсов
```bash
npm run build
```

Или для разработки с hot-reload:
```bash
npm run dev
```

### 8. Запуск сервера разработки

**Вариант 1: Стандартный способ**
```bash
php artisan serve
```
Сервер запустится на `http://localhost:8000`

**Вариант 2: С использованием встроенного скрипта (запускает сервер, очередь, логи и Vite одновременно)**
```bash
composer run dev
```

### Доступ к админ-панели Filament

После запуска проекта, админ-панель будет доступна по адресу:
```
http://localhost:8000/admin
```

**Примечание:** Вам потребуется создать первого пользователя-администратора. Обычно это делается через команду:
```bash
php artisan make:filament-user
```

## Настройка MySQL базы данных

### 1. Настройка .env файла

Отредактируйте `.env` файл и установите следующие параметры:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ringtone_db
DB_USERNAME=root
DB_PASSWORD=ваш_пароль
```

**Важно:** Замените `ringtone_db` на имя вашей базы данных, и укажите правильные `DB_USERNAME` и `DB_PASSWORD`.

### 2. Создание базы данных в MySQL

Откройте MySQL командную строку или phpMyAdmin и создайте базу данных:

**Вариант 1: Через MySQL командную строку**
```bash
mysql -u root -p
CREATE DATABASE ringtone_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

**Вариант 2: Через командную строку Windows (одной командой)**
```bash
mysql -u root -p -e "CREATE DATABASE ringtone_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 3. Импорт дампа базы данных

После создания базы данных импортируйте дамп с продакшена:

**Вариант 1: Через командную строку MySQL**
```bash
mysql -u root -p ringtone_db < путь\к\файлу\dump.sql
```

**Вариант 2: Через командную строку Windows (если MySQL в PATH)**
```cmd
mysql -u root -p ringtone_db < C:\путь\к\файлу\dump.sql
```

**Вариант 3: Через phpMyAdmin**
1. Откройте phpMyAdmin
2. Выберите созданную базу данных `ringtone_db`
3. Перейдите на вкладку "Импорт"
4. Выберите файл дампа
5. Нажмите "Вперед"

**Вариант 4: Если дамп находится в папке проекта**
```cmd
mysql -u root -p ringtone_db < database\dump.sql
```

### 4. После импорта дампа

Если дамп содержит полную структуру и данные:
- ✅ Миграции могут не понадобиться (структура уже создана)
- Проверьте подключение: `php artisan migrate:status`

Если нужно синхронизировать структуру с миграциями:
```bash
php artisan migrate --pretend  # посмотреть, какие миграции не применены
php artisan migrate            # применить недостающие миграции
```

**Важно:** Если в дампе уже есть данные, миграции могут вызвать ошибки (таблицы уже существуют). В этом случае используйте флаг `--force` только если уверены, или примените только недостающие миграции.

## Быстрый старт с MySQL (рекомендуется)

```bash
# 1. Установка зависимостей
composer install

# 2. Создание .env (если еще нет)
copy .env.example .env

# 3. Настройте .env - укажите параметры MySQL (DB_CONNECTION, DB_DATABASE, DB_USERNAME, DB_PASSWORD)

# 4. Генерация ключа приложения
php artisan key:generate

# 5. Создайте базу данных в MySQL и импортируйте дамп (см. инструкции выше)

# 6. Проверка подключения и миграций
php artisan migrate:status

# 7. Установка Node.js зависимостей
npm install

# 8. Сборка frontend
npm run build

# 9. Запуск сервера
php artisan serve
```

## Быстрый старт с SQLite (только для тестирования)

```bash
composer install
php artisan key:generate
New-Item -ItemType File -Path "database\database.sqlite" -Force
php artisan migrate
npm install
npm run build
php artisan serve
```

Или используйте встроенный скрипт (для SQLite):
```bash
composer run setup
php artisan serve
```

