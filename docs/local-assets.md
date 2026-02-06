# Локальные ресурсы (без CDN)

Сайт настроен на работу без внешних CDN: шрифты, иконки и jQuery подключаются из `public/`.

## Что уже есть

- **public/css/fonts-local.css** — системные шрифты (Exo 2, Roboto), без загрузки из интернета.
- **public/css/fontawesome/all.min.css** — минимальная заглушка Font Awesome (иконки не отображаются, вёрстка не ломается).
- **public/js/jquery-3.6.0.min.js** — минимальная заглушка jQuery (меню, скролл «Наверх» и закрытие по клику работают).

## Если нужны полные версии

1. **jQuery 3.6.0**  
   Скачайте с [jquery.com/download](https://jquery.com/download/) или [code.jquery.com/jquery-3.6.0.min.js](https://code.jquery.com/jquery-3.6.0.min.js) и замените файл `public/js/jquery-3.6.0.min.js`.

2. **Font Awesome 5.15.4**  
   Скачайте архив с [GitHub Releases](https://github.com/FortAwesome/Font-Awesome/releases/tag/5.15.4). Скопируйте:
   - `css/all.min.css` → `public/css/fontawesome/all.min.css`
   - папку `webfonts/` → `public/css/fontawesome/webfonts/`

3. **Шрифты Exo 2 и Roboto (по желанию)**  
   Можно добавить в `public/fonts/` файлы woff2 и в `fonts-local.css` — блоки `@font-face` с путями к ним.

После замены файлов сайт будет работать полностью локально, без обращений к заблокированным CDN.
