{extends file='layouts/main.tpl'}

{block name='content'}
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
                <span>{$featured.views} просмотров</span>
            </div>
            <a class="button" href="/article.php?slug={$featured.slug|escape:'url'}">Читать материал</a>
        </div>
    </div>
</section>
{else}
<section class="page-hero container reveal">
    <h1>{$app_name}</h1>
    <p class="lead">Обзоры, новости и практические советы об автомобилях.</p>
</section>
{/if}

<div class="container home-sections">
{if $sections|@count == 0}
    <p class="empty-state reveal">Пока нет опубликованных категорий.</p>
{else}
    {foreach from=$sections item=section}
        <section class="category-section reveal">
            <div class="section-heading">
                <div>
                    <h2>{$section.category.name}</h2>
                    <p>{$section.category.description}</p>
                </div>
                <a class="button button--ghost" href="/category.php?slug={$section.category.slug|escape:'url'}">Все статьи</a>
            </div>

            <div class="article-grid">
                {foreach from=$section.articles item=article}
                    {include file='partials/article-card.tpl' article=$article from_category_slug=$section.category.slug}
                {/foreach}
            </div>
        </section>
    {/foreach}
{/if}
</div>
{/block}
