<?php

namespace Core;

use Closure;
use Exception;

class App
{
    public Auth $auth;
    protected array $services = [];

    public function setService(string $name, mixed $service): void
    {
        $this->services[$name] = $service;
    }

    public function getService(string $name): mixed
    {
        if (!array_key_exists($name, $this->services)) {
            throw new Exception("Service '$name' not registered.");
        }

        if ($this->services[$name] instanceof Closure) {
            $this->services[$name] = ($this->services[$name])();
        }

        return $this->services[$name];
    }
}
