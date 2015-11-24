<?php

namespace Application\Model;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;
use Zend\Db\ResultSet\ResultSet;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\NamingStrategy\ArrayMapNamingStrategy;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

// http://stackoverflow.com/questions/12770966/service-locator-in-zend-framework-2
class ApplicationModel implements ServiceLocatorAwareInterface
{
    protected $serviceLocator;
    protected $dbAdapter;
    protected $hydrator;

    /**
     * @param AdapterInterface  $dbAdapter
     * @param HydratorInterface $hydrator
     * @param PostInterface    $postPrototype
     */
    public function __construct() {
        $this->hydrator         = new ClassMethods(false);
        $this->serviceLocator   = $this->serviceLocator()->get('\Zend\Db\Adapter\Adapter');
        var_dump($this->serviceLocator);exit();
        $this->dbAdapter        = $this->serviceLocator()->get('Zend\Db\Adapter\Adapter'); //$serviceLocator->get('Zend\Db\Adapter\Adapter');
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

}
