<?php

declare(strict_types=1);

namespace App\Support;

final class Request
{
    /** @param array<string, mixed> $query */
    public function __construct(private array $query = [])
    {
    }

    public static function fromGlobals(): self
    {
        return new self($_GET);
    }

    public function string(string $key, ?string $default = null): ?string
    {
        if (!array_key_exists($key, $this->query)) {
            return $default;
        }

        $value = $this->query[$key];

        if (!is_scalar($value)) {
            return $default;
        }

        $string = trim((string) $value);

        return $string === '' ? $default : $string;
    }

    public function int(string $key, int $default = 0): int
    {
        $value = $this->string($key);

        if ($value === null || !is_numeric($value)) {
            return $default;
        }

        return (int) $value;
    }

    public function page(string $key = 'page'): int
    {
        $page = $this->int($key, 1);

        return $page < 1 ? 1 : $page;
    }

    public function sort(string $key = 'sort'): string
    {
        return SortWhitelist::resolve($this->string($key));
    }
}
