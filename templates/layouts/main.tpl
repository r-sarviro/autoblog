<!DOCTYPE html>
<html lang="ru" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{$page_description|default:'Автомобильный блог: обзоры, новости и практические советы.'}">
    {if isset($robots) && $robots != ''}
    <meta name="robots" content="{$robots|escape:'html'}">
    {/if}
    <meta name="theme-color" content="#0d1217">
    <title>{$page_title|default:'Блог'} — {$app_name}</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    {if isset($canonical_url) && $canonical_url != ''}
    <link rel="canonical" href="{$canonical_url|escape:'html'}">
    {/if}
    {if isset($og)}
    <meta property="og:type" content="{$og.type|escape:'html'}">
    <meta property="og:title" content="{$og.title|escape:'html'}">
    <meta property="og:description" content="{$og.description|escape:'html'}">
    <meta property="og:url" content="{$og.url|escape:'html'}">
    <meta property="og:image" content="{$og.image|escape:'html'}">
    <meta property="og:locale" content="{$og.locale|default:'ru_RU'|escape:'html'}">
    <meta property="og:site_name" content="{$og.site_name|escape:'html'}">
    {/if}
    {if isset($twitter)}
    <meta name="twitter:card" content="{$twitter.card|escape:'html'}">
    <meta name="twitter:title" content="{$twitter.title|escape:'html'}">
    <meta name="twitter:description" content="{$twitter.description|escape:'html'}">
    <meta name="twitter:image" content="{$twitter.image|escape:'html'}">
    {/if}
    {if isset($json_ld)}
        {foreach from=$json_ld item=schema_json}
    <script type="application/ld+json">{$schema_json nofilter}</script>
        {/foreach}
    {/if}
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('theme');
                var theme = stored === 'light' || stored === 'dark' ? stored : 'dark';
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <a class="skip-link" href="#main-content">К содержанию</a>
    {include file='partials/header.tpl'}
    <main id="main-content" class="site-main{if isset($main_class)} {$main_class}{/if}" tabindex="-1">
        {block name='content'}{/block}
    </main>
    {include file='partials/footer.tpl'}
    <button type="button" class="to-top" data-to-top hidden aria-label="Наверх">
        <span aria-hidden="true">↑</span>
    </button>
    <script src="/assets/js/app.js" defer></script>
</body>
</html>
