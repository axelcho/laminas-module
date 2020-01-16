<?php

namespace DoctrineModule\Service;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\PredisCache;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Cache\CacheProvider;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use RuntimeException;
use DoctrineModule\Cache\ZendStorageCache;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Cache ServiceManager factory
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 */
class CacheFactory extends AbstractFactory
{
    /**
     * {@inheritDoc}
     *
     * @return Cache
     *
     * @throws RuntimeException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var $options \DoctrineModule\Options\Cache */
        $options = $this->getOptions($container, 'cache');
        $class   = $options->getClass();

        if (! $class) {
            throw new RuntimeException('Cache must have a class name to instantiate');
        }

        $instance = $options->getInstance();

        if (is_string($instance) && $container->has($instance)) {
            $instance = $container->get($instance);
        }

        if ($container->has($class)) {
            $cache = $container->get($class);
        } else {
            switch ($class) {
                case FilesystemCache::class:
                    $cache = new $class($options->getDirectory());
                    break;

                case ZendStorageCache::class:
                case PredisCache::class:
                    $cache = new $class($instance);
                    break;

                default:
                    $cache = new $class;
                    break;
            }
        }

        if ($cache instanceof MemcacheCache) {
            /* @var $cache MemcacheCache */
            $cache->setMemcache($instance);
        } elseif ($cache instanceof MemcachedCache) {
            /* @var $cache MemcachedCache */
            $cache->setMemcached($instance);
        } elseif ($cache instanceof RedisCache) {
            /* @var $cache RedisCache */
            $cache->setRedis($instance);
        }

        if ($cache instanceof CacheProvider && ($namespace = $options->getNamespace())) {
            $cache->setNamespace($namespace);
        }

        return $cache;
    }

    /**
     * @param ServiceLocatorInterface $container
     * @return Cache|mixed
     * @throws ContainerException
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, Cache::class);
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionsClass()
    {
        return 'DoctrineModule\Options\Cache';
    }
}
