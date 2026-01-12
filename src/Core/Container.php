<?php
namespace Core;

class Container
{
    private array $services = [];
    private array $instances = [];
    private array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function set(string $name, callable $factory): void
    {
        $this->services[$name] = $factory;
    }

    public function get(string $name)
    {
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        if (!isset($this->services[$name])) {
            throw new \RuntimeException("Service '{$name}' is not registered.");
        }

        $this->instances[$name] = call_user_func($this->services[$name], $this);
        return $this->instances[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->services[$name]);
    }

    public function config(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}
