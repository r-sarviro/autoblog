<?php

declare(strict_types=1);

use App\Http\NotFoundException;
use App\Http\Response;
use App\Config\Config;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Service\HomeService;
use App\View\SmartyView;

/** @var array{root: string, pdo: callable, view: callable} $app */
$app = require __DIR__ . '/bootstrap.php';

$view = ($app['view'])();
assert($view instanceof SmartyView);

try {
    $pdo = ($app['pdo'])();
    $categories = new CategoryRepository($pdo);
    $response = new Response($view, $categories);
    $service = new HomeService($categories, new ArticleRepository($pdo));
    $data = $service->getHomeData();

    $response->html('home.tpl', [
        'page_title' => 'Главная',
        'page_description' => 'Обзоры, новости и практические советы об автомобилях.',
        'canonical_url' => Config::appUrl() . '/',
        'featured' => $data['featured'],
        'sections' => $data['sections'],
    ]);
} catch (NotFoundException $exception) {
    $response = $response ?? new Response($view);
    $response->notFound($exception->getMessage());
} catch (Throwable $throwable) {
    $response = $response ?? new Response($view);
    $response->serverError($throwable);
}
