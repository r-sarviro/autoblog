<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\CategoryRepositoryInterface;

final class HomeService
{
    public function __construct(private CategoryRepositoryInterface $categories)
    {
    }

    /**
     * @return array{sections: list<array{category: array<string, mixed>, articles: list<array<string, mixed>>}>}
     */
    public function getHomeData(): array
    {
        return [
            'sections' => $this->categories->listWithLatestArticles(3),
        ];
    }
}
