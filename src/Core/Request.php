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

        $raw = file_get_contents('php://input');
        if ($raw !== false && strlen($raw) > 0) {
            $json = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->data = array_merge($this->data, $json);
            }
        }
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getBody(): array
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

    public function getQuery(string $key, $default = null): ?string
    {
        return $_GET[$key] ?? $default;
    }
}
