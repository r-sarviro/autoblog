<?php

declare(strict_types=1);

namespace App\Support;

use App\Config\Config;

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
     *   og: array<string, string>,
     *   twitter: array<string, string>,
     *   json_ld: list<string>
     * }
     */
    public static function home(): array
    {
        $title = 'Главная';
        $description = 'Обзоры, новости и практические советы об автомобилях.';
        $url = Config::appUrl() . '/';
        $image = self::absoluteUrl(self::DEFAULT_IMAGE);
        $siteName = Config::appName();

        return self::build(
            pageTitle: $title,
            pageDescription: $description,
            canonicalUrl: $url,
            robots: self::ROBOTS_INDEX,
            ogType: 'website',
            image: $image,
            jsonLd: [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => $siteName,
                    'url' => $url,
                    'description' => $description,
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
     *   og: array<string, string>,
     *   twitter: array<string, string>,
     *   json_ld: list<string>
     * }
     */
    public static function category(array $category, string $sort = 'date', int $page = 1): array
    {
        $slug = (string) ($category['slug'] ?? '');
        $title = (string) ($category['name'] ?? 'Категория');
        $description = (string) ($category['description'] ?? '');
        $canonical = Config::appUrl() . '/category.php?slug=' . rawurlencode($slug);

        if ($sort !== 'date') {
            $canonical .= '&sort=' . rawurlencode($sort);
        }
        if ($page > 1) {
            $canonical .= '&page=' . $page;
        }

        $image = self::absoluteUrl(self::DEFAULT_IMAGE);

        return self::build(
            pageTitle: $title,
            pageDescription: $description,
            canonicalUrl: $canonical,
            robots: self::ROBOTS_INDEX,
            ogType: 'website',
            image: $image,
            jsonLd: [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'CollectionPage',
                    'name' => $title,
                    'description' => $description,
                    'url' => $canonical,
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
     *   og: array<string, string>,
     *   twitter: array<string, string>,
     *   json_ld: list<string>
     * }
     */
    public static function article(array $article, ?array $contextCategory = null): array
    {
        $slug = (string) ($article['slug'] ?? '');
        $title = (string) ($article['title'] ?? 'Статья');
        $description = (string) ($article['description'] ?? '');
        $canonical = Config::appUrl() . '/article.php?slug=' . rawurlencode($slug);
        $image = self::absoluteUrl((string) ($article['image'] ?? self::DEFAULT_IMAGE));
        $published = self::toIso8601((string) ($article['published_at'] ?? ''));
        $modified = self::toIso8601((string) ($article['updated_at'] ?? $article['published_at'] ?? ''));

        $articleSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $title,
            'description' => $description,
            'image' => [$image],
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
                'name' => 'Главная',
                'item' => Config::appUrl() . '/',
            ],
        ];

        $position = 2;
        if ($contextCategory !== null) {
            $categorySlug = (string) ($contextCategory['slug'] ?? '');
            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => (string) ($contextCategory['name'] ?? ''),
                'item' => Config::appUrl() . '/category.php?slug=' . rawurlencode($categorySlug),
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
     *   og: array<string, string>,
     *   twitter: array<string, string>,
     *   json_ld: list<string>
     * }
     */
    public static function error(string $title, string $description): array
    {
        $image = self::absoluteUrl(self::DEFAULT_IMAGE);
        $url = Config::appUrl() . '/';

        $meta = self::build(
            pageTitle: $title,
            pageDescription: $description,
            canonicalUrl: $url,
            robots: self::ROBOTS_NOINDEX,
            ogType: 'website',
            image: $image,
            jsonLd: [],
        );

        unset($meta['canonical_url']);

        return $meta;
    }

    /**
     * @param list<array<string, mixed>> $jsonLd
     * @return array{
     *   page_title: string,
     *   page_description: string,
     *   canonical_url: string,
     *   robots: string,
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
        array $jsonLd,
    ): array {
        $siteName = Config::appName();

        return [
            'page_title' => $pageTitle,
            'page_description' => $pageDescription,
            'canonical_url' => $canonicalUrl,
            'robots' => $robots,
            'og' => [
                'type' => $ogType,
                'title' => $pageTitle,
                'description' => $pageDescription,
                'url' => $canonicalUrl,
                'image' => $image,
                'locale' => 'ru_RU',
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
