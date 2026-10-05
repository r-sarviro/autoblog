<?php

declare(strict_types=1);

namespace Tests\Unit\Service;

use App\Http\NotFoundException;
use App\Repository\ArticleRepositoryInterface;
use App\Service\ArticlePageService;
use PHPUnit\Framework\TestCase;

final class ArticlePageServiceTest extends TestCase
{
    public function testThrowsWhenArticleMissing(): void
    {
        $repository = $this->createMock(ArticleRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('findBySlug')
            ->with('missing')
            ->willReturn(null);
        $repository->expects(self::never())->method('incrementViews');

        $service = new ArticlePageService($repository);

        $this->expectException(NotFoundException::class);
        $service->getArticlePage('missing');
    }

    public function testIncrementsViewsAndLoadsRelatedData(): void
    {
        $article = [
            'id' => 7,
            'slug' => 'demo-article',
            'title' => 'Demo',
            'views' => 10,
        ];
        $categories = [['id' => 1, 'slug' => 'news', 'name' => 'Новости']];
        $related = [['id' => 8, 'slug' => 'other', 'title' => 'Other']];

        $repository = $this->createMock(ArticleRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('findBySlug')
            ->with('demo-article')
            ->willReturn($article);
        $repository->expects(self::once())
            ->method('incrementViews')
            ->with(7);
        $repository->expects(self::once())
            ->method('categoriesForArticle')
            ->with(7)
            ->willReturn($categories);
        $repository->expects(self::once())
            ->method('relatedBySharedCategories')
            ->with(7, 3)
            ->willReturn($related);

        $service = new ArticlePageService($repository);
        $result = $service->getArticlePage('demo-article');

        self::assertSame(11, $result['article']['views']);
        self::assertSame($categories, $result['categories']);
        self::assertSame($related, $result['related']);
    }
}
