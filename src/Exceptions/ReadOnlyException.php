<?php
namespace Nerdman\Container\Exceptions;

use Exception;
use Psr\Container\ContainerExceptionInterface;

class ReadOnlyException extends Exception implements ContainerExceptionInterface
{
}
