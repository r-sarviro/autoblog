<!DOCTYPE html>
<html lang="ru" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{$page_description|default:'Автомобильный блог: обзоры, новости и практические советы.'}">
    <title>{$page_title|default:'Блог'} — {$app_name}</title>
    {if isset($canonical_url) && $canonical_url != ''}
    <link rel="canonical" href="{$canonical_url|escape:'html'}">
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
