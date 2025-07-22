<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use Core\Session;

Session::start();

use Core\App;
use Core\Auth;
use Core\Db;
use Core\Request;
use Core\Router;
use App\Repositories\UserRepository;
use App\Repositories\FileRepository;
use App\Repositories\DirectoryRepository;
use \App\Repositories\PasswordResetRepository;

$app = new App();

$app->setService('db', fn() => new Db(
    $_ENV['DB_HOST'] ?? '127.0.0.1',
    $_ENV['DB_NAME'] ?? 'cloud_storage',
    $_ENV['DB_USER'] ?? 'root',
    $_ENV['DB_PASS'] ?? ''
));

//$app->setService('db', function () {
//    return new Db('localhost', 'cloud_storage', 'root', '');
//});
$app->setService('request', fn() => new Request());
$app->setService('router', fn() => new Router($app));
$app->setService('userRepository', fn() => new UserRepository($app));
$app->setService('fileRepository', fn() => new FileRepository($app));
$app->setService('directoryRepository', fn()=> new DirectoryRepository($app));
$app->setService('passwordResetRepository', fn()=> new PasswordResetRepository($app));

/** @var Router $router */
$app->auth = new Auth($app->getService('userRepository'));
$router = $app->getService('router');
$request = $app->getService('request');
$response = $router->processRequest($request);
$response->send();
