<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="{$url_home}">{$app_name}</a>

        <nav class="nav" id="site-nav" data-nav aria-label="{$t['nav.main']}">
            <a href="{$url_home}"{if $page_title|default:'' == $t['seo.home_title']} aria-current="page"{/if}>{$t['nav.home']}</a>
            {foreach from=$nav_categories|default:[] item=navCategory}
                <a
                    href="{$url_prefix}/category.php?slug={$navCategory.slug|escape:'url'}"
                    {if isset($active_nav_slug) && $active_nav_slug == $navCategory.slug} aria-current="page"{/if}
                >{$navCategory.name}</a>
            {/foreach}
        </nav>

        <div class="header-controls">
            <button
                type="button"
                class="nav-toggle"
                data-nav-toggle
                aria-expanded="false"
                aria-controls="site-nav"
                aria-label="{$t['nav.open_menu']}"
                data-label-open="{$t['nav.open_menu']}"
                data-label-close="{$t['nav.close_menu']}"
            >
                <span class="nav-toggle__bar" aria-hidden="true"></span>
                <span class="nav-toggle__bar" aria-hidden="true"></span>
                <span class="nav-toggle__bar" aria-hidden="true"></span>
            </button>

            <button
                type="button"
                class="theme-toggle"
                data-theme-toggle
                aria-pressed="true"
                aria-label="{$theme_to_light}"
                title="{$t['theme.toggle']}"
            >
                <span class="theme-toggle__moon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 14.3A8.5 8.5 0 0 1 9.7 3 7 7 0 1 0 21 14.3z"></path>
                    </svg>
                </span>
                <span class="theme-toggle__sun" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="4"></circle>
                        <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"></path>
                    </svg>
                </span>
            </button>

            <a
                class="lang-toggle"
                href="{$lang_url_alt}"
                hreflang="{$locale_alt}"
                lang="{$locale_alt}"
                aria-label="{$lang_switch_label}"
                title="{$lang_switch_label}"
            >
                <span class="lang-toggle__code" aria-hidden="true">{$lang_code_alt}</span>
            </a>
        </div>
    </div>
</header>
