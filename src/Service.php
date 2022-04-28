<?php
namespace Nerdman\Container;

class Service
{
    private mixed $instance = null;
    /** @var string[] */
    private array $dependencies;

    /**
     * @param class-string $class
     */
    public function __construct(private string $key, private string $class, string ...$dependencies)
    {
        $this->dependencies = $dependencies;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return class-string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    public function getInstance(): mixed
    {
        return $this->instance;
    }

    public function setInstance(mixed $instance): self
    {
        $this->instance = $instance;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }
}
