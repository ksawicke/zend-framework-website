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
use Zend\Db\ResultSet\ResultSet;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\NamingStrategy\ArrayMapNamingStrategy;

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

    public $postColumns = [];
    public $authUserColumns = [];
    public $docTypeColumns = [];

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
        $this->postColumns = [
            'id' => 'ID',
            'title' => 'TITLE',
            'bodytext' => 'TEXT' // set key...value here is the actual field name in the table
        ];
        $this->authUserColumns = [
            'id' => 'IDENTITY_ID',
            'userid' => 'USER_ID',
            'admin' => 'USER_ADMINISTRATOR'
        ];
        $this->docTypeColumns = [
            'document_type' => 'DOCUMENT_TYPE',
            'description' => 'DESCRIPTION',
            'create_user' => 'CREATE_USER'
        ];
        // Now tell the Hydrator to array_flip the keys on save.
        // Advantage: This allows us to refer to easier to understand field names on the
        // front end, but let the application deal with the real names on the back end
        // as in when doing an update.
        // Can pass in multiple arrays here.
        $this->hydrator->setNamingStrategy(new ArrayMapNamingStrategy($this->postColumns, $this->authUserColumns));
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
        $select = $sql->select('posts')->columns($this->postColumns);
        $select->where(['id = ?' => $id]);

        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        $currentResult = $result->current();

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
     *
     * Note: You can specify columns and give them aliases
     *   ->columns(['Group' => 'LGGRP', 'Section' => 'LGSEC', 'Option' => 'LGOPT','Amount' => 'LGAMT'])
         ->join('CHOICE.LTRMON', 'POPDTALIB.LTRGRP.LGGRP = CHOICE.LTRMON.LMGRP and POPDTALIB.LTRGRP.LGSEC = CHOICE.LTRMON.LMSEC', ['Letter_1' => 'LML1DT', 'Letter_2' => 'LML2DT' , 'Letter_3' => 'LML3DT', 'Reminder' => 'LMRDT', 'Suspended' => 'LMSDT'])
     *
     */
    public function findAll()
    {
        $sql    = new Sql($this->dbAdapter);
        $select = $sql->select('posts')->columns($this->postColumns);

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
         $postData = $this->hydrator->extract($postObject);

         unset($postData['ID']); // Neither Insert nor Update needs the ID in the array

         if ($postObject->getId()) {
             // ID present, it's an Update
             $action = new Update('posts');
             $action->set($postData);
             $action->where(['id = ?' => $postObject->getId()]);
         } else {
             // ID NOT present, it's an Insert
             $action = new Insert('posts');
             $action->values($postData);
         }

         $sql    = new Sql($this->dbAdapter);
         $stmt   = $sql->prepareStatementForSqlObject($action);

         try {
             $result = $stmt->execute();

         } catch (Exception $e) {
            throw new \Exception("Can't execute statement: " . $e->getMessage());
         }

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
         $action->where(['id = ?' => $postObject->getId()]);

         $sql    = new Sql($this->dbAdapter);
         $stmt   = $sql->prepareStatementForSqlObject($action);

         $result = $stmt->execute();

         return (bool) $result->getAffectedRows();
     }

     /**
      * Return an array of rows from a query.
      *
      * @return [[Type]] [[Description]]
      */
     public function findTestDataset()
     {
        $sql    = new Sql($this->dbAdapter);
        $select = $sql->select('pte_authorized_users')->columns($this->authUserColumns);
        //$select->where(['identity_id = ?' => 8]); // sinlge record
        $select->where(['identity_id' => [4,5,6,7,8,9]]); // WHERE identity_id IN_ARRAY(4,5,6,7,8,9)

        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        $resultSet = new ResultSet;
        $resultSet->initialize($result);

        $returnRows = [];
        foreach ($resultSet as $row) {
            $returnRows[] = (array) $row;
        }

        return $returnRows;
     }

     /**
      * Grab data, even by an array of matching values, with offsets! Woo hoo!
      * Even though DB2 does not natively support LIMIT and OFFSET like
      * MySQL, we can get similar results using the object oriented chaining
      * of ->limit(x)->offset(y) !
      * Probably use this for pagination
      *
      * @param  [[Type]] [$type = null] [[Description]]
      * @return [[Type]] [[Description]]
      */
     public function findAllDocumentTypes($type = null)
     {
        $sql    = new Sql($this->dbAdapter);
        $select = $sql->select('spdbkdtyp')->columns($this->docTypeColumns)->limit(3)->offset(1); // ->limit(100)->offset(100)
        if(is_array($type)) {
            $select->where(['document_type' => $type]);
        } elseif(!is_null($type)) {
            $select->where(['document_type = ?' => $type]);
        }

        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        $resultSet = new ResultSet;
        $resultSet->initialize($result);

        $returnRows = [];
        foreach ($resultSet as $row) {
            $returnRows[] = (array) $row;
        }

        return $returnRows;
     }
}
