<?php

declare(strict_types=1);

namespace App\Config;

final class Config
{
    /** @return array{host: string, port: int, database: string, username: string, password: string} */
    public static function database(): array
    {
        return [
            'host' => Env::get('DB_HOST', '127.0.0.1') ?? '127.0.0.1',
            'port' => Env::getInt('DB_PORT', 3306),
            'database' => Env::get('DB_DATABASE', 'auto_blog') ?? 'auto_blog',
            'username' => Env::get('DB_USERNAME', 'root') ?? 'root',
            'password' => Env::get('DB_PASSWORD', '') ?? '',
        ];
    }

    public static function perPage(): int
    {
        $perPage = Env::getInt('PER_PAGE', 10);

        return $perPage > 0 ? $perPage : 10;
    }

    public static function isDebug(): bool
    {
        return Env::getBool('APP_DEBUG', false);
    }

    public static function appName(): string
    {
        return Env::get('APP_NAME', 'AUTO-BLOG') ?? 'AUTO-BLOG';
    }

    public static function appEnv(): string
    {
        return Env::get('APP_ENV', 'local') ?? 'local';
    }
}
