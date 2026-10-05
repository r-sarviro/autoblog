<?php

declare(strict_types=1);

use App\Config\Config;

/** @var array{root: string, pdo: callable, view: callable} $app */
$app = require __DIR__ . '/bootstrap.php';

header('Content-Type: text/plain; charset=UTF-8');

$sitemapUrl = Config::appUrl() . '/sitemap.php';

echo "User-agent: *\n";
echo "Allow: /\n";
echo "\n";
echo 'Sitemap: ' . $sitemapUrl . "\n";
