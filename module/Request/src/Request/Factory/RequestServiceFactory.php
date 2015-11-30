<?php

namespace Request\Factory;

use Request\Service\RequestService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RequestServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $dependencyService = $serviceLocator->get('Request\Mapper\RequestMapperInterface');

        return new RequestService($dependencyService);
    }
}
