{if $pagination.total_items > 0 && $pagination.total_pages > 1}
<nav class="pagination" aria-label="{$t['a11y.pagination']}">
    {if $pagination.has_previous}
        <a class="pagination__nav" href="?slug={$category.slug|escape:'url'}&amp;sort={$sort|escape:'url'}&amp;page={$pagination.previous_page}" rel="prev" aria-label="{$t['a11y.prev_page']}">
            <span aria-hidden="true">←</span>
        </a>
    {else}
        <span class="pagination__nav pagination__nav--disabled" aria-disabled="true" aria-label="{$t['a11y.prev_page']}">
            <span aria-hidden="true">←</span>
        </span>
    {/if}

    <ol class="pagination__pages">
        {foreach from=$pagination.pages item=pageItem}
            <li>
                {if $pageItem == 'ellipsis'}
                    <span class="pagination__ellipsis" aria-hidden="true">…</span>
                {elseif $pageItem == $pagination.page}
                    <span class="pagination__page pagination__page--current" aria-current="page">{$pageItem}</span>
                {else}
                    <a class="pagination__page" href="?slug={$category.slug|escape:'url'}&amp;sort={$sort|escape:'url'}&amp;page={$pageItem}">{$pageItem}</a>
                {/if}
            </li>
        {/foreach}
    </ol>

    {if $pagination.has_next}
        <a class="pagination__nav" href="?slug={$category.slug|escape:'url'}&amp;sort={$sort|escape:'url'}&amp;page={$pagination.next_page}" rel="next" aria-label="{$t['a11y.next_page']}">
            <span aria-hidden="true">→</span>
        </a>
    {else}
        <span class="pagination__nav pagination__nav--disabled" aria-disabled="true" aria-label="{$t['a11y.next_page']}">
            <span aria-hidden="true">→</span>
        </span>
    {/if}
</nav>
{/if}
