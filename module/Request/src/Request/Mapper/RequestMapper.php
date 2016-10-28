<?php

namespace Request\Mapper;

use Request\Model\RequestInterface;
use Zend\Db\Adapter\AdapterInterface;
// use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Expression;
use Zend\Db\ResultSet\ResultSet;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\NamingStrategy\ArrayMapNamingStrategy;

class RequestMapper implements RequestMapperInterface {

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
    public $employerNumber;
    public $includeApproved;
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
    public static $requestStatusText = [
        'D' => 'draft',
        'A' => 'approved',
        'C' => 'cancelled',
        'P' => 'pendingApproval',
        'R' => 'beingReviewed'
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
        $this->employerNumber = '002';
        $this->includeApproved = 'N';

        // 'alias' => 'FIELDNAME'
        $this->employeeColumns = [
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
            'POSITION_TITLE' => 'PRTITL',
            'EMAIL_ADDRESS' => 'PREML1',
            'EMPLOYEE_HIRE_DATE' => 'PRDOHE',
            'POSITION_TITLE' => 'PRTITL',
            'GRANDFATHERED_EARNED' => 'PRAC5E',
            'GRANDFATHERED_TAKEN' => 'PRAC5T',
//             'GRANDFATHERED_AVAILABLE' => 'PRAC5E - employee.PRAC5T',
//             'GRANDFATHERED_REMAINING' => 'PRAC5E - employee.PRAC5T - pendingrequests.REQGFV',
            'PTO_EARNED' => 'PRVAC',
            'PTO_TAKEN' => 'PRVAT',
//             'PTO_AVAILABLE' => 'PRVAC - employee.PRVAT', // Need to manually add the table alias on 2nd field
//             'PTO_REMAINING' => 'PRVAC - employee.PRVAT - pendingrequests.REQPTO',
            'FLOAT_EARNED' => 'PRSHA',
            'FLOAT_TAKEN' => 'PRSHT',
//             'FLOAT_AVAILABLE' => 'PRSHA - employee.PRSHT', // Need to manually add the table alias on 2nd field
//             'FLOAT_REMAINING' => 'PRSHA - employee.PRSHT - pendingrequests.REQFLOAT',
            'SICK_EARNED' => 'PRSDA',
            'SICK_TAKEN' => 'PRSDT',
//             'SICK_AVAILABLE' => 'PRSDA - employee.PRSDT',
//             'SICK_REMAINING' => 'PRSDA - employee.PRSDT - pendingrequests.REQSICK',
            'COMPANY_MANDATED_EARNED' => 'PRAC4E',
            'COMPANY_MANDATED_TAKEN' => 'PRAC4T',
//             'COMPANY_MANDATED_AVAILABLE' => 'PRAC4E', // - employee.PRAC4T Need to manually add the table alias on 2nd field
            'DRIVER_SICK_EARNED' => 'PRAC6E',
            'DRIVER_SICK_TAKEN' => 'PRAC6T',
//             'DRIVER_SICK_AVAILABLE' => 'PRAC6E - employee.PRAC6T' // Need to manually add the table alias on 2nd field
        ];
        $this->pendingRequestColumns = [
//             'GRANDFATHERED_PENDING' => 'REQGFV',
            'PTO_PENDING' => 'REQPTO',
            'FLOAT_PENDING' => 'REQFLOAT',
            'SICK_PENDING' => 'REQSICK',
//             'TOM_PENDING' => 'REQTOM',
//             'VAC_PENDING' => 'REQVAC'
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
            'MANAGER_POSITION_TITLE' => 'PRTITL',
            'MANAGER_FIRST_NAME' => 'PRFNM',
            'MANAGER_MIDDLE_INITIAL' => 'PRMNM',
            'MANAGER_LAST_NAME' => 'PRLNM',
            'MANAGER_EMAIL_ADDRESS' => 'PREML1'
        ];
        $this->requesterAddonColumns = [
            'REQUESTER_EMPLOYER_NUMBER' => 'PRER',
            'REQUESTER_EMPLOYEE_NUMBER' => 'PREN',
            'REQUESTER_POSITION_TITLE' => 'PRTITL',
            'REQUESTER_FIRST_NAME' => 'PRFNM',
            'REQUESTER_MIDDLE_INITIAL' => 'PRMNM',
            'REQUESTER_LAST_NAME' => 'PRLNM',
            'REQUESTER_EMAIL_ADDRESS' => 'PREML1'
        ];
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

//    if(!$this->requestService->findEmployeeSchedule($this->employeeNumber)) {
//            $this->requestService->makeDefaultEmployeeSchedule($this->employeeNumber);
//
//        }
//        $employeeSchedule = $this->requestService->findEmployeeSchedule($this->employeeNumber);

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

        $employeeData = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql($this->dbAdapter, $rawSql);

        return \Request\Helper\Format::trimData($employeeData);
    }

    /**
     * Find time off balances by Employee Number lookup.
     *
     * {@inheritDoc}
     * @see \Request\Mapper\RequestMapperInterface::findTimeOffBalancesByEmployee()
     */
    // TODO: sawik 12/04/15 Change the join to join on the actual employee id,
    // hardcoded here.
    public function findTimeOffBalancesByEmployee($employeeNumber = null)
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select(['employee' => 'PRPMS'])
                ->columns($this->employeeColumns)
                ->join(['manager' => 'PRPSP'], "employee.PREN = manager.SPEN AND employee.prer = manager.sper ", []) // $this->employeeSupervisorColumns
                ->join(['manager_addons' => 'PRPMS'], 'manager_addons.PREN = manager.SPSPEN and manager_addons.prer = manager.spsper', $this->supervisorAddonColumns)
                ->join(['pendingrequests' => 'PAPREQ'], "pendingrequests.REQCLK# = '" . $employeeNumber . "'", $this->pendingRequestColumns, 'LEFT OUTER')
                ->join(['pendingpto' => "(
              select '" . $employeeNumber . "' as employee_number, sum(entry.requested_hours) as PTO_PENDING_APPROVAL from timeoff_request_entries entry
              inner join timeoff_requests request ON request.request_id = entry.request_id
              where
                    entry.request_id in (
                        select request.request_id from timeoff_requests request where
                            request.employee_number = '" . $employeeNumber . "' AND
                          request.request_status = 'P' AND
                      entry.request_code = 'P'
                    )
                )"], "pendingpto.EMPLOYEE_NUMBER = '" . $employeeNumber . "'", ['PTO_PENDING_APPROVAL' => 'PTO_PENDING_APPROVAL'])
                ->join(['pendingfloat' => "(
              select '" . $employeeNumber . "' as employee_number, sum(entry.requested_hours) as FLOAT_PENDING_APPROVAL from timeoff_request_entries entry
              inner join timeoff_requests request ON request.request_id = entry.request_id
              where
                    entry.request_id in (
                        select request.request_id from timeoff_requests request where
                            request.employee_number = '" . $employeeNumber . "' AND
                          request.request_status = 'P' AND
                      entry.request_code = 'K'
                    )
                )"], "pendingfloat.EMPLOYEE_NUMBER = '" . $employeeNumber . "'", ['FLOAT_PENDING_APPROVAL' => 'FLOAT_PENDING_APPROVAL'])
                ->join(['pendingsick' => "(
              select '" . $employeeNumber . "' as employee_number, sum(entry.requested_hours) as SICK_PENDING_APPROVAL from timeoff_request_entries entry
              inner join timeoff_requests request ON request.request_id = entry.request_id
              where
                    entry.request_id in (
                        select request.request_id from timeoff_requests request where
                            request.employee_number = '" . $employeeNumber . "' AND
                          request.request_status = 'P' AND
                      entry.request_code = 'S'
                    )
                )"], "pendingsick.EMPLOYEE_NUMBER = '" . $employeeNumber . "'", ['SICK_PENDING_APPROVAL' => 'SICK_PENDING_APPROVAL'])
                ->where(['trim(employee.PREN)' => trim($employeeNumber), 'employee.PRER' => '002']);

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

        $result['PTO_PENDING'] = \Request\Helper\Format::setStringDefaultToZero($result['PTO_PENDING']);
        $result['FLOAT_PENDING'] = \Request\Helper\Format::setStringDefaultToZero($result['FLOAT_PENDING']);
        $result['SICK_PENDING'] = \Request\Helper\Format::setStringDefaultToZero($result['SICK_PENDING']);
        $result['PTO_PENDING_APPROVAL'] = \Request\Helper\Format::setStringDefaultToZero($result['PTO_PENDING_APPROVAL']);
        $result['FLOAT_PENDING_APPROVAL'] = \Request\Helper\Format::setStringDefaultToZero($result['FLOAT_PENDING_APPROVAL']);
        $result['SICK_PENDING_APPROVAL'] = \Request\Helper\Format::setStringDefaultToZero($result['SICK_PENDING_APPROVAL']);

        /** Because we create a temp table for the pending approval amounts, we need
         *  to account for the fact that no result does not yield an integer.
         *  So we'll do the final calc here.
         */
//         $result['PTO_PENDING_APPROVAL'] = \Request\Helper\Format::setStringDefaultToZero($result['PTO_PENDING_APPROVAL']);
//         $result['FLOAT_PENDING_APPROVAL'] = \Request\Helper\Format::setStringDefaultToZero($result['FLOAT_PENDING_APPROVAL']);
//         $result['SICK_PENDING_APPROVAL'] = \Request\Helper\Format::setStringDefaultToZero($result['SICK_PENDING_APPROVAL']);
//         /** Do final calc **/
        $result['PTO_AVAILABLE'] = number_format(($result['PTO_EARNED'] - $result['PTO_TAKEN'] - $result['PTO_PENDING'] - $result['PTO_PENDING_APPROVAL']), 2);
        $result['FLOAT_AVAILABLE'] = number_format(($result['FLOAT_EARNED'] - $result['FLOAT_TAKEN'] - $result['FLOAT_PENDING'] - $result['FLOAT_PENDING_APPROVAL']), 2);
        $result['SICK_AVAILABLE'] = number_format(($result['SICK_EARNED'] - $result['SICK_TAKEN'] - $result['SICK_PENDING'] - $result['SICK_PENDING_APPROVAL']), 2);

        $result['UNEXCUSED_ABSENCE_AVAILABLE'] = 0;
        $result['UNEXCUSED_ABSENCE_PENDING'] = 0;
        $result['UNEXCUSED_ABSENCE_PENDING_APPROVAL'] = 0;
        $result['BEREAVEMENT_AVAILABLE'] = 0;
        $result['BEREAVEMENT_PENDING'] = 0;
        $result['BEREAVEMENT_PENDING_APPROVAL'] = 0;
        $result['CIVIC_DUTY_AVAILABLE'] = 0;
        $result['CIVIC_DUTY_PENDING'] = 0;
        $result['CIVIC_DUTY_PENDING_APPROVAL'] = 0;
        $result['GRANDFATHERED_AVAILABLE'] = 0;
        $result['GRANDFATHERED_PENDING'] = 0;
        $result['GRANDFATHERED_PENDING_APPROVAL'] = 0;
        $result['APPROVED_NO_PAY_AVAILABLE'] = 0;
        $result['APPROVED_NO_PAY_PENDING'] = 0;
        $result['APPROVED_NO_PAY_PENDING_APPROVAL'] = 0;

        $result['FIRST_NAME'] = trim($result['FIRST_NAME']);
        $result['LAST_NAME'] = trim($result['LAST_NAME']);
        $result['COMMON_NAME'] = trim($result['COMMON_NAME']);
        $result['EMPLOYEE_NUMBER'] = trim($result['EMPLOYEE_NUMBER']);
        $result['POSITION_TITLE'] = trim($result['POSITION_TITLE']);

        if(!$this->findEmployeeSchedule($employeeNumber)) {
            $this->makeDefaultEmployeeSchedule($employeeNumber);
        }
        $result['EMPLOYEE_SCHEDULE'] = $this->findEmployeeSchedule($employeeNumber);

        return $result;
    }

    public function findTimeOffRequestsByEmployeeAndStatus($employeeNumber = null, $status = "A")
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select(['entry' => 'TIMEOFF_REQUEST_ENTRIES'])
                ->columns($this->timeoffRequestEntryColumns)
                ->join(['request' => 'TIMEOFF_REQUESTS'], 'request.REQUEST_ID = entry.REQUEST_ID', $this->timeoffRequestColumns)
                ->join(['requestcode' => 'TIMEOFF_REQUEST_CODES'], 'requestcode.REQUEST_CODE = entry.REQUEST_CODE', $this->timeoffRequestCodeColumns)
                ->where(['trim(request.EMPLOYEE_NUMBER)' => trim($employeeNumber), 'request.REQUEST_STATUS' => $status])
                ->order(['entry.REQUEST_DATE ASC']);

        $return = \Request\Helper\ResultSetOutput::getResultArray($sql, $select);

        return $return;
    }

    /**
     * Find time off approved requests by Employee Number lookup.
     *
     * {@inheritDoc}
     * @see \Request\Mapper\RequestMapperInterface::findTimeOffApprovedRequestsByEmployee()
     */
    public function findTimeOffApprovedRequestsByEmployee($employeeNumber = null, $returnType = "datesOnly")
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select(['entry' => 'TIMEOFF_REQUEST_ENTRIES'])
                ->columns($this->timeoffRequestEntryColumns)
                ->join(['request' => 'TIMEOFF_REQUESTS'], 'request.REQUEST_ID = entry.REQUEST_ID', $this->timeoffRequestColumns)
                ->join(['requestcode' => 'TIMEOFF_REQUEST_CODES'], 'requestcode.REQUEST_CODE = entry.REQUEST_CODE', $this->timeoffRequestCodeColumns)
                ->where(['trim(request.EMPLOYEE_NUMBER)' => trim($employeeNumber), 'request.REQUEST_STATUS' => 'A'])
                ->order(['entry.REQUEST_DATE ASC']);

        $result = \Request\Helper\ResultSetOutput::getResultArray($sql, $select);

        $return = [];
        switch ($returnType) {
            case 'datesOnly':
            default:
                $return = $result;
                break;

            case 'managerQueue':
                $return = [];

                foreach ($result as $key => $data) {
                    if (!array_key_exists($data['REQUEST_ID'], $return)) {
                        $return[$data['REQUEST_ID']] = [ 'REQUEST_REASON' => $data['REQUEST_REASON'],
                            'DETAILS' => [],
                            'TOTALS' => [ 'PTO' => number_format(0.00, 2),
                                'Float' => number_format(0.00, 2),
                                'Sick' => number_format(0.00, 2),
                                'UnexcusedAbsence' => number_format(0.00, 2),
                                'Bereavement' => number_format(0.00, 2),
                                'CivicDuty' => number_format(0.00, 2),
                                'Grandfathered' => number_format(0.00, 2),
                                'ApprovedNoPay' => number_format(0.00, 2)
                            ]
                        ];
                    }
                    $return[$data['REQUEST_ID']]['DETAILS'][] = [
                        'REQUEST_DATE' => $data['REQUEST_DATE'],
                        'REQUESTED_HOURS' => $data['REQUESTED_HOURS'],
                        'REQUEST_TYPE' => $data['REQUEST_TYPE']
                    ];
                    $return[$data['REQUEST_ID']]['TOTALS'][$data['REQUEST_TYPE']] += $data['REQUESTED_HOURS'];
                    $return[$data['REQUEST_ID']]['TOTALS'][$data['REQUEST_TYPE']] = number_format($return[$data['REQUEST_ID']]['TOTALS'][$data['REQUEST_TYPE']], 2);
                }
                break;
        }

        return $return;
    }

    public function findRequestCalendarInviteData($requestId = null)
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select(['entry' => 'TIMEOFF_REQUEST_ENTRIES'])
            ->columns($this->timeoffRequestEntryColumns)
            ->join(['request' => 'TIMEOFF_REQUESTS'], 'request.REQUEST_ID = entry.REQUEST_ID', [])
            ->join(['code' => 'TIMEOFF_REQUEST_CODES'], 'entry.REQUEST_CODE = code.REQUEST_CODE', ['DESCRIPTION' => 'DESCRIPTION'])
            ->where(['request.REQUEST_ID' => $requestId])
            ->order(['entry.REQUEST_DATE ASC']);
        $result = \Request\Helper\ResultSetOutput::getResultArray($sql, $select);

        $sql = new Sql($this->dbAdapter);
        $select = $sql->select(['employee' => 'PRPMS'])
            ->columns($this->employeeColumns)
            ->join(['request' => 'TIMEOFF_REQUESTS'], "trim(request.EMPLOYEE_NUMBER) = trim(employee.PREN) and employee.prer = '002'", [])
            ->join(['manager' => 'PRPSP'], 'employee.PREN = manager.SPEN and employee.PRER = manager.SPER', [])
            ->join(['manager_addons' => 'PRPMS'], 'manager_addons.PREN = manager.SPSPEN and manager_addons.PRER = manager.SPSPER', $this->supervisorAddonColumns)
            ->where(['request.REQUEST_ID' => $requestId]);
        $result2 = \Request\Helper\ResultSetOutput::getResultRecord($sql, $select);

//        echo '<pre>';
//        print_r($result);
//        echo '</pre>';

//        echo '<pre>2!!';
//        print_r($result2);
//        echo '</pre>';
//        die("@@@");

        $datesRequested = [];
        if(count($result) > 0) {
            $datesRequested[] = [ 'start' => $result[0]['REQUEST_DATE'],
                                  'end' => $result[0]['REQUEST_DATE'],
                                  'type' => $result[0]['DESCRIPTION'],
                                  'hours' => $result[0]['REQUESTED_HOURS']
                                ];
        }
        $group = 0;

        for($ctr = 1; $ctr <= (count($result)-1); $ctr++) {
            if($result[$ctr]['REQUEST_DATE']!==$datesRequested[$group]['end'] &&
               $result[$ctr]['REQUEST_DATE']===date("Y-m-d", strtotime("+1 day", strtotime($datesRequested[$group]['end']))) &&
               $result[$ctr]['DESCRIPTION']===$datesRequested[$group]['type'] &&
               $result[$ctr]['REQUESTED_HOURS']===$datesRequested[$group]['hours']
              ) {
                $datesRequested[$group]['end'] = $result[$ctr]['REQUEST_DATE'];
            } else {
                $group++;
                $datesRequested[$group] = [ 'start' => $result[$ctr]['REQUEST_DATE'],
                                            'end' => $result[$ctr]['REQUEST_DATE'],
                                            'type' => $result[$ctr]['DESCRIPTION'],
                                            'hours' => $result[$ctr]['REQUESTED_HOURS']
                                          ];
            }
        }

//        $for = trim($result2['COMMON_NAME']) . " " . trim($result2['LAST_NAME']);
//        $forEmail = trim($result2['EMAIL_ADDRESS']);
//        $manager = trim($result2['MANAGER_FIRST_NAME']) . " " . trim($result2['MANAGER_LAST_NAME']);
//        $managerEmail = trim($result2['MANAGER_EMAIL_ADDRESS']);

        $return = [ 'datesRequested' => $datesRequested,
                    'for' => $result2
                  ];
//        echo '<pre>';
//        print_r($result2);
//        echo '</pre>';
//        die("@@");

        return $return;
    }

    public function findTimeOffPendingRequestsByEmployee($employeeNumber = null, $returnType = "datesOnly", $requestId = null)
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select(['entry' => 'TIMEOFF_REQUEST_ENTRIES'])
            ->columns($this->timeoffRequestEntryColumns)
            ->join(['request' => 'TIMEOFF_REQUESTS'], 'request.REQUEST_ID = entry.REQUEST_ID', $this->timeoffRequestColumns)
            ->join(['requestcode' => 'TIMEOFF_REQUEST_CODES'], 'requestcode.REQUEST_CODE = entry.REQUEST_CODE', $this->timeoffRequestCodeColumns);

        if ($employeeNumber != null) {
            $select->join(['pendingrequests' => 'PAPREQ'], "pendingrequests.REQCLK# = '" . $employeeNumber . "'", $this->pendingRequestColumns, 'LEFT OUTER')
                ->join(['pendingpto' => "(
                select '" . $employeeNumber . "' as employee_number, sum(entry.requested_hours) as PTO_PENDING_APPROVAL from timeoff_request_entries entry
                inner join timeoff_requests request ON request.request_id = entry.request_id
                where
                                entry.request_id in (
                                select request.request_id from timeoff_requests request where
                                        request.employee_number = '" . $employeeNumber . "' AND
                                        request.request_status = 'P' AND
                                entry.request_code = 'P'
                                )
                )"], "pendingpto.EMPLOYEE_NUMBER = '" . $employeeNumber . "'", ['PTO_PENDING_APPROVAL' => 'PTO_PENDING_APPROVAL'])
                ->join(['pendingfloat' => "(
                select '" . $employeeNumber . "' as employee_number, sum(entry.requested_hours) as FLOAT_PENDING_APPROVAL from timeoff_request_entries entry
                inner join timeoff_requests request ON request.request_id = entry.request_id
                where
                                entry.request_id in (
                                select request.request_id from timeoff_requests request where
                                        request.employee_number = '" . $employeeNumber . "' AND
                                        request.request_status = 'P' AND
                                entry.request_code = 'K'
                                )
                )"], "pendingfloat.EMPLOYEE_NUMBER = '" . $employeeNumber . "'", ['FLOAT_PENDING_APPROVAL' => 'FLOAT_PENDING_APPROVAL'])
                ->join(['pendingsick' => "(
                select '" . $employeeNumber . "' as employee_number, sum(entry.requested_hours) as SICK_PENDING_APPROVAL from timeoff_request_entries entry
                inner join timeoff_requests request ON request.request_id = entry.request_id
                where
                                entry.request_id in (
                                select request.request_id from timeoff_requests request where
                                        request.employee_number = '" . $employeeNumber . "' AND
                                        request.request_status = 'P' AND
                                entry.request_code = 'S'
                                )
                )"], "pendingsick.EMPLOYEE_NUMBER = '" . $employeeNumber . "'", ['SICK_PENDING_APPROVAL' => 'SICK_PENDING_APPROVAL']);
        }

        $select
            ->join(['employee' => 'PRPMS'], "trim(employee.PREN) = request.EMPLOYEE_NUMBER and employee.prer = '002'", $this->employeeColumns)
            ->join(['manager' => 'PRPSP'], 'employee.PREN = manager.SPEN and employee.PRER = manager.SPER', []) // $this->employeeSupervisorColumns
            ->join(['manager_addons' => 'PRPMS'], 'manager_addons.PREN = manager.SPSPEN and manager_addons.PRER = manager.SPSPER', $this->supervisorAddonColumns)
            ->join(['requester_addons' => 'PRPMS'], "trim(requester_addons.PREN) = request.CREATE_USER and trim(requester_addons.PRER) = '002'", $this->requesterAddonColumns)
            ->order(['entry.REQUEST_DATE ASC']);

        if ($requestId != null) {
//             $select->where(['trim(request.EMPLOYEE_NUMBER)' => trim($employeeNumber), 'request.REQUEST_STATUS' => 'P', 'request.REQUEST_ID' => $requestId]);
            $select->where(['request.REQUEST_ID' => $requestId]);
        }

        $result = \Request\Helper\ResultSetOutput::getResultArray($sql, $select);
        switch ($returnType) {
            case 'datesOnly':
            default:
                $return = $result;
                break;

            case 'managerQueue':
                $return = [];

                foreach ($result as $key => $data) {
                    $return[] = $data['REQUEST_TYPE'];
                }

//                echo '<pre>';
//                print_r($return);
//                echo '</pre>';
//                die("@@@");

                foreach ($result as $key => $data) {
                    if (!array_key_exists($data['REQUEST_ID'], $return)) {
                        $data['PTO_PENDING'] = \Request\Helper\Format::setStringDefaultToZero($data['PTO_PENDING']);
                        $data['FLOAT_PENDING'] = \Request\Helper\Format::setStringDefaultToZero($data['FLOAT_PENDING']);
                        $data['SICK_PENDING'] = \Request\Helper\Format::setStringDefaultToZero($data['SICK_PENDING']);
                        $data['PTO_PENDING_APPROVAL'] = \Request\Helper\Format::setStringDefaultToZero($data['PTO_PENDING_APPROVAL']);
                        $data['FLOAT_PENDING_APPROVAL'] = \Request\Helper\Format::setStringDefaultToZero($data['FLOAT_PENDING_APPROVAL']);
                        $data['SICK_PENDING_APPROVAL'] = \Request\Helper\Format::setStringDefaultToZero($data['SICK_PENDING_APPROVAL']);

                        $data['PTO_AVAILABLE'] = number_format(($data['PTO_EARNED'] - $data['PTO_TAKEN'] - $data['PTO_PENDING'] - $data['PTO_PENDING_APPROVAL']), 2);
                        $data['FLOAT_AVAILABLE'] = number_format(($data['FLOAT_EARNED'] - $data['FLOAT_TAKEN'] - $data['FLOAT_PENDING'] - $data['FLOAT_PENDING_APPROVAL']), 2);
                        $data['SICK_AVAILABLE'] = number_format(($data['SICK_EARNED'] - $data['SICK_TAKEN'] - $data['SICK_PENDING'] - $data['SICK_PENDING_APPROVAL']), 2);
                        $data['UNEXCUSED_ABSENCE_AVAILABLE'] = 0;
                        $data['BEREAVEMENT_AVAILABLE'] = 0;
                        $data['CIVIC_DUTY_AVAILABLE'] = 0;
                        $data['GRANDFATHERED_AVAILABLE'] = 0;
                        $data['APPROVED_NO_PAY_AVAILABLE'] = 0;

                        $return[$data['REQUEST_ID']] = [ 'REQUEST_REASON' => $data['REQUEST_REASON'],
                                    'REQUEST_STATUS_TEXT' => self::$requestStatusText[$data['REQUEST_STATUS']],
                                    'CREATE_TIMESTAMP' => $data['CREATE_TIMESTAMP'],
                                    'DETAILS' => [],
                                    'TOTALS' => [ 'PTO' => number_format(0.00, 2),
                                        'Float' => number_format(0.00, 2),
                                        'Sick' => number_format(0.00, 2),
                                        'UnexcusedAbsence' => number_format(0.00, 2),
                                        'Bereavement' => number_format(0.00, 2),
                                        'CivicDuty' => number_format(0.00, 2),
                                        'Grandfathered' => number_format(0.00, 2),
                                        'ApprovedNoPay' => number_format(0.00, 2),
                                        'Grand' => number_format(0.00, 2)
                                    ],
                                    'FIRST_DATE_REQUESTED' => '',
                                    'LAST_DATE_REQUESTED' => '',
                                    'REQUESTER' => [ 'EMPLOYEE_NUMBER' => trim($data['REQUESTER_EMPLOYEE_NUMBER']),
                                        'FIRST_NAME' => $data['REQUESTER_FIRST_NAME'],
                                        'MIDDLE_INITIAL' => $data['REQUESTER_MIDDLE_INITIAL'],
                                        'LAST_NAME' => $data['REQUESTER_LAST_NAME'],
                                        'EMAIL_ADDRESS' => $data['REQUESTER_EMAIL_ADDRESS'],
                                        'POSITION_TITLE' => $data['REQUESTER_POSITION_TITLE']
                                    ],
                                    'EMPLOYEE' => [ 'EMPLOYEE_NUMBER' => trim($data['EMPLOYEE_NUMBER']),
                                        'FIRST_NAME' => $data['FIRST_NAME'],
                                        'MIDDLE_INITIAL' => $data['MIDDLE_INITIAL'],
                                        'LAST_NAME' => $data['LAST_NAME'],
                                        'POSITION' => $data['POSITION'],
                                        'POSITION_TITLE' => $data['POSITION_TITLE'],
                                        'EMAIL_ADDRESS' => $data['EMAIL_ADDRESS'],
                                        'BALANCES' => [
                                            'PTO' => [
                                                'PTO_EARNED' => $data['PTO_EARNED'],
                                                'PTO_TAKEN' => $data['PTO_TAKEN'],
                                                'PTO_PENDING' => $data['PTO_PENDING'],
                                                'PTO_PENDING_APPROVAL' => $data['PTO_PENDING_APPROVAL'],
                                                'PTO_AVAILABLE' => $data['PTO_AVAILABLE']
                                            ],
                                            'Float' => [
                                                'FLOAT_EARNED' => $data['FLOAT_EARNED'],
                                                'FLOAT_TAKEN' => $data['FLOAT_TAKEN'],
                                                'FLOAT_PENDING' => $data['FLOAT_PENDING'],
                                                'FLOAT_PENDING_APPROVAL' => $data['FLOAT_PENDING_APPROVAL'],
                                                'FLOAT_AVAILABLE' => $data['FLOAT_AVAILABLE']
                                            ],
                                            'Sick' => [
                                                'SICK_EARNED' => $data['SICK_EARNED'],
                                                'SICK_TAKEN' => $data['SICK_TAKEN'],
                                                'SICK_PENDING' => $data['SICK_PENDING'],
                                                'SICK_PENDING_APPROVAL' => $data['SICK_PENDING_APPROVAL'],
                                                'SICK_AVAILABLE' => $data['SICK_AVAILABLE']
                                            ],
                                            'UnexcusedAbsence' => [
                                                'UNEXCUSED_ABSENCE_EARNED' => number_format(0.00, 2), //$data['UNEXCUSED_ABSENCE_EARNED'],
                                                'UNEXCUSED_ABSENCE_TAKEN' => number_format(0.00, 2), //$data['UNEXCUSED_ABSENCE_TAKEN'],
                                                'UNEXCUSED_ABSENCE_PENDING' => number_format(0.00, 2), //$data['UNEXCUSED_ABSENCE_PENDING'],
                                                'UNEXCUSED_ABSENCE_PENDING_APPROVAL' => number_format(0.00, 2), //$data['UNEXCUSED_ABSENCE_PENDING_APPROVAL'],
                                                'UNEXCUSED_ABSENCE_AVAILABLE' => number_format(0.00, 2), //$data['UNEXCUSED_ABSENCE_AVAILABLE']
                                            ],
                                            'Bereavement' => [
                                                'BEREAVEMENT_EARNED' => number_format(0.00, 2), //$data['BEREAVEMENT_EARNED'],
                                                'BEREAVEMENT_TAKEN' => number_format(0.00, 2), //$data['BEREAVEMENT_TAKEN'],
                                                'BEREAVEMENT_PENDING' => number_format(0.00, 2), //$data['BEREAVEMENT_PENDING'],
                                                'BEREAVEMENT_PENDING_APPROVAL' => number_format(0.00, 2), //$data['BEREAVEMENT_PENDING_APPROVAL'],
                                                'BEREAVEMENT_AVAILABLE' => number_format(0.00, 2), //$data['BEREAVEMENT_AVAILABLE']
                                            ],
                                            'CivicDuty' => [
                                                'CIVIC_DUTY_EARNED' => number_format(0.00, 2), //$data['CIVIC_DUTY_EARNED'],
                                                'CIVIC_DUTY_TAKEN' => number_format(0.00, 2), //$data['CIVIC_DUTY_TAKEN'],
                                                'CIVIC_DUTY_PENDING' => number_format(0.00, 2), //$data['CIVIC_DUTY_PENDING'],
                                                'CIVIC_DUTY_PENDING_APPROVAL' => number_format(0.00, 2), //$data['CIVIC_DUTY_PENDING_APPROVAL'],
                                                'CIVIC_DUTY_AVAILABLE' => number_format(0.00, 2), //$data['CIVIC_DUTY_AVAILABLE']
                                            ],
                                            'Grandfathered' => [
                                                'GRANDFATHERED_EARNED' => number_format(0.00, 2), //$data['GRANDFATHERED_EARNED'],
                                                'GRANDFATHERED_TAKEN' => number_format(0.00, 2), //$data['GRANDFATHERED_TAKEN'],
                                                'GRANDFATHERED_PENDING' => number_format(0.00, 2), //$data['GRANDFATHERED_PENDING'],
                                                'GRANDFATHERED_PENDING_APPROVAL' => number_format(0.00, 2), //$data['GRANDFATHERED_PENDING_APPROVAL'],
                                                'GRANDFATHERED_AVAILABLE' => number_format(0.00, 2), //$data['GRANDFATHERED_AVAILABLE']
                                            ],
                                            'ApprovedNoPay' => [
                                                'APPROVED_NO_PAY_EARNED' => number_format(0.00, 2), //$data['APPROVED_NO_PAY_EARNED'],
                                                'APPROVED_NO_PAY_TAKEN' => number_format(0.00, 2), //$data['APPROVED_NO_PAY_TAKEN'],
                                                'APPROVED_NO_PAY_PENDING' => number_format(0.00, 2), //$data['APPROVED_NO_PAY_PENDING'],
                                                'APPROVED_NO_PAY_PENDING_APPROVAL' => number_format(0.00, 2), //$data['APPROVED_NO_PAY_PENDING_APPROVAL'],
                                                'APPROVED_NO_PAY_AVAILABLE' => number_format(0.00, 2), //$data['APPROVED_NO_PAY_AVAILABLE']
                                            ]
                                        ]

                                    /**
                                     *  'MIDDLE_INITIAL' => 'PRMNM',
                                      'LAST_NAME' => 'PRLNM',
                                      'POSITION' => 'PRPOS',
                                      'EMAIL_ADDRESS' => 'PREML1',
                                      'EMPLOYEE_HIRE_DATE' => 'PRDOHE',
                                      'POSITION_TITLE' => 'PRTITL',
                                      'GRANDFATHERED_EARNED' => 'PRAC5E',
                                      'GRANDFATHERED_TAKEN' => 'PRAC5T',
                                      //             'GRANDFATHERED_AVAILABLE' => 'PRAC5E - employee.PRAC5T',
                                      //             'GRANDFATHERED_REMAINING' => 'PRAC5E - employee.PRAC5T - pendingrequests.REQGFV',
                                      'PTO_EARNED' => 'PRVAC',
                                      'PTO_TAKEN' => 'PRVAT',
                                      //             'PTO_AVAILABLE' => 'PRVAC - employee.PRVAT', // Need to manually add the table alias on 2nd field
                                      //             'PTO_REMAINING' => 'PRVAC - employee.PRVAT - pendingrequests.REQPTO',
                                      'FLOAT_EARNED' => 'PRSHA',
                                      'FLOAT_TAKEN' => 'PRSHT',
                                      //             'FLOAT_AVAILABLE' => 'PRSHA - employee.PRSHT', // Need to manually add the table alias on 2nd field
                                      //             'FLOAT_REMAINING' => 'PRSHA - employee.PRSHT - pendingrequests.REQFLOAT',
                                      'SICK_EARNED' => 'PRSDA',
                                      'SICK_TAKEN' => 'PRSDT',
                                     */
                                    ]
                        ];
                    }

//                     echo '<pre>';
//                     print_r($data);
//                     echo '</pre>';
//                     die("@@@");
                    $requestId = $data['REQUEST_ID'];

                    if ($data['REQUEST_TYPE'] === 'Unexcused') {
                        $data['REQUEST_TYPE'] = 'UnexcusedAbsence';
                    }
                    if ($data['REQUEST_TYPE'] === 'Time Off Without Pay') {
                        $data['REQUEST_TYPE'] = 'ApprovedNoPay';
                    }

                    $return[$requestId]['DETAILS'][] = [
                        'REQUEST_DATE' => $data['REQUEST_DATE'],
                        'REQUESTED_HOURS' => $data['REQUESTED_HOURS'],
                        'REQUEST_TYPE' => $data['REQUEST_TYPE']
                    ];

                    if ($return[$requestId]['FIRST_DATE_REQUESTED'] === '') {
                        $return[$requestId]['FIRST_DATE_REQUESTED'] = $data['REQUEST_DATE'];
                    }
                    $return[$requestId]['LAST_DATE_REQUESTED'] = $data['REQUEST_DATE'];

                    $return[$requestId]['TOTALS'][$data['REQUEST_TYPE']] += $data['REQUESTED_HOURS'];
                    $return[$requestId]['TOTALS']['Grand'] += $data['REQUESTED_HOURS'];
                    $return[$requestId]['TOTALS'][$data['REQUEST_TYPE']] = number_format($return[$requestId]['TOTALS'][$data['REQUEST_TYPE']], 2);
                    $return[$requestId]['TOTALS']['Grand'] = number_format($return[$requestId]['TOTALS']['Grand'], 2);
                    $return[$requestId]['EMPLOYEE']['BALANCES']['PTO']['PTO_POST_PENDING_APPROVAL'] = number_format(($return[$requestId]['EMPLOYEE']['BALANCES']['PTO']['PTO_PENDING_APPROVAL'] - $return[$requestId]['TOTALS']['PTO']), 2);
                    $return[$requestId]['EMPLOYEE']['BALANCES']['Float']['FLOAT_POST_PENDING_APPROVAL'] = number_format(($return[$requestId]['EMPLOYEE']['BALANCES']['Float']['FLOAT_PENDING_APPROVAL'] - $return[$requestId]['TOTALS']['Float']), 2);
                    $return[$requestId]['EMPLOYEE']['BALANCES']['Sick']['SICK_POST_PENDING_APPROVAL'] = number_format(($return[$requestId]['EMPLOYEE']['BALANCES']['Sick']['SICK_PENDING_APPROVAL'] - $return[$requestId]['TOTALS']['Sick']), 2);

                    $return[$requestId]['EMPLOYEE']['BALANCES']['UnexcusedAbsence']['UNEXCUSED_ABSENCE_POST_PENDING_APPROVAL'] = number_format(0.00, 2);
                    $return[$requestId]['EMPLOYEE']['BALANCES']['Bereavement']['BEREAVEMENT_POST_PENDING_APPROVAL'] = number_format(0.00, 2);
                    $return[$requestId]['EMPLOYEE']['BALANCES']['CivicDuty']['CIVIC_DUTY_POST_PENDING_APPROVAL'] = number_format(0.00, 2);
                    $return[$requestId]['EMPLOYEE']['BALANCES']['Grandfathered']['GRANDFATHERED_POST_PENDING_APPROVAL'] = number_format(0.00, 2);
                    $return[$requestId]['EMPLOYEE']['BALANCES']['ApprovedNoPay']['APPROVED_NO_PAY_POST_PENDING_APPROVAL'] = number_format(0.00, 2);

                    $return[$requestId]['EMPLOYEE']['BALANCES']['Grand'] = number_format(($return[$requestId]['EMPLOYEE']['BALANCES']['PTO']['PTO_POST_PENDING_APPROVAL'] +
                            $return[$requestId]['EMPLOYEE']['BALANCES']['Float']['FLOAT_POST_PENDING_APPROVAL'] +
                            $return[$requestId]['EMPLOYEE']['BALANCES']['Sick']['SICK_POST_PENDING_APPROVAL']
                            ), 2);
                }
                break;
        }

        return $return;
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
                ->join(['employee' => 'PRPMS'], "trim(PREN) = request.EMPLOYEE_NUMBER and trim(PRER) = '002'", ['EMPLOYEE_NUMBER' => 'PREN', 'LAST_NAME' => 'PRLNM', 'FIRST_NAME' => 'PRFNM']) // 'EMPLOYEENAME' => 'get_employee_common_name(employee.PRER, employee.PREN)'
                ->where(['request.REQUEST_STATUS' => 'A',
                    "trim(employee.PREN) IN( SELECT trim(SPEN) as EMPLOYEE_IDS FROM PRPSP WHERE trim(SPSPEN) = '" . $managerEmployeeNumber . "' and trim(SPSPER) = '002' )",
                    "entry.REQUEST_DATE BETWEEN '" . $startDate . "' AND '" . $endDate . "'"
                ])
                ->order(['REQUEST_DATE ASC', 'LAST_NAME ASC', 'FIRST_NAME ASC']);

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
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select(["data" => "table (SELECT trim(SPEN) as EMPLOYEE_NUMBER FROM sawik.PRPSP WHERE trim(SPSPEN) = '" . $managerEmployeeId . "')"]) // (care_get_manager_employees('002', '   229589', ''))
            ->columns(['EMPLOYEE_NUMBER'])
            ->join(['employee' => 'PRPMS'], "trim(employee.PREN) = data.EMPLOYEE_NUMBER and trim(employee.PRER) = '002'", $this->employeeColumns)
            ->join(['manager' => 'PRPSP'], 'employee.PREN = manager.SPEN and employee.PRER = manager.SPER', []) // $this->employeeSupervisorColumns
            ->join(['manager_addons' => 'PRPMS'], 'manager_addons.PREN = manager.SPSPEN and manager_addons.PRER = manager.SPSPER', $this->supervisorAddonColumns)
            ->join(['pendingrequests' => 'PAPREQ'], "pendingrequests.REQCLK# = '101639'", $this->pendingRequestColumns);

        $employeeData = \Request\Helper\ResultSetOutput::getResultArray($sql, $select);

        $queueData = [ 'pendingApproval' => [],
            'approved' => [],
        ];

        foreach ($employeeData as $counter => $employee) {
            $employeeData[$counter]['APPROVED_TIME_OFF'] = $this->findTimeOffApprovedRequestsByEmployee($employee->EMPLOYEE_NUMBER, "managerQueue");
            $employeeData[$counter]['PENDING_APPROVAL_TIME_OFF'] = $this->findTimeOffPendingRequestsByEmployee($employee->EMPLOYEE_NUMBER, "managerQueue", null);
        }

        return $employeeData;
    }

    public function findEmployeeSchedule($employeeNumber = null)
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select(['schedule' => 'TIMEOFF_REQUEST_EMPLOYEE_SCHEDULES'])
                ->columns(['SCHEDULE_MON' => 'SCHEDULE_MON',
                           'SCHEDULE_TUE' => 'SCHEDULE_TUE',
                           'SCHEDULE_WED' => 'SCHEDULE_WED',
                           'SCHEDULE_THU' => 'SCHEDULE_THU',
                           'SCHEDULE_FRI' => 'SCHEDULE_FRI',
                           'SCHEDULE_SAT' => 'SCHEDULE_SAT',
                           'SCHEDULE_SUN' => 'SCHEDULE_SUN'
                          ])
                ->where(['schedule.EMPLOYEE_NUMBER' => $employeeNumber]);

        $scheduleData = \Request\Helper\ResultSetOutput::getResultRecord($sql, $select);
        if(!$scheduleData) {
            $scheduleData = false;
        }

        return $scheduleData;
    }

    public function makeDefaultEmployeeSchedule($employeeNumber = null)
    {
        $action = new Insert('timeoff_request_employee_schedules');
        $action->values([
            'EMPLOYEE_NUMBER' => \Request\Helper\Format::rightPad($employeeNumber),
            'SCHEDULE_MON' => '8.00',
            'SCHEDULE_TUE' => '8.00',
            'SCHEDULE_WED' => '8.00',
            'SCHEDULE_THU' => '8.00',
            'SCHEDULE_FRI' => '8.00',
            'SCHEDULE_SAT' => '0.00',
            'SCHEDULE_SUN' => '0.00'
        ]);
        $sql = new Sql($this->dbAdapter);
        $stmt = $sql->prepareStatementForSqlObject($action);
        try {
            $result = $stmt->execute();
        } catch (\Exception $e) {
            throw new \Exception("Can't execute statement: " . $e->getMessage());
        }

        if($result) {
            return true;
        }
        return false;
    }

    public function findManagerEmployees($managerEmployeeNumber = null, $search = null, $directReportFilter = null)
    {
        $isPayrollAdmin = \Login\Helper\UserSession::getUserSessionVariable('IS_PAYROLL_ADMIN');
        $isPayrollAssistant = \Login\Helper\UserSession::getUserSessionVariable('IS_PAYROLL_ASSISTANT');
        $where = "WHERE (
            employee.PRER = '002' and
            employee.PRTEDH = 0 and
            employee.PRL02 <> 'DRV' and
            trim(employee.PREN) LIKE '%" . strtoupper($search) . "%' OR
            trim(employee.PRCOMN) || ' ' || trim(employee.PRLNM) LIKE '%" . strtoupper($search) . "%' OR
            trim(employee.PRFNM) || ' ' || trim(employee.PRLNM) LIKE '%" . strtoupper($search) . "%'
        )";

//            trim(employee.PRCOMN) || ' ' || trim(employee.PRLNM) LIKE '%" . strtoupper($search) . "%' OR
//            trim(employee.PRCOMNk) || ' ' || trim(employee.PRLNM) LIKE '%" . strtoupper($search) . "' OR
//            trim(employee.PRFNM) || ' ' || trim(employee.PRLNM) LIKE '%" . strtoupper($search) . "%' OR
//            trim(employee.PRFNM) || ' ' || trim(employee.PRLNM) LIKE '%" . strtoupper($search) . "

        if ($isPayrollAdmin === "N" && $isPayrollAssistant === "N" ) {
            $rawSql = "SELECT
                CASE
                    when trim(employee.PRCOMN) IS NOT NULL then trim(employee.PRLNM) || ', ' || trim(employee.PRCOMN)
                    else trim(employee.PRLNM) || ', ' || trim(employee.PRFNM)
                END AS EMPLOYEE_NAME,
                trim(employee.PREN) AS EMPLOYEE_NUMBER,
                employee.PRL01 AS LEVEL_1,
                employee.PRL02 AS LEVEL_2,
                employee.PRL03 AS LEVEL_3,
                employee.PRL04 AS LEVEL_4,
                hierarchy.DIRECT_INDIRECT,
                hierarchy.MANAGER_LEVEL,
                trim(employee.PRPOS) AS POSITION,
                trim(employee.PREML1) AS EMAIL_ADDRESS,
                employee.PRDOHE AS EMPLOYEE_HIRE_DATE,
                trim(employee.PRTITL) AS POSITION_TITLE,
                TRIM(hierarchy.DIRECT_MANAGER_EMPLOYEE_NUMBER) AS DIRECT_MANAGER_EMPLOYEE_NUMBER,
                trim(manager_addons.PRLNM) || ', ' || trim(manager_addons.PRFNM) AS DIRECT_MANAGER_NAME,
                manager_addons.PREML1 AS DIRECT_MANAGER_EMAIL_ADDRESS
            FROM PRPMS employee
            INNER JOIN table (
                  SELECT
                      trim(EMPLOYEE_ID) AS EMPLOYEE_NUMBER,
                      TRIM(DIRECT_MANAGER_EMPLOYEE_ID) AS DIRECT_MANAGER_EMPLOYEE_NUMBER,
                      DIRECT_INDIRECT,
                      MANAGER_LEVEL
                  FROM table (
                      GET_MANAGER_EMPLOYEES('002', '" . $managerEmployeeNumber . "', '" . $directReportFilter . "')
                  ) as data
            ) hierarchy
                  ON hierarchy.EMPLOYEE_NUMBER = trim(employee.PREN) and '002' = trim(employee.PRER)
            INNER JOIN PRPSP manager
                  ON employee.PREN = manager.SPEN and employee.PRER = manager.SPER
            INNER JOIN PRPMS manager_addons
                 ON manager_addons.PREN = manager.SPSPEN and manager_addons.PRER = manager.SPSPER
            " . $where . "
            ORDER BY employee.PRLNM ASC, employee.PRFNM ASC";
        } else {
            $rawSql = "SELECT
                CASE
                    when trim(employee.PRCOMN) IS NOT NULL then trim(employee.PRLNM) || ', ' || trim(employee.PRCOMN)
                    else trim(employee.PRLNM) || ', ' || trim(employee.PRFNM)
                END AS EMPLOYEE_NAME,
                trim(employee.PREN) AS EMPLOYEE_NUMBER,
                employee.PRL01 AS LEVEL_1,
                employee.PRL02 AS LEVEL_2,
                employee.PRL03 AS LEVEL_3,
                employee.PRL04 AS LEVEL_4,
                'N' AS DIRECT_INDIRECT,
                '0' AS MANAGER_LEVEL,
                trim(employee.PRPOS) AS POSITION,
                trim(employee.PREML1) AS EMAIL_ADDRESS,
                employee.PRDOHE AS EMPLOYEE_HIRE_DATE,
                trim(employee.PRTITL) AS POSITION_TITLE,
                '' AS DIRECT_MANAGER_EMPLOYEE_NUMBER,
                '' AS DIRECT_MANAGER_NAME,
                '' AS DIRECT_MANAGER_EMAIL_ADDRESS
                FROM PRPMS employee
                " . $where . "
                ORDER BY employee.PRLNM ASC, employee.PRFNM ASC";
        }

        $employeeData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql($this->dbAdapter, $rawSql);

        return $employeeData;
    }

    public function findQueuesByManager($managerEmployeeNumber = null)
    {
        $rawSql = "SELECT
            request.REQUEST_ID AS REQUEST_ID,
            request.EMPLOYEE_NUMBER,
            request.REQUEST_REASON AS REQUEST_REASON,
            request.REQUEST_STATUS AS REQUEST_STATUS,
            (
                    SELECT SUM(requested_hours) FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id
            ) AS requested_hours,
            (
                    SELECT MIN(REQUEST_DATE) FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id
            ) AS MIN_REQUEST_DATE,
            (
                    SELECT MAX(REQUEST_DATE) FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id
            ) AS MAX_REQUEST_DATE,

            employee.PRLNM AS EMPLOYEE_LAST_NAME, employee.PRFNM AS EMPLOYEE_FIRST_NAME, employee.PRMNM AS EMPLOYEE_MIDDLE_NAME,

            manager_addons.PRER AS MANAGER_EMPLOYER_NUMBER, manager_addons.PREN AS MANAGER_EMPLOYEE_NUMBER,
            manager_addons.PRFNM AS MANAGER_FIRST_NAME, manager_addons.PRMNM AS MANAGER_MIDDLE_INITIAL,
            manager_addons.PRLNM AS MANAGER_LAST_NAME, manager_addons.PREML1 AS MANAGER_EMAIL_ADDRESS

        FROM TIMEOFF_REQUESTS request
        INNER JOIN PRPMS employee ON trim(employee.PREN) = request.EMPLOYEE_NUMBER and trim(employee.PRER) = '002'
        INNER JOIN PRPSP manager ON employee.PREN = manager.SPEN and employee.PRER = manager.SPER
        INNER JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN and manager_addons.PRER = manager.SPSPER
        INNER JOIN table (
            SELECT
                trim(EMPLOYEE_ID) AS EMPLOYEE_NUMBER,
                TRIM(DIRECT_MANAGER_EMPLOYEE_ID) AS DIRECT_MANAGER_EMPLOYEE_NUMBER,
                DIRECT_INDIRECT,
                MANAGER_LEVEL
            FROM table (
                GET_MANAGER_EMPLOYEES('002', '" . $managerEmployeeNumber . "', 'D')
            ) as data
        ) hierarchy
            ON hierarchy.EMPLOYEE_NUMBER = trim(employee.PREN) and '002' = trim(employee.PRER)
        WHERE request.REQUEST_STATUS = 'P'
        ORDER BY MIN_REQUEST_DATE ASC";

        $employeeData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql($this->dbAdapter, $rawSql);

        return $employeeData;
    }

    public function isManager($employeeNumber = null)
    {
        $rawSql = "select is_manager_mg('002', '" . $employeeNumber . "') AS IS_MANAGER FROM sysibm.sysdummy1";
        $isSupervisorData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql($this->dbAdapter, $rawSql);

        return $isSupervisorData[0]->IS_MANAGER;
    }

    /**
     * Returns whether employee is Payroll Admin OR Assistant.
     *
     * @param type $employeeNumber
     * @return type
     */
    public function isPayroll($employeeNumber = null)
    {
        return ( ( $this->isPayrollAdmin( $employeeNumber ) === "Y" &&
                   $this->isPayrollAssistant( $employeeNumber ) === "Y" ) ? "Y" : "N" );
    }

    /**
     * Returns whether employee is a Payroll Admin.
     *
     * @param type $employeeNumber
     * @return type
     */
    public function isPayrollAdmin($employeeNumber = null)
    {
        /**
         * sawik 05/06/16 Modify to the following:
         *
         * Set as Payroll Admin: If Level 2 = FIN (from file PPRMS, field PRL02) and Level 3 starts with PY (from file PRPMS, field PRL02) and Training group = MGR2 (from file PRPMS, field PRTGRP)
         *
         */
        $rawSql = "SELECT
            (CASE WHEN (SUBSTRING(PRL03,0,3) = 'PY' AND PRTEDH = 0) THEN '1' ELSE '0' END) AS IS_PAYROLL_ADMIN
            FROM PRPMS
            WHERE TRIM(PRPMS.PREN) = '" . $employeeNumber . "' and TRIM(PRPMS.PRER) = '002'";

        $isSupervisorData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql($this->dbAdapter, $rawSql);

        return $isSupervisorData[0]->IS_MANAGER;
    }

    /**
     * Returns whether employee is a Payroll Assistant.
     *
     * @param type $employeeNumber
     * @return string
     */
    public function isPayrollAssistant($employeeNumber = null)
    {
        return "N";
    }

    public function submitRequestForApproval($employeeNumber = null, $requestData = [], $requestReason = null, $requesterEmployeeNumber = null)
    {
        $requestReturnData = ['request_id' => null];

        /** Insert record into TIMEOFF_REQUESTS * */
        $action = new Insert('timeoff_requests');
        $action->values([
            'EMPLOYEE_NUMBER' => $employeeNumber,
            'REQUEST_STATUS' => self::$requestStatuses['pendingApproval'],
            'CREATE_USER' => $requesterEmployeeNumber,
            'REQUEST_REASON' => $requestReason
        ]);
        $sql = new Sql($this->dbAdapter);
        $stmt = $sql->prepareStatementForSqlObject($action);
        try {
            $result = $stmt->execute();
        } catch (\Exception $e) {
            throw new \Exception("Can't execute statement: " . $e->getMessage());
        }

        $requestId = $result->getGeneratedValue();

        /** Insert record(s) into TIMEOFF_REQUEST_ENTRIES * */
        foreach ($requestData as $key => $request) {
            $action = new Insert('timeoff_request_entries');
            $action->values([
                'REQUEST_ID' => $requestId,
                'REQUEST_DATE' => $request['date'],
                'REQUESTED_HOURS' => $request['hours'],
                'REQUEST_CODE' => $request['type']
            ]);
            $sql = new Sql($this->dbAdapter);
            $stmt = $sql->prepareStatementForSqlObject($action);
            try {
                $result = $stmt->execute();
            } catch (\Exception $e) {
                throw new \Exception("Can't execute statement: " . $e->getMessage());
            }
        }
        $requestReturnData['request_id'] = $requestId;

        return $requestReturnData;
    }

    public function submitApprovalResponse($action = null, $requestId = null, $reviewRequestReason = null)
    {
        $requestReturnData = ['request_id' => null];
        $rawSql = "UPDATE timeoff_requests SET REQUEST_STATUS = '" . $action . "' WHERE REQUEST_ID = '" . $requestId . "'";
        $employeeData = \Request\Helper\ResultSetOutput::executeRawSql($this->dbAdapter, $rawSql);
        $requestReturnData['request_id'] = $requestId;

        // Send calendar invites for this time off to appropriate individual(s)


        return $requestReturnData;
    }

    public function checkHoursRequestedPerCategory($requestId = null)
    {
        $rawSql = "SELECT
            request.REQUEST_ID AS ID,
            request.EMPLOYEE_NUMBER,
            request.REQUEST_REASON AS REASON,
            (
                    SELECT SUM(requested_hours) FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id

            ) AS TOTAL,
            (
                    SELECT CASE WHEN SUM(requested_hours) > 0 THEN SUM(requested_hours) ELSE 0 END FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id
                    AND entry.request_code = 'P'
            ) AS PTO,
            (
                    SELECT CASE WHEN SUM(requested_hours) > 0 THEN SUM(requested_hours) ELSE 0 END FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id
                    AND entry.request_code = 'K'
            ) AS FLOAT,
            (
                    SELECT CASE WHEN SUM(requested_hours) > 0 THEN SUM(requested_hours) ELSE 0 END FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id
                    AND entry.request_code = 'S'
            ) AS SICK,
            (
                    SELECT CASE WHEN SUM(requested_hours) > 0 THEN SUM(requested_hours) ELSE 0 END FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id
                    AND entry.request_code = 'R'
            ) AS GRANDFATHERED
            FROM TIMEOFF_REQUESTS request
            WHERE request.REQUEST_ID = '" . $requestId . "'";

        $requestData = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql($this->dbAdapter, $rawSql);

        return $requestData;
    }

    public function logEntry($requestId = null, $employeeNumber = null, $comment = null)
    {
        $logEntry = new Insert('timeoff_request_log');
        $logEntry->values([
            'REQUEST_ID' => $requestId,
            'EMPLOYEE_NUMBER' => $employeeNumber,
            'COMMENT' => $comment
        ]);
        $sql = new Sql($this->dbAdapter);
        $stmt = $sql->prepareStatementForSqlObject($logEntry);
        try {
            $result = $stmt->execute();
        } catch (\Exception $e) {
            throw new \Exception("Can't execute statement: " . $e->getMessage());
        }
    }

}
