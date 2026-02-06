# SEO: старый проект → новый

## Как было в старом проекте (PHP/Smarty)

### Таблица `seo` в БД

- **Структура:** записи по типам и привязке к сущности.
- **Типы:**
  - `IND` — главная (seo_item = 0).
  - `ITE` — материал/рингтон (seo_item = id из `ringtone`).
  - `CAT` — категория (seo_item = cat_id из `cats`).
  - `PAG` — статическая страница (seo_item = page_id из `pages`).

- **Поля:** `seo_title`, `seo_description`, `seo_keywords`, `seo_h1` (для категорий).

- **Подстановки в текстах:**
  - `%site_name%`, `%year%`, `%item_name%` (для ITE: "исполнитель - название"),
  - `%cat_name%` (для CAT), `%page_name%` (для PAG).

- **Логика в `core.php`:**
  - Для каждой страницы выбирается запись из `seo` по типу и id.
  - Если своей записи нет или поля пустые — берётся шаблон с `seo_item='0'`.
  - Для категорий отдельно задаются title/description/keywords для «новые» (date), «хиты» (rating), «популярные» (plays), в т.ч. со «Страница N:» для пагинации.
  - Для поиска: title/description/keywords собираются из `$_GET['query']`.
  - В Smarty передаются: `seo_title`, `seo_description`, `seo_keywords`.

### Вывод в шапке (header.tpl)

- **Title:** `{if $rowstart>0}Страница {math equation=$rowstart/24+1}: {/if}{$seo_title}`
- **Description:** тот же префикс «Страница N:» + `$seo_description`
- **Keywords:** `$seo_keywords`
- **Canonical:** при наличии `$rel`
- **Robots:** `all` для всех

---

## Как переносим в новый проект (Laravel)

### Импорт при `import:old-site`

| Старое | Новое |
|--------|--------|
| **Категории:** `seo` (CAT) по `seo_item = cat_id` | В модель **Category** пишутся: `title` ← seo_title, `description` ← seo_description, `h1` ← seo_h1. При отсутствии SEO — из `cats` (cat_name, cat_description). |
| **Материалы:** `seo` (ITE) по `seo_item = ringtone.id` | В модель **Material** пишутся: `title` ← seo_title, `description` ← seo_description, `h1` ← seo_h1. При отсутствии — из ringtone (name + original_name, strip description). |
| **SEO keywords (ITE)** | Не копируются в отдельное поле; используются для создания **тегов** и связей material–tag (импорт тегов из `seo_keywords`). |

Отдельная таблица `seo` в новой БД **не создаётся**: всё нужное для страниц материалов и категорий уже в полях `title`, `description`, `h1` у Material и Category.

### Вывод в новом сайте

- **Layout** (`layouts/app.blade.php`):  
  `@yield('title')`, `@yield('description')`, Open Graph (`og_title`, `og_description`, `og_image`, `og_type`), Twitter Card, canonical.

- **Подстановки** (хелпер `meta_replace()` в `app/Helpers/meta.php`):
  - `%year%` → текущий год.
  - `%page%` → «Страница N» при page>1, иначе пусто.

- **По страницам:**
  - **Материал (play):** title/description/og из `$material->title`, `$material->description`; h1 на странице не выводится отдельно, используется заголовок материала.
  - **Категория:** title/description/h1 из `$category->title`, `$category->description`, `$category->h1`.
  - **Тег, тип:** из `$tag` / `$type` (title, description, h1).
  - **Поиск:** title = «Результаты поиска: {запрос}», description — по запросу.
  - **Популярные/хиты:** зашитые строки с `meta_replace` и подстановками %year%, %page%.
  - **Главная:** зашитые title/description с %year%.

- **JSON-LD:** на странице материала — `AudioObject` (название, описание, url, длительность, автор и т.д.).

### Чего нет в новом проекте по сравнению со старым

1. **Мета `keywords`** — в layout нет `<meta name="keywords">`. Поисковики им почти не пользуются; при необходимости можно добавить отдельное поле и вывод.
2. **Отдельная таблица SEO** — нет; правка только через поля сущностей (Material, Category, Page и т.д.) или захардкоженные строки.
3. **Шаблоны SEO для главной (IND)** — главная использует фиксированные строки, а не записи из старой `seo` (IND, seo_item=0). При желании можно один раз вытащить из старой БД и прописать в коде/конфиге.
4. **Префикс «Страница N:» в title/description** — реализован через `%page%` в `meta_replace`; в представлениях его нужно подставлять в строки (как в popular/best), где нужна пагинация в SEO.

### Рекомендации

- Для **страниц материалов и категорий** после импорта SEO уже перенесено (title, description, h1 из старой `seo`).
- Для **главной, популярных, хитов, поиска** при необходимости можно взять формулировки из старого `core.php` и вставить их в соответствующие Blade-шаблоны с использованием `meta_replace()` и, при необходимости, конфига с текстами по типам страниц.
