<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\I18n\Translator;
use App\Support\Locale;
use App\Support\SeoMeta;
use PHPUnit\Framework\TestCase;

final class SeoMetaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_ENV['APP_URL'] = 'https://example.test';
        $_ENV['APP_NAME'] = 'AUTO-BLOG';
        putenv('APP_URL=https://example.test');
        putenv('APP_NAME=AUTO-BLOG');
        Translator::setLangDir(dirname(__DIR__, 3) . '/lang');
        Locale::reset();
    }

    protected function tearDown(): void
    {
        Translator::reset();
        Locale::reset();
        parent::tearDown();
    }

    public function testHomeBuildsCanonicalOgAndWebsiteJsonLd(): void
    {
        $meta = SeoMeta::home();

        self::assertSame('Главная', $meta['page_title']);
        self::assertSame('https://example.test/', $meta['canonical_url']);
        self::assertSame('index,follow', $meta['robots']);
        self::assertSame('website', $meta['og']['type']);
        self::assertSame('ru_RU', $meta['og']['locale']);
        self::assertSame('en_US', $meta['og']['locale_alternate']);
        self::assertSame('https://example.test/apple-touch-icon.png', $meta['og']['image']);
        self::assertSame('summary_large_image', $meta['twitter']['card']);
        self::assertCount(1, $meta['json_ld']);
        self::assertCount(3, $meta['hreflang']);
        self::assertSame('https://example.test/en/', $meta['hreflang'][1]['href']);

        $schema = json_decode($meta['json_ld'][0], true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('WebSite', $schema['@type']);
        self::assertSame('https://example.test/', $schema['url']);
    }

    public function testEnglishHomeUsesPrefix(): void
    {
        Locale::set(Locale::EN);
        $meta = SeoMeta::home();

        self::assertSame('Home', $meta['page_title']);
        self::assertSame('https://example.test/en/', $meta['canonical_url']);
        self::assertSame('en_US', $meta['og']['locale']);
    }

    public function testCategoryCanonicalIncludesNonDefaultSortAndPage(): void
    {
        $meta = SeoMeta::category(
            ['name' => 'Новости', 'slug' => 'news', 'description' => 'Свежие новости'],
            'views',
            2
        );

        self::assertSame(
            'https://example.test/category.php?slug=news&sort=views&page=2',
            $meta['canonical_url']
        );
        self::assertSame('Новости', $meta['page_title']);
        self::assertSame('CollectionPage', json_decode($meta['json_ld'][0], true)['@type']);
    }

    public function testArticleBuildsAbsoluteImageArticleSchemaAndBreadcrumbs(): void
    {
        $meta = SeoMeta::article(
            [
                'title' => 'Тестовая статья',
                'description' => 'Краткое описание',
                'slug' => 'test-article',
                'image' => '/assets/images/articles/adas-2025.jpg',
                'published_at' => '2025-09-01 12:00:00',
                'updated_at' => '2025-09-02 15:30:00',
            ],
            ['name' => 'Технологии', 'slug' => 'tech']
        );

        self::assertSame(
            'https://example.test/article.php?slug=test-article',
            $meta['canonical_url']
        );
        self::assertSame('article', $meta['og']['type']);
        self::assertSame(
            'https://example.test/assets/images/articles/adas-2025.jpg',
            $meta['og']['image']
        );
        self::assertCount(2, $meta['json_ld']);

        $articleSchema = json_decode($meta['json_ld'][0], true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('Article', $articleSchema['@type']);
        self::assertSame('Тестовая статья', $articleSchema['headline']);
        self::assertNotEmpty($articleSchema['datePublished']);
        self::assertNotEmpty($articleSchema['dateModified']);

        $breadcrumbs = json_decode($meta['json_ld'][1], true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('BreadcrumbList', $breadcrumbs['@type']);
        self::assertCount(3, $breadcrumbs['itemListElement']);
        self::assertSame('Технологии', $breadcrumbs['itemListElement'][1]['name']);
        self::assertSame('Главная', $breadcrumbs['itemListElement'][0]['name']);
    }

    public function testErrorIsNoindexAndOmitsCanonical(): void
    {
        $meta = SeoMeta::error('404', 'Не найдено');

        self::assertSame('noindex,nofollow', $meta['robots']);
        self::assertArrayNotHasKey('canonical_url', $meta);
        self::assertArrayNotHasKey('hreflang', $meta);
        self::assertSame([], $meta['json_ld']);
    }

    public function testAbsoluteUrlKeepsExternalUrls(): void
    {
        self::assertSame(
            'https://cdn.example/img.jpg',
            SeoMeta::absoluteUrl('https://cdn.example/img.jpg')
        );
    }
}
