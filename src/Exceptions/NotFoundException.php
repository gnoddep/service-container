<?php
namespace Nerdman\Container\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \Exception implements NotFoundExceptionInterface
{
    public function __construct(string $key, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(\sprintf('Could not find service with key %s', $key), $code, $previous);
    }
}
