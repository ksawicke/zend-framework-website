<?php
namespace Request\Model;

use Zend\Db\Adapter\Adapter;

class BaseDB
{

    public $adapter;

    public function __construct()
    {
        $dbAdapter = \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter();
        $this->adapter = $dbAdapter;
    }

}