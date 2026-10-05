<?php

declare(strict_types=1);

use App\Http\NotFoundException;
use App\Http\Response;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Service\CategoryPageService;
use App\Support\Request;
use App\View\SmartyView;
use Throwable;

/** @var array{root: string, pdo: callable, view: callable} $app */
$app = require __DIR__ . '/bootstrap.php';

$view = ($app['view'])();
assert($view instanceof SmartyView);
$response = new Response($view);
$request = Request::fromGlobals();

try {
    $slug = $request->string('slug');

    if ($slug === null) {
        throw new NotFoundException('Категория не найдена.');
    }

    $pdo = ($app['pdo'])();
    $service = new CategoryPageService(
        new CategoryRepository($pdo),
        new ArticleRepository($pdo)
    );
    $data = $service->getCategoryPage(
        $slug,
        $request->sort('sort'),
        $request->page('page')
    );

    $response->html('category.tpl', [
        'page_title' => $data['category']['name'],
        'category' => $data['category'],
        'articles' => $data['articles'],
        'sort' => $data['sort'],
        'pagination' => $data['pagination'],
    ]);
} catch (NotFoundException $exception) {
    $response->notFound($exception->getMessage());
} catch (Throwable $throwable) {
    $response->serverError($throwable);
}
