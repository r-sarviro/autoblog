<?php

declare(strict_types=1);

namespace App\Support;

use App\Config\Config;

final class Url
{
    /**
     * @param array<string, scalar|null> $query
     */
    public static function to(string $path, array $query = [], ?string $locale = null): string
    {
        $locale ??= Locale::current();
        $path = '/' . ltrim($path, '/');

        if ($path === '/') {
            $prefixed = Locale::prefix($locale) . '/';
        } else {
            $prefixed = Locale::prefix($locale) . $path;
        }

        $query = array_filter(
            $query,
            static fn (mixed $value): bool => $value !== null && $value !== ''
        );

        if ($query === []) {
            return $prefixed === '' ? '/' : $prefixed;
        }

        return $prefixed . '?' . http_build_query($query);
    }

    public static function home(?string $locale = null): string
    {
        return self::to('/', [], $locale);
    }

    public static function category(string $slug, string $sort = 'date', int $page = 1, ?string $locale = null): string
    {
        $query = ['slug' => $slug];
        if ($sort !== 'date') {
            $query['sort'] = $sort;
        }
        if ($page > 1) {
            $query['page'] = $page;
        }

        return self::to('/category.php', $query, $locale);
    }

    public static function article(string $slug, ?string $fromCategorySlug = null, ?string $locale = null): string
    {
        $query = ['slug' => $slug];
        if ($fromCategorySlug !== null && $fromCategorySlug !== '') {
            $query['from'] = $fromCategorySlug;
        }

        return self::to('/article.php', $query, $locale);
    }

    /**
     * Absolute URL for the same logical page in another locale.
     *
     * @param array<string, scalar|null> $query
     */
    public static function absolute(string $path, array $query = [], ?string $locale = null): string
    {
        return Config::appUrl() . self::to($path, $query, $locale);
    }

    /**
     * Switch current page path+query to another locale (relative).
     *
     * @param array<string, scalar|null> $query
     */
    public static function switchLocale(string $path, array $query, string $targetLocale): string
    {
        $path = self::stripLocalePrefix($path);

        return self::to($path === '' ? '/' : $path, $query, $targetLocale);
    }

    public static function stripLocalePrefix(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        if (preg_match('#^/en(/.*)?$#', $path, $matches) === 1) {
            $rest = $matches[1] ?? '/';

            return $rest === '' ? '/' : $rest;
        }

        return $path;
    }

    /**
     * Current script path relative to public/ without locale prefix.
     */
    public static function currentLogicalPath(): string
    {
        $script = (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php');
        $script = self::stripLocalePrefix($script);

        if ($script === '/index.php' || $script === '/') {
            return '/';
        }

        return $script;
    }
}
