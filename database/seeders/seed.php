<?php

declare(strict_types=1);

use App\Config\Env;
use App\Database\Database;

$projectRoot = dirname(__DIR__, 2);

require_once $projectRoot . '/vendor/autoload.php';

Env::load($projectRoot);

$dataFile = __DIR__ . '/data/seed_data.json';

if (!is_readable($dataFile)) {
    fwrite(STDERR, "Seed data file not found: {$dataFile}\n");
    exit(1);
}

try {
    /** @var array{categories: list<array<string, mixed>>, articles: list<array<string, mixed>>} $data */
    $data = json_decode(
        (string) file_get_contents($dataFile),
        true,
        512,
        JSON_THROW_ON_ERROR
    );
} catch (JsonException $exception) {
    fwrite(STDERR, 'Invalid seed JSON: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

$pdo = Database::connection();

try {
    // DELETE (not TRUNCATE): MySQL TRUNCATE/ALTER cause implicit commits and break PDO transactions.
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    $pdo->exec('DELETE FROM article_category');
    $pdo->exec('DELETE FROM articles');
    $pdo->exec('DELETE FROM categories');
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

    $pdo->beginTransaction();

    $categoryStmt = $pdo->prepare(
        'INSERT INTO categories (id, name, description, slug, created_at, updated_at)
         VALUES (:id, :name, :description, :slug, :created_at, :updated_at)'
    );

    foreach ($data['categories'] as $category) {
        $categoryStmt->execute([
            'id' => $category['id'],
            'name' => $category['name'],
            'description' => $category['description'],
            'slug' => $category['slug'],
            'created_at' => $category['created_at'],
            'updated_at' => $category['updated_at'],
        ]);
    }

    $articleStmt = $pdo->prepare(
        'INSERT INTO articles (
            id, image, title, description, content, views, published_at, slug, created_at, updated_at
         ) VALUES (
            :id, :image, :title, :description, :content, :views, :published_at, :slug, :created_at, :updated_at
         )'
    );

    $linkStmt = $pdo->prepare(
        'INSERT INTO article_category (article_id, category_id)
         VALUES (:article_id, :category_id)'
    );

    foreach ($data['articles'] as $article) {
        $articleStmt->execute([
            'id' => $article['id'],
            'image' => $article['image'],
            'title' => $article['title'],
            'description' => $article['description'],
            'content' => $article['content'],
            'views' => $article['views'],
            'published_at' => $article['published_at'],
            'slug' => $article['slug'],
            'created_at' => $article['created_at'],
            'updated_at' => $article['updated_at'],
        ]);

        /** @var list<int> $categoryIds */
        $categoryIds = $article['category_ids'];
        foreach ($categoryIds as $categoryId) {
            $linkStmt->execute([
                'article_id' => $article['id'],
                'category_id' => $categoryId,
            ]);
        }
    }

    $pdo->commit();

    $nextCategoryId = (int) $pdo->query('SELECT COALESCE(MAX(id), 0) + 1 FROM categories')->fetchColumn();
    $nextArticleId = (int) $pdo->query('SELECT COALESCE(MAX(id), 0) + 1 FROM articles')->fetchColumn();
    $pdo->exec('ALTER TABLE categories AUTO_INCREMENT = ' . $nextCategoryId);
    $pdo->exec('ALTER TABLE articles AUTO_INCREMENT = ' . $nextArticleId);
} catch (Throwable $throwable) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, 'Seed failed: ' . $throwable->getMessage() . PHP_EOL);
    exit(1);
}

$categoryCount = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$articleCount = (int) $pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn();
$linkCount = (int) $pdo->query('SELECT COUNT(*) FROM article_category')->fetchColumn();

fwrite(STDOUT, sprintf(
    "Seed completed: %d categories, %d articles, %d links.\n",
    $categoryCount,
    $articleCount,
    $linkCount
));
