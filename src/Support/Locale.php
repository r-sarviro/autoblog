<?php

declare(strict_types=1);

namespace App\Support;

final class Locale
{
    public const RU = 'ru';
    public const EN = 'en';
    public const DEFAULT = self::RU;

    /** @var list<string> */
    public const SUPPORTED = [self::RU, self::EN];

    private static string $current = self::DEFAULT;

    public static function current(): string
    {
        return self::$current;
    }

    public static function set(string $locale): void
    {
        self::$current = self::normalize($locale);
    }

    public static function force(string $locale): void
    {
        self::set($locale);
    }

    public static function reset(): void
    {
        self::$current = self::DEFAULT;
    }

    public static function detectFromRequestUri(?string $requestUri = null): string
    {
        $uri = $requestUri ?? ($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH);
        if (!is_string($path)) {
            $path = '/';
        }

        if (preg_match('#^/en(/|$)#', $path) === 1) {
            return self::EN;
        }

        return self::DEFAULT;
    }

    public static function bootstrapFromRequest(): void
    {
        self::set(self::detectFromRequestUri());
    }

    public static function isDefault(?string $locale = null): bool
    {
        return ($locale ?? self::$current) === self::DEFAULT;
    }

    public static function prefix(?string $locale = null): string
    {
        $locale ??= self::$current;

        return $locale === self::EN ? '/en' : '';
    }

    public static function htmlLang(?string $locale = null): string
    {
        return match ($locale ?? self::$current) {
            self::EN => 'en',
            default => 'ru',
        };
    }

    public static function ogLocale(?string $locale = null): string
    {
        return match ($locale ?? self::$current) {
            self::EN => 'en_US',
            default => 'ru_RU',
        };
    }

    public static function alternateLocale(?string $locale = null): string
    {
        return ($locale ?? self::$current) === self::EN ? self::RU : self::EN;
    }

    public static function normalize(string $locale): string
    {
        $locale = strtolower(trim($locale));

        return in_array($locale, self::SUPPORTED, true) ? $locale : self::DEFAULT;
    }
}
