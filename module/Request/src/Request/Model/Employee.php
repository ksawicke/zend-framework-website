<?php
namespace Request\Model;

use Zend\Db\Sql\Sql;
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
    
    public $employerNumber = '002';
    public $includeApproved = 'N';

    public function __construct()
    {
        parent::__construct();
        $this->employerNumber = '002';
        $this->includeApproved = 'N';
    }
    
    public function findTimeOffEmployeeData($employeeNumber = null, $includeHourTotals = "Y")
    {
        $rawSql = ($includeHourTotals=="Y") ?
            "select * from table(sawik.timeoff_get_employee_data('" . $this->employerNumber .
            "', '" . $employeeNumber . "', '" . $this->includeApproved . "')) as data"
            :
            "select EMPLOYEE_NUMBER, EMPLOYEE_NAME, EMAIL_ADDRESS, LEVEL_1, LEVEL_2, LEVEL_3," .
              "LEVEL_4, POSITION, POSITION_TITLE, EMPLOYEE_HIRE_DATE, SALARY_TYPE," .
              "MANAGER_EMPLOYEE_NUMBER, MANAGER_POSITION, MANAGER_POSITION_TITLE," .
              "MANAGER_NAME, MANAGER_EMAIL_ADDRESS from table(sawik.timeoff_get_employee_data('" . $this->employerNumber .
            "', '" . $employeeNumber . "', '" . $this->includeApproved . "')) as data";

        $this->employeeData = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql($this->adapter, $rawSql);

        return $this->employeeData;
    }
    
    public function trimEmployeeData()
    {
        array_walk_recursive($this->employeeData, function( &$value, $key ) {
            /**
             * Value is of type string
             */
            if ( is_string( $value ) ) {
                $value = trim( $value );
            }
        });

        return $this;
    }
    
}