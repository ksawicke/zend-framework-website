<?php

namespace Simpler\Model;

use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;
use Zend\Db\ResultSet\ResultSet;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\NamingStrategy\ArrayMapNamingStrategy;
use Zend\Stdlib\Hydrator\ClassMethods;
//use Zend\Db\Adapter\AdapterInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
//use Zend\ServiceManager\FactoryInterface;

class PostModel // extends AbstractAdapterAware // extends \Application\Model\ApplicationModel
{
    /**
     * @var \Zend\Stdlib\Hydrator\HydratorInterface
     */
//    protected $hydrator;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $bodytext;

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
    * @param int $id
    */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
    * {@inheritDoc}
    */
    public function getTitle()
    {
        return $this->title;
    }

    /**
    * @param string $title
    */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
    * {@inheritDoc}
    */
    public function getBodytext()
    {
        return $this->bodytext;
    }

    /**
    * @param string $bodytext
    */
    public function setBodytext($bodytext)
    {
        $this->bodytext = $bodytext;
    }

    /**
     * @var \Blog\Model\PostInterface
     */
    protected $postPrototype;

    public $postColumns = [];
    public $authUserColumns = [];
    public $docTypeColumns = [];
    public $emailRecipientColumns = [];

    /**
     * @param AdapterInterface  $dbAdapter
     * @param HydratorInterface $hydrator
     * @param PostInterface    $postPrototype
     */
    public function __construct(
        $dbAdapter
//        HydratorInterface $hydrator
//        PostInterface $postPrototype
    ) {
//        parent::__construct();
        $this->dbAdapter        = $dbAdapter;
//        var_dump([1=>'asdfaasdfasdf', 2=>'dfasf']);
//        exit();
        $this->hydrator         = new ClassMethods(false);

//        error_log("", 0);
//        error_log("Why do they call it rush hour when nothing moves?", 0);
//
//        error_log("", 0);
//        error_log("A day without sunshine is like, you know, night.", 0);
//
//        error_log("", 0);
//        error_log("Two things are infinite: the universe and human stupidity; and I'm not sure about the universe.", 0);
//
//        error_log("", 0);
//        error_log("I always wanted to be somebody, but now I realize I should have been more specific.", 0);
//


//        var_dump($this->hydrator);exit();

//        $this->dbAdapter      = $dbAdapter;
//        $this->hydrator       = $hydrator;
//        $this->postPrototype  = $postPrototype;

//        var_dump($this->postPrototype);exit();
//        $this->hydrator = new ClassMethods(false);

//        echo '<pre>hydrator';
//        print_r($this->hydrator);
//        echo '</pre>';
//
//        echo '<pre>db';
//        print_r($this->db);
//        echo '</pre>';
//
//        die("YO!!");
//
//        echo '<pre>postPrototype';
//        print_r($this->postPrototype);
//        echo '</pre>';
//
//        die(".......");

        $this->postColumns = [
            'id' => 'ID',
            'title' => 'TITLE',
            'bodytext' => 'TEXT' // set key...value here is the actual field name in the table
        ];
        $this->postPrototype = (object) [ // Simpler\Model\Post // instanceOf Simpler\Model\Post
            'ID' => $this->getId(),
            'TITLE' => $this->getTitle(),
            'TEXT' => $this->getBodytext()
        ];
        $this->authUserColumns = [
            'id' => 'IDENTITY_ID',
            'userid' => 'USER_ID',
            'admin' => 'USER_ADMINISTRATOR'
        ];
        $this->docTypeColumns = [
            'document_id' => 'DOCUMENT_ID',
            'document_type' => 'DOCUMENT_TYPE',
            'description' => 'DESCRIPTION',
            'create_user' => 'CREATE_USER'
        ];
        $this->emailRecipientColumns = [
            'employee_number' => 'SPDEN',
            'initial_email_sent_on' => 'SPDDATSNTE',
            'first_reminder_sent_on' => 'SPD1STNTCE',
            'second_reminder_sent_on' => 'SPD2NDNTCE',
            'link_clicked_on' => 'SPDDATCNFE'
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
//        return array(['1' => 'asdfasdfasdf']);
//        var_dump($this->hydrator);
//        var_dump($this->dbAdapter);
//        exit();

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

//        echo '<pre>';
//        print_r($this->postPrototype);
//        echo '</pre>';
//        exit();

        if ($result->isQueryResult() && $result->getAffectedRows() &&
            $resultIsArray // $result instanceof ResultInterface &&
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
        $select =
            $sql->select('pte_authorized_users')
                ->columns($this->authUserColumns)
                //$select->where(['identity_id = ?' => 8]); // sinlge record
                ->where(['identity_id' => [4,5,6,7,8,9]]); // WHERE identity_id IN_ARRAY(4,5,6,7,8,9)

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

         $select =
             $sql->select('spdbkdtyp')
                 ->columns($this->docTypeColumns)
                 ->order("DESCRIPTION ASC, DOCUMENT_ID ASC")
                 ->limit(5)
                 ->offset(1); // ->limit(100)->offset(100)

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

    public function getEmailRecipientData()
    {
//        $sql = "SELECT
//                    c.spder AS employer_number,
//                    c.spden AS employee_number,
//                    c.spddatsnte AS initial_email_sent_on,
//                    c.spd1stntce AS first_reminder_sent_on,
//                    c.spd2ndntce AS second_reminder_sent_on,
//                    c.spddatcnfe AS link_clicked_on,
//                    p.prcknm as employee_name,
//                    p.preml1 as employee_email_address
//                    FROM cupspdctl c
//                    JOIN prpms p ON p.pren = c.spden
//                    WHERE SPDDOCCODE = '" . $document_code . "'";
//        if($employee_number!=NULL)
//        {
//            $sql .= " AND c.spden LIKE '%" . $employee_number . "%'";
//        }
//
//        if($override) {
//            $sql .= " AND c.spden in(";
//            foreach($emailOverrideEmployeeIds as $key => $eid) {
//                $sql .= "'" . str_pad(trim($eid), 9, ' ', STR_PAD_LEFT) . "',";
//            }
//            $sql = substr($sql, 0, -1);
//            $sql .= ")";
//        }

         $sql    = new Sql($this->dbAdapter);

         $select =
             $sql->select('cupspdctl')
                 ->columns($this->emailRecipientColumns)
//                 ->order("DESCRIPTION ASC, DOCUMENT_ID ASC")
                 ->limit(3);
//                 ->offset(1); // ->limit(100)->offset(100)

//        if(is_array($type)) {
//            $select->where(['document_type' => $type]);
//        } elseif(!is_null($type)) {
//            $select->where(['document_type = ?' => $type]);
//        }

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
