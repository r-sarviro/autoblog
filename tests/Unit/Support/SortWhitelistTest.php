<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\SortWhitelist;
use PHPUnit\Framework\TestCase;

final class SortWhitelistTest extends TestCase
{
    public function testResolvesKnownSortKeys(): void
    {
        self::assertSame('date', SortWhitelist::resolve('date'));
        self::assertSame('views', SortWhitelist::resolve('views'));
    }

    public function testFallsBackToDateForUnknownOrEmptyValues(): void
    {
        self::assertSame('date', SortWhitelist::resolve(null));
        self::assertSame('date', SortWhitelist::resolve('hack; DROP TABLE'));
        self::assertSame('date', SortWhitelist::resolve('published_at'));
    }

    public function testMapsKeysToSafeSqlColumns(): void
    {
        self::assertSame('published_at', SortWhitelist::column('date'));
        self::assertSame('views', SortWhitelist::column('views'));
        self::assertSame('published_at', SortWhitelist::column('unknown'));
    }

    public function testExposesAllowedKeys(): void
    {
        self::assertSame(['date', 'views'], SortWhitelist::allowedKeys());
    }
}
