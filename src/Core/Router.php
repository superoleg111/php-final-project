<?php

namespace Core;

use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\FileController;
use App\Controllers\FileShareController;
use App\Controllers\DirectoryController;
use \App\Controllers\AdminController;
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
//                '/' => ['App\\Controllers\\HomeController', 'index'],
//                '/ping' => ['App\\Controllers\\TestController', 'ping'],
                '/logout' => ['App\\Controllers\\UserController', 'logout'],
                '/me' => ['App\\Controllers\\UserController', 'me'],
                '/users/list' => ['App\\Controllers\\UserController', 'list'],
                '/files/list' => ['App\\Controllers\\FileController', 'list'],
                '/files/get/{id}' => ['App\\Controllers\\FileController', 'get'],
//                '/files/download' => ['App\\Controllers\\FileController', 'download'],
//                '/public/download' => ['App\\Controllers\\FileController', 'publicDownload'],
                '/files/shared' => ['App\\Controllers\\FileController', 'shared'],
                '/files/share/{file_id}' => ['App\\Controllers\\FileShareController', 'list'],
                '/admin/users/list' => ['App\\Controllers\\AdminController', 'list'],
                '/users/do_reset' => ['App\\Controllers\\UserController', 'doReset'],
                '/directories/get/{id}' => ['App\\Controllers\\DirectoryController','get'],
            ],
            'POST' => [
                '/register' => ['App\\Controllers\\UserController','register'],
                '/login' => ['App\\Controllers\\UserController', 'login'],
                '/files/add' => ['App\\Controllers\\FileController', 'add'],
                '/files/share' => ['App\\Controllers\\FileController', 'share'],
                '/files/unshare' => ['App\\Controllers\\FileController', 'unshare'],
                '/reset_password' => ['App\\Controllers\\UserController', 'resetPassword'],
                '/directories/add' => ['App\\Controllers\\DirectoryController','add'],
            ],
            'DELETE' => [
                '/files/remove/{id}' => ['App\\Controllers\\FileController', 'removeById'],
                '/files/share/{file_id}/{user_id}' => ['App\\Controllers\\FileController', 'revoke'],
                '/directories/delete/{id}' => ['App\\Controllers\\DirectoryController','delete'],
            ],
            'PUT' => [
                '/users/update' => ['App\\Controllers\\UserController', 'update'],
                '/files/share/{file_id}/{user_id}' => ['App\\Controllers\\FileShareController', 'share'],
                '/admin/users/update/{id}' => ['App\\Controllers\\AdminController', 'update'],
                '/files/rename' => ['App\\Controllers\\FileController', 'rename'],
                '/directories/rename' => ['App\\Controllers\\DirectoryController','rename'],
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

        if ($method === 'GET' && preg_match('#^/admin/users/get/(\d+)$#', $route, $matches)) {
            $ctrl = new AdminController($this->app);
            return $ctrl->get($request, (int)$matches[1]);
        }

        if ($method === 'GET' && preg_match('#^/users/get/(\d+)$#', $route, $matches)) {
            $controller = new UserController($this->app);
            $response = $controller->get($request, (int)$matches[1]);
            return $response instanceof \Core\Response
                ? $response
                : new \Core\Response($response);
        }

        if ($method==='GET' && preg_match('#^/files/get/(\d+)$#',$route,$matches)) {
            $controller = new FileController($this->app);
            return $controller->get($request,(int)$matches[1]);
        }

        if ($method === 'PUT' && preg_match('#^/files/share/(\d+)/(\d+)$#', $route, $matches)) {
            $controller = new FileShareController($this->app);
            return $controller->share((int)$matches[1], (int)$matches[2]);
        }

        if ($method === 'GET' && preg_match('#^/files/share/(\d+)$#', $route, $matches)) {
            $controller = new FileShareController($this->app);
            return $controller->list((int)$matches[1]);
        }

        // DELETE /files/delete/{id}
        if ($method === 'DELETE' && preg_match('#^/files/remove/(\d+)$#', $route, $m)) {
            $controller = new FileController($this->app);
            return $controller->removeById($request, (int)$m[1]);
        }

        if ($method === 'DELETE' && preg_match('#^/files/share/(\d+)/(\d+)$#', $route, $matches)) {
            $controller = new FileShareController($this->app);
            return $controller->revoke((int)$matches[1], (int)$matches[2]);
        }

        // GET /admin/users/get/{id}
        if ($method === 'GET' && preg_match('#^/admin/users/get/(\d+)$#', $route, $matches)) {
            $controller = new AdminController($this->app);
            return $controller->get($request, (int)$matches[1]);
        }

        // PUT /admin/users/update/{id}
        if ($method === 'PUT' && preg_match('#^/admin/users/update/(\d+)$#', $route, $matches)) {
            $controller = new AdminController($this->app);
            return $controller->update($request, (int)$matches[1]);
        }

        // DELETE /admin/users/delete/{id}
        if ($method === 'DELETE' && preg_match('#^/admin/users/delete/(\d+)$#', $route, $matches)) {
            $controller = new AdminController($this->app);
            return $controller->delete($request, (int)$matches[1]);
        }

        if ($method==='GET' && preg_match('#^/directories/get/(\d+)$#',$route,$matches)) {
            $controller = new DirectoryController($this->app);
            return $controller->get($request,(int)$matches[1]);
        }
        if ($method==='POST' && $route==='/directories/add') {
            return (new DirectoryController($this->app))->add($request);
        }
        if ($method==='PUT' && $route==='/directories/rename') {
            return (new DirectoryController($this->app))->rename($request);
        }
        if ($method==='DELETE' && preg_match('#^/directories/delete/(\d+)$#',$route,$matches)) {
            $c= new DirectoryController($this->app);
            return $c->delete($request,(int)$matches[1]);
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
