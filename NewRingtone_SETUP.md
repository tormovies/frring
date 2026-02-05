# Установка NewRingtone (Laravel)

Папка проекта: **c:\projects\freeringtones.ru\NewRingtone\**

## Уже сделано

- Структура проекта поднята в корень `NewRingtone\` (artisan, composer.json, app/, config/ и т.д.).
- Файл **`.env`** создан и настроен:
  - MySQL: база **`newringtone`**, пользователь **root**, пароль из твоего локалхоста.
  - Локаль **ru**, APP_URL http://localhost:8000.
- База MySQL **`newringtone`** создана (utf8mb4).

## Что сделать вручную

1. **Установить зависимости PHP**
   ```bash
   cd c:\projects\freeringtones.ru\NewRingtone
   composer install
   ```
   Если будут таймауты к GitHub — повтори команду или выполни в другой сети.

2. **Сгенерировать ключ приложения**
   ```bash
   php artisan key:generate
   ```

3. **Миграции**
   ```bash
   php artisan migrate
   ```
   При запросе подтверждения введи `yes`.

4. **Запуск**
   ```bash
   php artisan serve
   ```
   Сайт откроется по адресу **http://localhost:8000**.

5. **Фронт (по желанию)**
   ```bash
   npm install
   npm run build
   ```
   Или для разработки с hot reload: `npm run dev`.

## Импорт данных старого проекта

После запуска «из коробки» можно подключать импорт по документу **OLD_PROJECT_MIGRATION.md** (таблицы, SEO, пути к аудио; сами аудиофайлы не переносим).
