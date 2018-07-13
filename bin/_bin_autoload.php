<?php

use Furified\Web\Engine\Exceptions\FurifiedException;
use Furified\Web\Engine\GlobalConfig;

define('FURIFIED_ROOT', \dirname(__DIR__));
define('FURIFIED_WEB_ROOT', __DIR__);

require_once FURIFIED_ROOT . '/vendor/autoload.php';

try {
    $config = GlobalConfig::instance();
} catch (FurifiedException $ex) {
    echo $ex->getMessage(), PHP_EOL;
    echo $ex->getTraceAsString(), PHP_EOL;
    exit(1);
}
