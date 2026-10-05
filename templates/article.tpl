{extends file='layouts/main.tpl'}

{block name='content'}
<article class="article-page container reveal">
    <nav class="breadcrumbs" aria-label="{$t['a11y.breadcrumbs']}">
        {if $context_category}
            <a class="breadcrumbs__back" href="{$url_prefix}/category.php?slug={$context_category.slug|escape:'url'}">{$t['a11y.back']}</a>
        {else}
            <a class="breadcrumbs__back" href="{$url_home}">{$t['a11y.back']}</a>
        {/if}
        <ol class="breadcrumbs__list">
            <li class="breadcrumbs__item"><a href="{$url_home}">{$t['nav.home']}</a></li>
            {if $context_category}
                <li class="breadcrumbs__item">
                    <a href="{$url_prefix}/category.php?slug={$context_category.slug|escape:'url'}">{$context_category.name}</a>
                </li>
            {/if}
            <li class="breadcrumbs__item">
                <span class="breadcrumbs__current" aria-current="page">{$article.title}</span>
            </li>
        </ol>
    </nav>

    <h1>{$article.title}</h1>
    <p class="lead">{$article.description}</p>

    <div class="article-meta">
        <time datetime="{$article.published_at}">{$article.published_at|date_format:'%d.%m.%Y %H:%M'}</time>
        <span>{$t['article.views']|replace:':count':$article.views}</span>
    </div>

    {if $categories|@count > 0}
        <ul class="tag-list">
            {foreach from=$categories item=category}
                <li><a href="{$url_prefix}/category.php?slug={$category.slug|escape:'url'}">{$category.name}</a></li>
            {/foreach}
        </ul>
    {/if}

    <figure class="article-cover">
        <img src="{$article.image}" alt="" width="1280" height="720">
    </figure>

    <div class="article-body">
        {foreach from=$article.paragraphs item=paragraph}
            <p>{$paragraph|escape}</p>
        {/foreach}
    </div>
</article>

{if $related|@count > 0}
<section class="related container reveal">
    <h2>{$t['article.related']}</h2>
    <div class="article-grid">
        {foreach from=$related item=article}
            {include file='partials/article-card.tpl' article=$article from_category_slug=$context_category.slug|default:''}
        {/foreach}
    </div>
</section>
{/if}
{/block}
