<?php
namespace Nerdman\Container;

use Nerdman\Container\Exceptions\CircularDependencyException;
use Nerdman\Container\Exceptions\ExistsException;
use Nerdman\Container\Exceptions\NotFoundException;
use Nerdman\Container\Exceptions\ReadOnlyException;
use Psr\Container\ContainerInterface;

class ServiceContainer implements ContainerInterface
{
    private bool $writable = true;
    /** @var Service[] */
    private array $services = [];

    /**
     * @throws CircularDependencyException
     * @throws NotFoundException
     */
    public function get(string $id, array $stack = []): mixed
    {
        if (!$this->has($id)) {
            throw new NotFoundException($id);
        }

        if (isset($stack[$id])) {
            throw new CircularDependencyException($id, $stack);
        } else {
            $stack[$id] = true;
        }

        $service = $this->services[$id];

        if (!$service->getInstance()) {
            $class = $service->getClass();

            $arguments = \array_map(function (string $dependency) use ($stack) {
                return $this->get($dependency, $stack);
            }, $service->getDependencies());

            $service->setInstance(new $class(...$arguments));
        }

        return $service->getInstance();
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }

    /**
     * @throws NotFoundException
     */
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

    /**
     * @throws ExistsException
     * @throws ReadOnlyException
     */
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
