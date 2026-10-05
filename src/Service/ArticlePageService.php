<?php

declare(strict_types=1);

namespace App\Service;

use App\Http\NotFoundException;
use App\I18n\Translator;
use App\Repository\ArticleRepositoryInterface;

final class ArticlePageService
{
    public function __construct(private ArticleRepositoryInterface $articles)
    {
    }

    /**
     * @return array{
     *   article: array<string, mixed>,
     *   categories: list<array<string, mixed>>,
     *   context_category: array<string, mixed>|null,
     *   related: list<array<string, mixed>>
     * }
     */
    public function getArticlePage(string $slug, ?string $fromCategorySlug = null): array
    {
        $article = $this->articles->findBySlug($slug);

        if ($article === null) {
            throw new NotFoundException(Translator::get('error.article_not_found'));
        }

        $this->articles->incrementViews((int) $article['id']);
        $article['views'] = (int) $article['views'] + 1;
        $article['paragraphs'] = $this->splitParagraphs((string) ($article['content'] ?? ''));

        $categories = $this->articles->categoriesForArticle((int) $article['id']);

        return [
            'article' => $article,
            'categories' => $categories,
            'context_category' => $this->resolveContextCategory($categories, $fromCategorySlug),
            'related' => $this->articles->relatedBySharedCategories((int) $article['id'], 3),
        ];
    }

    /**
     * Prefer the category the user came from, if the article actually belongs to it.
     *
     * @param list<array<string, mixed>> $categories
     * @return array<string, mixed>|null
     */
    private function resolveContextCategory(array $categories, ?string $fromCategorySlug): ?array
    {
        if ($categories === []) {
            return null;
        }

        if ($fromCategorySlug !== null) {
            foreach ($categories as $category) {
                if (($category['slug'] ?? null) === $fromCategorySlug) {
                    return $category;
                }
            }
        }

        return $categories[0];
    }

    /**
     * @return list<string>
     */
    private function splitParagraphs(string $content): array
    {
        $parts = preg_split("/\R\s*\R/u", $content) ?: [];

        $paragraphs = [];
        foreach ($parts as $part) {
            $normalized = trim(preg_replace("/\R+/u", ' ', $part) ?? '');
            if ($normalized !== '') {
                $paragraphs[] = $normalized;
            }
        }

        return $paragraphs;
    }
}
