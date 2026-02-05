# Заметки по оптимизации (шаблоны, стили, HTML)

## Сделано

- **Общий верхний блок (top-bar)** вынесен в `resources/views/partials/top-bar.blade.php`. Сюда потом добавить счётчик на всех страницах: `@include('partials.counter')` или виджет в месте с комментарием «Место для счётчика».
- **Scroll-to-top** вынесен в `layouts/app-old.blade.php` один раз; дубли в main, category, tag, material, search, page убраны.
- **Страница материала (аудио)**:
  - Добавлен JSON-LD `MusicRecording` (Schema.org) в `<head>`: name, description, url, contentUrl, duration (ISO 8601), byArtist, publisher.
  - Контент обёрнут в `<article itemscope itemtype="https://schema.org/MusicRecording">`.
  - Заголовок страницы изменён с `<h2>` на `<h1 itemprop="name">` (одна h1 на страницу для SEO).
- **Inline CSS убран** с публичных страниц: блок `<style>` со страницы материала перенесён в `style-old-site.css`; переключение list/grid делается через классы `.view-container` и CSS; прочие `style="..."` (пустые списки, подпись автора, формы лайка, Метрика noscript) заменены на классы в том же файле; JS переключает вид через класс контейнера, а не через inline display.
- **Лишние CSS:** удалены неиспользуемые `public/css/style-old.css` и `public/css/layout-old.css`.
- **Критический CSS:** в `app-old` добавлен inline `<style>` для первого экрана (body, section_content, header, top-line, container-fluid + медиа) — меньше FOUC.
- **Кэш статики:** в `public/.htaccess` добавлено кэширование для шрифтов (woff, woff2 в Cache-Control и Expires).

## Рекомендации по стилям

- **Подключение:** в `app-old` используются `grid.min.css`, `style-old-site.css`, `fonts-local.css`, `fontawesome/all.min.css`. Неиспользуемые `style-old.css` и `layout-old.css` удалены из `public/css/`.
- **Шрифты:** подключены локально (fonts-local.css, Font Awesome в `public/css/fontawesome/`), без CDN.
- **Критический CSS:** вынесен в inline `<style>` в `app-old` (шапка, top-line, section_content, container-fluid + медиа для мобильных) — меньше FOUC до загрузки основных стилей.

## Рекомендации по HTML

- В `app-old` уже есть: один canonical на страницу, meta description, CSRF в head.
- Форма поиска в top-bar: добавлены `role="search"` и `aria-label="Поиск"`; иконки помечены `aria-hidden="true"`.
- На страницах списков (главная, категория, тег) добавлен JSON-LD `ItemList`: url страницы, numberOfItems, itemListElement с позицией и элементом типа MusicRecording (name, url).

## Счётчик в шапке

Яндекс.Метрика уже вставлена в `app-old` перед `</head>`. Если понадобится свой счётчик (просмотров/звонков): создать `partials/counter.blade.php` и подключить в `partials/top-bar.blade.php`.

---

## Идеи по оптимизации (можно сделать дальше)

### Загрузка и рендеринг
- **Локальные ресурсы:** шрифты (fonts-local.css), Font Awesome и jQuery подключены локально, без CDN; для Метрики оставлен только dns-prefetch на mc.yandex.ru.
- **Шрифты:** при желании подгружать только нужные начертания (400, 600) или заменить системным стеком — меньше запросов.
- **Font Awesome:** если используется мало иконок, заменить на SVG-спрайт или только нужные иконки — уменьшит размер CSS.

### Стили
- Лишние файлы `style-old.css` и `layout-old.css` удалены.

### Контент и кэш
- **Изображения:** добавлены `width`/`height` у логотипа (app-old), у превью статей (article/index — list и grid), у фото автора (author/show); у статей и автора уже был `loading="lazy"` — меньше CLS и отложенная загрузка.
- **Кэширование:** в `public/.htaccess` настроены Expires и Cache-Control для статики (img, css, js, шрифты woff/woff2); gzip включён. Для продакшена Laravel выполнить: `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache` (или `php artisan optimize`).

### SEO
- JSON-LD `ItemList` на главной, в категории и в теге реализован (список материалов с позицией и ссылкой).
- Проверить в панели Яндекс.Вебмастер, что мета-описания и заголовки выглядят корректно после выката.
