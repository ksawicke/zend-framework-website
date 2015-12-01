<?php
namespace Request\Mapper;

use Request\Model\RequestInterface;
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

class RequestMapper implements RequestMapperInterface
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
     * @var \Request\Model\RequestInterface
     */
    protected $requestPrototype;

    public $requestColumns = [];

    public $authUserColumns = [];

    public $docTypeColumns = [];

    public $emailRecipientColumns = [];

    public $approvedTimeOffColumns = [];

    /**
     *
     * @param AdapterInterface $dbAdapter
     * @param HydratorInterface $hydrator
     * @param PostInterface $postPrototype
     */
    public function __construct(AdapterInterface $dbAdapter, HydratorInterface $hydrator, RequestInterface $requestPrototype)
    {
        $this->dbAdapter = $dbAdapter;
        $this->hydrator = $hydrator;
        $this->requestPrototype = $requestPrototype;

        // 'alias' => 'FIELDNAME'
        $this->employeeColumns = [
            'EMPLOYER_NUMBER' => 'PRER',
            'EMPLOYEE_NUMBER' => 'PREN',
            'LEVEL_1' => 'PRL01',
            'LEVEL_2' => 'PRL02',
            'LEVEL_3' => 'PRL03',
            'LEVEL_4' => 'PRL04',
            'FIRST_NAME' => 'PRFNM',
            'MIDDLE_INITIAL' => 'PRMNM',
            'LAST_NAME' => 'PRLNM',
            'POSITION' => 'PRPOS',
            'EMAIL_ADDRESS' => 'PREML1',
            'EMPLOYEE_HIRE_DATE' => 'PRDOHE',
            'POSITION_TITLE' => 'PRTITL',
            'GRANDFATHERED_EARNED' => 'PRAC5E',
            'GRANDFATHERED_TAKEN' => 'PRAC5T',
            'PTO_EARNED' => 'PRVAC',
            'PTO_TAKEN' => 'PRVAT',
            'FLOAT_EARNED' => 'PRSHA',
            'FLOAT_TAKEN' => 'PRSHT',
            'SICK_EARNED' => 'PRSDA',
            'SICK_TAKEN' => 'PRSDT',
            'COMPANY_MANDATED_EARNED' => 'PRAC4E',
            'COMPANY_MANDATED_TAKEN' => 'PRAC4T',
            'DRIVER_SICK_EARNED' => 'PRAC6E',
            'DRIVER_SICK_TAKEN' => 'PRAC6T'
        ];
//         $this->employeeSupervisorColumns = [
//             'MANAGER_EMPLOYEE_NUMBER' => 'SPSPEN',
//             'MANAGER_FIRST_NAME' => 'SPSPFNM',
//             'MANAGER_MIDDLE_INITIAL' => 'SPSPMI',
//             'MANAGER_LAST_NAME' => 'SPSPLNM'
//         ];
        $this->supervisorAddonColumns = [
            'MANAGER_EMPLOYER_NUMBER' => 'PRER',
            'MANAGER_EMPLOYEE_NUMBER' => 'PREN',
            'MANAGER_FIRST_NAME' => 'PRFNM',
            'MANAGER_MIDDLE_INITIAL' => 'PRMNM',
            'MANAGER_LAST_NAME' => 'PRLNM',
            'MANAGER_EMAIL_ADDRESS' => 'PREML1'
        ];
        $this->approvedTimeOffColumns = [
            'WEEK_ENDING_DATE' => 'AAWEND',
            'WEEK_END_YR' => 'AAWEYR',
            'WEEK_END_MO' => 'AAWEMO',
            'WEEK_END_DA' => 'AAWEDA',

            'WEEK_1_DAY_1_DAY_OF_WEEK' => 'AAWK1DA1',
            'WEEK_1_DAY_1_ABSENT_DATE_1' => 'AAWK1DT1',
            'WEEK_1_DAY_1_ABSENT_HOURS_1' => 'AAWK1HR1A',
            'WEEK_1_DAY_1_ABSENT_REASON_1' => 'AAWK1RC1A',
            'WEEK_1_DAY_1_ABSENT_HOURS_2' => 'AAWK1HR1B',
            'WEEK_1_DAY_1_ABSENT_REASON_2' => 'AAWK1RC1B',

            'WEEK_1_DAY_2_DAY_OF_WEEK' => 'AAWK1DA2',
            'WEEK_1_DAY_2_ABSENT_DATE_1' => 'AAWK1DT2',
            'WEEK_1_DAY_2_ABSENT_HOURS_1' => 'AAWK1HR2A',
            'WEEK_1_DAY_2_ABSENT_REASON_1' => 'AAWK1RC2A',
            'WEEK_1_DAY_2_ABSENT_HOURS_2' => 'AAWK1HR2B',
            'WEEK_1_DAY_2_ABSENT_REASON_2' => 'AAWK1RC2B',

            'WEEK_1_DAY_3_DAY_OF_WEEK' => 'AAWK1DA3',
            'WEEK_1_DAY_3_ABSENT_DATE_1' => 'AAWK1DT3',
            'WEEK_1_DAY_3_ABSENT_HOURS_1' => 'AAWK1HR3A',
            'WEEK_1_DAY_3_ABSENT_REASON_1' => 'AAWK1RC3A',
            'WEEK_1_DAY_3_ABSENT_HOURS_2' => 'AAWK1HR3B',
            'WEEK_1_DAY_3_ABSENT_REASON_2' => 'AAWK1RC3B',

            'WEEK_1_DAY_4_DAY_OF_WEEK' => 'AAWK1DA4',
            'WEEK_1_DAY_4_ABSENT_DATE_1' => 'AAWK1DT4',
            'WEEK_1_DAY_4_ABSENT_HOURS_1' => 'AAWK1HR4A',
            'WEEK_1_DAY_4_ABSENT_REASON_1' => 'AAWK1RC4A',
            'WEEK_1_DAY_4_ABSENT_HOURS_2' => 'AAWK1HR4B',
            'WEEK_1_DAY_4_ABSENT_REASON_2' => 'AAWK1RC4B',

            'WEEK_1_DAY_5_DAY_OF_WEEK' => 'AAWK1DA5',
            'WEEK_1_DAY_5_ABSENT_DATE_1' => 'AAWK1DT5',
            'WEEK_1_DAY_5_ABSENT_HOURS_1' => 'AAWK1HR5A',
            'WEEK_1_DAY_5_ABSENT_REASON_1' => 'AAWK1RC5A',
            'WEEK_1_DAY_5_ABSENT_HOURS_2' => 'AAWK1HR5B',
            'WEEK_1_DAY_5_ABSENT_REASON_2' => 'AAWK1RC5B',

            'WEEK_1_DAY_6_DAY_OF_WEEK' => 'AAWK1DA6',
            'WEEK_1_DAY_6_ABSENT_DATE_1' => 'AAWK1DT6',
            'WEEK_1_DAY_6_ABSENT_HOURS_1' => 'AAWK1HR6A',
            'WEEK_1_DAY_6_ABSENT_REASON_1' => 'AAWK1RC6A',
            'WEEK_1_DAY_6_ABSENT_HOURS_2' => 'AAWK1HR6B',
            'WEEK_1_DAY_6_ABSENT_REASON_2' => 'AAWK1RC6B',

            'WEEK_1_DAY_7_DAY_OF_WEEK' => 'AAWK1DA7',
            'WEEK_1_DAY_7_ABSENT_DATE_1' => 'AAWK1DT7',
            'WEEK_1_DAY_7_ABSENT_HOURS_1' => 'AAWK1HR7A',
            'WEEK_1_DAY_7_ABSENT_REASON_1' => 'AAWK1RC7A',
            'WEEK_1_DAY_7_ABSENT_HOURS_2' => 'AAWK1HR7B',
            'WEEK_1_DAY_7_ABSENT_REASON_2' => 'AAWK1RC7B',

            'WEEK_2_DAY_1_DAY_OF_WEEK' => 'AAWK1DA1',
            'WEEK_2_DAY_1_ABSENT_DATE_1' => 'AAWK1DT1',
            'WEEK_2_DAY_1_ABSENT_HOURS_1' => 'AAWK1HR1A',
            'WEEK_2_DAY_1_ABSENT_REASON_1' => 'AAWK1RC1A',
            'WEEK_2_DAY_1_ABSENT_HOURS_2' => 'AAWK1HR1B',
            'WEEK_2_DAY_1_ABSENT_REASON_2' => 'AAWK1RC1B',

            'WEEK_2_DAY_2_DAY_OF_WEEK' => 'AAWK1DA2',
            'WEEK_2_DAY_2_ABSENT_DATE_1' => 'AAWK1DT2',
            'WEEK_2_DAY_2_ABSENT_HOURS_1' => 'AAWK1HR2A',
            'WEEK_2_DAY_2_ABSENT_REASON_1' => 'AAWK1RC2A',
            'WEEK_2_DAY_2_ABSENT_HOURS_2' => 'AAWK1HR2B',
            'WEEK_2_DAY_2_ABSENT_REASON_2' => 'AAWK1RC2B',

            'WEEK_2_DAY_3_DAY_OF_WEEK' => 'AAWK1DA3',
            'WEEK_2_DAY_3_ABSENT_DATE_1' => 'AAWK1DT3',
            'WEEK_2_DAY_3_ABSENT_HOURS_1' => 'AAWK1HR3A',
            'WEEK_2_DAY_3_ABSENT_REASON_1' => 'AAWK1RC3A',
            'WEEK_2_DAY_3_ABSENT_HOURS_2' => 'AAWK1HR3B',
            'WEEK_2_DAY_3_ABSENT_REASON_2' => 'AAWK1RC3B',

            'WEEK_2_DAY_4_DAY_OF_WEEK' => 'AAWK1DA4',
            'WEEK_2_DAY_4_ABSENT_DATE_1' => 'AAWK1DT4',
            'WEEK_2_DAY_4_ABSENT_HOURS_1' => 'AAWK1HR4A',
            'WEEK_2_DAY_4_ABSENT_REASON_1' => 'AAWK1RC4A',
            'WEEK_2_DAY_4_ABSENT_HOURS_2' => 'AAWK1HR4B',
            'WEEK_2_DAY_4_ABSENT_REASON_2' => 'AAWK1RC4B',

            'WEEK_2_DAY_5_DAY_OF_WEEK' => 'AAWK1DA5',
            'WEEK_2_DAY_5_ABSENT_DATE_1' => 'AAWK1DT5',
            'WEEK_2_DAY_5_ABSENT_HOURS_1' => 'AAWK1HR5A',
            'WEEK_2_DAY_5_ABSENT_REASON_1' => 'AAWK1RC5A',
            'WEEK_2_DAY_5_ABSENT_HOURS_2' => 'AAWK1HR5B',
            'WEEK_2_DAY_5_ABSENT_REASON_2' => 'AAWK1RC5B',

            'WEEK_2_DAY_6_DAY_OF_WEEK' => 'AAWK1DA6',
            'WEEK_2_DAY_6_ABSENT_DATE_1' => 'AAWK1DT6',
            'WEEK_2_DAY_6_ABSENT_HOURS_1' => 'AAWK1HR6A',
            'WEEK_2_DAY_6_ABSENT_REASON_1' => 'AAWK1RC6A',
            'WEEK_2_DAY_6_ABSENT_HOURS_2' => 'AAWK1HR6B',
            'WEEK_2_DAY_6_ABSENT_REASON_2' => 'AAWK1RC6B',

            'WEEK_2_DAY_7_DAY_OF_WEEK' => 'AAWK1DA7',
            'WEEK_2_DAY_7_ABSENT_DATE_1' => 'AAWK1DT7',
            'WEEK_2_DAY_7_ABSENT_HOURS_1' => 'AAWK1HR7A',
            'WEEK_2_DAY_7_ABSENT_REASON_1' => 'AAWK1RC7A',
            'WEEK_2_DAY_7_ABSENT_HOURS_2' => 'AAWK1HR7B',
            'WEEK_2_DAY_7_ABSENT_REASON_2' => 'AAWK1RC7B',
        ];

        // Now tell the Hydrator to array_flip the keys on save.
        // Advantage: This allows us to refer to easier to understand field names on the
        // front end, but let the application deal with the real names on the back end
        // as in when doing an update.
        // Can pass in multiple arrays here.
        $this->hydrator->setNamingStrategy(new ArrayMapNamingStrategy($this->employeeColumns, $this->supervisorAddonColumns));
        // $this->employeeSupervisorColumns
    }

    public function findTimeOffBalancesByEmployee($employeeId = null)
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select(['employee' => 'PRPMS'])
            ->columns($this->employeeColumns)
            ->join(['manager' => 'PRPSP'], 'employee.PREN = manager.SPEN', []) // $this->employeeSupervisorColumns
            ->join(['manager_addons' => 'PRPMS'], 'manager_addons.PREN = manager.SPSPEN', $this->supervisorAddonColumns)
            ->where(['trim(employee.PREN)' => trim($employeeId)]);

        return \Request\Helper\ResultSetOutput::getResultRecord($sql, $select);
    }

    public function findTimeOffApprovedRequestsByEmployee($employeeId = null)
    {
        // SELECT * FROM PAPAA WHERE AACLK# = '   348370'
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select(['approvedtime' => 'PAPAA'])
            ->columns($this->approvedTimeOffColumns)
            ->where(['trim(approvedtime.AACLK#)' => trim($employeeId)]);

        return \Request\Helper\ResultSetOutput::getResultArray($sql, $select);
    }

    public function findTimeOffBalancesByManager($managerEmployeeId = null)
    {
        // select EMPLOYEE_ID from table (care_get_manager_employees('002', '   229589', 'D')) as data;;
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select(["data" => "table (care_get_manager_employees('002', '   229589', ''))"])
            ->columns(['EMPLOYEE_ID'])
            ->join(['employee' => 'PRPMS'], 'employee.PREN = data.EMPLOYEE_ID', $this->employeeColumns);

        return \Request\Helper\ResultSetOutput::getResultArray($sql, $select);
    }
}
