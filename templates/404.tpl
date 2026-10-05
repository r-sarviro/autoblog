{extends file='layouts/main.tpl'}

{block name='content'}
<section class="error-page">
    <h1>404</h1>
    <p>{$message|default:'Страница не найдена'}</p>
    <a class="button" href="/">На главную</a>
</section>
{/block}
