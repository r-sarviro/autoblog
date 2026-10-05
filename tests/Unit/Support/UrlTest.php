<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\Locale;
use App\Support\Url;
use PHPUnit\Framework\TestCase;

final class UrlTest extends TestCase
{
    protected function tearDown(): void
    {
        Locale::reset();
        parent::tearDown();
    }

    public function testBuildsLocalizedPaths(): void
    {
        Locale::set(Locale::RU);
        self::assertSame('/', Url::home());
        self::assertSame('/category.php?slug=news', Url::category('news'));
        self::assertSame(
            '/article.php?slug=test&from=news',
            Url::article('test', 'news')
        );

        Locale::set(Locale::EN);
        self::assertSame('/en/', Url::home());
        self::assertSame('/en/category.php?slug=news&sort=views&page=2', Url::category('news', 'views', 2));
        self::assertSame('/en/article.php?slug=test', Url::article('test'));
    }

    public function testSwitchLocaleKeepsQuery(): void
    {
        self::assertSame(
            '/en/category.php?slug=news&sort=views',
            Url::switchLocale('/category.php', ['slug' => 'news', 'sort' => 'views'], Locale::EN)
        );
        self::assertSame(
            '/category.php?slug=news',
            Url::switchLocale('/en/category.php', ['slug' => 'news'], Locale::RU)
        );
    }

    public function testStripLocalePrefix(): void
    {
        self::assertSame('/', Url::stripLocalePrefix('/en'));
        self::assertSame('/', Url::stripLocalePrefix('/en/'));
        self::assertSame('/article.php', Url::stripLocalePrefix('/en/article.php'));
        self::assertSame('/article.php', Url::stripLocalePrefix('/article.php'));
    }
}
