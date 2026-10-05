{if isset($breadcrumbs) && $breadcrumbs|@count > 0}
<nav class="breadcrumbs" aria-label="{$t['a11y.breadcrumbs']}">
    {if isset($back_url) && $back_url != ''}
        <a class="breadcrumbs__back" href="{$back_url}">{$t['a11y.back']}</a>
    {/if}
    <ol class="breadcrumbs__list">
        {foreach from=$breadcrumbs item=crumb name=crumbs}
            <li class="breadcrumbs__item">
                {if $smarty.foreach.crumbs.last || !isset($crumb.url)}
                    <span class="breadcrumbs__current" aria-current="page">{$crumb.label}</span>
                {else}
                    <a href="{$crumb.url}">{$crumb.label}</a>
                {/if}
            </li>
        {/foreach}
    </ol>
</nav>
{/if}
