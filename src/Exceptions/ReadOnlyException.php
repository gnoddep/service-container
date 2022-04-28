<?php
namespace Nerdman\Container\Exceptions;

use Psr\Container\ContainerExceptionInterface;

class ReadOnlyException extends \Exception implements ContainerExceptionInterface
{
}
