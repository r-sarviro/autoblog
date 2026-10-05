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
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    $pdo->exec('DELETE FROM article_category');
    $pdo->exec('DELETE FROM article_translations');
    $pdo->exec('DELETE FROM category_translations');
    $pdo->exec('DELETE FROM articles');
    $pdo->exec('DELETE FROM categories');
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

    $pdo->beginTransaction();

    $categoryStmt = $pdo->prepare(
        'INSERT INTO categories (id, slug, created_at, updated_at)
         VALUES (:id, :slug, :created_at, :updated_at)'
    );
    $categoryTranslationStmt = $pdo->prepare(
        'INSERT INTO category_translations (category_id, locale, name, description)
         VALUES (:category_id, :locale, :name, :description)'
    );

    foreach ($data['categories'] as $category) {
        $categoryStmt->execute([
            'id' => $category['id'],
            'slug' => $category['slug'],
            'created_at' => $category['created_at'],
            'updated_at' => $category['updated_at'],
        ]);

        /** @var array<string, array{name: string, description: string}> $translations */
        $translations = $category['translations'] ?? [];
        foreach ($translations as $locale => $fields) {
            $categoryTranslationStmt->execute([
                'category_id' => $category['id'],
                'locale' => $locale,
                'name' => $fields['name'],
                'description' => $fields['description'],
            ]);
        }
    }

    $articleStmt = $pdo->prepare(
        'INSERT INTO articles (
            id, image, views, published_at, slug, created_at, updated_at
         ) VALUES (
            :id, :image, :views, :published_at, :slug, :created_at, :updated_at
         )'
    );
    $articleTranslationStmt = $pdo->prepare(
        'INSERT INTO article_translations (article_id, locale, title, description, content)
         VALUES (:article_id, :locale, :title, :description, :content)'
    );
    $linkStmt = $pdo->prepare(
        'INSERT INTO article_category (article_id, category_id)
         VALUES (:article_id, :category_id)'
    );

    foreach ($data['articles'] as $article) {
        $articleStmt->execute([
            'id' => $article['id'],
            'image' => $article['image'],
            'views' => $article['views'],
            'published_at' => $article['published_at'],
            'slug' => $article['slug'],
            'created_at' => $article['created_at'],
            'updated_at' => $article['updated_at'],
        ]);

        /** @var array<string, array{title: string, description: string, content: string}> $translations */
        $translations = $article['translations'] ?? [];
        foreach ($translations as $locale => $fields) {
            $articleTranslationStmt->execute([
                'article_id' => $article['id'],
                'locale' => $locale,
                'title' => $fields['title'],
                'description' => $fields['description'],
                'content' => $fields['content'],
            ]);
        }

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
$categoryTrCount = (int) $pdo->query('SELECT COUNT(*) FROM category_translations')->fetchColumn();
$articleTrCount = (int) $pdo->query('SELECT COUNT(*) FROM article_translations')->fetchColumn();

fwrite(STDOUT, sprintf(
    "Seed completed: %d categories, %d articles, %d links, %d category translations, %d article translations.\n",
    $categoryCount,
    $articleCount,
    $linkCount,
    $categoryTrCount,
    $articleTrCount
));
