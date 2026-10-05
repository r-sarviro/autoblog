{extends file='layouts/main.tpl'}

{block name='content'}
<section class="page-hero">
    <h1>Автомобильный блог</h1>
    <p>Обзоры, новости и практические советы об автомобилях.</p>
</section>

{if $sections|@count == 0}
    <p class="empty-state">Пока нет опубликованных категорий.</p>
{else}
    {foreach from=$sections item=section}
        <section class="category-section">
            <div class="section-heading">
                <div>
                    <h2>{$section.category.name}</h2>
                    <p>{$section.category.description}</p>
                </div>
                <a class="button" href="/category.php?slug={$section.category.slug|escape:'url'}">Все статьи</a>
            </div>

            <div class="article-grid">
                {foreach from=$section.articles item=article}
                    {include file='partials/article-card.tpl' article=$article}
                {/foreach}
            </div>
        </section>
    {/foreach}
{/if}
{/block}
