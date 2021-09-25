<?php

declare(strict_types=1);

namespace BjyAuthorizeTest\Service;

use BjyAuthorize\Service\CacheKeyGeneratorFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

use function is_callable;

/**
 * PHPUnit tests for {@see \BjyAuthorize\Service\CacheKeyGeneratorFactory}
 */
class CacheKeyGeneratorFactoryTest extends TestCase
{
    /**
     * @covers \BjyAuthorize\Service\CacheKeyGeneratorFactory::__invoke
     */
    public function testInvokeReturnsDefaultCallable()
    {
        $container = $this->createMock(ContainerInterface::class);
        $config    = [];

        $container
            ->expects($this->any())
            ->method('get')
            ->with('BjyAuthorize\Config')
            ->will($this->returnValue($config));

        $factory = new CacheKeyGeneratorFactory();

        $cacheKeyGenerator = $factory($container, CacheKeyGeneratorFactory::class);
        $this->assertTrue(is_callable($cacheKeyGenerator));
        $this->assertEquals('bjyauthorize_acl', $cacheKeyGenerator());
    }

    /**
     * @covers \BjyAuthorize\Service\CacheKeyGeneratorFactory::__invoke
     */
    public function testInvokeReturnsCacheKeyGeneratorCallable()
    {
        $container = $this->createMock(ContainerInterface::class);
        $config    = [
            'cache_key' => 'some_new_value',
        ];

        $container
            ->expects($this->any())
            ->method('get')
            ->with('BjyAuthorize\Config')
            ->will($this->returnValue($config));

        $factory = new CacheKeyGeneratorFactory();

        $cacheKeyGenerator = $factory($container, CacheKeyGeneratorFactory::class);
        $this->assertTrue(is_callable($cacheKeyGenerator));
        $this->assertEquals('some_new_value', $cacheKeyGenerator());
    }
}
