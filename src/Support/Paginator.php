<?php

declare(strict_types=1);

namespace App\Support;

final class Paginator
{
    private int $page;
    private int $perPage;
    private int $totalItems;
    private int $totalPages;

    public function __construct(int $page, int $perPage, int $totalItems)
    {
        $this->perPage = max(1, $perPage);
        $this->totalItems = max(0, $totalItems);
        $this->totalPages = max(1, (int) ceil($this->totalItems / $this->perPage));
        $this->page = max(1, min($page > 0 ? $page : 1, $this->totalPages));

        if ($this->totalItems === 0) {
            $this->totalPages = 1;
            $this->page = 1;
        }
    }

    public static function fromRequest(int $page, int $perPage, int $totalItems): self
    {
        return new self($page < 1 ? 1 : $page, $perPage, $totalItems);
    }

    public function page(): int
    {
        return $this->page;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function totalItems(): int
    {
        return $this->totalItems;
    }

    public function totalPages(): int
    {
        return $this->totalPages;
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    public function hasPrevious(): bool
    {
        return $this->page > 1;
    }

    public function hasNext(): bool
    {
        return $this->page < $this->totalPages;
    }

    public function previousPage(): ?int
    {
        return $this->hasPrevious() ? $this->page - 1 : null;
    }

    public function nextPage(): ?int
    {
        return $this->hasNext() ? $this->page + 1 : null;
    }

    /** @return array<string, int|bool|null> */
    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'per_page' => $this->perPage,
            'total_items' => $this->totalItems,
            'total_pages' => $this->totalPages,
            'has_previous' => $this->hasPrevious(),
            'has_next' => $this->hasNext(),
            'previous_page' => $this->previousPage(),
            'next_page' => $this->nextPage(),
        ];
    }
}
