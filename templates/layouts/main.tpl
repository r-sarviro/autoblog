<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$page_title|default:'Блог'} — {$app_name}</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    {include file='partials/header.tpl'}
    <main class="site-main">
        <div class="container">
            {block name='content'}{/block}
        </div>
    </main>
    {include file='partials/footer.tpl'}
</body>
</html>
