# Отладка производительности продакшена (LCP, задержки)

Контекст: два сайта (freeringtones.ru и neurozvuk.ru) работают рядом с разницей в несколько раз по скорости. На главной freeringtones.ru LCP 2,62 с, элемент LCP — h1.

---

## 1. Включить ProfilePerformance для диагностики

Middleware `ProfilePerformance` уже есть, но не подключён. Добавить в `bootstrap/app.php`:

```php
$middleware->web(append: [
    \App\Http\Middleware\CheckUserStatus::class,
    \App\Http\Middleware\ProfilePerformance::class,  // временно для отладки
]);
```

Или только в production при `APP_DEBUG=false` через условие.

**Что даёт:** в заголовках ответа (в local) или в логах — `X-Execution-Time`, `X-Query-Count`, `X-Memory-Used`, `X-Slow-Queries`. Логи пишутся при >500 ms, >20 запросов или при медленных запросах (>100 ms).

**На проде:** проверять `storage/logs/laravel-*.log` на записи "Performance Profile".

---

## 2. Узкие места (серверная часть)

### 2.1 SeoTemplate без кеша

`seo_template('home')` вызывает `SeoTemplate::getBySlug('home')` — запрос в БД при каждом запросе.

**Решение:** кешировать:

```php
// app/Helpers/meta.php или в SeoTemplate
return Cache::remember("seo_template_{$slug}", 3600, fn () => 
    SeoTemplate::where('slug', $slug)->first()
);
```

### 2.2 Пагинация — два запроса

`Material::with(['type','authors'])->paginate($perPage)` делает:
1. `SELECT * FROM materials ... LIMIT ...` 
2. `SELECT COUNT(*) FROM materials ...`

При большом `materials` COUNT может быть медленным. Проверить индексы: `status`, `created_at`, составной `(status, created_at)`.

### 2.3 Кеширование Laravel

Убедиться, что на проде выполняются:

```bash
/usr/local/php83/bin/php artisan config:cache
/usr/local/php83/bin/php artisan route:cache
/usr/local/php83/bin/php artisan view:cache
# или
/usr/local/php83/bin/php artisan optimize
```

Без этого Blade компилируется при каждом запросе.

---

## 3. Узкие места (клиентская часть, LCP)

LCP-элемент — **h1** в `.header_title`. LCP 2,62 с означает, что отрисовка h1 сильно задерживается.

### 3.1 Render-blocking CSS

В `app-old.blade.php` в `<head>` загружаются 4 CSS-файла подряд:

- `grid.min.css`
- `style-old-site.css`
- `fonts-local.css`
- `fontawesome/all.min.css` (~80 KB+, мы используем малую часть иконок)

До их загрузки браузер блокирует рендеринг. Чем больше и медленнее CSS — тем позже отрисуется h1.

**Рекомендации:**
- Оставить в head только критический CSS (уже есть inline) и grid/style.
- Font Awesome загружать `media="print" onload="this.media='all'"` или подключать только нужные иконки.
- Либо заменить иконки на SVG-спрайт.

### 3.2 Font Awesome и font-display

В `fontawesome/all.min.css`:

```css
@font-face {
  font-display: block;  /* блокирует отрисовку текста до загрузки шрифта */
}
```

`font-display: block` задерживает первый рендер текста. Для иконок лучше `font-display: optional` или `swap`.

### 3.3 Порядок загрузки

Текущий порядок: critical inline CSS → 4 CSS → Yandex.Metrika → body. Контент (h1) идёт после header, top-bar, section_content. Критический CSS уже уменьшает FOUC, но 4 внешних CSS всё равно блокируют.

### 3.4 JSON-LD в head

`main/index.blade.php` через `@push('head')` добавляет большой JSON-LD ItemList. Он увеличивает размер HTML, но не блокирует рендер. Можно оставить или вынести в `@push('scripts')` в конец body.

---

## 4. Сравнение двух сайтов (чек-лист)

| Параметр              | freeringtones.ru              | neurozvuk.ru                 |
|-----------------------|-------------------------------|------------------------------|
| Хостинг               | DirectAdmin VPS (195.62.53.151) | Beget shared                 |
| PHP                   | /usr/local/php83/bin/php      | php8.3                       |
| Laravel optimize      | ?                             | ?                            |
| Кеш config/route/view | ?                             | ?                            |
| Размер БД materials   | ?                             | ?                            |
| Количество CSS в head | 4 файла                       | ?                            |
| Font Awesome          | полный all.min.css            | ?                            |
| CDN для статики       | нет                           | ?                            |

Нужно проверить на обоих:
1. `php artisan config:cache` и `optimize` — выполняются ли при деплое.
2. Количество и размер CSS/JS в head главной.
3. TTFB (Time to First Byte) — через DevTools Network или `curl -w "%{time_starttransfer}"`.

---

## 5. Быстрые шаги для улучшения LCP

1. Включить ProfilePerformance, посмотреть логи и заголовки.
2. Убедиться, что на проде выполнен `artisan optimize`.
3. Закешировать `SeoTemplate::getBySlug()`.
4. Отложить загрузку Font Awesome (media="print" onload или отдельный бандл иконок).
5. Проверить индексы БД на `materials` (status, created_at).
6. Включить HTTP-кеш и gzip для статики (см. PERFORMANCE_ANALYSIS.md).

---

## 6. Измерения

- **Chrome DevTools** → Lighthouse → Performance, посмотреть LCP, TBT, TTI.
- **Network** → Disable cache, смотреть waterfall, какой ресурс задерживает LCP.
- **curl** для TTFB:  
  `curl -w "TTFB: %{time_starttransfer}s\n" -o /dev/null -s https://freeringtones.ru/`
