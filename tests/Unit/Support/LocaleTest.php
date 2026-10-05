<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\Locale;
use PHPUnit\Framework\TestCase;

final class LocaleTest extends TestCase
{
    protected function tearDown(): void
    {
        Locale::reset();
        parent::tearDown();
    }

    public function testDetectsEnglishPrefix(): void
    {
        self::assertSame(Locale::EN, Locale::detectFromRequestUri('/en'));
        self::assertSame(Locale::EN, Locale::detectFromRequestUri('/en/'));
        self::assertSame(Locale::EN, Locale::detectFromRequestUri('/en/article.php?slug=x'));
    }

    public function testDetectsRussianDefault(): void
    {
        self::assertSame(Locale::RU, Locale::detectFromRequestUri('/'));
        self::assertSame(Locale::RU, Locale::detectFromRequestUri('/category.php?slug=news'));
    }

    public function testPrefixAndHtmlLang(): void
    {
        Locale::set(Locale::EN);
        self::assertSame('/en', Locale::prefix());
        self::assertSame('en', Locale::htmlLang());
        self::assertSame('en_US', Locale::ogLocale());

        Locale::set(Locale::RU);
        self::assertSame('', Locale::prefix());
        self::assertSame('ru', Locale::htmlLang());
        self::assertSame('ru_RU', Locale::ogLocale());
    }
}
