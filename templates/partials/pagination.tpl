{if $pagination.total_items > 0}
<nav class="pagination" aria-label="Пагинация">
    {if $pagination.has_previous}
        <a class="pagination__link" href="?slug={$category.slug|escape:'url'}&amp;sort={$sort|escape:'url'}&amp;page={$pagination.previous_page}">Назад</a>
    {else}
        <span class="pagination__link pagination__link--disabled">Назад</span>
    {/if}

    <span class="pagination__status">Страница {$pagination.page} из {$pagination.total_pages}</span>

    {if $pagination.has_next}
        <a class="pagination__link" href="?slug={$category.slug|escape:'url'}&amp;sort={$sort|escape:'url'}&amp;page={$pagination.next_page}">Вперёд</a>
    {else}
        <span class="pagination__link pagination__link--disabled">Вперёд</span>
    {/if}
</nav>
{/if}
