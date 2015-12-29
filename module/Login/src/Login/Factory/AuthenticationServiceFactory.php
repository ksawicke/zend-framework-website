<?php
namespace Login\Factory;

use Login\Service\AuthenticationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthenticationServiceFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $dependencyService = $serviceLocator->get('Login\Mapper\LoginMapperInterface');

        return new AuthenticationService($dependencyService);
    }
}
