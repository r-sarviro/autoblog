<?php

declare(strict_types=1);

namespace App\Repository;

interface CategoryRepositoryInterface
{
    /** @return array<string, mixed>|null */
    public function findBySlug(string $slug): ?array;

    /**
     * @return list<array{category: array<string, mixed>, articles: list<array<string, mixed>>}>
     */
    public function listWithLatestArticles(int $articlesPerCategory = 3): array;
}
