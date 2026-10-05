<?php

declare(strict_types=1);

namespace App\Http;

use App\Config\Config;
use App\View\SmartyView;
use Throwable;

final class Response
{
    public function __construct(private SmartyView $view)
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function html(string $template, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=UTF-8');
        $this->view->display($template, array_merge([
            'app_name' => Config::appName(),
        ], $data));
    }

    public function notFound(string $message = 'Страница не найдена'): void
    {
        $this->html('404.tpl', [
            'page_title' => '404',
            'message' => $message,
        ], 404);
    }

    public function serverError(Throwable $throwable): void
    {
        $message = Config::isDebug()
            ? $throwable->getMessage()
            : 'Произошла внутренняя ошибка. Попробуйте позже.';

        $this->html('500.tpl', [
            'page_title' => 'Ошибка',
            'message' => $message,
        ], 500);
    }
}
