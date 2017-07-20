<?php
namespace Nerdman\Container\Psr;

interface ContainerInterface
{
    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key);

    public function has(string $key): bool;
}
