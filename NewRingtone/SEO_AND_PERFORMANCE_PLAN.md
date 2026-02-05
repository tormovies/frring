# План по отчёту SEO и производительности

Пункты из `SEO_AND_PERFORMANCE_ANALYSIS.md` — делаем по порядку.

---

## Уже сделано (можно не трогать)

- [x] **MainController:** `popular` и `best` с `->with(['type', 'authors'])`, материалы по типам с `->with(['authors', 'categories'])`
- [x] **MaterialController::show:** блок «Похожие» с `->with(['type', 'authors'])`
- [x] **script.js** подключён с атрибутом `defer`

---

## Блок 1: SEO

### 1.1 Open Graph и Twitter Card
**Что:** В `resources/views/layouts/app.blade.php` в `<head>` добавить мета-теги.
- `og:title`, `og:description`, `og:url`, `og:image`, `og:type`, `og:locale`
- `twitter:card`, `twitter:title`, `twitter:description`, `twitter:image`

**Как:** Использовать `@yield` / `@stack` или переменные, чтобы страницы (материал, статья и т.д.) могли подставлять свои title/description/image; по умолчанию — из конфига/главной.

- [x] Сделать (2026-01-29)

---

### 1.2 Canonical URL
**Что:** В том же layout в `<head>` добавить:
```html
<link rel="canonical" href="{{ url()->current() }}">
```
(для страниц с пагинацией при необходимости — свой canonical в шаблоне страницы.)

- [x] Сделать (по умолчанию `url()->current()`, переопределить через `@section('canonical')` при пагинации)

---

### 1.3 Sitemap.xml
**Что:** Реализовать отдачу sitemap по адресу `/sitemap.xml`.
- Вариант А: свой контроллер + маршрут (материалы, категории, типы, теги, статьи, страницы).
- Вариант Б: пакет `spatie/laravel-sitemap`.

- [x] Сделано (вариант А: SitemapController, маршрут `/sitemap.xml`; robots.txt отдаётся маршрутом со ссылкой на sitemap)

---

### 1.4 Пустые description
**Что:** Пройти по всем публичным страницам (главная, материал, категория, тег, тип, автор, статьи, страница, поиск) и убедиться, что везде задан осмысленный `@section('description')`.

- [x] Сделано: добавлены fallback для material/show, article/show, page/show — при пустом description подставляется название или текст вида «… на НейроЗвук»

---

### 1.5 JSON-LD (по желанию)
**Что:** Добавить структурированные данные Schema.org (Article для статей, MusicRecording или AudioObject для материалов) — в шаблоны `article/show`, `material/show` или через @stack в layout.

- [x] Сделано: материал — AudioObject (аудио), статья — Article, главная — WebSite (каталог рингтонов и музыки); материалы помечены og:type music.song

---

## Блок 2: Производительность

### 2.1 Индекс по status в таблице materials
**Что:** Создать миграцию: добавить индекс по полю `status` или составной индекс `(status, type_id)` / `(status, created_at)` в таблице `materials`.

- [x] Сделано: миграция `2026_01_29_000001_add_status_index_to_materials_table.php` (выполнить: `php artisan migrate`)

---

### 2.2 Lazy loading для изображений
**Что:** В шаблонах, где выводятся списки с картинками (статьи, автор и т.д.), добавить атрибут `loading="lazy"` к тегам `<img>`.

- [x] Сделано: author/show, article/index (вид списка и плитки)

---

### 2.3 Кеширование Laravel на продакшене (документация/деплой)
**Что:** Убедиться, что в процессе деплоя выполняются:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
# или
php artisan optimize
```
Зафиксировать в `DEPLOY_TO_PRODUCTION.md` или в скрипте деплоя.

- [x] Сделано: в DEPLOY_TO_PRODUCTION.md добавлен шаг с config:cache, route:cache, view:cache после очистки кеша

---

## Порядок работы

1. **SEO:** 1.1 → 1.2 → 1.3 → 1.4 → (1.5 по желанию)
2. **Производительность:** 2.1 → 2.2 → 2.3

После каждого пункта можно отмечать галочкой в этом файле.

---

## Важно: Blade и символ @ в JSON-LD

В Blade символ **@** начинает директиву. В Schema.org JSON-LD используются ключи **"@context"** и **"@type"** — если писать их в шаблоне как есть, Blade воспринимает `@context` и `@type` как директивы и ломает компиляцию (ошибка «expecting endif»).

**Как делать:** формировать JSON для Schema.org в блоке **@php** и выводить через **json_encode()** (массив с ключами `'@context'`, `'@type'` в кавычках внутри PHP). Так делается в:
- `resources/views/main/index.blade.php` (WebSite)
- `resources/views/material/show.blade.php` (AudioObject)
- `resources/views/article/show.blade.php` (Article)

**Альтернатива:** в обычном тексте вывести буквальный @ можно как **@@** (экранирование в Blade).
