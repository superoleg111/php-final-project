<?php

namespace App;

class App
{
    /**
     * @var array
     */
    protected array $services = [];

    /**
     * @param string $name
     * @param mixed $service
     */
    public function setService(string $name, mixed $service): void
    {
        $this->services[$name] = $service;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws Exception
     */
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
