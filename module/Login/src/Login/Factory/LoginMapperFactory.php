<?php
namespace Login\Factory;

use Login\Mapper\LoginMapper;
use Login\Model\Login;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\Hydrator\ClassMethods;

class LoginMapperFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $hydrator = new ClassMethods(false);
        return new LoginMapper($serviceLocator->get('Zend\Db\Adapter\Adapter'), $hydrator, new Login());
    }
}
