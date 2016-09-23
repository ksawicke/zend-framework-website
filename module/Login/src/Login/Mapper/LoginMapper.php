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

    private $nonProductionPassword = 'timeoffrocks';

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

    public function authenticateUser( $username = null, $password = null )
    {
        /**
         * We can validate differently in development or production here.
         */
        switch(ENVIRONMENT) {
            case 'testing':
            case 'development':
                $return = ( $password==$this->nonProductionPassword ? $this->getUserDataByUsername( $username ) : 0 );
            break;

            case 'production':
            default:
                $Login = new \Login\Model\Login();
                $verifyLogin = $Login->verifyLogin( strtoupper(trim($username)), $password );
                $return = ( $verifyLogin===true ? $this->getUserDataByUsername( $username ) : 0 );
                break;
        }

        return $return;
    }

    public function getUserDataByUsername( $username = null )
    {
        $sql = new Sql($this->dbAdapter);
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
            ->join(['manager' => 'PRPSP'], 'employee.PREN = manager.SPEN', [])
            ->join(['manager_addons' => 'PRPMS'], 'manager_addons.PREN = manager.SPSPEN', $this->supervisorAddonColumns)
            ->where(['trim(employee.PRURL1)' => strtoupper(trim($username))]);

        return \Request\Helper\ResultSetOutput::getResultArray($sql, $select);
    }

    public function isManager($employeeNumber = null)
    {
        $rawSql = "select is_manager_mg('002', '" . $employeeNumber . "') AS IS_MANAGER FROM sysibm.sysdummy1";

        $data = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql($this->dbAdapter, $rawSql);

        return $data->IS_MANAGER;
    }

    public function isSupervisor($employeeNumber = null)
    {
        $rawSql = "select is_supervisor('002', '" . $employeeNumber . "') AS IS_SUPERVISOR FROM sysibm.sysdummy1";

        $data = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql($this->dbAdapter, $rawSql);

        return $data->IS_SUPERVISOR;
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
         *    If Level 2 = FIN (from file PPRMS, field PRL02) and
         *    Level 3 starts with PY (from file PRPMS, field PRL02) and
         *    Training group = MGR2 (from file PRPMS, field PRTGRP)
         * 08/23/16 sawik Add:
         *    If in new table TIMEOFF_PAYROLL
         */
        $rawSqlPRPMS = "SELECT (CASE WHEN (
                           PRL02 = 'FIN' AND
                           SUBSTRING(PRL03,0,3) = 'PY' AND
                           PRTGRP = 'MGR2' AND
                           PRTEDH = 0
                        ) THEN 'Y' ELSE 'N' END) AS IS_PAYROLL_ADMIN
                       FROM PRPMS
                       WHERE TRIM(PRPMS.PREN) = '" . $employeeNumber . "'";
        $dataPRPMS = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->dbAdapter, $rawSqlPRPMS );

        /**
         * 2nd check to see if they are added in the db
         */
        $rawSqlTimeOffAdded = "SELECT COUNT(*) AS PAYROLL_ADMIN_ADDED_COUNT
                          FROM TIMEOFF_REQUESTS_PAYROLL_ADMINS
                          WHERE TRIM(EMPLOYEE_NUMBER) = '" . $employeeNumber . "' AND STATUS = 1";
        $dataTimeOffAdded = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->dbAdapter, $rawSqlTimeOffAdded );

        $rawSqlTimeOffDisabled = "SELECT COUNT(*) AS PAYROLL_ADMIN_DISABLED_COUNT
                          FROM TIMEOFF_REQUESTS_PAYROLL_ADMINS
                          WHERE TRIM(EMPLOYEE_NUMBER) = '" . $employeeNumber . "' AND STATUS = 0";
        $dataTimeOffDisabled = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->dbAdapter, $rawSqlTimeOffDisabled );

        $validation = [ 'IS_PAYROLL_ADMIN' => $dataPRPMS[0]->IS_PAYROLL_ADMIN,
                        'PAYROLL_ADMIN_ADDED_COUNT' => $dataTimeOffAdded[0]->PAYROLL_ADMIN_ADDED_COUNT,
                        'PAYROLL_ADMIN_DISABLED_COUNT' => $dataTimeOffDisabled[0]->PAYROLL_ADMIN_DISABLED_COUNT ];

        $isPayrollAdmin = "N";
        if( ( $validation['IS_PAYROLL_ADMIN']=="Y" && $validation['PAYROLL_ADMIN_ADDED_COUNT']==0 && $validation['PAYROLL_ADMIN_DISABLED_COUNT']==0 ) ||
            ( $validation['IS_PAYROLL_ADMIN']=="N" && $validation['PAYROLL_ADMIN_ADDED_COUNT']==1 && $validation['PAYROLL_ADMIN_DISABLED_COUNT']==0 ) ) {
            $isPayrollAdmin = "Y";
        }
        if( $validation['IS_PAYROLL_ADMIN']=="Y" && $validation['PAYROLL_ADMIN_ADDED_COUNT']==0 && $validation['PAYROLL_ADMIN_DISABLED_COUNT']==1 ) {
            $isPayrollAdmin = "N";
        }

        return $isPayrollAdmin;
    }

    /**
     * Returns whether employee is Payroll or not.
     *
     * @param integer $employeeNumber   Integer, up to 9 places. Does not need to be justified.
     * @return boolean   "Y" or "N"
     */
    public function isPayrollAssistant( $employeeNumber = null ) {
        $rawSql = "SELECT COUNT(*) AS RCOUNT FROM TIMEOFF_REQUESTS_PAYROLL_ASSISTANTS
                   WHERE TRIM(EMPLOYEE_NUMBER) = '" . $employeeNumber . "' AND
                   STATUS = 1";

        $data = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql($this->dbAdapter, $rawSql);

        return ( $data[0]->RCOUNT >= 1 ? 'Y' : 'N' );
    }

    public function isProxy($employeeNumber = null)
    {
        $rawSql = "SELECT COUNT(*) AS RCOUNT FROM TIMEOFF_REQUEST_EMPLOYEE_PROXIES
                   WHERE TRIM(PROXY_EMPLOYEE_NUMBER) = '" . $employeeNumber . "' AND
                   STATUS = 1";

        $data = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql($this->dbAdapter, $rawSql);

        return ( $data[0]->RCOUNT >= 1 ? 'Y' : 'N' );
    }

    public function isProxyForManager($employeeNumber = null)
    {
        $rawSql = "SELECT COUNT(*) AS RCOUNT FROM TIMEOFF_REQUEST_EMPLOYEE_PROXIES
                   WHERE TRIM(PROXY_EMPLOYEE_NUMBER) = '" . $employeeNumber . "' AND
                   STATUS = 1 AND (IS_MANAGER_MG('002', EMPLOYEE_NUMBER) = 'Y' OR IS_SUPERVISOR('002', EMPLOYEE_NUMBER) = 'Y' )";

        $data = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql($this->dbAdapter, $rawSql);

        return ( $data[0]->RCOUNT >= 1 ? 'Y' : 'N' );
    }
}
