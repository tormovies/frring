<?php

if (!function_exists('clean_lyrics_content')) {
    /**
     * Очищает текст песни от HTML и оставляет только разрешенные символы
     * Разрешены: русские/английские буквы, точка, запятая, пробелы, скобки круглые (), квадратные [], хештег #, восклицательный знак !, вопросительный знак ?
     *
     * @param string $content
     * @return string
     */
    function clean_lyrics_content(string $content): string
    {
        // Удаляем все HTML теги
        $content = strip_tags($content);
        
        // Обрезаем пробелы в начале и конце
        $content = trim($content);
        
        // Удаляем все символы кроме разрешенных
        // Разрешены: русские буквы (а-я, А-Я, ё, Ё), английские буквы (a-z, A-Z), цифры (0-9),
        // точка (.), запятая (,), пробелы, скобки круглые ()(), квадратные [][], хештег (#), восклицательный знак (!), вопросительный знак (?), дефис (-), переносы строк (\n, \r\n)
        $content = preg_replace('/[^а-яёА-ЯЁa-zA-Z0-9 .,()[\]\n\r#\-!?]/u', '', $content);
        
        // Оставляем переносы строк как есть, но нормализуем \r\n в \n
        $content = str_replace("\r\n", "\n", $content);
        
        return $content;
    }
}

if (!function_exists('wrap_lyrics_content')) {
    /**
     * Оборачивает текст песни в div с классом lyrics-content
     * Преобразует переносы строк (\n) в HTML-теги <br>
     *
     * @param string $content
     * @return string
     */
    function wrap_lyrics_content(string $content): string
    {
        // Если это уже обернутый контент, сначала разворачиваем его
        if (strpos($content, '<div class="lyrics-content">') === 0) {
            $content = unwrap_lyrics_content($content);
        }
        
        // Преобразуем существующие <br> теги в переносы строк перед очисткой
        $content = preg_replace('/<br\s*\/?>\s*/i', "\n", $content);
        
        $cleaned = clean_lyrics_content($content);
        
        if (empty(trim($cleaned))) {
            return '';
        }
        
        // Преобразуем переносы строк в <br> теги
        // Используем str_replace вместо nl2br для контроля формата
        $cleaned = str_replace("\n", "<br>", $cleaned);
        
        return '<div class="lyrics-content">' . $cleaned . '</div>';
    }
}

if (!function_exists('unwrap_lyrics_content')) {
    /**
     * Удаляет обертку <div class="lyrics-content"> из текста
     * Преобразует <br> и <br/> теги обратно в переносы строк (\n)
     *
     * @param string $content
     * @return string
     */
    function unwrap_lyrics_content(string $content): string
    {
        if (empty($content)) {
            return '';
        }
        
        // Удаляем <div class="lyrics-content"> в начале
        $content = preg_replace('/^<div\s+class=["\']lyrics-content["\'][^>]*>/i', '', $content);
        
        // Удаляем </div> в конце
        $content = preg_replace('/<\/div>\s*$/i', '', $content);
        
        // Преобразуем <br> и <br/> теги обратно в переносы строк
        $content = preg_replace('/<br\s*\/?>/i', "\n", $content);
        
        // Декодируем HTML entities
        $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
        
        return trim($content);
    }
}
