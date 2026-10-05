<?php

declare(strict_types=1);

use App\Config\Config;
use App\Config\Env;
use App\Database\Database;
use App\View\SmartyView;
use PDO;

$projectRoot = dirname(__DIR__);

require_once $projectRoot . '/vendor/autoload.php';

Env::load($projectRoot);

if (Config::isDebug()) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
}

return [
    'root' => $projectRoot,
    'pdo' => static fn (): PDO => Database::connection(),
    'view' => static fn (): SmartyView => new SmartyView($projectRoot),
    'config' => Config::class,
];
