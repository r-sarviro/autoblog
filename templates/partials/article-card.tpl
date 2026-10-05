<article class="article-card">
    <a class="article-card__media" href="/article.php?slug={$article.slug|escape:'url'}">
        <img src="{$article.image}" alt="{$article.title}" loading="lazy" width="640" height="360">
    </a>
    <div class="article-card__body">
        <h3 class="article-card__title">
            <a href="/article.php?slug={$article.slug|escape:'url'}">{$article.title}</a>
        </h3>
        <p class="article-card__excerpt">{$article.description}</p>
        <div class="article-card__meta">
            <time datetime="{$article.published_at}">{$article.published_at|date_format:'%d.%m.%Y'}</time>
            <span>{$article.views} просм.</span>
        </div>
    </div>
</article>
