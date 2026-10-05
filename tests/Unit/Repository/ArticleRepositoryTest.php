<?php

declare(strict_types=1);

namespace Tests\Unit\Repository;

use App\Repository\ArticleRepository;
use App\Support\Locale;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

final class ArticleRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Locale::reset();
        parent::tearDown();
    }

    public function testIncrementViewsUsesAtomicUpdate(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('execute')
            ->with(['id' => 15])
            ->willReturn(true);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::callback(static function (string $sql): bool {
                return str_contains($sql, 'SET views = views + 1')
                    && str_contains($sql, 'WHERE id = :id');
            }))
            ->willReturn($statement);

        $repository = new ArticleRepository($pdo);
        $repository->incrementViews(15);
    }

    public function testListByCategoryUsesWhitelistedOrderColumn(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::exactly(4))->method('bindValue');
        $statement->expects(self::once())->method('execute')->willReturn(true);
        $statement->expects(self::once())->method('fetchAll')->willReturn([]);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::callback(static function (string $sql): bool {
                return str_contains($sql, 'ORDER BY a.views DESC')
                    && str_contains($sql, 'article_translations')
                    && !str_contains($sql, 'ORDER BY a.published_at DESC');
            }))
            ->willReturn($statement);

        $repository = new ArticleRepository($pdo);
        $repository->listByCategoryId(1, 'views', 10, 0);
    }

    public function testFindBySlugReturnsNullWhenMissing(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->method('execute')->willReturn(true);
        $statement->method('fetch')->willReturn(false);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($statement);

        $repository = new ArticleRepository($pdo);

        self::assertNull($repository->findBySlug('missing'));
    }

    public function testFindLatestReturnsNullWhenEmpty(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->method('execute')->willReturn(true);
        $statement->method('fetch')->willReturn(false);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::callback(static function (string $sql): bool {
                return str_contains($sql, 'ORDER BY a.published_at DESC')
                    && str_contains($sql, 'article_translations')
                    && str_contains($sql, 'LIMIT 1');
            }))
            ->willReturn($statement);

        $repository = new ArticleRepository($pdo);

        self::assertNull($repository->findLatest());
    }
}
