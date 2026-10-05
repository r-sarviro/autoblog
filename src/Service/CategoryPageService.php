<?php

declare(strict_types=1);

namespace App\Service;

use App\Config\Config;
use App\Http\NotFoundException;
use App\I18n\Translator;
use App\Repository\ArticleRepositoryInterface;
use App\Repository\CategoryRepositoryInterface;
use App\Support\Paginator;
use App\Support\SortWhitelist;

final class CategoryPageService
{
    public function __construct(
        private CategoryRepositoryInterface $categories,
        private ArticleRepositoryInterface $articles,
    ) {
    }

    /**
     * @return array{
     *   category: array<string, mixed>,
     *   articles: list<array<string, mixed>>,
     *   sort: string,
     *   pagination: array<string, int|bool|null>
     * }
     */
    public function getCategoryPage(string $slug, string $sort, int $page): array
    {
        $category = $this->categories->findBySlug($slug);

        if ($category === null) {
            throw new NotFoundException(Translator::get('error.category_not_found'));
        }

        $resolvedSort = SortWhitelist::resolve($sort);
        $total = $this->articles->countByCategoryId((int) $category['id']);
        $paginator = Paginator::fromRequest($page, Config::perPage(), $total);
        $items = $this->articles->listByCategoryId(
            (int) $category['id'],
            $resolvedSort,
            $paginator->perPage(),
            $paginator->offset()
        );

        return [
            'category' => $category,
            'articles' => $items,
            'sort' => $resolvedSort,
            'pagination' => $paginator->toArray(),
        ];
    }
}
