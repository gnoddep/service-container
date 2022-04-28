<?php
namespace Nerdman\Container\Exceptions;

use Psr\Container\ContainerExceptionInterface;

class CircularDependencyException extends \Exception implements ContainerExceptionInterface
{
    /**
     * @param string[] $stack
     */
    public function __construct(string $key, array $stack, int $code = 0, \Throwable $previous = null)
    {
        $filteredStack = [];
        foreach ($stack as $previousKey => $value) {
            if ($key == $previousKey || \count($filteredStack)) {
                $filteredStack[] = $previousKey;
            }
        }

        parent::__construct(
            \sprintf('Circular dependency detected: %s -> %s', \implode(' -> ', $filteredStack), $key),
            $code,
            $previous
        );
    }
}
