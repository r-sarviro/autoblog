<?php

declare(strict_types=1);

namespace App\Repository;

use App\Support\Locale;
use App\Support\TranslationSql;
use PDO;

final class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(
        private PDO $pdo,
        private ?string $locale = null,
    ) {
    }

    private function locale(): string
    {
        return $this->locale ?? Locale::current();
    }

    /** @return array<string, mixed>|null */
    public function findBySlug(string $slug): ?array
    {
        $sql = 'SELECT ' . TranslationSql::categorySelect('c') . '
             FROM categories c
             ' . TranslationSql::categoryJoins('c') . '
             WHERE c.slug = :slug
             LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['slug' => $slug, 'locale' => $this->locale()]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * @return list<array{id: int|string, name: string, slug: string}>
     */
    public function listNavCategories(): array
    {
        $sql = 'SELECT ' . TranslationSql::categorySelectNav('c') . '
             FROM categories c
             ' . TranslationSql::categoryJoins('c') . '
             WHERE EXISTS (
                 SELECT 1
                 FROM article_category ac
                 WHERE ac.category_id = c.id
             )
             ORDER BY name ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['locale' => $this->locale()]);

        return $stmt->fetchAll();
    }

    /**
     * Categories that have at least one article, each with up to 3 latest articles.
     *
     * @return list<array{category: array<string, mixed>, articles: list<array<string, mixed>>}>
     */
    public function listWithLatestArticles(int $articlesPerCategory = 3): array
    {
        $categoriesSql = 'SELECT ' . TranslationSql::categorySelect('c') . '
             FROM categories c
             ' . TranslationSql::categoryJoins('c') . '
             WHERE EXISTS (
                 SELECT 1
                 FROM article_category ac
                 WHERE ac.category_id = c.id
             )
             ORDER BY name ASC';

        $categoriesStmt = $this->pdo->prepare($categoriesSql);
        $categoriesStmt->execute(['locale' => $this->locale()]);
        $categories = $categoriesStmt->fetchAll();

        if ($categories === []) {
            return [];
        }

        $articlesSql = 'SELECT ranked.id, ranked.image, ranked.title, ranked.description, ranked.content,
                    ranked.views, ranked.published_at, ranked.slug, ranked.category_id
             FROM (
                 SELECT a.id, a.image,
                        COALESCE(at_pref.title, at_ru.title) AS title,
                        COALESCE(at_pref.description, at_ru.description) AS description,
                        COALESCE(at_pref.content, at_ru.content) AS content,
                        a.views, a.published_at, a.slug, ac.category_id,
                        ROW_NUMBER() OVER (
                            PARTITION BY ac.category_id
                            ORDER BY a.published_at DESC, a.id DESC
                        ) AS row_num
                 FROM articles a
                 INNER JOIN article_category ac ON ac.article_id = a.id
                 ' . TranslationSql::articleJoins('a') . '
             ) AS ranked
             WHERE ranked.row_num <= :limit
             ORDER BY ranked.category_id ASC, ranked.published_at DESC, ranked.id DESC';

        $articlesStmt = $this->pdo->prepare($articlesSql);
        $articlesStmt->bindValue('limit', $articlesPerCategory, PDO::PARAM_INT);
        $articlesStmt->bindValue('locale', $this->locale());
        $articlesStmt->execute();
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
            'SELECT c.slug, c.updated_at AS lastmod
             FROM categories c
             LEFT JOIN category_translations ct_ru
                ON ct_ru.category_id = c.id AND ct_ru.locale = \'ru\'
             ORDER BY ct_ru.name ASC'
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
