{extends file='layouts/main.tpl'}

{block name='content'}
{if $sections|@count == 0 && !$featured}
<div class="container">
    <section class="error-page reveal">
        <img class="error-page__icon" src="/favicon.svg" alt="" width="48" height="48">
        <h1>{$t['home.empty_title']}</h1>
        <p>{$t['home.empty_categories']}</p>
    </section>
</div>
{else}
{if $featured}
<section class="featured-hero reveal">
    <div class="featured-hero__media">
        <img
            src="{$featured.image}"
            alt=""
            width="1600"
            height="900"
            data-hero-image
            class="featured-hero__image"
        >
    </div>
    <div class="featured-hero__overlay">
        <div class="container featured-hero__content">
            <p class="eyebrow">{$app_name}</p>
            <h1 class="featured-hero__title">{$featured.title}</h1>
            <p class="featured-hero__lead">{$featured.description}</p>
            <div class="featured-hero__meta">
                <time datetime="{$featured.published_at}">{$featured.published_at|date_format:'%d.%m.%Y'}</time>
                <span>{$t['home.views']|replace:':count':$featured.views}</span>
            </div>
            <a class="button" href="{$url_prefix}/article.php?slug={$featured.slug|escape:'url'}">{$t['home.read']}</a>
        </div>
    </div>
</section>
{else}
<section class="page-hero container reveal">
    <h1>{$app_name}</h1>
    <p class="lead">{$t['home.lead']}</p>
</section>
{/if}

<div class="container home-sections">
    {foreach from=$sections item=section}
        <section class="category-section reveal">
            <div class="section-heading">
                <div>
                    <h2>{$section.category.name}</h2>
                    <p>{$section.category.description}</p>
                </div>
                <a class="button button--ghost" href="{$url_prefix}/category.php?slug={$section.category.slug|escape:'url'}">{$t['home.all_articles']}</a>
            </div>

            <div class="article-grid">
                {foreach from=$section.articles item=article}
                    {include file='partials/article-card.tpl' article=$article from_category_slug=$section.category.slug}
                {/foreach}
            </div>
        </section>
    {/foreach}
</div>
{/if}
{/block}
