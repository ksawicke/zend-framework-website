<?php

namespace Blog\Mapper;

use Blog\Model\PostInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;

class ZendDbSqlMapper implements PostMapperInterface
{
    /**
    * @var \Zend\Db\Adapter\AdapterInterface
    */
    protected $dbAdapter;

    /**
    * @param AdapterInterface  $dbAdapter
    */
    public function __construct(AdapterInterface $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
    }

    /**
    * @param int|string $ID
    *
    * @return PostInterface
    * @throws \InvalidArgumentException
    */
    public function find($ID)
    {
    }

    /**
    * @return array|PostInterface[]
    */
    public function findAll()
    {
        $sql    = new Sql($this->dbAdapter);
        $select = $sql->select('posts');

        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet();

            \Zend\Debug\Debug::dump($resultSet->initialize($result));die();
        }

        die("no data");
    }
}
