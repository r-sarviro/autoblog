<?php

declare(strict_types=1);

use App\Config\Config;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;

/** @var array{root: string, pdo: callable, view: callable} $app */
$app = require __DIR__ . '/bootstrap.php';

try {
    $pdo = ($app['pdo'])();
    $categories = new CategoryRepository($pdo);
    $articles = new ArticleRepository($pdo);

    $baseUrl = Config::appUrl();
    $urls = [];

    $urls[] = [
        'loc' => $baseUrl . '/',
        'lastmod' => null,
    ];

    foreach ($categories->listAllForSitemap() as $category) {
        $urls[] = [
            'loc' => $baseUrl . '/category.php?slug=' . rawurlencode($category['slug']),
            'lastmod' => $category['lastmod'],
        ];
    }

    foreach ($articles->listAllForSitemap() as $article) {
        $urls[] = [
            'loc' => $baseUrl . '/article.php?slug=' . rawurlencode($article['slug']),
            'lastmod' => $article['lastmod'],
        ];
    }

    header('Content-Type: application/xml; charset=UTF-8');

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($urls as $url) {
        echo "  <url>\n";
        echo '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>\n";
        if ($url['lastmod'] !== null && $url['lastmod'] !== '') {
            $timestamp = strtotime($url['lastmod']);
            if ($timestamp !== false) {
                echo '    <lastmod>' . date('Y-m-d', $timestamp) . "</lastmod>\n";
            }
        }
        echo "  </url>\n";
    }

    echo '</urlset>' . "\n";
} catch (Throwable $throwable) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo Config::isDebug() ? $throwable->getMessage() : 'Sitemap unavailable.';
}
