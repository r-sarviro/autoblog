{extends file='layouts/main.tpl'}

{block name='content'}
<div class="container">
    {include file='partials/breadcrumbs.tpl'
        back_url='/'
        breadcrumbs=[
            ['label' => 'Главная', 'url' => '/'],
            ['label' => $category.name]
        ]
    }

    <section class="page-hero reveal">
        <h1>{$category.name}</h1>
        <p class="lead">{$category.description}</p>
    </section>

    <div class="toolbar reveal" role="group" aria-label="Сортировка статей">
        <span class="toolbar__label">Сортировка</span>
        <a
            class="chip{if $sort == 'date'} chip--active{/if}"
            href="?slug={$category.slug|escape:'url'}&amp;sort=date&amp;page=1"
            {if $sort == 'date'}aria-current="page"{/if}
        >По дате</a>
        <a
            class="chip{if $sort == 'views'} chip--active{/if}"
            href="?slug={$category.slug|escape:'url'}&amp;sort=views&amp;page=1"
            {if $sort == 'views'}aria-current="page"{/if}
        >По просмотрам</a>
    </div>

    {if $articles|@count == 0}
        <p class="empty-state reveal">В этой категории пока нет статей.</p>
    {else}
        <div class="article-grid reveal">
            {foreach from=$articles item=article}
                {include file='partials/article-card.tpl' article=$article from_category_slug=$category.slug}
            {/foreach}
        </div>
        {include file='partials/pagination.tpl'}
    {/if}
</div>
{/block}
