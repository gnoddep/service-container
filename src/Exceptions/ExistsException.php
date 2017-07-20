<?php
namespace Nerdman\Container\Exceptions;

use Exception;
use Psr\Container\ContainerExceptionInterface;

class ExistsException extends Exception implements ContainerExceptionInterface
{
}
