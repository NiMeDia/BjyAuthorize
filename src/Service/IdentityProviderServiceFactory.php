<?php

declare(strict_types=1);

namespace BjyAuthorize\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Factory responsible of building {@see \BjyAuthorize\Provider\Identity\ProviderInterface}
 */
class IdentityProviderServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @see \Laminas\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return $container->get($container->get('BjyAuthorize\Config')['identity_provider']);
    }
}
