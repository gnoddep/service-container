<?php
namespace Nerdman\Container;

use Nerdman\Container\Exceptions\CircularDependencyException;
use Nerdman\Container\Exceptions\ExistsException;
use Nerdman\Container\Exceptions\NotFoundException;
use Nerdman\Container\Exceptions\ReadOnlyException;
use Nerdman\Container\Psr\ContainerInterface;

class ServiceContainer implements ContainerInterface
{
    /** @var bool  */
    private $writable = true;
    /** @var Service[] */
    private $services = [];

    /**
     * @param string $key
     * @param array $stack
     * @return mixed
     * @throws CircularDependencyException
     * @throws NotFoundException
     */
    public function get(string $key, array $stack = [])
    {
        if (!$this->has($key)) {
            throw new NotFoundException($key);
        }

        if (isset($stack[$key])) {
            throw new CircularDependencyException($key, $stack);
        } else {
            $stack[$key] = true;
        }

        $service = $this->services[$key];

        if (!$service->getInstance()) {
            $class = $service->getClass();

            $arguments = array_map(function (string $dependency) use ($stack) {
                return $this->get($dependency, $stack);
            }, $service->getDependencies());

            $service->setInstance(new $class(...$arguments));
        }

        return $service->getInstance();
    }

    public function has(string $key): bool
    {
        return isset($this->services[$key]);
    }

    public function isResolved(string $key): bool
    {
        if (!$this->has($key)) {
            throw new NotFoundException($key);
        }

        return $this->services[$key]->getInstance() !== null;
    }

    public function close(): void
    {
        $this->writable = false;
    }

    public function add(Service $service): self
    {
        if (!$this->writable) {
            throw new ReadOnlyException();
        }

        $key = $service->getKey();

        if ($this->has($key)) {
            throw new ExistsException();
        }

        $this->services[$key] = $service;

        return $this;
    }
}
