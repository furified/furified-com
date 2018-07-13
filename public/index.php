<?php
declare(strict_types=1);

use Furified\Web\Engine\Exceptions\FurifiedException;
use Furified\Web\Engine\GlobalConfig;
use GuzzleHttp\Psr7\ServerRequest;

/**
 * Furified.com landing point
 *
 * This should be the only publicly accessible URI that executes code. All else
 * should be static resources.
 */

define('FURIFIED_ROOT', \dirname(__DIR__));
define('FURIFIED_WEB_ROOT', __DIR__);

require_once FURIFIED_ROOT . '/vendor/autoload.php';

try {
    $config = GlobalConfig::instance();
} catch (FurifiedException $ex) {
    \header('Content-Type: text/plain;charset=UTF-8');
    echo $ex->getMessage(), PHP_EOL;
    exit(1);
}

try {
    $config->getRouter()
        ->serve(ServerRequest::fromGlobals());
} catch (Throwable $ex) {
    if ($config->isDebug()) {
        \header('Content-Type: text/plain;charset=UTF-8');
        echo 'Uncaught ', \get_class($ex), ': ', PHP_EOL;
        echo $ex->getMessage(), PHP_EOL;
        echo PHP_EOL;
        echo $ex->getFile(), PHP_EOL;
        echo 'Line ', $ex->getLine(), PHP_EOL;
        echo $ex->getTraceAsString();
    }
}
