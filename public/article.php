<?php

declare(strict_types=1);

use App\Http\NotFoundException;
use App\Http\Response;
use App\Repository\ArticleRepository;
use App\Service\ArticlePageService;
use App\Support\Request;
use App\View\SmartyView;

/** @var array{root: string, pdo: callable, view: callable} $app */
$app = require __DIR__ . '/bootstrap.php';

$view = ($app['view'])();
assert($view instanceof SmartyView);
$response = new Response($view);
$request = Request::fromGlobals();

try {
    $slug = $request->string('slug');

    if ($slug === null) {
        throw new NotFoundException('Статья не найдена.');
    }

    $pdo = ($app['pdo'])();
    $service = new ArticlePageService(new ArticleRepository($pdo));
    $data = $service->getArticlePage($slug);

    $response->html('article.tpl', [
        'page_title' => $data['article']['title'],
        'article' => $data['article'],
        'categories' => $data['categories'],
        'related' => $data['related'],
    ]);
} catch (NotFoundException $exception) {
    $response->notFound($exception->getMessage());
} catch (Throwable $throwable) {
    $response->serverError($throwable);
}
