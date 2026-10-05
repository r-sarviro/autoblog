{extends file='layouts/main.tpl'}

{block name='content'}
<section class="error-page">
    <h1>500</h1>
    <p>{$message|default:'Внутренняя ошибка сервера'}</p>
    <a class="button" href="/">На главную</a>
</section>
{/block}
