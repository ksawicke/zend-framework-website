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
use Zend\Db\Sql\Expression;
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
            'MANAGER_FIRST_NAME' => 'PRFNM',
            'MANAGER_MIDDLE_INITIAL' => 'PRMNM',
            'MANAGER_LAST_NAME' => 'PRLNM',
            'MANAGER_EMAIL_ADDRESS' => 'PREML1'
        ];
        $this->timeoffRequestColumns = [
            'REQUEST_ID' => 'REQUEST_ID',
            'REQUEST_REASON' => 'REQUEST_REASON',
            'CREATE_TIMESTAMP' => 'CREATE_TIMESTAMP',
            'REQUEST_STATUS' => 'REQUEST_STATUS'
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
    public function findTimeOffBalancesByEmployee($employeeNumber = null)
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select(['employee' => 'PRPMS'])
            ->columns($this->employeeColumns)
            ->join(['manager' => 'PRPSP'], 'employee.PREN = manager.SPEN', []) // $this->employeeSupervisorColumns
            ->join(['manager_addons' => 'PRPMS'], 'manager_addons.PREN = manager.SPSPEN', $this->supervisorAddonColumns)
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
            ->where(['trim(employee.PREN)' => trim($employeeNumber)]);

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
        $result['POSITION_TITLE'] = trim($result['POSITION_TITLE']);
        
//         echo '<pre>';
//         print_r($result);
//         echo '</pre>';
//         die("@@@");
        
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
        switch($returnType) {
            case 'datesOnly':
            default:
                $return = $result;
                break;
        
            case 'managerQueue':
                $return = [];
        
                foreach($result as $key => $data) {
                    if(!array_key_exists($data['REQUEST_ID'], $return)) {
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
    
    public function findTimeOffPendingRequestsByEmployee($employeeNumber = null, $returnType = "datesOnly", $requestId = null)
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select(['entry' => 'TIMEOFF_REQUEST_ENTRIES'])
            ->columns($this->timeoffRequestEntryColumns)
            ->join(['request' => 'TIMEOFF_REQUESTS'], 'request.REQUEST_ID = entry.REQUEST_ID', $this->timeoffRequestColumns)
            ->join(['requestcode' => 'TIMEOFF_REQUEST_CODES'], 'requestcode.REQUEST_CODE = entry.REQUEST_CODE', $this->timeoffRequestCodeColumns)
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
            ->join(['employee' => 'PRPMS'], 'trim(employee.PREN) = request.EMPLOYEE_NUMBER', $this->employeeColumns)
            ->join(['manager' => 'PRPSP'], 'employee.PREN = manager.SPEN', []) // $this->employeeSupervisorColumns
            ->join(['manager_addons' => 'PRPMS'], 'manager_addons.PREN = manager.SPSPEN', $this->supervisorAddonColumns)
            ->order(['entry.REQUEST_DATE ASC']);
        if($requestId!=null) {
//             $select->where(['trim(request.EMPLOYEE_NUMBER)' => trim($employeeNumber), 'request.REQUEST_STATUS' => 'P', 'request.REQUEST_ID' => $requestId]);
            $select->where(['request.REQUEST_ID' => $requestId]);
        }
    
        $result = \Request\Helper\ResultSetOutput::getResultArray($sql, $select);
        switch($returnType) {
            case 'datesOnly':
            default:
                $return = $result;
                break;
                
            case 'managerQueue':
                $return = [];
                
                foreach($result as $key => $data) {
                    if(!array_key_exists($data['REQUEST_ID'], $return)) {
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
                        
                        $return[$data['REQUEST_ID']] =
                            [ 'REQUEST_REASON' => $data['REQUEST_REASON'],
                              'REQUEST_STATUS_TEXT' => self::$requestStatusText[$data['REQUEST_STATUS']],
                              'CREATE_TIMESTAMP' => $data['CREATE_TIMESTAMP'],
                              'DETAILS'        => [],
                              'TOTALS'         => [ 'PTO' => number_format(0.00, 2),
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
                              'EMPLOYEE'       => [ 'EMPLOYEE_NUMBER' => $data['EMPLOYEE_NUMBER'],
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
                    
                    $return[$data['REQUEST_ID']]['DETAILS'][] = [
                        'REQUEST_DATE' => $data['REQUEST_DATE'],
                        'REQUESTED_HOURS' => $data['REQUESTED_HOURS'],
                        'REQUEST_TYPE' => $data['REQUEST_TYPE']
                    ];
                    
                    if( $return[$data['REQUEST_ID']]['FIRST_DATE_REQUESTED']=='' ) {
                        $return[$data['REQUEST_ID']]['FIRST_DATE_REQUESTED'] = $data['REQUEST_DATE'];
                    }
                    $return[$data['REQUEST_ID']]['LAST_DATE_REQUESTED'] = $data['REQUEST_DATE'];
                    
                    $return[$data['REQUEST_ID']]['TOTALS'][$data['REQUEST_TYPE']] += $data['REQUESTED_HOURS'];
                    $return[$data['REQUEST_ID']]['TOTALS']['Grand'] += $data['REQUESTED_HOURS'];
                    $return[$data['REQUEST_ID']]['TOTALS'][$data['REQUEST_TYPE']] = number_format($return[$data['REQUEST_ID']]['TOTALS'][$data['REQUEST_TYPE']], 2);
                    $return[$data['REQUEST_ID']]['TOTALS']['Grand'] = number_format($return[$data['REQUEST_ID']]['TOTALS']['Grand'], 2);
                    $return[$data['REQUEST_ID']]['EMPLOYEE']['BALANCES']['PTO']['PTO_POST_PENDING_APPROVAL'] =
                        number_format(($return[$data['REQUEST_ID']]['EMPLOYEE']['BALANCES']['PTO']['PTO_PENDING_APPROVAL'] - $return[$data['REQUEST_ID']]['TOTALS']['PTO']), 2);
                    $return[$data['REQUEST_ID']]['EMPLOYEE']['BALANCES']['Float']['FLOAT_POST_PENDING_APPROVAL'] =
                        number_format(($return[$data['REQUEST_ID']]['EMPLOYEE']['BALANCES']['Float']['FLOAT_PENDING_APPROVAL'] - $return[$data['REQUEST_ID']]['TOTALS']['Float']), 2);
                    $return[$data['REQUEST_ID']]['EMPLOYEE']['BALANCES']['Sick']['SICK_POST_PENDING_APPROVAL'] =
                        number_format(($return[$data['REQUEST_ID']]['EMPLOYEE']['BALANCES']['Sick']['SICK_PENDING_APPROVAL'] - $return[$data['REQUEST_ID']]['TOTALS']['Sick']), 2);
                    
                    $return[$data['REQUEST_ID']]['EMPLOYEE']['BALANCES']['UnexcusedAbsence']['UNEXCUSED_ABSENCE_POST_PENDING_APPROVAL'] = number_format(0.00, 2);
                    $return[$data['REQUEST_ID']]['EMPLOYEE']['BALANCES']['Bereavement']['BEREAVEMENT_POST_PENDING_APPROVAL'] = number_format(0.00, 2);
                    $return[$data['REQUEST_ID']]['EMPLOYEE']['BALANCES']['CivicDuty']['CIVIC_DUTY_POST_PENDING_APPROVAL'] = number_format(0.00, 2);
                    $return[$data['REQUEST_ID']]['EMPLOYEE']['BALANCES']['Grandfathered']['GRANDFATHERED_POST_PENDING_APPROVAL'] = number_format(0.00, 2);
                    $return[$data['REQUEST_ID']]['EMPLOYEE']['BALANCES']['ApprovedNoPay']['APPROVED_NO_PAY_POST_PENDING_APPROVAL'] = number_format(0.00, 2);
                    
                    $return[$data['REQUEST_ID']]['EMPLOYEE']['BALANCES']['Grand'] =
                        number_format(($return[$data['REQUEST_ID']]['EMPLOYEE']['BALANCES']['PTO']['PTO_POST_PENDING_APPROVAL'] +
                                       $return[$data['REQUEST_ID']]['EMPLOYEE']['BALANCES']['Float']['FLOAT_POST_PENDING_APPROVAL'] +
                                       $return[$data['REQUEST_ID']]['EMPLOYEE']['BALANCES']['Sick']['SICK_POST_PENDING_APPROVAL']
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
            ->join(['employee' => 'PRPMS'], 'trim(PREN) = request.EMPLOYEE_NUMBER', ['EMPLOYEE_NUMBER' => 'PREN', 'LAST_NAME' => 'PRLNM', 'FIRST_NAME' => 'PRFNM']) // 'EMPLOYEENAME' => 'get_employee_common_name(employee.PRER, employee.PREN)'
            ->where(['request.REQUEST_STATUS' => 'A',
                     "trim(employee.PREN) IN( SELECT trim(SPEN) as EMPLOYEE_IDS FROM PRPSP WHERE trim(SPSPEN) = '" . $managerEmployeeNumber . "' )",
                     "entry.REQUEST_DATE BETWEEN '" . $startDate . "' AND '" . $endDate . "'"
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
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select(["data" => "table (SELECT trim(SPEN) as EMPLOYEE_NUMBER FROM sawik.PRPSP WHERE trim(SPSPEN) = '" . $managerEmployeeId . "')"]) // (care_get_manager_employees('002', '   229589', ''))
            ->columns(['EMPLOYEE_NUMBER'])
            ->join(['employee' => 'PRPMS'], 'trim(employee.PREN) = data.EMPLOYEE_NUMBER', $this->employeeColumns)
            ->join(['manager' => 'PRPSP'], 'employee.PREN = manager.SPEN', []) // $this->employeeSupervisorColumns
            ->join(['manager_addons' => 'PRPMS'], 'manager_addons.PREN = manager.SPSPEN', $this->supervisorAddonColumns)
            ->join(['pendingrequests' => 'PAPREQ'], "pendingrequests.REQCLK# = '101639'", $this->pendingRequestColumns);

        $employeeData = \Request\Helper\ResultSetOutput::getResultArray($sql, $select);

        $queueData = [ 'pendingApproval' => [],
                       'approved' => [],
                     ];
        
        foreach($employeeData as $counter => $employee) {
            $employeeData[$counter]['APPROVED_TIME_OFF'] = $this->findTimeOffApprovedRequestsByEmployee($employee->EMPLOYEE_NUMBER, "managerQueue");
            $employeeData[$counter]['PENDING_APPROVAL_TIME_OFF'] = $this->findTimeOffPendingRequestsByEmployee($employee->EMPLOYEE_NUMBER, "managerQueue", null);
        }

        return $employeeData;
    }
    
    public function findManagerEmployees($managerEmployeeNumber = null, $search = null)
    {
//         $sql = "SELECT * FROM PRPMS employee WHERE trim(employee.PREN) = '49499'";
//         $statement = $this->dbAdapter->query($sql);
//         $result = $statement->execute();
        
//         $resultSet = new ResultSet;
//         $resultSet->initialize($result);
        
//         $array = [];
//         foreach($resultSet as $row) {
//             $array[] = $row;
//         }
        
//         echo '<pre>';
//         print_r($array);
//         echo '</pre>';
        
//         die('@@@@@@@@@@@@@@@');
        
        $rawSql = "SELECT
              case
        	  when trim(employee.PRCOMN) IS NOT NULL then trim(employee.PRLNM) || ', ' || trim(employee.PRCOMN)
                  else trim(employee.PRLNM) || ', ' || trim(employee.PRFNM)
              end as EMPLOYEE_NAME,
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
        	  CARE_GET_MANAGER_EMPLOYEES('002', '" . $managerEmployeeNumber . "', 'B')
              ) as data
            
        ) hierarchy
              ON hierarchy.EMPLOYEE_NUMBER = trim(employee.PREN)
        INNER JOIN PRPSP manager
              ON employee.PREN = manager.SPEN
        INNER JOIN PRPMS manager_addons
             ON manager_addons.PREN = manager.SPSPEN
        WHERE
             ( trim(employee.PREN) LIKE '%" . strtoupper($search) . "%' OR
               trim(employee.PRCOMN) || ' ' || trim(employee.PRLNM) LIKE '%" . strtoupper($search) . "%' OR
               trim(employee.PRFNM) || ' ' || trim(employee.PRLNM) LIKE '%" . strtoupper($search) . "%'
             )
        ORDER BY employee.PRLNM ASC, employee.PRFNM ASC";
        
        $employeeData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql($this->dbAdapter, $rawSql);
        
        return $employeeData;
                
        //$sql = new Sql($this->dbAdapter);
        
        /**
         * 
         * SELECT
         *      employee.PREN AS employeeNumber, employee.PRCOMN AS employeeCommonName,
         *      employee.PRLNM AS employeeLastName, employee.PRFNM AS employeeFirstName,
         *      manager_addons.PRER AS MANAGER_EMPLOYER_NUMBER, manager_addons.PREN AS MANAGER_EMPLOYEE_NUMBER,
         *      manager_addons.PRFNM AS MANAGER_FIRST_NAME, manager_addons.PRMNM AS MANAGER_MIDDLE_INITIAL,
         *      manager_addons.PRLNM AS MANAGER_LAST_NAME,
         *      manager_addons.PREML1 AS MANAGER_EMAIL_ADDRESS
         * FROM PRPMS employee
         * INNER JOIN PRPSP manager
         *      ON employee.PREN = manager.SPEN
         * INNER JOIN PRPMS manager_addons
         *      ON manager_addons.PREN = manager.SPSPEN
         * WHERE
         *      trim(manager.SPSPEN) = '49602' AND
         *      ( trim(employee.PREN) LIKE '%SENA%' OR
         *        trim(employee.PRFNM) LIKE '%SENA%' OR
         *        trim(employee.PRLNM) LIKE '%SENA%'
         *      )
         * ORDER BY employee.PRLNM ASC
         * 
         * @var unknown
         */
//         $select = $sql->select(['employee' => 'PRPMS'])
//             ->columns(['employeeNumber' => 'PREN',
//                        'employeeCommonName' => 'PRCOMN',
//                        'employeeLastName' => 'PRLNM',
//                        'employeeFirstName' => 'PRFNM'
//                       ])
//             ->join(['manager' => 'PRPSP'], 'employee.PREN = manager.SPEN', []) // $this->employeeSupervisorColumns
//             ->join(['manager_addons' => 'PRPMS'], 'manager_addons.PREN = manager.SPSPEN', $this->supervisorAddonColumns)
//             ->where("trim(manager.SPSPEN) = '$managerEmployeeNumber' AND ( trim(employee.PREN) LIKE '%" . strtoupper($search) . "%' OR trim(employee.PRFNM) LIKE '%" . strtoupper($search) . "%' OR trim(employee.PRLNM) LIKE '%" . strtoupper($search) . "%' )")
            
//         $select = $sql->select(['employee' => 'PRPMS'])
//             ->columns(['EMPLOYEE_NAME' => 'trim(employee.PRLNM)',
//                        'LEVEL_1' => 'PRL01',
//                        'LEVEL_2' => 'PRL02',
//                        'LEVEL_3' => 'PRL03',
//                        'LEVEL_4' => 'PRL04',
//                       ])
//             ->join(['hierarchy' => 'table (
           
//                  SELECT
//                      trim(EMPLOYEE_ID) AS EMPLOYEE_NUMBER,
//         	     TRIM(DIRECT_MANAGER_EMPLOYEE_ID) AS DIRECT_MANAGER_EMPLOYEE_NUMBER,
//         	     DIRECT_INDIRECT,
//         	     MANAGER_LEVEL
//                  FROM table (
//         	     CARE_GET_MANAGER_EMPLOYEES("002", "49602", "B")
//                  )
            
//               )'], 'hierarchy.EMPLOYEE_NUMBER = trim(employee.PREN)', ['DIRECT_INDIRECT' => 'DIRECT_INDIRECT', 'MANAGER_LEVEL' => 'MANAGER_LEVEL'])
//             ->join(['manager' => 'PRPSP'], 'employee.PREN = manager.SPEN', $this->supervisorAddonColumns)
//             ->join(['manager_addons' => 'PRPMS'], 'manager_addons.PREN = manager.SPSPEN', $this->supervisorAddonColumns)
//             ->where("( trim(employee.PREN) LIKE '%HE%' OR
//                trim(employee.PRCOMN) || ' ' || trim(employee.PRLNM) LIKE '%HE%' OR
//                trim(employee.PRFNM) || ' ' || trim(employee.PRLNM) LIKE '%HE%'
//              )");
        
        /**
         * 12/31/15 WANT TO CHANGE TO THIS QUERY:
         * 
         * SELECT
              case
        	  when trim(employee.PRCOMN) IS NOT NULL then trim(employee.PRLNM) || ', ' || trim(employee.PRCOMN)
                  else 'NULL'
              end as EMPLOYEE_NAME,
              trim(employee.PREN) AS EMPLOYEE_NUMBER,
              employee.PRL01 AS LEVEL_1,
              employee.PRL02 AS LEVEL_2,
              employee.PRL03 AS LEVEL_3,
              employee.PRL04 AS LEVEL_4,
              hierarchy.DIRECT_INDIRECT,
              hierarchy.MANAGER_LEVEL,
              employee.PRPOS AS POSITION,
              employee.PREML1 AS EMAIL_ADDRESS,
              employee.PRDOHE AS EMPLOYEE_HIRE_DATE,
              employee.PRTITL AS POSITION_TITLE,
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
        	  CARE_GET_MANAGER_EMPLOYEES('002', '49602', 'B')
              ) as data
            
        ) hierarchy
              ON hierarchy.EMPLOYEE_NUMBER = trim(employee.PREN)
        INNER JOIN PRPSP manager
              ON employee.PREN = manager.SPEN
        INNER JOIN PRPMS manager_addons
             ON manager_addons.PREN = manager.SPSPEN
        WHERE
             ( trim(employee.PREN) LIKE '%HE%' OR
               trim(employee.PRCOMN) || ' ' || trim(employee.PRLNM) LIKE '%HE%' OR
               trim(employee.PRFNM) || ' ' || trim(employee.PRLNM) LIKE '%HE%'
             )
        ORDER BY employee.PRLNM ASC, employee.PRFNM ASC;;
         */    
            
            
            // AND concat( concat( concat( concat( concat(trim(employee.PRLNM),', '), trim(employee.PRFNM) ),' ('), trim(employee.PREN) ),')' ) LIKE '%gas%'
            
            // This works:
            // ->where("trim(manager.SPSPEN) = '$managerEmployeeNumber' OR trim(manager.SPSPEN) = '49602'")
            
//             ->where(['trim(manager.SPSPEN)' => $managerEmployeeNumber
// //                      'employeeSearchResult' => 'SENA, RANDY (296261)'
//                     ])
//             ->where(['employeeSearchResult LIKE ?', "%$search%"])
//             ->order(['employee.PRLNM ASC, employee.PRFNM ASC']);
//         die($select->getSqlString());
//         $employeeData = \Request\Helper\ResultSetOutput::getResultArray($sql, $select);
        
//         return $employeeData;
    }
    
    public function findQueuesByManager($managerEmployeeNumber = null)
    {
//         $select = $this->dbAdapter->query("select
        
//    request.request_id, request.request_reason,
        
//    employee.PREN AS EMPLOYEE_NUMBER, trim(employee.PRLNM) || ', ' || trim(employee.PRFNM) || ' ' || trim(employee.PRMNM) AS EMPLOYEE_NAME, employee.PRTITL AS POSITION_TITLE,
//    (select sum(requested_hours)
//       FROM timeoff_request_entries entry
//       WHERE entry.request_id = request.request_id) AS TOTAL_HOURS_REQUESTED
        
// FROM timeoff_requests request
// INNER JOIN PRPMS employee ON trim(employee.PREN) = trim(request.employee_number)
// WHERE request.REQUEST_STATUS = 'P'");
//         return $select->execute();
        
        $sql = new Sql($this->dbAdapter);

        $select = $sql->select(['request' => 'TIMEOFF_REQUESTS'])
            ->columns(['REQUEST_ID' => 'REQUEST_ID', 'REQUEST_REASON' => 'REQUEST_REASON', 'REQUEST_STATUS' => 'REQUEST_STATUS',
                'requested_hours' => new Expression('(SELECT SUM(requested_hours) FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id)')                
                ])
            ->join(['employee' => 'PRPMS'], 'trim(employee.PREN) = request.EMPLOYEE_NUMBER',
                   ['EMPLOYEE_LAST_NAME' => 'PRLNM', 'EMPLOYEE_FIRST_NAME' => 'PRFNM', 'EMPLOYEE_MIDDLE_NAME' => 'PRMNM'
                   ])
            ->join(['manager' => 'PRPSP'], 'employee.PREN = manager.SPEN', []) // $this->employeeSupervisorColumns
            ->join(['manager_addons' => 'PRPMS'], 'manager_addons.PREN = manager.SPSPEN', $this->supervisorAddonColumns)
            ->where(['request.REQUEST_STATUS' => 'P']);
            
        // 'TOTAL_HOURS_REQUESTED' => '(select sum(requested_hours) FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id)'
//         var_dump($select);exit();

        return \Request\Helper\ResultSetOutput::getResultArray($sql, $select);
        //         select
        
        //         request.request_id, request.request_reason,
        
        //         employee.PREN AS EMPLOYEE_NUMBER, trim(employee.PRLNM) || ', ' || trim(employee.PRFNM) || ' ' || trim(employee.PRMNM) AS EMPLOYEE_NAME, employee.PRTITL AS POSITION_TITLE,
        //         (select sum(requested_hours)
        //             FROM timeoff_request_entries r
        //             WHERE r.request_id = request.request_id) AS TOTAL_HOURS_REQUESTED
        
        //             FROM timeoff_requests request
        //             INNER JOIN PRPMS employee ON trim(employee.PREN) = trim(request.employee_number)
        //             WHERE request.REQUEST_STATUS = 'P';
        /// END.
    }
    
    public function submitRequestForApproval($employeeNumber = null, $requestData = [], $requestReason = null)
    {
        $requestReturnData = ['request_id' => null];
        
        /** Insert record into TIMEOFF_REQUESTS **/
        $action = new Insert('timeoff_requests');
        $action->values([
            'EMPLOYEE_NUMBER' => $employeeNumber,
            'REQUEST_STATUS' => self::$requestStatuses['pendingApproval'],
            'CREATE_USER' => $employeeNumber,
            'REQUEST_REASON' => $requestReason
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
        $requestReturnData['request_id'] = $requestId;
        
        return $requestReturnData;
    }
}
