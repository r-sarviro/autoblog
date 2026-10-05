<?php

declare(strict_types=1);

namespace App\Support;

final class SortWhitelist
{
    private const MAP = [
        'date' => 'published_at',
        'views' => 'views',
    ];

    private const DEFAULT = 'date';

    public static function resolve(?string $sort): string
    {
        $key = $sort ?? self::DEFAULT;

        return array_key_exists($key, self::MAP) ? $key : self::DEFAULT;
    }

    public static function column(?string $sort): string
    {
        $key = self::resolve($sort);

        return self::MAP[$key];
    }

    /** @return list<string> */
    public static function allowedKeys(): array
    {
        return array_keys(self::MAP);
    }
}
