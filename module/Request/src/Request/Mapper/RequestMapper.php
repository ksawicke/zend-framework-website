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
    
    public static $requestStatuses = [
        'draft' => 'D',
        'approved' => 'A',
        'cancelled' => 'C',
        'pendingApproval' => 'P',
        'beingReviewed' => 'R'
    ];

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
            'GRANDFATHERED_AVAILABLE' => 'PRAC5E - employee.PRAC5T',
            'GRANDFATHERED_REMAINING' => 'PRAC5E - employee.PRAC5T - pendingrequests.REQGFV',
            'PTO_EARNED' => 'PRVAC',
            'PTO_TAKEN' => 'PRVAT',
            'PTO_AVAILABLE' => 'PRVAC - employee.PRVAT', // Need to manually add the table alias on 2nd field
            'PTO_REMAINING' => 'PRVAC - employee.PRVAT - pendingrequests.REQPTO',
            'FLOAT_EARNED' => 'PRSHA',
            'FLOAT_TAKEN' => 'PRSHT',
            'FLOAT_AVAILABLE' => 'PRSHA - employee.PRSHT', // Need to manually add the table alias on 2nd field
            'FLOAT_REMAINING' => 'PRSHA - employee.PRSHT - pendingrequests.REQFLOAT',
            'SICK_EARNED' => 'PRSDA',
            'SICK_TAKEN' => 'PRSDT',
            'SICK_AVAILABLE' => 'PRSDA - employee.PRSDT',
            'SICK_REMAINING' => 'PRSDA - employee.PRSDT - pendingrequests.REQSICK',
            'COMPANY_MANDATED_EARNED' => 'PRAC4E',
            'COMPANY_MANDATED_TAKEN' => 'PRAC4T',
            'COMPANY_MANDATED_AVAILABLE' => 'PRAC4E', // - employee.PRAC4T Need to manually add the table alias on 2nd field
            'DRIVER_SICK_EARNED' => 'PRAC6E',
            'DRIVER_SICK_TAKEN' => 'PRAC6T',
            'DRIVER_SICK_AVAILABLE' => 'PRAC6E - employee.PRAC6T' // Need to manually add the table alias on 2nd field
        ];
        $this->pendingRequestColumns = [
            'GRANDFATHERED_PENDING' => 'REQGFV',
            'PTO_PENDING' => 'REQPTO',
            'FLOAT_PENDING' => 'REQFLOAT',
            'SICK_PENDING' => 'REQSICK',
            'TOM_PENDING' => 'REQTOM',
            'VAC_PENDING' => 'REQVAC'            
        ];
        $this->employeeCalendarColumns = [
            'REQUEST_EMPLOYEE_NUMBER' => 'PREN',
            'REQUEST_FIRST_NAME' => 'PRFNM',
            'REQUEST_MIDDLE_INITIAL' => 'PRMNM',
            'REQUEST_LAST_NAME' => 'PRLNM'
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
        $this->timeoffRequestColumns = [
            'REQUEST_ID' => 'REQUEST_ID'
        ];
        $this->timeoffRequestEntryColumns = [
            'REQUEST_DATE' => 'REQUEST_DATE',
            'REQUESTED_HOURS' => 'REQUESTED_HOURS'
        ];
        $this->timeoffRequestCodeColumns = [
            'REQUEST_TYPE' => 'DESCRIPTION'
        ];
//         $this->approvedTimeOffColumns = [
//             'WEEK_ENDING_DATE' => 'AAWEND',
//             'WEEK_END_YR' => 'AAWEYR',
//             'WEEK_END_MO' => 'AAWEMO',
//             'WEEK_END_DA' => 'AAWEDA',

//             'WEEK_1_DAY_1_DAY_OF_WEEK' => 'AAWK1DA1',
//             'WEEK_1_DAY_1_ABSENT_DATE_1' => 'AAWK1DT1',
//             'WEEK_1_DAY_1_ABSENT_HOURS_1' => 'AAWK1HR1A',
//             'WEEK_1_DAY_1_ABSENT_REASON_1' => 'AAWK1RC1A',
//             'WEEK_1_DAY_1_ABSENT_HOURS_2' => 'AAWK1HR1B',
//             'WEEK_1_DAY_1_ABSENT_REASON_2' => 'AAWK1RC1B',

//             'WEEK_1_DAY_2_DAY_OF_WEEK' => 'AAWK1DA2',
//             'WEEK_1_DAY_2_ABSENT_DATE_1' => 'AAWK1DT2',
//             'WEEK_1_DAY_2_ABSENT_HOURS_1' => 'AAWK1HR2A',
//             'WEEK_1_DAY_2_ABSENT_REASON_1' => 'AAWK1RC2A',
//             'WEEK_1_DAY_2_ABSENT_HOURS_2' => 'AAWK1HR2B',
//             'WEEK_1_DAY_2_ABSENT_REASON_2' => 'AAWK1RC2B',

//             'WEEK_1_DAY_3_DAY_OF_WEEK' => 'AAWK1DA3',
//             'WEEK_1_DAY_3_ABSENT_DATE_1' => 'AAWK1DT3',
//             'WEEK_1_DAY_3_ABSENT_HOURS_1' => 'AAWK1HR3A',
//             'WEEK_1_DAY_3_ABSENT_REASON_1' => 'AAWK1RC3A',
//             'WEEK_1_DAY_3_ABSENT_HOURS_2' => 'AAWK1HR3B',
//             'WEEK_1_DAY_3_ABSENT_REASON_2' => 'AAWK1RC3B',

//             'WEEK_1_DAY_4_DAY_OF_WEEK' => 'AAWK1DA4',
//             'WEEK_1_DAY_4_ABSENT_DATE_1' => 'AAWK1DT4',
//             'WEEK_1_DAY_4_ABSENT_HOURS_1' => 'AAWK1HR4A',
//             'WEEK_1_DAY_4_ABSENT_REASON_1' => 'AAWK1RC4A',
//             'WEEK_1_DAY_4_ABSENT_HOURS_2' => 'AAWK1HR4B',
//             'WEEK_1_DAY_4_ABSENT_REASON_2' => 'AAWK1RC4B',

//             'WEEK_1_DAY_5_DAY_OF_WEEK' => 'AAWK1DA5',
//             'WEEK_1_DAY_5_ABSENT_DATE_1' => 'AAWK1DT5',
//             'WEEK_1_DAY_5_ABSENT_HOURS_1' => 'AAWK1HR5A',
//             'WEEK_1_DAY_5_ABSENT_REASON_1' => 'AAWK1RC5A',
//             'WEEK_1_DAY_5_ABSENT_HOURS_2' => 'AAWK1HR5B',
//             'WEEK_1_DAY_5_ABSENT_REASON_2' => 'AAWK1RC5B',

//             'WEEK_1_DAY_6_DAY_OF_WEEK' => 'AAWK1DA6',
//             'WEEK_1_DAY_6_ABSENT_DATE_1' => 'AAWK1DT6',
//             'WEEK_1_DAY_6_ABSENT_HOURS_1' => 'AAWK1HR6A',
//             'WEEK_1_DAY_6_ABSENT_REASON_1' => 'AAWK1RC6A',
//             'WEEK_1_DAY_6_ABSENT_HOURS_2' => 'AAWK1HR6B',
//             'WEEK_1_DAY_6_ABSENT_REASON_2' => 'AAWK1RC6B',

//             'WEEK_1_DAY_7_DAY_OF_WEEK' => 'AAWK1DA7',
//             'WEEK_1_DAY_7_ABSENT_DATE_1' => 'AAWK1DT7',
//             'WEEK_1_DAY_7_ABSENT_HOURS_1' => 'AAWK1HR7A',
//             'WEEK_1_DAY_7_ABSENT_REASON_1' => 'AAWK1RC7A',
//             'WEEK_1_DAY_7_ABSENT_HOURS_2' => 'AAWK1HR7B',
//             'WEEK_1_DAY_7_ABSENT_REASON_2' => 'AAWK1RC7B',

//             'WEEK_2_DAY_1_DAY_OF_WEEK' => 'AAWK1DA1',
//             'WEEK_2_DAY_1_ABSENT_DATE_1' => 'AAWK1DT1',
//             'WEEK_2_DAY_1_ABSENT_HOURS_1' => 'AAWK1HR1A',
//             'WEEK_2_DAY_1_ABSENT_REASON_1' => 'AAWK1RC1A',
//             'WEEK_2_DAY_1_ABSENT_HOURS_2' => 'AAWK1HR1B',
//             'WEEK_2_DAY_1_ABSENT_REASON_2' => 'AAWK1RC1B',

//             'WEEK_2_DAY_2_DAY_OF_WEEK' => 'AAWK1DA2',
//             'WEEK_2_DAY_2_ABSENT_DATE_1' => 'AAWK1DT2',
//             'WEEK_2_DAY_2_ABSENT_HOURS_1' => 'AAWK1HR2A',
//             'WEEK_2_DAY_2_ABSENT_REASON_1' => 'AAWK1RC2A',
//             'WEEK_2_DAY_2_ABSENT_HOURS_2' => 'AAWK1HR2B',
//             'WEEK_2_DAY_2_ABSENT_REASON_2' => 'AAWK1RC2B',

//             'WEEK_2_DAY_3_DAY_OF_WEEK' => 'AAWK1DA3',
//             'WEEK_2_DAY_3_ABSENT_DATE_1' => 'AAWK1DT3',
//             'WEEK_2_DAY_3_ABSENT_HOURS_1' => 'AAWK1HR3A',
//             'WEEK_2_DAY_3_ABSENT_REASON_1' => 'AAWK1RC3A',
//             'WEEK_2_DAY_3_ABSENT_HOURS_2' => 'AAWK1HR3B',
//             'WEEK_2_DAY_3_ABSENT_REASON_2' => 'AAWK1RC3B',

//             'WEEK_2_DAY_4_DAY_OF_WEEK' => 'AAWK1DA4',
//             'WEEK_2_DAY_4_ABSENT_DATE_1' => 'AAWK1DT4',
//             'WEEK_2_DAY_4_ABSENT_HOURS_1' => 'AAWK1HR4A',
//             'WEEK_2_DAY_4_ABSENT_REASON_1' => 'AAWK1RC4A',
//             'WEEK_2_DAY_4_ABSENT_HOURS_2' => 'AAWK1HR4B',
//             'WEEK_2_DAY_4_ABSENT_REASON_2' => 'AAWK1RC4B',

//             'WEEK_2_DAY_5_DAY_OF_WEEK' => 'AAWK1DA5',
//             'WEEK_2_DAY_5_ABSENT_DATE_1' => 'AAWK1DT5',
//             'WEEK_2_DAY_5_ABSENT_HOURS_1' => 'AAWK1HR5A',
//             'WEEK_2_DAY_5_ABSENT_REASON_1' => 'AAWK1RC5A',
//             'WEEK_2_DAY_5_ABSENT_HOURS_2' => 'AAWK1HR5B',
//             'WEEK_2_DAY_5_ABSENT_REASON_2' => 'AAWK1RC5B',

//             'WEEK_2_DAY_6_DAY_OF_WEEK' => 'AAWK1DA6',
//             'WEEK_2_DAY_6_ABSENT_DATE_1' => 'AAWK1DT6',
//             'WEEK_2_DAY_6_ABSENT_HOURS_1' => 'AAWK1HR6A',
//             'WEEK_2_DAY_6_ABSENT_REASON_1' => 'AAWK1RC6A',
//             'WEEK_2_DAY_6_ABSENT_HOURS_2' => 'AAWK1HR6B',
//             'WEEK_2_DAY_6_ABSENT_REASON_2' => 'AAWK1RC6B',

//             'WEEK_2_DAY_7_DAY_OF_WEEK' => 'AAWK1DA7',
//             'WEEK_2_DAY_7_ABSENT_DATE_1' => 'AAWK1DT7',
//             'WEEK_2_DAY_7_ABSENT_HOURS_1' => 'AAWK1HR7A',
//             'WEEK_2_DAY_7_ABSENT_REASON_1' => 'AAWK1RC7A',
//             'WEEK_2_DAY_7_ABSENT_HOURS_2' => 'AAWK1HR7B',
//             'WEEK_2_DAY_7_ABSENT_REASON_2' => 'AAWK1RC7B',
//         ];

        // Now tell the Hydrator to array_flip the keys on save.
        // Advantage: This allows us to refer to easier to understand field names on the
        // front end, but let the application deal with the real names on the back end
        // as in when doing an update.
        // Can pass in multiple arrays here.
        $this->hydrator->setNamingStrategy(new ArrayMapNamingStrategy($this->employeeColumns, $this->employeeCalendarColumns, $this->supervisorAddonColumns, $this->timeoffRequestColumns));
        // $this->employeeSupervisorColumns
    }

    /**
     * Find time off balances by Employee Number lookup.
     * 
     * {@inheritDoc}
     * @see \Request\Mapper\RequestMapperInterface::findTimeOffBalancesByEmployee()
     */
    // TODO: sawik 12/04/15 Change the join to join on the actual employee id,
    // hardcoded here.
    public function findTimeOffBalancesByEmployee($employeeId = null)
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select(['employee' => 'PRPMS'])
            ->columns($this->employeeColumns)
            ->join(['manager' => 'PRPSP'], 'employee.PREN = manager.SPEN', []) // $this->employeeSupervisorColumns
            ->join(['manager_addons' => 'PRPMS'], 'manager_addons.PREN = manager.SPSPEN', $this->supervisorAddonColumns)
            ->join(['pendingrequests' => 'PAPREQ'], "pendingrequests.REQCLK# = '101639'", $this->pendingRequestColumns)
            ->join(['pendingpto' => "(
            	select '" . $employeeId . "' as employee_number, sum(entry.requested_hours) as PTO_PENDING_APPROVAL from timeoff_request_entries entry
            	inner join timeoff_requests request ON request.request_id = entry.request_id
            	where
                		entry.request_id in (
                    		select request.request_id from timeoff_requests request where
                        		request.employee_number = '" . $employeeId . "' AND
                	    		request.request_status = 'P' AND
            	    		entry.request_code = 'P'
                		)
                )"], "pendingpto.EMPLOYEE_NUMBER = '" . $employeeId . "'", ['PTO_PENDING_APPROVAL' => 'PTO_PENDING_APPROVAL'])
            ->join(['pendingfloat' => "(
            	select '" . $employeeId . "' as employee_number, sum(entry.requested_hours) as FLOAT_PENDING_APPROVAL from timeoff_request_entries entry
            	inner join timeoff_requests request ON request.request_id = entry.request_id
            	where
                		entry.request_id in (
                    		select request.request_id from timeoff_requests request where
                        		request.employee_number = '" . $employeeId . "' AND
                	    		request.request_status = 'P' AND
            	    		entry.request_code = 'K'
                		)
                )"], "pendingfloat.EMPLOYEE_NUMBER = '" . $employeeId . "'", ['FLOAT_PENDING_APPROVAL' => 'FLOAT_PENDING_APPROVAL'])
            ->join(['pendingsick' => "(
            	select '" . $employeeId . "' as employee_number, sum(entry.requested_hours) as SICK_PENDING_APPROVAL from timeoff_request_entries entry
            	inner join timeoff_requests request ON request.request_id = entry.request_id
            	where
                		entry.request_id in (
                    		select request.request_id from timeoff_requests request where
                        		request.employee_number = '" . $employeeId . "' AND
                	    		request.request_status = 'P' AND
            	    		entry.request_code = 'S'
                		)
                )"], "pendingsick.EMPLOYEE_NUMBER = '" . $employeeId . "'", ['SICK_PENDING_APPROVAL' => 'SICK_PENDING_APPROVAL'])
            ->where(['trim(employee.PREN)' => trim($employeeId)]);

        // select * from papreq where reqclk# = '101639';;
        /**
         * This will get sum of all requests submitted for approval in the timeoff_request_entries
         * table by employee id
         * 
         * select sum(entry.requested_hours) as pending_pto_local from timeoff_request_entries entry where
                entry.request_id in (
                    select request.request_id from timeoff_requests request where
                        request.employee_number = '49499' AND
                	    request.request_status = 'P'
                );;
         */
        $result = \Request\Helper\ResultSetOutput::getResultRecord($sql, $select);
        
        /** Because we create a temp table for the pending approval amounts, we need
         *  to account for the fact that no result does not yield an integer.
         *  So we'll do the final calc here.
         */
        if(empty($result['PTO_PENDING_APPROVAL'])) {
            $result['PTO_PENDING_APPROVAL'] = number_format(0, 2);
        }
        if(empty($result['FLOAT_PENDING_APPROVAL'])) {
            $result['FLOAT_PENDING_APPROVAL'] = number_format(0, 2);
        }
        if(empty($result['SICK_PENDING_APPROVAL'])) {
            $result['SICK_PENDING_APPROVAL'] = number_format(0, 2);
        }
        
        /** Do final calc **/
        $result['PTO_AVAILABLE'] = $result['PTO_REMAINING'] - $result['PTO_PENDING_APPROVAL'];
        $result['FLOAT_AVAILABLE'] = $result['FLOAT_REMAINING'] - $result['FLOAT_PENDING_APPROVAL'];
        $result['SICK_AVAILABLE'] = $result['SICK_REMAINING'] - $result['SICK_PENDING_APPROVAL'];
        
//         echo '<pre>';
//         print_r($result);
//         echo '</pre>';
//         die("@@@");
        
        return $result;
    }

    /**
     * Find time off approved requests by Employee Number lookup.
     * 
     * {@inheritDoc}
     * @see \Request\Mapper\RequestMapperInterface::findTimeOffApprovedRequestsByEmployee()
     */
    public function findTimeOffApprovedRequestsByEmployee($employeeNumber = null)
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select(['entry' => 'TIMEOFF_REQUEST_ENTRIES'])
            ->columns($this->timeoffRequestEntryColumns)
            ->join(['request' => 'TIMEOFF_REQUESTS'], 'request.REQUEST_ID = entry.REQUEST_ID', $this->timeoffRequestColumns)
            ->join(['requestcode' => 'TIMEOFF_REQUEST_CODES'], 'requestcode.REQUEST_CODE = entry.REQUEST_CODE', $this->timeoffRequestCodeColumns)
            ->where(['trim(request.EMPLOYEE_NUMBER)' => trim($employeeNumber), 'request.REQUEST_STATUS' => 'A']);

        return \Request\Helper\ResultSetOutput::getResultArray($sql, $select);
    }
    
    public function findTimeOffPendingRequestsByEmployee($employeeNumber = null)
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select(['entry' => 'TIMEOFF_REQUEST_ENTRIES'])
        ->columns($this->timeoffRequestEntryColumns)
        ->join(['request' => 'TIMEOFF_REQUESTS'], 'request.REQUEST_ID = entry.REQUEST_ID', $this->timeoffRequestColumns)
        ->join(['requestcode' => 'TIMEOFF_REQUEST_CODES'], 'requestcode.REQUEST_CODE = entry.REQUEST_CODE', $this->timeoffRequestCodeColumns)
        ->where(['trim(request.EMPLOYEE_NUMBER)' => trim($employeeNumber), 'request.REQUEST_STATUS' => 'P']);
    
        return \Request\Helper\ResultSetOutput::getResultArray($sql, $select);
    }
    
    /**
     * Find time off calendar data by Manager Employee Number lookup.
     * 
     * {@inheritDoc}
     * @see \Request\Mapper\RequestMapperInterface::findTimeOffCalendarByManager()
     */
    public function findTimeOffCalendarByManager($managerEmployeeNumber = null, $startDate = null, $endDate = null)
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select(['entry' => 'TIMEOFF_REQUEST_ENTRIES'])
            ->columns(['REQUEST_DATE' => 'REQUEST_DATE', 'REQUESTED_HOURS' => 'REQUESTED_HOURS'])
            ->join(['request' => 'TIMEOFF_REQUESTS'], 'request.REQUEST_ID = entry.REQUEST_ID', [])
            ->join(['requestcode' => 'TIMEOFF_REQUEST_CODES'], 'requestcode.REQUEST_CODE = entry.REQUEST_CODE', ['REQUEST_TYPE' => 'DESCRIPTION'])
            ->join(['employee' => 'PRPMS'], 'trim(PREN) = request.EMPLOYEE_NUMBER', ['EID' => 'PREN', 'LAST_NAME' => 'PRLNM', 'FIRST_NAME' => 'PRFNM']) // 'EMPLOYEENAME' => 'get_employee_common_name(employee.PRER, employee.PREN)'
            ->where(['request.REQUEST_STATUS' => 'A',
                     "trim(employee.PREN) IN( SELECT trim(SPEN) as EMPLOYEE_IDS FROM PRPSP WHERE trim(SPSPEN) = '" . $managerEmployeeNumber . "' )",
                     "entry.REQUEST_DATE BETWEEN '2015-12-01' AND '2015-12-31'"
                    ])
            ->order(['REQUEST_DATE ASC', 'LAST_NAME ASC', 'FIRST_NAME ASC']);
            
        /**
         * Select time off data for Mary Jackson for December 2015
         * 
         *
            
            SELECT
            	entry.REQUEST_DATE AS REQUEST_DATE,
            	get_employee_common_name(employee.PRER, employee.PREN) as EMPLOYEENAME,
            	entry.REQUESTED_HOURS AS REQUESTED_HOURS,
            	requestcode.DESCRIPTION AS REQUEST_TYPE
            FROM TIMEOFF_REQUEST_ENTRIES entry
            INNER JOIN TIMEOFF_REQUESTS request
            	ON request.REQUEST_ID = entry.REQUEST_ID
            INNER JOIN TIMEOFF_REQUEST_CODES requestcode
            	ON requestcode.REQUEST_CODE = entry.REQUEST_CODE
            INNER JOIN PRPMS employee
            	ON trim(PREN) = request.EMPLOYEE_NUMBER
            WHERE request.REQUEST_STATUS = 'A'
            AND trim(employee.PREN) IN( SELECT trim(SPEN) as EMPLOYEE_IDS FROM PRPSP WHERE trim(SPSPEN) = '229589' )
            AND MONTH(entry.REQUEST_DATE) = '12'
            AND YEAR(entry.REQUEST_DATE) = '2015'
            ORDER BY REQUEST_DATE ASC, EMPLOYEENAME ASC;;
            
         */    
            
        $calendarData = \Request\Helper\ResultSetOutput::getResultArray($sql, $select);
            
        return $calendarData;
    }

    /**
     * Find Time off balances by Manager Employee Number lookup.
     * 
     * {@inheritDoc}
     * @see \Request\Mapper\RequestMapperInterface::findTimeOffBalancesByManager()
     */
    public function findTimeOffBalancesByManager($managerEmployeeId = null)
    {
        // select EMPLOYEE_ID from table (care_get_manager_employees('002', '   229589', 'D')) as data;;
        // trim(employee.PREN) IN( SELECT trim(SPEN) as EMPLOYEE_IDS FROM PRPSP WHERE trim(SPSPEN) = '" . $managerEmployeeNumber . "' )
        // SELECT trim(SPEN) as EMPLOYEE_ID FROM sawik.PRPSP WHERE trim(SPSPEN) = '229589';;
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select(["data" => "table (SELECT trim(SPEN) as EMPLOYEE_NUMBER FROM sawik.PRPSP WHERE trim(SPSPEN) = '229589')"]) // (care_get_manager_employees('002', '   229589', ''))
            ->columns(['EMPLOYEE_NUMBER'])
            ->join(['employee' => 'PRPMS'], 'trim(employee.PREN) = data.EMPLOYEE_NUMBER', $this->employeeColumns)
            ->join(['pendingrequests' => 'PAPREQ'], "pendingrequests.REQCLK# = '101639'", $this->pendingRequestColumns);

        $employeeData = \Request\Helper\ResultSetOutput::getResultArray($sql, $select);

        foreach($employeeData as $counter => $employee) {
            $employeeData[$counter]['APPROVED_TIME_OFF'] = $this->findTimeOffApprovedRequestsByEmployee($employee->EMPLOYEE_NUMBER);
            $employeeData[$counter]['PENDING_APPROVAL_TIME_OFF'] = $this->findTimeOffPendingRequestsByEmployee($employee->EMPLOYEE_NUMBER);
        }

        return $employeeData;
    }
    
    public function submitRequestForApproval($employeeNumber, $requestData)
    {
        $requestReturnData = ['request_id' => null];
        foreach($requestData as $key => $request) {
            /** Insert record into TIMEOFF_REQUESTS **/
            $action = new Insert('timeoff_requests');
            $action->values([
                'EMPLOYEE_NUMBER' => $employeeNumber,
                'REQUEST_STATUS' => self::$requestStatuses['pendingApproval'],
                'CREATE_USER' => $employeeNumber
            ]);
            $sql    = new Sql($this->dbAdapter);
            $stmt   = $sql->prepareStatementForSqlObject($action);
            try {
                $result = $stmt->execute();
            
            } catch (Exception $e) {
                throw new \Exception("Can't execute statement: " . $e->getMessage());
            }
            
            $requestId = $result->getGeneratedValue();
            
            /** Insert record(s) into TIMEOFF_REQUEST_ENTRIES **/
            foreach($requestData as $key => $request) {
                $action = new Insert('timeoff_request_entries');
                $action->values([
                    'REQUEST_ID' => $requestId,
                    'REQUEST_DATE' => $request['date'],
                    'REQUESTED_HOURS' => $request['hours'],
                    'REQUEST_CODE' => $request['type']
                ]);
                $sql    = new Sql($this->dbAdapter);
                $stmt   = $sql->prepareStatementForSqlObject($action);
                try {
                    $result = $stmt->execute();
                
                } catch (Exception $e) {
                    throw new \Exception("Can't execute statement: " . $e->getMessage());
                }   
            }
        }
        $requestReturnData['request_id'] = $requestId;
        
        return $requestReturnData;
    }
}
