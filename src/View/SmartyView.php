<?php

declare(strict_types=1);

namespace App\View;

use Smarty;

final class SmartyView
{
    private Smarty $smarty;

    public function __construct(string $projectRoot)
    {
        $this->smarty = new Smarty();
        $this->smarty->setTemplateDir($projectRoot . '/templates');
        $this->smarty->setCompileDir($projectRoot . '/storage/cache/templates_c');
        $this->smarty->setCacheDir($projectRoot . '/storage/cache/smarty_cache');
        $this->smarty->setEscapeHtml(true);
        $this->smarty->caching = Smarty::CACHING_OFF;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        $this->smarty->clearAllAssign();
        $this->smarty->assign($data);

        return $this->smarty->fetch($template);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function display(string $template, array $data = []): void
    {
        echo $this->render($template, $data);
    }
}
