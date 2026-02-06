# Деплой на продакшен (freeringtones.ru)

Сервер: `/home/admin/domains/freeringtones.ru`  
Раньше сайт был в `public_html`. Ставим Laravel из репозитория.

---

## 1. Подготовка (на сервере)

Зайди по SSH в каталог домена:

```bash
cd /home/admin/domains/freeringtones.ru
```

Сделай бэкап и переименуй старый сайт:

```bash
# переименовать старый сайт (не удалять)
mv public_html public_html-old
```

Проверь, что папка переименована:

```bash
ls -la
# должна быть public_html-old, public_html не должно быть
```

---

## 2. Клонирование репозитория

Клонируй репозиторий в текущую папку (получится структура: NewRingtone/, .git, README и т.д.):

```bash
cd /home/admin/domains/freeringtones.ru
git clone https://github.com/tormovies/frring.git .
```

Если папка не пустая (остались файлы кроме public_html-old), клонируй в подпапку и потом перенеси:

```bash
git clone https://github.com/tormovies/frring.git repo-tmp
mv repo-tmp/* repo-tmp/.[!.]* . 2>/dev/null; rmdir repo-tmp
```

Laravel-приложение лежит в **NewRingtone/**.

---

## 3. Document root веб-сервера

Нужно, чтобы в браузере открывалась папка **public** Laravel, а не корень домена.

- **Nginx:** в конфиге виртуального хоста поменяй `root` на:
  ```
  root /home/admin/domains/freeringtones.ru/NewRingtone/public;
  ```
- **Apache:** в конфиге хоста поменяй `DocumentRoot` на:
  ```
  DocumentRoot /home/admin/domains/freeringtones.ru/NewRingtone/public
  ```
  И добавь блок с `Directory` для этой папки с `AllowOverride All` (если используешь .htaccess).

После правок перезапусти веб-сервер (nginx reload / apache restart).

---

## 4. База данных

Создай новую БД и пользователя (через панель хостинга или MySQL):

```sql
CREATE DATABASE freeringtones_new CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'freeringtones_user'@'localhost' IDENTIFIED BY 'надежный_пароль';
GRANT ALL ON freeringtones_new.* TO 'freeringtones_user'@'localhost';
FLUSH PRIVILEGES;
```

Если нужны данные со старого сайта — сделай дамп старой БД и импортируй в `freeringtones_new` (или сначала импорт, потом миграции, в зависимости от того, как у тебя устроен старый дамп).

---

## 5. Файл .env в NewRingtone

```bash
cd /home/admin/domains/freeringtones.ru/NewRingtone
cp .env.example .env
php artisan key:generate
```

Отредактируй `.env` (nano/vim или через панель):

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://freeringtones.ru

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=freeringtones_new
DB_USERNAME=freeringtones_user
DB_PASSWORD=надежный_пароль
```

Остальное (FILESYSTEM_DISK, CDN, очередь и т.д.) — по необходимости из твоего текущего .env или документации проекта.

---

## 6. Зависимости и миграции

```bash
cd /home/admin/domains/freeringtones.ru/NewRingtone
composer install --no-dev --optimize-autoloader
php artisan migrate --force
```

Если импортировал старый дамп и таблицы уже есть — сначала проверь:

```bash
php artisan migrate:status
```

При необходимости: `php artisan migrate --force` только для недостающих миграций.

---

## 7. Права и ссылки

```bash
cd /home/admin/domains/freeringtones.ru/NewRingtone
php artisan storage:link
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

Пользователь веб-сервера может быть `www-data`, `nginx`, `apache` — уточни на хостинге (`ps aux | grep nginx` или `ps aux | grep apache`).

---

## 8. Статика (если нужна сборка фронта)

```bash
cd /home/admin/domains/freeringtones.ru/NewRingtone
npm ci
npm run build
```

Если на проде не трогаешь фронт — можно не ставить node и не запускать build, если всё уже в репозитории.

---

## 9. Проверка

- Открой https://freeringtones.ru — должна открыться главная Laravel.
- Проверь скачивание, лайки, поиск.
- Старый сайт остаётся в `public_html-old` (до него можно не давать доступ или отключить поддомен/отдельный хост, если нужен только новый).

---

## 10. Если что-то пошло не так

Вернуть старый сайт:

- В конфиге веб-сервера вернуть `root`/`DocumentRoot` на `.../public_html-old` (или как у тебя был старый путь).
- Перезапустить веб-сервер.

Новый код при этом не трогай — просто переключи корень сайта обратно на старую папку.

---

## 11. Текущая структура на сервере (актуальная)

- Домен: `/home/admin/domains/freeringtones.ru`
- Laravel: в папке **laravel/** (или **NewRingtone/** — в зависимости от того, как клонировали).
- Document root: **public_html** — симлинк на `laravel/public` (или `NewRingtone/public`).
- Старый сайт: **public_html-old**
- Субдомен cp1.freeringtones.ru: **cp1/** — симлинки `mp3` и `m4r` ведут в `laravel/storage/app/public/mp4` и `m4r30`.

В `.env` должен быть: `RINGTONE_CDN_URL=https://cp1.freeringtones.ru`

---

## 12. Обновление кода на продакшене (релиз)

Когда выкатываешь новую версию после правок в репозитории:

```bash
cd /home/admin/domains/freeringtones.ru
# Если приложение в папке NewRingtone:
# cd NewRingtone
# Если переименовано в laravel:
cd laravel

git pull origin main
# или: git pull origin master

composer install --no-dev --optimize-autoloader
# при необходимости: php8.3 /path/to/composer install --no-dev --optimize-autoloader

php artisan migrate --force
# при необходимости: php8.3 artisan migrate --force
```

Если менялся фронт (Vite/Blade):

```bash
npm ci
npm run build
```

Очистка и кеш для продакшена:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
# или одной командой: php artisan optimize
```

Проверка: открыть https://freeringtones.ru и cp1 (воспроизведение/скачивание рингтонов).
