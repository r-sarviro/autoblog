<?php

declare(strict_types=1);

use App\Http\NotFoundException;
use App\Http\Response;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Service\HomeService;
use App\View\SmartyView;
use Throwable;

/** @var array{root: string, pdo: callable, view: callable} $app */
$app = require __DIR__ . '/bootstrap.php';

$view = ($app['view'])();
assert($view instanceof SmartyView);
$response = new Response($view);

try {
    $pdo = ($app['pdo'])();
    $service = new HomeService(new CategoryRepository($pdo));
    $data = $service->getHomeData();

    $response->html('home.tpl', [
        'page_title' => 'Главная',
        'sections' => $data['sections'],
    ]);
} catch (NotFoundException $exception) {
    $response->notFound($exception->getMessage());
} catch (Throwable $throwable) {
    $response->serverError($throwable);
}
