<?php

declare(strict_types=1);

namespace BjyAuthorize\Service;

use BjyAuthorize\Provider\Role\LaminasDb;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Factory responsible of instantiating {@see \BjyAuthorize\Provider\Role\LaminasDb}
 */
class LaminasDbRoleProviderServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @see \Laminas\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new LaminasDb(
            $container->get('BjyAuthorize\Config')['role_providers'][LaminasDb::class],
            $container
        );
    }
}
