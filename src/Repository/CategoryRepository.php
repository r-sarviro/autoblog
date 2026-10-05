<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array<string, mixed>|null */
    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, description, slug, created_at, updated_at
             FROM categories
             WHERE slug = :slug
             LIMIT 1'
        );
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * @return list<array{id: int|string, name: string, slug: string}>
     */
    public function listNavCategories(): array
    {
        $stmt = $this->pdo->query(
            'SELECT c.id, c.name, c.slug
             FROM categories c
             WHERE EXISTS (
                 SELECT 1
                 FROM article_category ac
                 WHERE ac.category_id = c.id
             )
             ORDER BY c.name ASC'
        );

        return $stmt->fetchAll();
    }

    /**
     * Categories that have at least one article, each with up to 3 latest articles.
     *
     * @return list<array{category: array<string, mixed>, articles: list<array<string, mixed>>}>
     */
    public function listWithLatestArticles(int $articlesPerCategory = 3): array
    {
        $categoriesStmt = $this->pdo->query(
            'SELECT c.id, c.name, c.description, c.slug, c.created_at, c.updated_at
             FROM categories c
             WHERE EXISTS (
                 SELECT 1
                 FROM article_category ac
                 WHERE ac.category_id = c.id
             )
             ORDER BY c.name ASC'
        );
        $categories = $categoriesStmt->fetchAll();

        if ($categories === []) {
            return [];
        }

        $articlesStmt = $this->pdo->prepare(
            'SELECT ranked.id, ranked.image, ranked.title, ranked.description, ranked.content,
                    ranked.views, ranked.published_at, ranked.slug, ranked.category_id
             FROM (
                 SELECT a.id, a.image, a.title, a.description, a.content, a.views,
                        a.published_at, a.slug, ac.category_id,
                        ROW_NUMBER() OVER (
                            PARTITION BY ac.category_id
                            ORDER BY a.published_at DESC, a.id DESC
                        ) AS row_num
                 FROM articles a
                 INNER JOIN article_category ac ON ac.article_id = a.id
             ) AS ranked
             WHERE ranked.row_num <= :limit
             ORDER BY ranked.category_id ASC, ranked.published_at DESC, ranked.id DESC'
        );
        $articlesStmt->execute(['limit' => $articlesPerCategory]);
        $articleRows = $articlesStmt->fetchAll();

        $articlesByCategory = [];
        foreach ($articleRows as $article) {
            $categoryId = (int) $article['category_id'];
            unset($article['category_id']);
            $articlesByCategory[$categoryId][] = $article;
        }

        $result = [];
        foreach ($categories as $category) {
            $categoryId = (int) $category['id'];
            $result[] = [
                'category' => $category,
                'articles' => $articlesByCategory[$categoryId] ?? [],
            ];
        }

        return $result;
    }

    /**
     * @return list<array{slug: string, lastmod: string}>
     */
    public function listAllForSitemap(): array
    {
        $stmt = $this->pdo->query(
            'SELECT slug, updated_at AS lastmod
             FROM categories
             ORDER BY name ASC'
        );

        $rows = $stmt->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'slug' => (string) $row['slug'],
                'lastmod' => (string) $row['lastmod'],
            ];
        }

        return $result;
    }
}
