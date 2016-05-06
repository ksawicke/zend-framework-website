<?php
namespace Login\Mapper;

use Login\Model\LoginInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Expression;
use Zend\Db\ResultSet\ResultSet;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\NamingStrategy\ArrayMapNamingStrategy;

class LoginMapper implements LoginMapperInterface
{

    /**
     *
     * @var \Zend\Db\Adapter\AdapterInterface
     */
    protected $dbAdapter;

    /**
     *
     * @var \Zend\Stdlib\Hydrator\HydratorInterface
     */
    protected $hydrator;

    /**
     *
     * @var \Login\Model\LoginInterface
     */
    protected $loginPrototype;

    /**
     *
     * @param AdapterInterface $dbAdapter
     * @param HydratorInterface $hydrator
     * @param PostInterface $postPrototype
     */
    public function __construct(AdapterInterface $dbAdapter, HydratorInterface $hydrator, LoginInterface $loginPrototype)
    {
        $this->dbAdapter = $dbAdapter;
        $this->hydrator = $hydrator;
        $this->loginPrototype = $loginPrototype;
        
//         $this->employeeColumns = [
//             'EMPLOYER_NUMBER' => 'PRER',
//             'EMPLOYEE_NUMBER' => 'PREN',
//             'LEVEL_1' => 'PRL01',
//             'LEVEL_2' => 'PRL02',
//             'LEVEL_3' => 'PRL03',
//             'LEVEL_4' => 'PRL04',
//             'FIRST_NAME' => 'PRFNM',
//             'MIDDLE_INITIAL' => 'PRMNM',
//             'LAST_NAME' => 'PRLNM',
//             'POSITION' => 'PRPOS',
//             'EMAIL_ADDRESS' => 'PREML1',
//             'EMPLOYEE_HIRE_DATE' => 'PRDOHE',
//             'POSITION_TITLE' => 'PRTITL'
//         ];
        
        $this->supervisorAddonColumns = [
            'MANAGER_EMPLOYER_NUMBER' => 'PRER',
            'MANAGER_EMPLOYEE_NUMBER' => 'PREN',
            'MANAGER_FIRST_NAME' => 'PRFNM',
            'MANAGER_MIDDLE_INITIAL' => 'PRMNM',
            'MANAGER_LAST_NAME' => 'PRLNM',
            'MANAGER_EMAIL_ADDRESS' => 'PREML1'
        ];
        
        // Now tell the Hydrator to array_flip the keys on save.
        // Advantage: This allows us to refer to easier to understand field names on the
        // front end, but let the application deal with the real names on the back end
        // as in when doing an update.
        // Can pass in multiple arrays here.
        $this->hydrator->setNamingStrategy(new ArrayMapNamingStrategy($this->supervisorAddonColumns));
        // $this->employeeSupervisorColumns
    }

    public function authenticateUser($username = null, $password = null)
    {
        $sql = new Sql($this->dbAdapter);
        
        /**
         * We can validate differently in development or production here.
         */
        switch(ENVIRONMENT) {
            case 'testing':
            case 'development':
            case 'production':
            default:
                $select = $sql->select(['employee' => 'PRPMS'])
                    ->columns([
                               'EMPLOYER_NUMBER' => 'PRER',
                               'EMPLOYEE_NUMBER' => 'PREN',
                               'LEVEL_1' => 'PRL01',
                               'LEVEL_2' => 'PRL02',
                               'LEVEL_3' => 'PRL03',
                               'LEVEL_4' => 'PRL04',
                               'COMMON_NAME' => 'PRCOMN',
                               'FIRST_NAME' => 'PRFNM',
                               'MIDDLE_INITIAL' => 'PRMNM',
                               'LAST_NAME' => 'PRLNM',
                               'POSITION' => 'PRPOS',
                               'EMAIL_ADDRESS' => 'PREML1',
                               'USERNAME' => 'PRURL1',
                               'EMPLOYEE_HIRE_DATE' => 'PRDOHE',
                               'POSITION_TITLE' => 'PRTITL'
                             ])
//                     ->where(['trim(employee.PREML1)' => trim($username)]);
                    ->join(['manager' => 'PRPSP'], 'employee.PREN = manager.SPEN', [])
                    ->join(['manager_addons' => 'PRPMS'], 'manager_addons.PREN = manager.SPSPEN', $this->supervisorAddonColumns)
                    ->where(['trim(employee.PRURL1)' => strtoupper(trim($username))]);
                break;
        }
        
        $return = \Request\Helper\ResultSetOutput::getResultArray($sql, $select);
        
        return $return;
    }
    
    public function isManager($employeeNumber = null)
    {
        $rawSql = "select is_manager_mg('002', '" . $employeeNumber . "') AS IS_MANAGER FROM sysibm.sysdummy1";
    
        $data = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql($this->dbAdapter, $rawSql);
    
        return $data[0]->IS_MANAGER;
    }
    
    /**
     * Returns whether employee is Payroll Admin OR Assistant.
     * 
     * @param integer $employeeNumber   Integer, up to 9 places. Does not need to be justified.
     * @return boolean   "Y" or "N"
     */
    public function isPayroll( $employeeNumber = null ) {
        return ( ( $this->isPayrollAdmin( $employeeNumber ) === "Y" ) ||
                 ( $this->isPayrollAssistant( $employeeNumber) === "Y" ) ? "Y" : "N" );
    }

    /**
     * Returns whether employee is Payroll or not.
     * 
     * @param integer $employeeNumber   Integer, up to 9 places. Does not need to be justified.
     * @return boolean   "Y" or "N"
     */
    public function isPayrollAdmin( $employeeNumber = null ) {
        /**
         * 05/06/16 sawik Change to:
         * If Level 2 = FIN (from file PPRMS, field PRL02) and
         *    Level 3 starts with PY (from file PRPMS, field PRL02) and
         *    Training group = MGR2 (from file PRPMS, field PRTGRP)
         */
        $rawSql = "SELECT
                   (CASE WHEN (
                      PRL02 = 'FIN' AND
                      SUBSTRING(PRL03,0,3) = 'PY' AND
                      PRTGRP = 'MGR2' AND
                      PRTEDH = 0
                   ) THEN 'Y' ELSE 'N' END) AS IS_PAYROLL_ADMIN
                   FROM PRPMS
                   WHERE TRIM(PRPMS.PREN) = '" . $employeeNumber . "'";

        $data = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->dbAdapter, $rawSql );
        
        return $data[0]->IS_PAYROLL_ADMIN;
    }
    
    /**
     * Returns whether employee is Payroll or not.
     * 
     * @param integer $employeeNumber   Integer, up to 9 places. Does not need to be justified.
     * @return boolean   "Y" or "N"
     */
    public function isPayrollAssistant( $employeeNumber = null ) {
        /**
         * 05/06/16 sawik TODO: Add query on new table to see if they were added as a Payroll Assistant
         */
        return "N";
    }
    
    public function isProxy($employeeNumber = null)
    {
        $rawSql = "SELECT COUNT(*) AS RCOUNT FROM TIMEOFF_REQUEST_EMPLOYEE_PROXIES
                   WHERE TRIM(PROXY_EMPLOYEE_NUMBER) = '" . $employeeNumber . "' AND
                   STATUS = 1";
    
        $data = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql($this->dbAdapter, $rawSql);
    
        return ( $data[0]->RCOUNT >= 1 ? 'Y' : 'N' );
    }
}
