<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\Request;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function testReadsSafeStringAndDefaults(): void
    {
        $request = new Request([
            'slug' => '  news  ',
            'empty' => '   ',
        ]);

        self::assertSame('news', $request->string('slug'));
        self::assertNull($request->string('empty'));
        self::assertSame('fallback', $request->string('missing', 'fallback'));
    }

    public function testNormalizesPageAndSort(): void
    {
        $request = new Request([
            'page' => '-10',
            'sort' => 'views',
        ]);

        self::assertSame(1, $request->page());
        self::assertSame('views', $request->sort());

        $invalid = new Request([
            'page' => 'abc',
            'sort' => 'published_at',
        ]);

        self::assertSame(1, $invalid->page());
        self::assertSame('date', $invalid->sort());
    }
}
