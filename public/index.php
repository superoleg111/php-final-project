<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Session;
Session::start();

use Core\App;
use App\Repositories\FileRepository;
use Core\Db;
use Core\Request;
use Core\Router;
use App\Repositories\UserRepository;

$app = new App();

$app->setService('db', function () {
    return new Db('localhost', 'cloud_storage', 'root', '');
});
$app->setService('request', fn() => new Request());
$app->setService('router', fn() => new Router($app));
$app->setService('userRepository', fn() => new UserRepository($app));
$app->setService('fileRepository', fn() => new FileRepository($app));

// handle the request
/** @var Router $router */
$router   = $app->getService('router');
$request  = $app->getService('request');
$response = $router->processRequest($request);
$response->send();
