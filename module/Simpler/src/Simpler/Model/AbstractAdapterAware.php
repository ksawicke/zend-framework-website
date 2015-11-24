<?php

namespace Simpler\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;

abstract class AbstractAdapterAware implements AdapterAwareInterface
{

    /**
     * Database instance
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $db;

    /**
     * Set database adapter
     *
     * @param Adapter $db
     * @return void
     */
    public function setDbAdapter(Adapter $adapter)
    {
        $this->db = $adapter;
    }

    public function getAdapter()
    {
       if (!$this->adapter) {
          $sm = $this->getServiceLocator();
          $this->adapter = $sm->get('Zend\Db\Adapter\Adapter');
       }
       return $this->adapter;
    }

}
