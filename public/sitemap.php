<?php

declare(strict_types=1);

use App\Config\Config;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Support\Locale;
use App\Support\Url;

/** @var array{root: string, pdo: callable, view: callable} $app */
$app = require __DIR__ . '/bootstrap.php';

try {
    $pdo = ($app['pdo'])();
    $categories = new CategoryRepository($pdo);
    $articles = new ArticleRepository($pdo);

    $urls = [];

    foreach ([Locale::RU, Locale::EN] as $locale) {
        $urls[] = [
            'loc' => Url::absolute('/', [], $locale),
            'lastmod' => null,
            'alternates' => [
                Locale::RU => Url::absolute('/', [], Locale::RU),
                Locale::EN => Url::absolute('/', [], Locale::EN),
            ],
        ];

        foreach ($categories->listAllForSitemap() as $category) {
            $query = ['slug' => $category['slug']];
            $urls[] = [
                'loc' => Url::absolute('/category.php', $query, $locale),
                'lastmod' => $category['lastmod'],
                'alternates' => [
                    Locale::RU => Url::absolute('/category.php', $query, Locale::RU),
                    Locale::EN => Url::absolute('/category.php', $query, Locale::EN),
                ],
            ];
        }

        foreach ($articles->listAllForSitemap() as $article) {
            $query = ['slug' => $article['slug']];
            $urls[] = [
                'loc' => Url::absolute('/article.php', $query, $locale),
                'lastmod' => $article['lastmod'],
                'alternates' => [
                    Locale::RU => Url::absolute('/article.php', $query, Locale::RU),
                    Locale::EN => Url::absolute('/article.php', $query, Locale::EN),
                ],
            ];
        }
    }

    header('Content-Type: application/xml; charset=UTF-8');

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
        . ' xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

    foreach ($urls as $url) {
        echo "  <url>\n";
        echo '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>\n";
        if ($url['lastmod'] !== null && $url['lastmod'] !== '') {
            $timestamp = strtotime($url['lastmod']);
            if ($timestamp !== false) {
                echo '    <lastmod>' . date('Y-m-d', $timestamp) . "</lastmod>\n";
            }
        }
        foreach ($url['alternates'] as $hreflang => $href) {
            echo '    <xhtml:link rel="alternate" hreflang="'
                . htmlspecialchars($hreflang, ENT_XML1 | ENT_QUOTES, 'UTF-8')
                . '" href="'
                . htmlspecialchars($href, ENT_XML1 | ENT_QUOTES, 'UTF-8')
                . "\"/>\n";
        }
        echo '    <xhtml:link rel="alternate" hreflang="x-default" href="'
            . htmlspecialchars($url['alternates'][Locale::RU], ENT_XML1 | ENT_QUOTES, 'UTF-8')
            . "\"/>\n";
        echo "  </url>\n";
    }

    echo '</urlset>' . "\n";
} catch (Throwable $throwable) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo Config::isDebug() ? $throwable->getMessage() : 'Sitemap unavailable.';
}
