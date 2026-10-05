<?php

declare(strict_types=1);

use App\Http\NotFoundException;
use App\Http\Response;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Service\CategoryPageService;
use App\Support\Request;
use App\Support\SeoMeta;
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

    $response->html('category.tpl', array_merge(
        SeoMeta::category(
            $data['category'],
            $data['sort'],
            (int) $data['pagination']['page']
        ),
        [
            'active_nav_slug' => $data['category']['slug'],
            'category' => $data['category'],
            'articles' => $data['articles'],
            'sort' => $data['sort'],
            'pagination' => $data['pagination'],
        ]
    ));
} catch (NotFoundException $exception) {
    $response = $response ?? new Response($view);
    $response->notFound($exception->getMessage());
} catch (Throwable $throwable) {
    $response = $response ?? new Response($view);
    $response->serverError($throwable);
}
