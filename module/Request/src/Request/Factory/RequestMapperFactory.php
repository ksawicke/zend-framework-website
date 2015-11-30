<?php
namespace Request\Factory;

use Request\Mapper\RequestMapper;
use Request\Model\Request;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\Hydrator\ClassMethods;

class RequestMapperFactory implements FactoryInterface
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
        return new RequestMapper($serviceLocator->get('Zend\Db\Adapter\Adapter'), $hydrator, new Request());
    }
}
