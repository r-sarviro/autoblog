<?php

declare(strict_types=1);

namespace App\Support;

use App\Config\Config;
use App\I18n\Translator;

final class SeoMeta
{
    private const DEFAULT_IMAGE = '/apple-touch-icon.png';
    private const ROBOTS_INDEX = 'index,follow';
    private const ROBOTS_NOINDEX = 'noindex,nofollow';

    /**
     * @return array{
     *   page_title: string,
     *   page_description: string,
     *   canonical_url: string,
     *   robots: string,
     *   hreflang: list<array{lang: string, href: string}>,
     *   og: array<string, string>,
     *   twitter: array<string, string>,
     *   json_ld: list<string>
     * }
     */
    public static function home(): array
    {
        $title = Translator::get('seo.home_title');
        $description = Translator::get('seo.home_description');
        $url = Url::absolute('/', [], Locale::current());
        $image = self::absoluteUrl(self::DEFAULT_IMAGE);
        $siteName = Config::appName();

        return self::build(
            pageTitle: $title,
            pageDescription: $description,
            canonicalUrl: $url,
            robots: self::ROBOTS_INDEX,
            ogType: 'website',
            image: $image,
            hreflangPath: '/',
            hreflangQuery: [],
            jsonLd: [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => $siteName,
                    'url' => $url,
                    'description' => $description,
                    'inLanguage' => Locale::htmlLang(),
                ],
            ],
        );
    }

    /**
     * @param array<string, mixed> $category
     * @return array{
     *   page_title: string,
     *   page_description: string,
     *   canonical_url: string,
     *   robots: string,
     *   hreflang: list<array{lang: string, href: string}>,
     *   og: array<string, string>,
     *   twitter: array<string, string>,
     *   json_ld: list<string>
     * }
     */
    public static function category(array $category, string $sort = 'date', int $page = 1): array
    {
        $slug = (string) ($category['slug'] ?? '');
        $title = (string) ($category['name'] ?? Translator::get('seo.category_fallback'));
        $description = (string) ($category['description'] ?? '');
        $query = ['slug' => $slug];
        if ($sort !== 'date') {
            $query['sort'] = $sort;
        }
        if ($page > 1) {
            $query['page'] = $page;
        }

        $canonical = Url::absolute('/category.php', $query, Locale::current());
        $image = self::absoluteUrl(self::DEFAULT_IMAGE);

        return self::build(
            pageTitle: $title,
            pageDescription: $description,
            canonicalUrl: $canonical,
            robots: self::ROBOTS_INDEX,
            ogType: 'website',
            image: $image,
            hreflangPath: '/category.php',
            hreflangQuery: $query,
            jsonLd: [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'CollectionPage',
                    'name' => $title,
                    'description' => $description,
                    'url' => $canonical,
                    'inLanguage' => Locale::htmlLang(),
                ],
            ],
        );
    }

    /**
     * @param array<string, mixed> $article
     * @param array<string, mixed>|null $contextCategory
     * @return array{
     *   page_title: string,
     *   page_description: string,
     *   canonical_url: string,
     *   robots: string,
     *   hreflang: list<array{lang: string, href: string}>,
     *   og: array<string, string>,
     *   twitter: array<string, string>,
     *   json_ld: list<string>
     * }
     */
    public static function article(array $article, ?array $contextCategory = null): array
    {
        $slug = (string) ($article['slug'] ?? '');
        $title = (string) ($article['title'] ?? Translator::get('seo.article_fallback'));
        $description = (string) ($article['description'] ?? '');
        $query = ['slug' => $slug];
        $canonical = Url::absolute('/article.php', $query, Locale::current());
        $image = self::absoluteUrl((string) ($article['image'] ?? self::DEFAULT_IMAGE));
        $published = self::toIso8601((string) ($article['published_at'] ?? ''));
        $modified = self::toIso8601((string) ($article['updated_at'] ?? $article['published_at'] ?? ''));

        $articleSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $title,
            'description' => $description,
            'image' => [$image],
            'inLanguage' => Locale::htmlLang(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $canonical,
            ],
        ];

        if ($published !== null) {
            $articleSchema['datePublished'] = $published;
        }
        if ($modified !== null) {
            $articleSchema['dateModified'] = $modified;
        }

        $breadcrumbItems = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => Translator::get('nav.home'),
                'item' => Url::absolute('/', [], Locale::current()),
            ],
        ];

        $position = 2;
        if ($contextCategory !== null) {
            $categorySlug = (string) ($contextCategory['slug'] ?? '');
            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => (string) ($contextCategory['name'] ?? ''),
                'item' => Url::absolute('/category.php', ['slug' => $categorySlug], Locale::current()),
            ];
            $position++;
        }

        $breadcrumbItems[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $title,
            'item' => $canonical,
        ];

        return self::build(
            pageTitle: $title,
            pageDescription: $description,
            canonicalUrl: $canonical,
            robots: self::ROBOTS_INDEX,
            ogType: 'article',
            image: $image,
            hreflangPath: '/article.php',
            hreflangQuery: $query,
            jsonLd: [
                $articleSchema,
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => $breadcrumbItems,
                ],
            ],
        );
    }

    /**
     * @return array{
     *   page_title: string,
     *   page_description: string,
     *   robots: string,
     *   hreflang: list<array{lang: string, href: string}>,
     *   og: array<string, string>,
     *   twitter: array<string, string>,
     *   json_ld: list<string>
     * }
     */
    public static function error(string $title, string $description): array
    {
        $image = self::absoluteUrl(self::DEFAULT_IMAGE);
        $url = Url::absolute('/', [], Locale::current());

        $meta = self::build(
            pageTitle: $title,
            pageDescription: $description,
            canonicalUrl: $url,
            robots: self::ROBOTS_NOINDEX,
            ogType: 'website',
            image: $image,
            hreflangPath: '/',
            hreflangQuery: [],
            jsonLd: [],
        );

        unset($meta['canonical_url'], $meta['hreflang']);

        return $meta;
    }

    /**
     * @param list<array<string, mixed>> $jsonLd
     * @param array<string, scalar|null> $hreflangQuery
     * @return array{
     *   page_title: string,
     *   page_description: string,
     *   canonical_url: string,
     *   robots: string,
     *   hreflang: list<array{lang: string, href: string}>,
     *   og: array<string, string>,
     *   twitter: array<string, string>,
     *   json_ld: list<string>
     * }
     */
    private static function build(
        string $pageTitle,
        string $pageDescription,
        string $canonicalUrl,
        string $robots,
        string $ogType,
        string $image,
        string $hreflangPath,
        array $hreflangQuery,
        array $jsonLd,
    ): array {
        $siteName = Config::appName();
        $locale = Locale::current();
        $altLocale = Locale::alternateLocale();

        return [
            'page_title' => $pageTitle,
            'page_description' => $pageDescription,
            'canonical_url' => $canonicalUrl,
            'robots' => $robots,
            'hreflang' => [
                ['lang' => 'ru', 'href' => Url::absolute($hreflangPath, $hreflangQuery, Locale::RU)],
                ['lang' => 'en', 'href' => Url::absolute($hreflangPath, $hreflangQuery, Locale::EN)],
                ['lang' => 'x-default', 'href' => Url::absolute($hreflangPath, $hreflangQuery, Locale::RU)],
            ],
            'og' => [
                'type' => $ogType,
                'title' => $pageTitle,
                'description' => $pageDescription,
                'url' => $canonicalUrl,
                'image' => $image,
                'locale' => Locale::ogLocale($locale),
                'locale_alternate' => Locale::ogLocale($altLocale),
                'site_name' => $siteName,
            ],
            'twitter' => [
                'card' => 'summary_large_image',
                'title' => $pageTitle,
                'description' => $pageDescription,
                'image' => $image,
            ],
            'json_ld' => array_map(static fn (array $schema): string => self::encodeJsonLd($schema), $jsonLd),
        ];
    }

    public static function absoluteUrl(string $path): string
    {
        if ($path === '') {
            $path = self::DEFAULT_IMAGE;
        }

        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        return Config::appUrl() . '/' . ltrim($path, '/');
    }

    private static function toIso8601(string $datetime): ?string
    {
        if ($datetime === '') {
            return null;
        }

        $timestamp = strtotime($datetime);

        return $timestamp === false ? null : date('c', $timestamp);
    }

    /** @param array<string, mixed> $schema */
    private static function encodeJsonLd(array $schema): string
    {
        return json_encode(
            $schema,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_THROW_ON_ERROR
        );
    }
}
