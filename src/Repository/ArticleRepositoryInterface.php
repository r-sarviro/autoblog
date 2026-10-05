<?php

declare(strict_types=1);

namespace App\Repository;

interface ArticleRepositoryInterface
{
    /** @return array<string, mixed>|null */
    public function findBySlug(string $slug): ?array;

    public function countByCategoryId(int $categoryId): int;

    /**
     * @return list<array<string, mixed>>
     */
    public function listByCategoryId(int $categoryId, string $sort, int $limit, int $offset): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function categoriesForArticle(int $articleId): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function relatedBySharedCategories(int $articleId, int $limit = 3): array;

    public function incrementViews(int $articleId): void;
}
