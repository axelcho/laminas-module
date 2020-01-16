<?php

namespace DoctrineModule\ServiceFactory;

use DoctrineModule\Service\AbstractFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;


/**
 * Abstract service factory capable of instantiating services whose names match the
 * pattern <code>doctrine.$serviceType.$serviceName</doctrine>
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class AbstractDoctrineServiceFactory implements AbstractFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return false !== $this->getFactoryMapping($container, $requestedName);
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $mappings = $this->getFactoryMapping($container, $requestedName);

        if (! $mappings) {
            throw new ServiceNotFoundException();
        }

        $factoryClass = $mappings['factoryClass'];
        /* @var $factory AbstractFactory */
        $factory = new $factoryClass($mappings['serviceName']);

        return (method_exists($factory, 'createService'))? $factory->createService($container): null;
    }



    /**
     * @param ContainerInterface $serviceLocator
     * @param string             $name
     *
     * @return array|bool
     */
    private function getFactoryMapping(ContainerInterface $serviceLocator, $name)
    {
        $matches = [];

        if (! preg_match(
            '/^doctrine\.((?<mappingType>orm|odm)\.|)(?<serviceType>[a-z0-9_]+)\.(?<serviceName>[a-z0-9_]+)$/',
            $name,
            $matches
        )) {
            return false;
        }

        $config      = $serviceLocator->get('config');
        $mappingType = $matches['mappingType'];
        $serviceType = $matches['serviceType'];
        $serviceName = $matches['serviceName'];

        if ($mappingType == '') {
            if (! isset($config['doctrine_factories'][$serviceType]) ||
                 ! isset($config['doctrine'][$serviceType][$serviceName])
            ) {
                return false;
            }

            return [
                'serviceType'  => $serviceType,
                'serviceName'  => $serviceName,
                'factoryClass' => $config['doctrine_factories'][$serviceType],
            ];
        } else {
            if (! isset($config['doctrine_factories'][$mappingType]) ||
                 ! isset($config['doctrine_factories'][$mappingType][$serviceType]) ||
                 ! isset($config['doctrine'][$mappingType][$serviceType][$serviceName])
            ) {
                return false;
            }
            return [
                'serviceType'  => $serviceType,
                'serviceName'  => $serviceName,
                'factoryClass' => $config['doctrine_factories'][$mappingType][$serviceType],
            ];
        }
    }
}
