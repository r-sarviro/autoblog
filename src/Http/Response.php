<?php

declare(strict_types=1);

namespace App\Http;

use App\Config\Config;
use App\I18n\Translator;
use App\Repository\CategoryRepositoryInterface;
use App\Support\Locale;
use App\Support\SeoMeta;
use App\Support\Url;
use App\View\SmartyView;
use Throwable;

final class Response
{
    public function __construct(
        private SmartyView $view,
        private ?CategoryRepositoryInterface $categories = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function html(string $template, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Language: ' . Locale::htmlLang());

        $this->view->display($template, array_merge($this->sharedData(), $data));
    }

    public function notFound(?string $message = null): void
    {
        $message ??= Translator::get('error.not_found');

        $this->html('404.tpl', array_merge(
            SeoMeta::error(Translator::get('error.eyebrow') . ' 404', Translator::get('error.not_found_detail')),
            ['message' => $message]
        ), 404);
    }

    public function serverError(Throwable $throwable): void
    {
        $message = Config::isDebug()
            ? $throwable->getMessage()
            : Translator::get('error.server_message');

        $this->html('500.tpl', array_merge(
            SeoMeta::error(Translator::get('error.server'), Translator::get('error.server_detail')),
            ['message' => $message]
        ), 500);
    }

    /**
     * @return array<string, mixed>
     */
    private function sharedData(): array
    {
        $logicalPath = Url::currentLogicalPath();
        $query = $_GET;
        $localeAlt = Locale::alternateLocale();

        return [
            'app_name' => Config::appName(),
            'nav_categories' => $this->categories?->listNavCategories() ?? [],
            'locale' => Locale::current(),
            'locale_alt' => $localeAlt,
            'html_lang' => Locale::htmlLang(),
            'url_prefix' => Locale::prefix(),
            't' => Translator::all(),
            'url_home' => Url::home(),
            'lang_url_ru' => Url::switchLocale($logicalPath, $query, Locale::RU),
            'lang_url_en' => Url::switchLocale($logicalPath, $query, Locale::EN),
            'lang_url_alt' => Url::switchLocale($logicalPath, $query, $localeAlt),
            'lang_code_alt' => strtoupper($localeAlt),
            'lang_switch_label' => Translator::get(
                $localeAlt === Locale::EN ? 'nav.switch_to_en' : 'nav.switch_to_ru'
            ),
            'theme_to_light' => Translator::get('theme.to_light'),
            'theme_to_dark' => Translator::get('theme.to_dark'),
        ];
    }
}
