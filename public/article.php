<?php

declare(strict_types=1);

use App\Http\NotFoundException;
use App\Http\Response;
use App\I18n\Translator;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Service\ArticlePageService;
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
        throw new NotFoundException(Translator::get('error.article_not_found'));
    }

    $pdo = ($app['pdo'])();
    $categories = new CategoryRepository($pdo);
    $response = new Response($view, $categories);
    $service = new ArticlePageService(new ArticleRepository($pdo));
    $data = $service->getArticlePage($slug, $request->string('from'));

    $response->html('article.tpl', array_merge(
        SeoMeta::article($data['article'], $data['context_category']),
        [
            'active_nav_slug' => $data['context_category']['slug'] ?? null,
            'article' => $data['article'],
            'categories' => $data['categories'],
            'context_category' => $data['context_category'],
            'related' => $data['related'],
        ]
    ));
} catch (NotFoundException $exception) {
    $response = $response ?? new Response($view);
    $response->notFound($exception->getMessage());
} catch (Throwable $throwable) {
    $response = $response ?? new Response($view);
    $response->serverError($throwable);
}
