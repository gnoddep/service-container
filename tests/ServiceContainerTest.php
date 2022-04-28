<?php
namespace Nerdman\Test\Container;

use Nerdman\Container\Exceptions\CircularDependencyException;
use Nerdman\Container\Exceptions\ExistsException;
use Nerdman\Container\Exceptions\NotFoundException;
use Nerdman\Container\Exceptions\ReadOnlyException;
use Nerdman\Container\ServiceContainer;
use Nerdman\Container\Service;
use PHPUnit\Framework\TestCase;

class ServiceContainerTest extends TestCase
{
    public function testReadOnlyClosedServiceContainer()
    {
        self::expectException(ReadOnlyException::class);
        $container = new ServiceContainer();
        $container->close();
        $container->add(new Service('service', ServiceWithoutDependencies::class));
    }

    public function testGetNotFound()
    {
        self::expectException(NotFoundException::class);
        $container = new ServiceContainer();
        $container->close();
        $container->get('service');
    }

    public function testIsResolvedServiceNotFound()
    {
        self::expectException(NotFoundException::class);
        $container = new ServiceContainer();
        $container->close();
        $container->isResolved('test');
    }

    public function testServiceIsResolved()
    {
        $container = (new ServiceContainer())->add(new Service('service', ServiceWithoutDependencies::class));
        $container->close();
        self::assertFalse($container->isResolved('service'));
        $container->get('service');
        self::assertTrue($container->isResolved('service'));
    }

    public function testGetServiceWithoutDependency()
    {
        $container = (new ServiceContainer())->add(new Service('service', ServiceWithoutDependencies::class));
        $container->close();
        self::assertInstanceOf(ServiceWithoutDependencies::class, $container->get('service'));
    }

    public function testAddDuplicateService()
    {
        self::expectException(ExistsException::class);
        (new ServiceContainer())
            ->add(new Service('service', ServiceWithoutDependencies::class))
            ->add(new Service('service', ServiceWithoutDependencies::class));
    }

    public function testGetServiceWithOneDependency()
    {
        $container = (new ServiceContainer())
            ->add(new Service('dependency', ServiceWithoutDependencies::class))
            ->add(new Service('service', ServiceWithOneDependency::class, 'dependency'));
        $container->close();
        self::assertFalse($container->isResolved('service'));
        self::assertFalse($container->isResolved('dependency'));
        self::assertInstanceOf(ServiceWithOneDependency::class, $container->get('service'));
        self::assertTrue($container->isResolved('dependency'));
        self::assertInstanceOf(ServiceWithoutDependencies::class, $container->get('service')->getDependency());
    }

    public function testGetServiceWithTwoDependencies()
    {
        $container = (new ServiceContainer())
            ->add(new Service('dependency', ServiceWithoutDependencies::class))
            ->add(new Service('another.dependency', AnotherServiceWithoutDependencies::class))
            ->add(new Service('service', ServiceWithTwoDependencies::class, 'dependency', 'another.dependency'));
        $container->close();
        self::assertFalse($container->isResolved('service'));
        self::assertFalse($container->isResolved('dependency'));
        self::assertFalse($container->isResolved('another.dependency'));
        self::assertInstanceOf(ServiceWithTwoDependencies::class, $container->get('service'));
        self::assertTrue($container->isResolved('dependency'));
        self::assertTrue($container->isResolved('another.dependency'));
        self::assertInstanceOf(ServiceWithoutDependencies::class, $container->get('service')->getDependency());
        self::assertInstanceOf(
            AnotherServiceWithoutDependencies::class,
            $container->get('service')->getAnotherDependency()
        );
    }

    public function testGetServiceWithDeeperDependencies()
    {
        $container = (new ServiceContainer())
            ->add(new Service('dependency', ServiceWithoutDependencies::class))
            ->add(new Service('another.dependency', AnotherServiceWithoutDependencies::class))
            ->add(new Service('one.dependency', ServiceWithOneDependency::class, 'another.dependency'))
            ->add(new Service('service', ServiceWithTwoDependencies::class, 'dependency', 'one.dependency'));
        $container->close();

        self::assertFalse($container->isResolved('service'));
        self::assertFalse($container->isResolved('dependency'));
        self::assertFalse($container->isResolved('another.dependency'));
        self::assertFalse($container->isResolved('one.dependency'));

        self::assertInstanceOf(ServiceWithTwoDependencies::class, $container->get('service'));

        self::assertTrue($container->isResolved('dependency'));
        self::assertTrue($container->isResolved('another.dependency'));
        self::assertTrue($container->isResolved('one.dependency'));

        self::assertInstanceOf(ServiceWithoutDependencies::class, $container->get('service')->getDependency());
        self::assertInstanceOf(ServiceWithOneDependency::class, $container->get('service')->getAnotherDependency());

        self::assertInstanceOf(
            AnotherServiceWithoutDependencies::class,
            $container->get('one.dependency')->getDependency()
        );
    }

    public function testCircularDependency()
    {
        $container = (new ServiceContainer())
            ->add(new Service('dependency', ServiceWithoutDependencies::class))
            ->add(new Service('one.dependency', ServiceWithOneDependency::class, 'circular.dependency'))
            ->add(new Service('circular.dependency', ServiceWithOneDependency::class, 'service'))
            ->add(new Service('service', ServiceWithTwoDependencies::class, 'dependency', 'one.dependency'));
        $container->close();

        self::expectException(CircularDependencyException::class);
        self::assertInstanceOf(ServiceWithTwoDependencies::class, $container->get('service'));
    }
}

class ServiceWithoutDependencies
{
}

class AnotherServiceWithoutDependencies
{
}

class ServiceWithOneDependency
{
    private mixed $dependency;

    public function __construct(mixed $dependency)
    {
        $this->dependency = $dependency;
    }

    public function getDependency(): mixed
    {
        return $this->dependency;
    }
}

class ServiceWithTwoDependencies extends ServiceWithOneDependency
{
    private mixed $anotherDependency;

    public function __construct(mixed $dependency, mixed $anotherDependency)
    {
        parent::__construct($dependency);
        $this->anotherDependency = $anotherDependency;
    }

    public function getAnotherDependency(): mixed
    {
        return $this->anotherDependency;
    }
}
