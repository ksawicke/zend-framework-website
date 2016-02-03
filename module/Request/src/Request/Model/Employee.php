<?php
namespace Request\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Request\Model\BaseDB;

/**
 * All Database functions for employees
 *
 * @author sawik
 *
 */
class Employee extends BaseDB
{

    /**
     * @var array
     */
    public $employeeData = [];
    public $employerNumber = '';
    public $includeApproved = '';
    public $timeoffRequestColumns;
    public $timeoffRequestEntryColumns;
    public $timeoffRequestCodeColumns;

    public function __construct()
    {
        parent::__construct();
        $this->employerNumber = '002';
        $this->includeApproved = 'N';
        $this->timeoffRequestColumns = [
            'REQUEST_ID' => 'REQUEST_ID',
            'REQUEST_REASON' => 'REQUEST_REASON',
            'CREATE_TIMESTAMP' => 'CREATE_TIMESTAMP',
            'REQUEST_STATUS' => 'REQUEST_STATUS',
            'REQUESTER_EMPLOYEE_ID' => 'CREATE_USER'
        ];
        $this->timeoffRequestEntryColumns = [
            'REQUEST_DATE' => 'REQUEST_DATE',
            'REQUESTED_HOURS' => 'REQUESTED_HOURS',
            'REQUEST_CODE' => 'REQUEST_CODE'
        ];
        $this->timeoffRequestCodeColumns = [
            'REQUEST_TYPE' => 'DESCRIPTION'
        ];
    }
    
    public function findTimeOffEmployeeData($employeeNumber = null, $includeHourTotals = "Y")
    {        
//         $rawSql = "SELECT * FROM TABLE (timeoff_get_employee_data('002', '49499', 'N')) as DATA";
        
        $rawSql = "select * from table(select prurl1, prfnm from prpms where trim(pren) = '49499') as data";
        
        $statement = $this->adapter->query($rawSql);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        
        echo '<pre>! A  ';
        print_r($resultSet);
        echo '</pre>';
        
        echo '<pre>! B  ';
        print_r($resultSet->toArray()[0]);
        echo '</pre>';
        
        echo '<pre>! C  ';
        print_r($resultSet->getDataSource());
        echo '</pre>';
        
        echo '<pre>! D  ';
        print_r($resultSet->current());
        echo '</pre>';
        
        echo '<pre>! E';
        print_r($resultSet->valid());
        echo '</pre>';
        
        
        
        $rawSql = "select employee_number from table(timeoff_get_employee_data('002', '49499', 'N')) as data";
//        $rawSql = "select * from table(select prurl1, prfnm from prpms where trim(pren) = '49499') as data";
        
        $statement = $this->adapter->query($rawSql);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        
        echo '<pre>! A  ';
        print_r($resultSet);
        echo '</pre>';
        
        echo '<pre>! B  ';
        print_r($resultSet->toArray()[0]);
        echo '</pre>';
        
        echo '<pre>! C  ';
        print_r($resultSet->getDataSource());
        echo '</pre>';
        
        echo '<pre>! D  ';
        print_r($resultSet->current());
        echo '</pre>';
        
        echo '<pre>! E';
        print_r($resultSet->valid());
        echo '</pre>';
        
        die("####################");
        
        $statement = $this->adapter->query($rawSql);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        
        echo '<pre>';
        print_r($resultSet);
        echo '</pre>';
        
        echo '<pre>';
        print_r($result);
        echo '</pre>';
        
        
        
        
        die("$~~$");
        
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet;
            $resultSet->initialize($result);
            echo '<pre>';
            var_dump($resultSet->current());
            echo '</pre>';
//            $this->employeeData = $resultSet->toArray();
        } else {
            $this->employeeData = [];
        }
//        var_dump($resultSet);
        die("!~&");
        
        return $this->trimData($this->employeeData);
    }
    
    public function findTimeOffRequestsByEmployeeAndStatus($employeeNumber = null, $status = "A")
    {   
        $sql = new Sql($this->adapter);
        $select = $sql->select(['entry' => 'TIMEOFF_REQUEST_ENTRIES'])
                ->columns($this->timeoffRequestEntryColumns)
                ->join(['request' => 'TIMEOFF_REQUESTS'], 'request.REQUEST_ID = entry.REQUEST_ID', $this->timeoffRequestColumns)
                ->join(['requestcode' => 'TIMEOFF_REQUEST_CODES'], 'requestcode.REQUEST_CODE = entry.REQUEST_CODE', $this->timeoffRequestCodeColumns)
                ->where(['trim(request.EMPLOYEE_NUMBER)' => trim($employeeNumber), 'request.REQUEST_STATUS' => $status])
                ->order(['entry.REQUEST_DATE ASC']);
        
        try {
            $statement = $sql->prepareStatementForSqlObject($select);
        } catch(Exception $e) {
            var_dump($e);
        }
        
        $result = $statement->execute();

        $resultSet = new ResultSet();
        $resultSet->initialize($result);

        $array = [];
        foreach($resultSet as $row) {
            $array[] = $row;
        }

        return $array;
    }
    
    public function trimData($object)
    {
        array_walk_recursive($object, function( &$value, $key ) {
            /**
             * Value is of type string
             */
            if ( is_string( $value ) ) {
                $value = trim( $value );
            }
        });

        return $object;
    }
    
    /**
     * Use Table Gateway to do queries
     *
//        $sql = new Sql($this->adapter);
//        $select = $sql->select();
//        $select->from('TIMEOFF_REQUEST_ENTRIES');
//        $statement = $sql->prepareStatementForSqlObject($select);
//        $result = $statement->execute();
//        
//        if ($result instanceof ResultInterface && $result->isQueryResult()) {
//            $resultSet = new ResultSet();
//            $resultSet->initialize($result);
//
//            $data = $resultSet->toArray();
//        }
//        return $data;
     */
    
}