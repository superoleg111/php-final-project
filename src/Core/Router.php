<?php

namespace Core;

use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\FileShareController;
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
                '/files/share/{file_id}' => ['App\\Controllers\\FileShareController', 'list'],
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
                '/admin/users/delete/{id}' => ['App\\Controllers\\UserController', 'adminDelete'],
                '/files/delete' => ['App\\Controllers\\FileController', 'delete'],
                '/files/share/{file_id}/{user_id}' => ['App\\Controllers\\FileController', 'revoke'],
            ],
            'PUT' => [
                '/users/update' => ['App\\Controllers\\UserController', 'update'],
                '/files/share/{file_id}/{user_id}' => ['App\\Controllers\\FileShareController', 'share'],
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

        if ($method === 'GET' && $route === '/admin/users/list') {
            $controller = new UserController($this->app);
            return $controller->adminList($request);
        }

        if ($method === 'GET' && preg_match('#^/users/get/(\d+)$#', $route, $matches)) {
            $controller = new UserController($this->app);
            $response = $controller->get($request, (int)$matches[1]);
            return $response instanceof \Core\Response
                ? $response
                : new \Core\Response($response);
        }

        if ($method === 'PUT' && preg_match('#^/files/share/(\d+)/(\d+)$#', $route, $matches)) {
            $controller = new FileShareController($this->app);
            return $controller->share((int)$matches[1], (int)$matches[2]);
        }

        if ($method === 'GET' && preg_match('#^/files/share/(\d+)$#', $route, $matches)) {
            $controller = new FileShareController($this->app);
            return $controller->list((int)$matches[1]);
        }

        if ($method === 'DELETE' && preg_match('#^/files/share/(\d+)/(\d+)$#', $route, $matches)) {
            $controller = new FileShareController($this->app);
            return $controller->revoke((int)$matches[1], (int)$matches[2]);
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
