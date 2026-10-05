<?php

declare(strict_types=1);

namespace Tests\Unit\Service;

use App\Repository\ArticleRepositoryInterface;
use App\Repository\CategoryRepositoryInterface;
use App\Service\HomeService;
use PHPUnit\Framework\TestCase;

final class HomeServiceTest extends TestCase
{
    public function testReturnsFeaturedAndSections(): void
    {
        $featured = [
            'id' => 9,
            'slug' => 'latest',
            'title' => 'Latest',
        ];
        $sections = [
            [
                'category' => ['id' => 1, 'name' => 'Новости', 'slug' => 'news'],
                'articles' => [['id' => 1, 'slug' => 'a']],
            ],
        ];

        $categories = $this->createMock(CategoryRepositoryInterface::class);
        $categories->expects(self::once())
            ->method('listWithLatestArticles')
            ->with(3)
            ->willReturn($sections);

        $articles = $this->createMock(ArticleRepositoryInterface::class);
        $articles->expects(self::once())
            ->method('findLatest')
            ->willReturn($featured);

        $service = new HomeService($categories, $articles);

        self::assertSame([
            'featured' => $featured,
            'sections' => $sections,
        ], $service->getHomeData());
    }

    public function testFeaturedCanBeNull(): void
    {
        $categories = $this->createMock(CategoryRepositoryInterface::class);
        $categories->method('listWithLatestArticles')->willReturn([]);

        $articles = $this->createMock(ArticleRepositoryInterface::class);
        $articles->method('findLatest')->willReturn(null);

        $service = new HomeService($categories, $articles);

        self::assertSame([
            'featured' => null,
            'sections' => [],
        ], $service->getHomeData());
    }
}
