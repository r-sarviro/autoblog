<?php

declare(strict_types=1);

namespace App\Repository;

use App\Support\Locale;
use App\Support\SortWhitelist;
use App\Support\TranslationSql;
use PDO;

final class ArticleRepository implements ArticleRepositoryInterface
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
        $sql = 'SELECT ' . TranslationSql::articleSelect('a') . '
             FROM articles a
             ' . TranslationSql::articleJoins('a') . '
             WHERE a.slug = :slug
             LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['slug' => $slug, 'locale' => $this->locale()]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /** @return array<string, mixed>|null */
    public function findLatest(): ?array
    {
        $sql = 'SELECT ' . TranslationSql::articleSelect('a') . '
             FROM articles a
             ' . TranslationSql::articleJoins('a') . '
             ORDER BY a.published_at DESC, a.id DESC
             LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['locale' => $this->locale()]);
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
            'SELECT %s
             FROM articles a
             INNER JOIN article_category ac ON ac.article_id = a.id
             %s
             WHERE ac.category_id = :category_id
             ORDER BY a.%s DESC, a.id DESC
             LIMIT :limit OFFSET :offset',
            TranslationSql::articleSelectList('a'),
            TranslationSql::articleJoins('a'),
            $orderColumn
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue('locale', $this->locale());
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
        $sql = 'SELECT ' . TranslationSql::categorySelect('c') . '
             FROM categories c
             INNER JOIN article_category ac ON ac.category_id = c.id
             ' . TranslationSql::categoryJoins('c') . '
             WHERE ac.article_id = :article_id
             ORDER BY name ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['article_id' => $articleId, 'locale' => $this->locale()]);

        return $stmt->fetchAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function relatedBySharedCategories(int $articleId, int $limit = 3): array
    {
        $sql = 'SELECT a.id, a.image,
                        ANY_VALUE(COALESCE(at_pref.title, at_ru.title)) AS title,
                        ANY_VALUE(COALESCE(at_pref.description, at_ru.description)) AS description,
                        a.views, a.published_at, a.slug
             FROM articles a
             INNER JOIN article_category ac ON ac.article_id = a.id
             ' . TranslationSql::articleJoins('a') . '
             WHERE ac.category_id IN (
                 SELECT category_id
                 FROM article_category
                 WHERE article_id = :article_id
             )
               AND a.id <> :exclude_id
             GROUP BY a.id, a.image, a.views, a.published_at, a.slug
             ORDER BY COUNT(DISTINCT ac.category_id) DESC, a.published_at DESC, a.id DESC
             LIMIT :limit';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('article_id', $articleId, PDO::PARAM_INT);
        $stmt->bindValue('exclude_id', $articleId, PDO::PARAM_INT);
        $stmt->bindValue('locale', $this->locale());
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

    /**
     * @return list<array{slug: string, lastmod: string}>
     */
    public function listAllForSitemap(): array
    {
        $stmt = $this->pdo->query(
            'SELECT slug, COALESCE(updated_at, published_at) AS lastmod
             FROM articles
             ORDER BY published_at DESC, id DESC'
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
