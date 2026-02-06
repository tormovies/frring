# FreeRingtones.ru — запуск бэкапа локально

## Что внутри бэкапа

- **Архив** `freeringtones.ru.zip` распакован в папку `unpacked\freeringtones.ru\`.
- **Корень сайта** — `unpacked\freeringtones.ru\public_html\`.
- **База данных** — дамп MySQL в файле `admin_freeringtones_1770285580.sql`.

## Стек

| Компонент | Описание |
|-----------|----------|
| **Язык** | PHP (используется mysqli, короткие теги `<?`) |
| **БД** | MySQL/MariaDB |
| **Шаблоны** | Smarty |
| **Веб-сервер** | Нужен Apache с mod_rewrite (или аналог) |

## Шаги для запуска

### 1. Установить окружение

Нужны:

- **PHP** 7.x или 5.x (с расширениями: mysqli, gd, mbstring, zip — для getid3 и загрузок).
- **MySQL** или **MariaDB**.
- **Apache** с включённым `mod_rewrite` (или, например, OpenServer/XAMPP/Denwer на Windows).

Либо использовать встроенный сервер PHP (без ЧПУ из `.htaccess`, только для проверки главной и скриптов):

```bash
cd unpacked\freeringtones.ru\public_html
php -S localhost:8080
```

Тогда главная будет: `http://localhost:8080/` (редиректы с домена и HTTPS работать не будут).

### 2. Создать базу и импортировать дамп

В MySQL выполнить:

```sql
CREATE DATABASE admin_freeringtones CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Импорт дампа (в консоли, из корня проекта):

```bash
mysql -u root -p admin_freeringtones < admin_freeringtones_1770285580.sql
```

Либо через phpMyAdmin: создать БД `admin_freeringtones`, затем «Импорт» → выбрать файл `admin_freeringtones_1770285580.sql`.

### 3. Настроить доступ к БД

Файл конфигурации: **`unpacked\freeringtones.ru\public_html\config.php`**.

Текущие параметры в бэкапе:

- Хост: `localhost`
- База: `admin_freeringtones`
- Пользователь: `admin_freeringtones`
- Пароль: `kLHi1rh1SI`

Для локального запуска либо создать пользователя MySQL с такими же логином/паролем, либо изменить в `config.php` под своего пользователя:

```php
$db['host'] = "localhost";
$db['base'] = "admin_freeringtones";
$db['user'] = "ваш_пользователь";
$db['pass'] = "ваш_пароль";
```

### 4. Домен и редиректы

В `index.php` есть проверка хоста: редирект на `freeringtones.ru`. Чтобы работать локально без редиректа, можно временно закомментировать блок (в начале `index.php`):

```php
// if($_SERVER['HTTP_HOST']!=$domaisd && $_SERVER['HTTP_HOST']!="www.".$domaisd){
//     header("HTTP/1.1 301 Moved Permanently");
//     header("Location: http://".$domaisd.$_SERVER['REQUEST_URI']);
//     exit();
// }
```

Либо добавить в `config.php` (и в проверку в `index.php`) поддержку локального хоста, например `localhost` или `127.0.0.1`.

### 5. Папки для записи

Убедиться, что веб-сервер может писать в:

- `public_html/smarty/templates_c/`
- `public_html/cache/`
- при загрузке рингтонов — каталоги вроде `temp_ringtone`, `temp_thumbs`, `thumbs`, `content` (если скрипты туда пишут).

### 6. Открыть сайт

- Если используете **Apache** (OpenServer/XAMPP и т.п.):  
  - Корень сайта (DocumentRoot) укажите на `unpacked\freeringtones.ru\public_html`.  
  - Откройте в браузере выданный локальный адрес (например `http://freeringtones.ru` при прописанном в hosts или `http://localhost/site`).
- Если запускали **`php -S localhost:8080`** из `public_html`:  
  - Откройте `http://localhost:8080/`.

## Структура (кратко)

- `public_html/` — корень сайта: `index.php`, `config.php`, `core.php`, `func.php`.
- `public_html/template/`, `template3/` — шаблоны Smarty (`.tpl`).
- `public_html/cms/` — админка и phpMyAdmin.
- `public_html/getid3/`, `securimage/`, `smarty/` — сторонние библиотеки.
- Дамп БД содержит таблицы: `ringtone`, `cats`, `users`, `pages`, `settings`, `seo` и др.

## Безопасность

Пароли и ключи API в `config.php` взяты из бэкапа продакшена. Для локальной разработки лучше заменить их на тестовые и не выкладывать `config.php` в открытый репозиторий.

После настройки БД и конфига сайт должен открываться и показывать рингтоны из таблицы `ringtone`; при необходимости проверьте логи PHP и MySQL при ошибках.
