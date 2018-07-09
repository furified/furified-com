<?php
declare(strict_types=1);
namespace Furified\Web;

use FastRoute\RouteCollector;
use Furified\Web\RequestHandler\DefaultPage;

/* This script must return a callable */
return function(RouteCollector $r) {
    $r->addRoute('GET', '/', DefaultPage::class);
    $r->addRoute('GET', '', DefaultPage::class);

    /*
    $r->addGroup('/user', function (RouteCollector $r) {
        $r->addRoute('GET', '/{name}/{id:[0-9]+}', 'handler0');
        $r->addRoute('GET', '/{id:[0-9]+}', 'handler1');
        $r->addRoute('GET', '/{name}', 'handler2');
    });
    $r->addRoute('GET', '/user/{name}/{id:[0-9]+}', 'handler0');
    $r->addRoute('GET', '/user/{id:[0-9]+}', 'handler1');
    $r->addRoute('GET', '/user/{name}', 'handler2');
    */
};
