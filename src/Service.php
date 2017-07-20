<?php
namespace Nerdman\Container;

class Service
{
    /** @var string */
    private $key;
    /** @var string */
    private $class;
    /** @var mixed */
    private $instance;
    /** @var mixed[] */
    private $dependencies = [];

    public function __construct(string $key, string $class, string ...$dependencies)
    {
        $this->key = $key;
        $this->class = $class;
        $this->dependencies = $dependencies;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return mixed
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @param mixed $instance
     * @return Service
     */
    public function setInstance($instance): self
    {
        $this->instance = $instance;
        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }
}
