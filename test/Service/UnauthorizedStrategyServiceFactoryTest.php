<?php

declare(strict_types=1);

namespace BjyAuthorizeTest\Service;

use BjyAuthorize\Service\UnauthorizedStrategyServiceFactory;
use BjyAuthorize\View\UnauthorizedStrategy;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test for {@see \BjyAuthorize\Service\UnauthorizedStrategyServiceFactory}
 */
class UnauthorizedStrategyServiceFactoryTest extends TestCase
{
    /**
     * @covers \BjyAuthorize\Service\UnauthorizedStrategyServiceFactory::__invoke
     */
    public function testInvoke()
    {
        $factory            = new UnauthorizedStrategyServiceFactory();
        $containerInterface = $this->createMock(ContainerInterface::class);
        $config             = [
            'template' => 'foo/bar',
        ];

        $containerInterface
            ->expects($this->any())
            ->method('get')
            ->with('BjyAuthorize\Config')
            ->will($this->returnValue($config));

        $strategy = $factory($containerInterface, UnauthorizedStrategyServiceFactory::class);

        $this->assertInstanceOf(UnauthorizedStrategy::class, $strategy);
        $this->assertSame('foo/bar', $strategy->getTemplate());
    }
}
