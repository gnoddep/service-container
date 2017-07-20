<?php
namespace Nerdman\Container\Exceptions;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Throwable;

class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    /**
     * @param string $key
     * @param string[] $stack
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $key, array $stack, int $code = 0, Throwable $previous = null)
    {
        $filteredStack = [];
        foreach ($stack as $previousKey => $value) {
            if ($key == $previousKey || count($filteredStack)) {
                $filteredStack[] = $previousKey;
            }
        }

        parent::__construct(
            sprintf('Circular dependency detected: %s -> %s', implode(' -> ', $filteredStack), $key),
            $code,
            $previous
        );
    }
}
