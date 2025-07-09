<?php

namespace Core;

use App\Controllers\HomeController;
use App\Controllers\UserController;
use Core\Response;
use Core\Request;

class Router
{
    private array $routes = [];
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;

        $this->routes = [
            'GET' => [
                '/' => ['App\\Controllers\\HomeController', 'index'],
                '/ping' => ['App\\Controllers\\TestController', 'ping'],
                '/users/me' => ['App\\Controllers\\UserController', 'me'],
                '/users/list' => ['App\\Controllers\\UserController', 'list'],
                '/files/list' => ['App\\Controllers\\FileController', 'list'],
                '/files/download' => ['App\\Controllers\\FileController', 'download'],
                '/public/download' => ['App\\Controllers\\FileController', 'publicDownload'],
                '/files/shared' => ['App\\Controllers\\FileController', 'shared'],
            ],
            'POST' => [
                '/users/login' => ['App\\Controllers\\UserController', 'login'],
                '/users/logout' => ['App\\Controllers\\UserController', 'logout'],
                '/files/add' => ['App\\Controllers\\FileController', 'add'],
                '/files/rename' => ['App\\Controllers\\FileController', 'rename'],
                '/files/share' => ['App\\Controllers\\FileController', 'share'],
                '/files/unshare' => ['App\\Controllers\\FileController', 'unshare'],
            ],
            'DELETE' => [
                '/files/delete' => ['App\\Controllers\\FileController', 'delete'],
            ],
        ];
    }

    public function processRequest(Request $request): Response
    {
        $method = $request->getMethod();
        $route = rtrim($request->getRoute(), '/');

        if ($route === '') {
            $route = '/';
        }

        if (isset($this->routes[$method][$route])) {
            [$controllerName, $action] = $this->routes[$method][$route];
            $controller = new $controllerName($this->app);
            $data = $controller->$action($request);

            if ($data instanceof Response) {
                return $data;
            }

            return new Response($data);
        }

        return new Response(['error' => 'Route not found'], 404);
    }
}
