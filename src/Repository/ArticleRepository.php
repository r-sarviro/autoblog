<?php

declare(strict_types=1);

namespace App\Repository;

use App\Support\SortWhitelist;
use PDO;

final class ArticleRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array<string, mixed>|null */
    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, image, title, description, content, views, published_at, slug, created_at, updated_at
             FROM articles
             WHERE slug = :slug
             LIMIT 1'
        );
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function countByCategoryId(int $categoryId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM articles a
             INNER JOIN article_category ac ON ac.article_id = a.id
             WHERE ac.category_id = :category_id'
        );
        $stmt->execute(['category_id' => $categoryId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listByCategoryId(int $categoryId, string $sort, int $limit, int $offset): array
    {
        $orderColumn = SortWhitelist::column($sort);

        $sql = sprintf(
            'SELECT a.id, a.image, a.title, a.description, a.content, a.views, a.published_at, a.slug
             FROM articles a
             INNER JOIN article_category ac ON ac.article_id = a.id
             WHERE ac.category_id = :category_id
             ORDER BY a.%s DESC, a.id DESC
             LIMIT :limit OFFSET :offset',
            $orderColumn
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function categoriesForArticle(int $articleId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.name, c.description, c.slug
             FROM categories c
             INNER JOIN article_category ac ON ac.category_id = c.id
             WHERE ac.article_id = :article_id
             ORDER BY c.name ASC'
        );
        $stmt->execute(['article_id' => $articleId]);

        return $stmt->fetchAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function relatedBySharedCategories(int $articleId, int $limit = 3): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.id, a.image, a.title, a.description, a.views, a.published_at, a.slug
             FROM articles a
             INNER JOIN article_category ac ON ac.article_id = a.id
             WHERE ac.category_id IN (
                 SELECT category_id
                 FROM article_category
                 WHERE article_id = :article_id
             )
               AND a.id <> :exclude_id
             GROUP BY a.id, a.image, a.title, a.description, a.views, a.published_at, a.slug
             ORDER BY COUNT(DISTINCT ac.category_id) DESC, a.published_at DESC, a.id DESC
             LIMIT :limit'
        );
        $stmt->bindValue('article_id', $articleId, PDO::PARAM_INT);
        $stmt->bindValue('exclude_id', $articleId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function incrementViews(int $articleId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE articles
             SET views = views + 1
             WHERE id = :id'
        );
        $stmt->execute(['id' => $articleId]);
    }
}
