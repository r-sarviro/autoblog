{extends file='layouts/main.tpl'}

{block name='content'}
<div class="container">
    <section class="error-page reveal">
        <p class="eyebrow">{$t['error.eyebrow']}</p>
        <h1>404</h1>
        <p>{$message|default:$t['error.not_found']}</p>
        <a class="button" href="{$url_home}">{$t['error.back_home']}</a>
    </section>
</div>
{/block}
