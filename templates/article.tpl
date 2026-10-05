{extends file='layouts/main.tpl'}

{block name='content'}
<article class="article-page">
    <p class="eyebrow"><a href="/">Главная</a></p>
    <h1>{$article.title}</h1>
    <p class="lead">{$article.description}</p>

    <div class="article-meta">
        <time datetime="{$article.published_at}">{$article.published_at|date_format:'%d.%m.%Y %H:%M'}</time>
        <span>{$article.views} просмотров</span>
    </div>

    {if $categories|@count > 0}
        <ul class="tag-list">
            {foreach from=$categories item=category}
                <li><a href="/category.php?slug={$category.slug|escape:'url'}">{$category.name}</a></li>
            {/foreach}
        </ul>
    {/if}

    <figure class="article-cover">
        <img src="{$article.image}" alt="{$article.title}" width="1280" height="720">
    </figure>

    <div class="article-body">
        {$article.content|escape|nl2br nofilter}
    </div>
</article>

{if $related|@count > 0}
<section class="related">
    <h2>Похожие статьи</h2>
    <div class="article-grid">
        {foreach from=$related item=article}
            {include file='partials/article-card.tpl' article=$article}
        {/foreach}
    </div>
</section>
{/if}
{/block}
