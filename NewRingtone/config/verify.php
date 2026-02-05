<?php

return [
    /*
    | Базовые URL для команды verify:sites-seo (сравнение старого и нового сайта).
    | Можно переопределить через --old и --new в командной строке.
    */
    'old_site_url' => env('VERIFY_OLD_SITE_URL', 'https://freeringtones.ru'),
    'new_site_url' => env('VERIFY_NEW_SITE_URL', 'http://127.0.0.1:3000'),
];
