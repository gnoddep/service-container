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
        $this->expectException(ReadOnlyException::class);
        $container = new ServiceContainer();
        $container->close();
        $container->add(new Service('service', ServiceWithoutDependencies::class));
    }

    public function testGetNotFound()
    {
        $this->expectException(NotFoundException::class);
        $container = new ServiceContainer();
        $container->close();
        $container->get('service');
    }

    public function testIsResolvedServiceNotFound()
    {
        $this->expectException(NotFoundException::class);
        $container = new ServiceContainer();
        $container->close();
        $container->isResolved('test');
    }

    public function testServiceIsResolved()
    {
        $container = (new ServiceContainer())->add(new Service('service', ServiceWithoutDependencies::class));
        $container->close();
        $this->assertFalse($container->isResolved('service'));
        $container->get('service');
        $this->assertTrue($container->isResolved('service'));
    }

    public function testGetServiceWithoutDependency()
    {
        $container = (new ServiceContainer())->add(new Service('service', ServiceWithoutDependencies::class));
        $container->close();
        $this->assertInstanceOf(ServiceWithoutDependencies::class, $container->get('service'));
    }

    public function testAddDuplicateService()
    {
        $this->expectException(ExistsException::class);
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
        $this->assertFalse($container->isResolved('service'));
        $this->assertFalse($container->isResolved('dependency'));
        $this->assertInstanceOf(ServiceWithOneDependency::class, $container->get('service'));
        $this->assertTrue($container->isResolved('dependency'));
        $this->assertInstanceOf(ServiceWithoutDependencies::class, $container->get('service')->getDependency());
    }

    public function testGetServiceWithTwoDependencies()
    {
        $container = (new ServiceContainer())
            ->add(new Service('dependency', ServiceWithoutDependencies::class))
            ->add(new Service('another.dependency', AnotherServiceWithoutDependencies::class))
            ->add(new Service('service', ServiceWithTwoDependencies::class, 'dependency', 'another.dependency'));
        $container->close();
        $this->assertFalse($container->isResolved('service'));
        $this->assertFalse($container->isResolved('dependency'));
        $this->assertFalse($container->isResolved('another.dependency'));
        $this->assertInstanceOf(ServiceWithTwoDependencies::class, $container->get('service'));
        $this->assertTrue($container->isResolved('dependency'));
        $this->assertTrue($container->isResolved('another.dependency'));
        $this->assertInstanceOf(ServiceWithoutDependencies::class, $container->get('service')->getDependency());
        $this->assertInstanceOf(
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

        $this->assertFalse($container->isResolved('service'));
        $this->assertFalse($container->isResolved('dependency'));
        $this->assertFalse($container->isResolved('another.dependency'));
        $this->assertFalse($container->isResolved('one.dependency'));

        $this->assertInstanceOf(ServiceWithTwoDependencies::class, $container->get('service'));

        $this->assertTrue($container->isResolved('dependency'));
        $this->assertTrue($container->isResolved('another.dependency'));
        $this->assertTrue($container->isResolved('one.dependency'));

        $this->assertInstanceOf(ServiceWithoutDependencies::class, $container->get('service')->getDependency());
        $this->assertInstanceOf(ServiceWithOneDependency::class, $container->get('service')->getAnotherDependency());

        $this->assertInstanceOf(
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

        $this->expectException(CircularDependencyException::class);
        $this->assertInstanceOf(ServiceWithTwoDependencies::class, $container->get('service'));
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
    private $dependency;

    public function __construct($dependency)
    {
        $this->dependency = $dependency;
    }

    public function getDependency()
    {
        return $this->dependency;
    }
}

class ServiceWithTwoDependencies extends ServiceWithOneDependency
{
    private $anotherDependency;

    public function __construct($dependency, $anotherDependency)
    {
        parent::__construct($dependency);
        $this->anotherDependency = $anotherDependency;
    }

    public function getAnotherDependency()
    {
        return $this->anotherDependency;
    }
}
