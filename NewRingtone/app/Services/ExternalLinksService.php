<?php

namespace App\Services;

/**
 * Определение внешних URL и удаление внешних ссылок из текста.
 * Ссылки на продакшен (freeringtones.ru) всегда считаются внутренними и не удаляются.
 */
class ExternalLinksService
{
    /** Домены сайта: внутренние ссылки не удаляются (даже при APP_URL=localhost). */
    private const INTERNAL_HOSTS = ['freeringtones.ru', 'www.freeringtones.ru'];

    private string $appHost;
    /** @var list<string> */
    private array $internalHosts;

    public function __construct()
    {
        $baseUrl = config('app.url', 'http://127.0.0.1:3000');
        $parsed = parse_url($baseUrl);
        $this->appHost = isset($parsed['host']) ? strtolower($parsed['host']) : '';

        $this->internalHosts = array_values(array_unique(array_merge(
            self::INTERNAL_HOSTS,
            $this->appHost !== '' ? [$this->appHost] : []
        )));
    }

    public function getAppHost(): string
    {
        return $this->appHost;
    }

    /** Ссылки на эти хосты не считаются внешними (не удаляются). */
    public function getInternalHosts(): array
    {
        return $this->internalHosts;
    }

    public function isExternal(string $url): bool
    {
        if ($url === '' || str_starts_with($url, '#')) {
            return false;
        }
        $parsed = parse_url($url);
        if (!isset($parsed['host'])) {
            return false;
        }
        $host = strtolower($parsed['host']);

        foreach ($this->internalHosts as $internal) {
            if ($host === $internal || str_ends_with($host, '.' . $internal)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Удаляет из HTML/текста внешние ссылки:
     * - <a href="внешний">текст</a> → текст
     * - <img ... src="внешний" ...> → удаляется
     * - голые внешние URL → удаляются
     */
    public function stripExternalLinksFromText(string $text): string
    {
        $self = $this;

        // Внешние <a href="...">...</a> — оставляем только внутренний текст
        $text = preg_replace_callback(
            '#<a\s[^>]*href\s*=\s*["\'](https?://[^"\']+)["\'][^>]*>(.*?)</a>#ius',
            function (array $m) use ($self) {
                $url = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                return $self->isExternal($url) ? $m[2] : $m[0];
            },
            $text
        );

        // Внешние <img ... src="...">
        $text = preg_replace_callback(
            '#<img\s[^>]*src\s*=\s*["\'](https?://[^"\']+)["\'][^>]*/?>#iu',
            function (array $m) use ($self) {
                $url = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                return $self->isExternal($url) ? '' : $m[0];
            },
            $text
        );

        // Голые внешние URL в тексте
        $text = preg_replace_callback(
            '#https?://[^\s<>"\']+#u',
            function (array $m) use ($self) {
                return $self->isExternal($m[0]) ? '' : $m[0];
            },
            $text
        );

        return $text;
    }
}
