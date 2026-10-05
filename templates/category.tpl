{extends file='layouts/main.tpl'}

{block name='content'}
<section class="page-hero">
    <p class="eyebrow"><a href="/">Главная</a> / {$category.name}</p>
    <h1>{$category.name}</h1>
    <p>{$category.description}</p>
</section>

<div class="toolbar">
    <span class="toolbar__label">Сортировка:</span>
    <a class="chip{if $sort == 'date'} chip--active{/if}" href="?slug={$category.slug|escape:'url'}&amp;sort=date&amp;page=1">По дате (новые → старые)</a>
    <a class="chip{if $sort == 'views'} chip--active{/if}" href="?slug={$category.slug|escape:'url'}&amp;sort=views&amp;page=1">По просмотрам (больше → меньше)</a>
</div>

{if $articles|@count == 0}
    <p class="empty-state">В этой категории пока нет статей.</p>
{else}
    <div class="article-grid">
        {foreach from=$articles item=article}
            {include file='partials/article-card.tpl' article=$article}
        {/foreach}
    </div>
    {include file='partials/pagination.tpl'}
{/if}
{/block}
