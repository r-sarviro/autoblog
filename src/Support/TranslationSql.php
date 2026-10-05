<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Shared SQL fragments for localized content with RU fallback.
 */
final class TranslationSql
{
    public static function articleSelect(string $alias = 'a'): string
    {
        return sprintf(
            '%1$s.id, %1$s.image,
             COALESCE(at_pref.title, at_ru.title) AS title,
             COALESCE(at_pref.description, at_ru.description) AS description,
             COALESCE(at_pref.content, at_ru.content) AS content,
             %1$s.views, %1$s.published_at, %1$s.slug, %1$s.created_at, %1$s.updated_at',
            $alias
        );
    }

    public static function articleSelectList(string $alias = 'a'): string
    {
        return sprintf(
            '%1$s.id, %1$s.image,
             COALESCE(at_pref.title, at_ru.title) AS title,
             COALESCE(at_pref.description, at_ru.description) AS description,
             %1$s.views, %1$s.published_at, %1$s.slug',
            $alias
        );
    }

    public static function articleJoins(string $alias = 'a'): string
    {
        return sprintf(
            'LEFT JOIN article_translations at_pref
                ON at_pref.article_id = %1$s.id AND at_pref.locale = :locale
             LEFT JOIN article_translations at_ru
                ON at_ru.article_id = %1$s.id AND at_ru.locale = \'ru\'',
            $alias
        );
    }

    public static function categorySelect(string $alias = 'c'): string
    {
        return sprintf(
            '%1$s.id,
             COALESCE(ct_pref.name, ct_ru.name) AS name,
             COALESCE(ct_pref.description, ct_ru.description) AS description,
             %1$s.slug, %1$s.created_at, %1$s.updated_at',
            $alias
        );
    }

    public static function categorySelectNav(string $alias = 'c'): string
    {
        return sprintf(
            '%1$s.id, COALESCE(ct_pref.name, ct_ru.name) AS name, %1$s.slug',
            $alias
        );
    }

    public static function categoryJoins(string $alias = 'c'): string
    {
        return sprintf(
            'LEFT JOIN category_translations ct_pref
                ON ct_pref.category_id = %1$s.id AND ct_pref.locale = :locale
             LEFT JOIN category_translations ct_ru
                ON ct_ru.category_id = %1$s.id AND ct_ru.locale = \'ru\'',
            $alias
        );
    }
}
