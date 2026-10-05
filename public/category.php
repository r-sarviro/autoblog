<?php

declare(strict_types=1);

use App\Http\NotFoundException;
use App\Http\Response;
use App\Config\Config;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Service\CategoryPageService;
use App\Support\Request;
use App\View\SmartyView;

/** @var array{root: string, pdo: callable, view: callable} $app */
$app = require __DIR__ . '/bootstrap.php';

$view = ($app['view'])();
assert($view instanceof SmartyView);
$request = Request::fromGlobals();

try {
    $slug = $request->string('slug');

    if ($slug === null) {
        throw new NotFoundException('Категория не найдена.');
    }

    $pdo = ($app['pdo'])();
    $categories = new CategoryRepository($pdo);
    $response = new Response($view, $categories);
    $service = new CategoryPageService($categories, new ArticleRepository($pdo));
    $data = $service->getCategoryPage(
        $slug,
        $request->sort('sort'),
        $request->page('page')
    );

    $canonical = Config::appUrl() . '/category.php?slug=' . rawurlencode($data['category']['slug']);
    if ($data['sort'] !== 'date') {
        $canonical .= '&sort=' . rawurlencode($data['sort']);
    }
    if ((int) $data['pagination']['page'] > 1) {
        $canonical .= '&page=' . (int) $data['pagination']['page'];
    }

    $response->html('category.tpl', [
        'page_title' => $data['category']['name'],
        'page_description' => $data['category']['description'] ?? '',
        'canonical_url' => $canonical,
        'active_nav_slug' => $data['category']['slug'],
        'category' => $data['category'],
        'articles' => $data['articles'],
        'sort' => $data['sort'],
        'pagination' => $data['pagination'],
    ]);
} catch (NotFoundException $exception) {
    $response = $response ?? new Response($view);
    $response->notFound($exception->getMessage());
} catch (Throwable $throwable) {
    $response = $response ?? new Response($view);
    $response->serverError($throwable);
}
