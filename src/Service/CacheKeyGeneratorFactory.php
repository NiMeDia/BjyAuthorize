<?php

declare(strict_types=1);

namespace BjyAuthorize\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Factory for building a cache key generator
 */
class CacheKeyGeneratorFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @see \Laminas\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config   = $container->get('BjyAuthorize\Config');
        $cacheKey = empty($config['cache_key']) ? 'bjyauthorize_acl' : (string) $config['cache_key'];

        return function () use ($cacheKey) {
            return $cacheKey;
        };
    }
}
