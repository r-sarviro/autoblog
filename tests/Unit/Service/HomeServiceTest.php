<?php

declare(strict_types=1);

namespace Tests\Unit\Service;

use App\Repository\CategoryRepositoryInterface;
use App\Service\HomeService;
use PHPUnit\Framework\TestCase;

final class HomeServiceTest extends TestCase
{
    public function testReturnsSectionsFromRepository(): void
    {
        $sections = [
            [
                'category' => ['id' => 1, 'name' => 'Новости', 'slug' => 'news'],
                'articles' => [['id' => 1, 'slug' => 'a']],
            ],
        ];

        $repository = $this->createMock(CategoryRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('listWithLatestArticles')
            ->with(3)
            ->willReturn($sections);

        $service = new HomeService($repository);

        self::assertSame(['sections' => $sections], $service->getHomeData());
    }
}
