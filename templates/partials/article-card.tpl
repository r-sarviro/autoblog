{assign var=cardVariant value=$variant|default:'compact'}
{* Use raw & here — Smarty HTML-escapes href output to &amp; once. *}
{capture assign=articleHref}{$url_prefix}/article.php?slug={$article.slug|escape:'url'}{if isset($from_category_slug) && $from_category_slug != ''}&from={$from_category_slug|escape:'url'}{/if}{/capture}
<article class="article-card article-card--{$cardVariant}">
    <a class="article-card__media" href="{$articleHref}" tabindex="-1" aria-hidden="true">
        <img src="{$article.image}" alt="" loading="lazy" width="640" height="360">
    </a>
    <div class="article-card__body">
        <h3 class="article-card__title">
            <a href="{$articleHref}">{$article.title}</a>
        </h3>
        <p class="article-card__excerpt">{$article.description}</p>
        <div class="article-card__meta">
            <time datetime="{$article.published_at}">{$article.published_at|date_format:'%d.%m.%Y'}</time>
            <span>{$t['article.views_short']|replace:':count':$article.views}</span>
        </div>
    </div>
</article>
