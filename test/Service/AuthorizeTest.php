<?php

namespace BjyAuthorizeTest\Service;

use BjyAuthorize\Acl\Role;
use BjyAuthorize\Provider\Identity\ProviderInterface;
use BjyAuthorize\Provider\Resource\Config as ResourceConfig;
use BjyAuthorize\Provider\Role\Config as RoleConfig;
use BjyAuthorize\Service\Authorize;
use BjyAuthorize\Service\GuardsServiceFactory;
use BjyAuthorize\Service\ResourceProvidersServiceFactory;
use BjyAuthorize\Service\RoleProvidersServiceFactory;
use BjyAuthorize\Service\RuleProvidersServiceFactory;
use Laminas\Cache\Storage\Adapter\AbstractAdapter;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\GenericResource;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for {@see \BjyAuthorize\Service\Authorize}
 *
 * @author Christian Bergau <cbergau86@gmail.com>
 */
class AuthorizeTest extends TestCase
{
    /** @var  ServiceManager */
    protected $serviceManager;

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp(): void
    {
        $cache = $this->getMockBuilder(AbstractAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cache->expects($this->any())->method('getItem');
        $cache->expects($this->any())->method('setItem');

        $serviceManager = new ServiceManager();
        $serviceManager->setService('BjyAuthorize\Cache', $cache);
        $serviceManager->setService(ProviderInterface::class, $this->createMock(ProviderInterface::class));
        $serviceManager->setService(
            'BjyAuthorize\RoleProviders',
            $this->createMock(RoleProvidersServiceFactory::class)
        );
        $serviceManager->setService(
            'BjyAuthorize\ResourceProviders',
            $this->createMock(ResourceProvidersServiceFactory::class)
        );
        $serviceManager->setService(
            'BjyAuthorize\RuleProviders',
            $this->createMock(RuleProvidersServiceFactory::class)
        );
        $serviceManager->setService('BjyAuthorize\Guards', $this->createMock(GuardsServiceFactory::class));
        $serviceManager->setService(
            'BjyAuthorize\CacheKeyGenerator',
            function () {
                return 'bjyauthorize-acl';
            }
        );
        $this->serviceManager = $serviceManager;
    }

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::tearDown()
     */
    protected function tearDown(): void
    {
        unset($this->serviceManager);
    }

    /**
     * @covers \BjyAuthorize\Service\Authorize::load
     */
    public function testLoadLoadsAclFromCacheAndDoesNotBuildANewAclObject()
    {
        $this->markTestSkipped('TODO refactoring');
        $acl = $this->createMock(Acl::class);

        $cache = $this->getMockBuilder(AbstractAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cache
            ->expects($this->once())
            ->method('getItem')
            ->will(
                $this->returnCallback(
                    function ($key, &$success) use ($acl) {
                        $success = true;

                        return $acl;
                    }
                )
            );

        $serviceManager = new ServiceManager();
        $serviceManager->setService(ProviderInterface::class, $this->createMock(ProviderInterface::class));
        $serviceManager->setService('BjyAuthorize\Cache', $cache);
        $serviceManager->setService(
            'BjyAuthorize\CacheKeyGenerator',
            function () {
                return 'bjyauthorize-acl';
            }
        );
        $authorize = new Authorize(['cache_key' => 'bjyauthorize-acl'], $serviceManager);
        $authorize->load();

        $this->assertSame($acl, $authorize->getAcl());
    }

    /**
     * @covers \BjyAuthorize\Service\Authorize::load
     */
    public function testLoadWritesAclToCacheIfCacheIsEnabledButAclIsNotStoredInCache()
    {
        $cache = $this->getMockBuilder(AbstractAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cache->expects($this->any())->method('getItem');
        $cache->expects($this->any())->method('setItem');

        $serviceManager = new ServiceManager();
        $serviceManager->setService('BjyAuthorize\Cache', $cache);
        $serviceManager->setService(ProviderInterface::class, $this->createMock(ProviderInterface::class));
        $serviceManager->setService(
            'BjyAuthorize\RoleProviders',
            $this->createMock(RoleProvidersServiceFactory::class)
        );
        $serviceManager->setService(
            'BjyAuthorize\ResourceProviders',
            $this->createMock(ResourceProvidersServiceFactory::class)
        );
        $serviceManager->setService(
            'BjyAuthorize\RuleProviders',
            $this->createMock(RuleProvidersServiceFactory::class)
        );
        $serviceManager->setService('BjyAuthorize\Guards', $this->createMock(GuardsServiceFactory::class));
        $serviceManager->setService(
            'BjyAuthorize\CacheKeyGenerator',
            function () {
                return 'acl';
            }
        );
        $authorize = new Authorize(['cache_key' => 'acl'], $serviceManager);
        $authorize->load();

        $this->assertTrue(true);
    }


    /**
     * @group bjyoungblood/BjyAuthorize#258
     */
    public function testCanAddResourceInterfaceToLoadResource()
    {
        $serviceManager = $this->serviceManager;
        $serviceManager->setAllowOverride(true);

        $resourceProviderMock = $this->getMockBuilder(ResourceConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resourceProviderMock
            ->expects($this->once())
            ->method('getResources')
            ->will(
                $this->returnValue(
                    [new GenericResource('test')]
                )
            );

        $serviceManager->setService(ResourceConfig::class, $resourceProviderMock);
        $serviceManager->setService('BjyAuthorize\ResourceProviders', [$resourceProviderMock]);

        $authorize = new Authorize(['cache_key' => 'acl'], $this->serviceManager);
        $authorize->load();

        $acl = $authorize->getAcl();

        $this->assertTrue($acl->hasResource('test'));
    }

    /**
     * @group bjyoungblood/BjyAuthorize#258
     */
    public function testCanAddTraversableResourceToLoadResource()
    {
        $serviceManager = $this->serviceManager;
        $serviceManager->setAllowOverride(true);

        $resourceProviderMock = $this->getMockBuilder(ResourceConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resourceProviderMock
            ->expects($this->once())
            ->method('getResources')
            ->will(
                $this->returnValue(
                    new ArrayObject(['test'])
                )
            );

        $serviceManager->setService(ResourceConfig::class, $resourceProviderMock);
        $serviceManager->setService('BjyAuthorize\ResourceProviders', [$resourceProviderMock]);

        $authorize = new Authorize(['cache_key' => 'acl'], $serviceManager);

        $acl = $authorize->getAcl();

        $this->assertTrue($acl->hasResource('test'));
    }


    /**
     * @group bjyoungblood/BjyAuthorize#258
     */
    public function testCanAddNonTraversableResourceToLoadResourceThrowsInvalidArgumentException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $serviceManager = $this->serviceManager;
        $serviceManager->setAllowOverride(true);

        $resourceProviderMock = $this->getMockBuilder(ResourceConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resourceProviderMock
            ->expects($this->once())
            ->method('getResources')
            ->will(
                $this->returnValue(
                    'test'
                )
            );

        $serviceManager->setService(ResourceConfig::class, $resourceProviderMock);
        $serviceManager->setService('BjyAuthorize\ResourceProviders', [$resourceProviderMock]);

        $authorize = new Authorize(['cache_key' => 'acl'], $this->serviceManager);
        $authorize->load();
    }

    /**
     * @group bjyoungblood/BjyAuthorize#258
     */
    public function testCanAddTraversableRoleToLoadRole()
    {
        $serviceManager = $this->serviceManager;
        $serviceManager->setAllowOverride(true);

        $roleProviderMock = $this->getMockBuilder(RoleConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $roleProviderMock
            ->expects($this->once())
            ->method('getRoles')
            ->will(
                $this->returnValue(
                    new ArrayObject([new Role('test')])
                )
            );

        $serviceManager->setService(RoleConfig::class, $roleProviderMock);
        $serviceManager->setService('BjyAuthorize\RoleProviders', [$roleProviderMock]);

        $authorize = new Authorize(['cache_key' => 'acl'], $this->serviceManager);
        $authorize->load();

        $acl = $authorize->getAcl();

        $this->assertTrue($acl->hasRole('test'));
    }
}
