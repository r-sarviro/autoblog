<?php

declare(strict_types=1);

namespace Tests\Unit\I18n;

use App\I18n\Translator;
use App\Support\Locale;
use PHPUnit\Framework\TestCase;

final class TranslatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Translator::setLangDir(dirname(__DIR__, 3) . '/lang');
        Locale::reset();
    }

    protected function tearDown(): void
    {
        Translator::reset();
        Locale::reset();
        parent::tearDown();
    }

    public function testReturnsRussianAndEnglishStrings(): void
    {
        Locale::set(Locale::RU);
        self::assertSame('Главная', Translator::get('nav.home'));

        Locale::set(Locale::EN);
        self::assertSame('Home', Translator::get('nav.home'));
    }

    public function testReplacesPlaceholders(): void
    {
        Locale::set(Locale::EN);
        self::assertSame('12 views', Translator::get('article.views', ['count' => 12]));
    }

    public function testFallsBackToRussianThenKey(): void
    {
        Locale::set(Locale::EN);
        self::assertSame('missing.key', Translator::get('missing.key'));
    }
}
