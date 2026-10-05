<?php

declare(strict_types=1);

namespace App\Repository;

interface CategoryRepositoryInterface
{
    /** @return array<string, mixed>|null */
    public function findBySlug(string $slug): ?array;

    /**
     * Categories that have at least one article (for navigation).
     *
     * @return list<array{id: int|string, name: string, slug: string}>
     */
    public function listNavCategories(): array;

    /**
     * @return list<array{category: array<string, mixed>, articles: list<array<string, mixed>>}>
     */
    public function listWithLatestArticles(int $articlesPerCategory = 3): array;
}
