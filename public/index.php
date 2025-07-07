<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\App;
use Core\Db;
use Core\Request;
use Core\Router;

$app = new App();

$app->setService('db', function () {
    return new Db('localhost', 'cloud_storage', 'root', '');
});
$app->setService('request', fn() => new Request());
$app->setService('router', fn() => new Router($app));

// handle the request
/** @var Router $router */
$router   = $app->getService('router');
$request  = $app->getService('request');
$response = $router->processRequest($request);
$response->send();
