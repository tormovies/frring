# Коммит и пуш — выполнить вручную

В терминале в папке проекта выполни:

```bash
cd c:\projects\Ringtone

git add -A
git status

git commit -m "SEO, sitemap, производительность, админка SEO, исправление Blade @ в JSON-LD

- SEO: Open Graph, Twitter Card, canonical, sitemap.xml, robots.txt (маршрут), fallback description
- JSON-LD: WebSite (главная), AudioObject (материалы), Article (статьи); генерация через @php/json_encode (без @ в Blade)
- Производительность: eager load в MainController/MaterialController, индекс materials.status, loading=lazy у img, defer у script.js
- Деплой: в DEPLOY_TO_PRODUCTION.md добавлен шаг config/route/view cache
- Админка: страница SEO в разделе Administration (URL sitemap, статистика по типам, кеш sitemap, кнопка обновить кеш)
- SitemapService: кеширование sitemap на 24ч, SitemapController использует сервис
- MaterialForm: в поле Type только активные типы + текущий выбранный
- План и отчёт: SEO_AND_PERFORMANCE_ANALYSIS.md, SEO_AND_PERFORMANCE_PLAN.md, памятка про Blade и @ в JSON-LD"

git push origin master
```

Если `git push` запросит логин/пароль или SSH-ключ — введи их вручную.
