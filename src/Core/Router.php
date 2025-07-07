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
                '/users/list' => ['App\\Controllers\\UserController', 'list'],
            ],
            'POST' => [
                '/users/login' => ['App\\Controllers\\UserController', 'login'],
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
            return new Response($data);
        }

        return new Response(['error' => 'Route not found'], 404);
    }
}
