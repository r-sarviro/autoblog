<?php

declare(strict_types=1);

namespace App\Service;

use App\Http\NotFoundException;
use App\Repository\ArticleRepository;

final class ArticlePageService
{
    public function __construct(private ArticleRepository $articles)
    {
    }

    /**
     * @return array{
     *   article: array<string, mixed>,
     *   categories: list<array<string, mixed>>,
     *   related: list<array<string, mixed>>
     * }
     */
    public function getArticlePage(string $slug): array
    {
        $article = $this->articles->findBySlug($slug);

        if ($article === null) {
            throw new NotFoundException('Статья не найдена.');
        }

        $this->articles->incrementViews((int) $article['id']);
        $article['views'] = (int) $article['views'] + 1;

        return [
            'article' => $article,
            'categories' => $this->articles->categoriesForArticle((int) $article['id']),
            'related' => $this->articles->relatedBySharedCategories((int) $article['id'], 3),
        ];
    }
}
