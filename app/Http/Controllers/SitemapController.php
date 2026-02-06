<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(
        private SitemapService $sitemap
    ) {}

    /**
     * Генерирует sitemap.xml для поисковиков (из кеша или строит заново).
     */
    public function index(): Response
    {
        $xml = $this->sitemap->getXml();

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Charset' => 'UTF-8',
        ]);
    }
}
