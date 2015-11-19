<?php

namespace Blog\Mapper;

use Blog\Model\PostInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;
use Zend\Stdlib\Hydrator\HydratorInterface;

class ZendDbSqlMapper implements PostMapperInterface
{
    /**
     * @var \Zend\Db\Adapter\AdapterInterface
     */
    protected $dbAdapter;

    /**
     * @var \Zend\Stdlib\Hydrator\HydratorInterface
     */
    protected $hydrator;

    /**
     * @var \Blog\Model\PostInterface
     */
    protected $postPrototype;

    public $postsColumns = [];

    /**
     * @param AdapterInterface  $dbAdapter
     * @param HydratorInterface $hydrator
     * @param PostInterface    $postPrototype
     */
    public function __construct(
        AdapterInterface $dbAdapter,
        HydratorInterface $hydrator,
        PostInterface $postPrototype
    ) {
        $this->dbAdapter      = $dbAdapter;
        $this->hydrator       = $hydrator;
        $this->postPrototype  = $postPrototype;
        $this->postsColumns = [
            'id' => 'ID',
            'title' => 'TITLE',
            'bodytext' => 'TEXT' // you can give an alias to fields here by chaning the key...value here is the actual field name in the table
        ];
    }

    /**
    * @param int|string $ID
    *
    * @return PostInterface
    * @throws \InvalidArgumentException
    */
    public function find($id)
    {
        $sql    = new Sql($this->dbAdapter);
        $select = $sql->select('posts')->columns($this->postsColumns);
        $select->where(array('id = ?' => $id));

        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        $currentResult = $result->current();

//        var_dump($currentResult);die("@@@@@");

        $resultIsArray = true;
        if( is_array($currentResult) === false ) {
            $resultIsArray = false;
        }

        if ($result instanceof ResultInterface && $result->isQueryResult() && $result->getAffectedRows() &&
            $resultIsArray
           ) {
            return $this->hydrator->hydrate($currentResult, $this->postPrototype);
        }

        throw new \InvalidArgumentException("Blog with given ID:{$id} not found.");
    }

    /**
    * @return array|PostInterface[]
    */
    public function findAll()
    {
        $sql    = new Sql($this->dbAdapter);
        $select = $sql->select('posts')->columns($this->postsColumns);

        /**
         *

         ->columns(['Group' => 'LGGRP', 'Section' => 'LGSEC', 'Option' => 'LGOPT','Amount' => 'LGAMT'])
         ->join('CHOICE.LTRMON', 'POPDTALIB.LTRGRP.LGGRP = CHOICE.LTRMON.LMGRP and POPDTALIB.LTRGRP.LGSEC = CHOICE.LTRMON.LMSEC',
                   ['Letter_1' => 'LML1DT', 'Letter_2' => 'LML2DT' , 'Letter_3' => 'LML3DT', 'Reminder' => 'LMRDT', 'Suspended' => 'LMSDT'])

         **/

        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new HydratingResultSet($this->hydrator, $this->postPrototype);

            return $resultSet->initialize($result);
        }

        return array();
    }

    /**
      * {@inheritDoc}
      */
     public function save(PostInterface $postObject)
     {
         $postDataTmp = $this->hydrator->extract($postObject);

         foreach($this->postsColumns as $key => $val) {
             $postData[strtolower($val)] = $postDataTmp[$key];
         }

         unset($postData['id']); // Neither Insert nor Update needs the ID in the array

         if ($postObject->getId()) {
             // ID present, it's an Update
             $action = new Update('posts');
             $action->set($postData);
             $action->where(array('id = ?' => $postObject->getId()));
         } else {
             // ID NOT present, it's an Insert
             $action = new Insert('posts');
             $action->values($postData);
         }

         $sql    = new Sql($this->dbAdapter);
         $stmt   = $sql->prepareStatementForSqlObject($action);

         $result = $stmt->execute();

         if ($result instanceof ResultInterface) {
             if ($newId = $result->getGeneratedValue()) {
                 // When a value has been generated, set it on the object
                 $postObject->setId($newId);
             }

             return $postObject;
         }

         throw new \Exception("Database error");
     }

     /**
      * {@inheritDoc}
      */
     public function delete(PostInterface $postObject)
     {
         $action = new Delete('posts');
         $action->where(array('id = ?' => $postObject->getId()));

         $sql    = new Sql($this->dbAdapter);
         $stmt   = $sql->prepareStatementForSqlObject($action);
         $result = $stmt->execute();

         return (bool)$result->getAffectedRows();
     }
}
