<?php

namespace Application\Model;

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
// http://cmyker.blogspot.com/2012/11/zend-framework-2-model-database-adapter.html
class ApplicationModel extends AbstractAdapterAware
{
    protected $hydrator;

    /**
     * @param AdapterInterface  $dbAdapter
     * @param HydratorInterface $hydrator
     * @param PostInterface    $postPrototype
     */
    public function __construct() {
//        var_dump([1 => 2, 3 => 4]);
//        exit();
//        var_dump($this->db);
//        exit();
        $this->hydrator         = new ClassMethods(false);
//        $this->serviceLocator   = $this->serviceLocator()->get('\Zend\Db\Adapter\Adapter');
//        var_dump($this->serviceLocator);exit();
//        $this->dbAdapter        = $this->serviceLocator()->get('Zend\Db\Adapter\Adapter'); //$serviceLocator->get('Zend\Db\Adapter\Adapter');
    }

//    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
//        $this->serviceLocator = $serviceLocator;
//        return $this;
//    }
//
//    public function getServiceLocator() {
//        return $this->serviceLocator;
//    }

}
