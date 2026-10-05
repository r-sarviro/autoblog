<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ArticleRepositoryInterface;
use App\Repository\CategoryRepositoryInterface;

final class HomeService
{
    public function __construct(
        private CategoryRepositoryInterface $categories,
        private ArticleRepositoryInterface $articles,
    ) {
    }

    /**
     * @return array{
     *   featured: array<string, mixed>|null,
     *   sections: list<array{category: array<string, mixed>, articles: list<array<string, mixed>>}>
     * }
     */
    public function getHomeData(): array
    {
        return [
            'featured' => $this->articles->findLatest(),
            'sections' => $this->categories->listWithLatestArticles(3),
        ];
    }
}
