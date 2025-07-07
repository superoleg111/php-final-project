<?php

namespace Core;

class Request
{
    private array $data;
    private string $method;
    private string $route;

    public function __construct()
    {
        $this->data = $_REQUEST;
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getRoute(): string
    {
        return $this->route;
    }
}
