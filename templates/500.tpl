{extends file='layouts/main.tpl'}

{block name='content'}
<div class="container">
    <section class="error-page reveal">
        <p class="eyebrow">Ошибка</p>
        <h1>500</h1>
        <p>{$message|default:'Внутренняя ошибка сервера'}</p>
        <a class="button" href="/">На главную</a>
    </section>
</div>
{/block}
