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
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

class ApplicationModel implements FactoryInterface
{
    protected $dbAdapter;
    protected $hydrator;

    /**
     * @param AdapterInterface  $dbAdapter
     * @param HydratorInterface $hydrator
     * @param PostInterface    $postPrototype
     */
    public function __construct() {
        $this->hydrator         = new ClassMethods(false);
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
    }
}
