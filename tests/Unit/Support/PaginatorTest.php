<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\Paginator;
use PHPUnit\Framework\TestCase;

final class PaginatorTest extends TestCase
{
    public function testCalculatesOffsetAndPages(): void
    {
        $paginator = Paginator::fromRequest(3, 10, 27);

        self::assertSame(3, $paginator->page());
        self::assertSame(10, $paginator->perPage());
        self::assertSame(27, $paginator->totalItems());
        self::assertSame(3, $paginator->totalPages());
        self::assertSame(20, $paginator->offset());
        self::assertTrue($paginator->hasPrevious());
        self::assertFalse($paginator->hasNext());
        self::assertSame(2, $paginator->previousPage());
        self::assertNull($paginator->nextPage());
    }

    public function testNormalizesInvalidPageNumbers(): void
    {
        $tooLow = Paginator::fromRequest(0, 10, 25);
        $tooHigh = Paginator::fromRequest(99, 10, 25);

        self::assertSame(1, $tooLow->page());
        self::assertSame(3, $tooHigh->page());
    }

    public function testHandlesEmptyResultSet(): void
    {
        $paginator = Paginator::fromRequest(5, 10, 0);

        self::assertSame(1, $paginator->page());
        self::assertSame(1, $paginator->totalPages());
        self::assertSame(0, $paginator->offset());
        self::assertFalse($paginator->hasPrevious());
        self::assertFalse($paginator->hasNext());
    }

    public function testPagesWithoutEllipsisForShortRanges(): void
    {
        $paginator = Paginator::fromRequest(2, 12, 36); // 3 pages

        self::assertSame([1, 2, 3], $paginator->pages());
    }

    public function testPagesUsesEllipsisForLongRanges(): void
    {
        $middle = Paginator::fromRequest(5, 10, 100); // 10 pages
        self::assertSame([1, 'ellipsis', 4, 5, 6, 'ellipsis', 10], $middle->pages());

        $start = Paginator::fromRequest(1, 10, 100);
        self::assertSame([1, 2, 'ellipsis', 10], $start->pages());

        $end = Paginator::fromRequest(10, 10, 100);
        self::assertSame([1, 'ellipsis', 9, 10], $end->pages());
    }
}
