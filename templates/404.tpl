{extends file='layouts/main.tpl'}

{block name='content'}
<div class="container">
    <section class="error-page reveal">
        <p class="eyebrow">Ошибка</p>
        <h1>404</h1>
        <p>{$message|default:'Страница не найдена'}</p>
        <a class="button" href="/">На главную</a>
    </section>
</div>
{/block}
