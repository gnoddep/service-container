<?php
namespace Nerdman\Container\Exceptions;

use Exception;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class NotFoundException extends Exception implements NotFoundExceptionInterface
{
    public function __construct(string $key, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('Could not find service with key %s', $key), $code, $previous);
    }
}
