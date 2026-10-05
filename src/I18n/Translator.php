<?php

declare(strict_types=1);

namespace App\I18n;

use App\Support\Locale;

final class Translator
{
    /** @var array<string, array<string, string>> */
    private static array $catalogs = [];

    private static ?string $langDir = null;

    public static function setLangDir(string $dir): void
    {
        self::$langDir = rtrim($dir, '/');
        self::$catalogs = [];
    }

    public static function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale = Locale::normalize($locale ?? Locale::current());
        $catalog = self::catalog($locale);
        $text = $catalog[$key] ?? null;

        if ($text === null && $locale !== Locale::DEFAULT) {
            $text = self::catalog(Locale::DEFAULT)[$key] ?? null;
        }

        if ($text === null) {
            $text = $key;
        }

        foreach ($replace as $name => $value) {
            $text = str_replace(':' . $name, (string) $value, $text);
        }

        return $text;
    }

    /**
     * @return array<string, string>
     */
    public static function all(?string $locale = null): array
    {
        return self::catalog(Locale::normalize($locale ?? Locale::current()));
    }

    public static function reset(): void
    {
        self::$catalogs = [];
        self::$langDir = null;
    }

    /**
     * @return array<string, string>
     */
    private static function catalog(string $locale): array
    {
        if (isset(self::$catalogs[$locale])) {
            return self::$catalogs[$locale];
        }

        $dir = self::$langDir ?? dirname(__DIR__, 2) . '/lang';
        $file = $dir . '/' . $locale . '.php';

        if (!is_readable($file)) {
            self::$catalogs[$locale] = [];

            return self::$catalogs[$locale];
        }

        /** @var mixed $loaded */
        $loaded = require $file;
        self::$catalogs[$locale] = is_array($loaded) ? $loaded : [];

        return self::$catalogs[$locale];
    }
}
