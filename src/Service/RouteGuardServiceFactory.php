<?php

declare(strict_types=1);

namespace BjyAuthorize\Service;

use BjyAuthorize\Guard\Route;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Factory responsible of instantiating {@see \BjyAuthorize\Guard\Route}
 */
class RouteGuardServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @see \Laminas\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new Route($container->get('BjyAuthorize\Config')['guards'][Route::class], $container);
    }
}
