<?php
namespace Request\Model;

use Zend\Db\Adapter\Adapter;

class BaseDB
{
    public $adapter;
    
    public function __construct()
    {
        $this->adapter = \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter();
    }

}