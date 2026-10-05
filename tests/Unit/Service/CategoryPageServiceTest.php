<?php

declare(strict_types=1);

namespace Tests\Unit\Service;

use App\Http\NotFoundException;
use App\Repository\ArticleRepositoryInterface;
use App\Repository\CategoryRepositoryInterface;
use App\Service\CategoryPageService;
use PHPUnit\Framework\TestCase;

final class CategoryPageServiceTest extends TestCase
{
    public function testThrowsWhenCategoryMissing(): void
    {
        $categories = $this->createMock(CategoryRepositoryInterface::class);
        $articles = $this->createMock(ArticleRepositoryInterface::class);

        $categories->expects(self::once())
            ->method('findBySlug')
            ->with('archive')
            ->willReturn(null);
        $articles->expects(self::never())->method('countByCategoryId');

        putenv('PER_PAGE=10');
        $_ENV['PER_PAGE'] = '10';

        $service = new CategoryPageService($categories, $articles);

        $this->expectException(NotFoundException::class);
        $service->getCategoryPage('archive', 'date', 1);
    }

    public function testBuildsPaginatedCategoryPayload(): void
    {
        $categories = $this->createMock(CategoryRepositoryInterface::class);
        $articles = $this->createMock(ArticleRepositoryInterface::class);

        $category = [
            'id' => 1,
            'name' => 'Новости',
            'slug' => 'news',
            'description' => 'Desc',
        ];
        $items = [
            ['id' => 1, 'title' => 'A', 'slug' => 'a'],
            ['id' => 2, 'title' => 'B', 'slug' => 'b'],
        ];

        $categories->method('findBySlug')->with('news')->willReturn($category);
        $articles->method('countByCategoryId')->with(1)->willReturn(12);
        $articles->expects(self::once())
            ->method('listByCategoryId')
            ->with(1, 'views', 10, 10)
            ->willReturn($items);

        putenv('PER_PAGE=10');
        $_ENV['PER_PAGE'] = '10';

        $service = new CategoryPageService($categories, $articles);
        $result = $service->getCategoryPage('news', 'views', 2);

        self::assertSame($category, $result['category']);
        self::assertSame($items, $result['articles']);
        self::assertSame('views', $result['sort']);
        self::assertSame(2, $result['pagination']['page']);
        self::assertSame(12, $result['pagination']['total_items']);
        self::assertSame(2, $result['pagination']['total_pages']);
    }
}
