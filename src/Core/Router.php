<?php

namespace Core;


use Exception;

class Router
{
    private array $routes = [];
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;

        $this->routes = [
            'GET' => [
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

        if (isset($this->routes[$method][$route])) {
            [$controllerName, $action] = $this->routes[$method][$route];
            $controller = new $controllerName($this->app);
            $data = $controller->$action($request);
            return new Response($data);
        }

        return new Response(['error' => 'Route not found'], 404);
    }
}
