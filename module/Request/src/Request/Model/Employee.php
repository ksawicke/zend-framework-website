<?php
namespace Request\Model;

use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Expression;
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
    public $employeeData = []; //['BEREAVEMENT_PENDING' => '.00',
//        'BEREAVEMENT_PENDING_TMP' => '.00',
//        'BEREAVEMENT_PENDING_TOTAL' => '.00',
//        'BEREAVEMENT_UNAPPROVED' => '.00',
//        'CIVIC_DUTY_PENDING' => '.00',
//        'CIVIC_DUTY_PENDING_TMP' => '.00',
//        'CIVIC_DUTY_PENDING_TOTAL' => '.00',
//        'CIVIC_DUTY_UNAPPROVED' => '.00',
//        'EMAIL_ADDRESS' => 'James_Gasior@Swifttrans.com',
//        'EMPLOYEE_HIRE_DATE' => '8/06/1999',
//        'EMPLOYEE_NAME' => 'GASIOR, JAMES',
//        'EMPLOYEE_NUMBER' => '49499"
//        'FLOAT_AVAILABLE' => '.00',
//        'FLOAT_EARNED' => '152.00',
//        'FLOAT_PENDING' => '.00',
//        'FLOAT_PENDING_TMP' => '.00',
//        'FLOAT_PENDING_TOTAL' => '.00',
//        'FLOAT_TAKEN' => '152.00',
//        'FLOAT_UNAPPROVED' => '.00',
//        'GF_AVAILABLE' => '.00',
//        'GF_EARNED' => '.00',
//        'GF_PENDING' => '.00',
//        'GF_PENDING_TMP' => '.00',
//        'GF_PENDING_TOTAL' => '.00',
//        'GF_TAKEN' => '.00',
//        'GF_UNAPPROVED' => '.00',
//        'LEVEL_1' => '10100"
//        'LEVEL_2' => 'IT"
//        'LEVEL_3' => 'DV00X"
//        'LEVEL_4' => '92510"
//        'MANAGER_EMAIL_ADDRESS' => 'Mary_Jackson@swifttrans.com"
//        'MANAGER_EMPLOYEE_NUMBER' => '229589"
//        'MANAGER_NAME' => 'JACKSON, MARY"
//        'MANAGER_POSITION' => 'MESITP"
//        'MANAGER_POSITION_TITLE' => 'SAVTN-SR IT PROJECT LDR"
//        'POSITION' => 'AZSDA3"
//        'POSITION_TITLE' => 'PHOAZ-SOFTWARE DEV/ANALYST III"
//        'PTO_AVAILABLE' => '-230.67"
//        'PTO_EARNED' => '1993.33"
//        'PTO_PENDING' => '8.00',
//        'PTO_PENDING_TMP' => '.00',
//        'PTO_PENDING_TOTAL' => '632.00',
//        'PTO_TAKEN' => '1592.00',
//        'PTO_UNAPPROVED' => '624.00',
//        'SALARY_TYPE' => 'S"
//        'SCHEDULE_FRI' => '8.00',
//        'SCHEDULE_MON' => '8.00',
//        'SCHEDULE_SAT' => '.00',
//        'SCHEDULE_SUN' => '.00',
//        'SCHEDULE_THU' => '8.00',
//        'SCHEDULE_TUE' => '8.00',
//        'SCHEDULE_WED' => '8.00',
//        'SICK_AVAILABLE' => '-6.67"
//        'SICK_EARNED' => '513.33"
//        'SICK_PENDING' => '.00',
//        'SICK_PENDING_TMP' => '.00',
//        'SICK_PENDING_TOTAL' => '96.00',
//        'SICK_TAKEN' => '424.00',
//        'SICK_UNAPPROVED' => '96.00',
//        'UNEXCUSED_PENDING' => '.00',
//        'UNEXCUSED_PENDING_TMP' => '.00',
//        'UNEXCUSED_PENDING_TOTAL' => '.00',
//        'UNEXCUSED_UNAPPROVED' => '.00',
//        'UNPAID_PENDING' => '.00',
//        'UNPAID_PENDING_TMP' => '.00',
//        'UNPAID_PENDING_TOTAL' => '.00',
//        'UNPAID_UNAPPROVED' => '.00'
//    ];
    public $employerNumber = '';
    public $includeApproved = '';
    public $timeoffRequestColumns;
    public $timeoffRequestEntryColumns;
    public $timeoffRequestCodeColumns;
    
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
    
    protected static $typesToCodes = [
        'timeOffPTO' => 'P',
        'timeOffFloat' => 'K',
        'timeOffSick' => 'S',
        'timeOffUnexcusedAbsence' => 'X',
        'timeOffBereavement' => 'B',
        'timeOffCivicDuty' => 'J',
        'timeOffGrandfathered' => 'R',
        'timeOffApprovedNoPay' => 'A'
    ];
    
    protected static $categoryToClass = [
        'PTO' => 'timeOffPTO',
        'Float' => 'timeOffFloat',
        'Sick' => 'timeOffSick',
        'UnexcusedAbsence' => 'timeOffUnexcusedAbsence',
        'Bereavement' => 'timeOffBereavement',
        'CivicDuty' => 'timeOffCivicDuty',
        'Grandfathered' => 'timeOffGrandfathered',
        'ApprovedNoPay' => 'timeOffApprovedNoPay'
    ];
    
    protected static $codesToKronos = [
        'P' => 'PTO',
        'R' => 'GFVAC',
        'B' => 'BR',
        'K' => 'FHP',
        'S' => 'SK',
        'V' => 'VA'
    ];

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
        $rawSql = "select * from table(timeoff_get_employee_data('002', '" . $employeeNumber . "', '" . $includeHourTotals . "')) as data";
        $statement = $this->adapter->query($rawSql);
        $result = $statement->execute();
        
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet;
            $resultSet->initialize($result);
            $this->employeeData = $resultSet->current();
        } else {
            $this->employeeData = [];
        }
        
        return \Request\Helper\Format::trimData( $this->employeeData );
    }
    
    public function findManagerEmployees($managerEmployeeNumber = null, $search = null, $directReportFilter = null)
    {
        $isPayroll = \Login\Helper\UserSession::getUserSessionVariable('IS_PAYROLL');
        $where = "WHERE (
            employee.PRER = '002' and
            employee.PRTEDH = 0 and
            employee.PRL02 <> 'DRV' and
            trim(employee.PREN) LIKE '" . strtoupper($search) . "%' OR
            trim(employee.PRCOMN) || ' ' || trim(employee.PRLNM) LIKE '" . strtoupper($search) . "%' OR
            trim(employee.PRFNM) || ' ' || trim(employee.PRLNM) LIKE '" . strtoupper($search) . "%'
        )";

        if ($isPayroll === "N") {
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
                      CARE_GET_MANAGER_EMPLOYEES('002', '" . $managerEmployeeNumber . "', '" . $directReportFilter . "')
                  ) as data
            ) hierarchy
                  ON hierarchy.EMPLOYEE_NUMBER = trim(employee.PREN)
            INNER JOIN PRPSP manager
                  ON employee.PREN = manager.SPEN
            INNER JOIN PRPMS manager_addons
                 ON manager_addons.PREN = manager.SPSPEN
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
        
//        die($rawSql);

        $employeeData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql($this->adapter, $rawSql);

        return $employeeData;
    }
    
    public function appendAllRequestsJsonArray($request)
    {
        return ['date' => date("m/d/Y", strtotime($request['REQUEST_DATE'])),
                'dateYmd' => date("Y-m-d", strtotime($request['REQUEST_DATE'])),
                'hours' => $request['REQUESTED_HOURS'],
                'category' => self::$categoryToClass[$request['REQUEST_TYPE']],
                'status' => $request['REQUEST_STATUS']
               ];
    }
    
    public function appendRequestJsonArray($request)
    {
        return ['REQUEST_DATE' => date("m/d/Y", strtotime($request['REQUEST_DATE'])),
                'REQUESTED_HOURS' => $request['REQUESTED_HOURS'],
                'REQUEST_TYPE' => self::$categoryToClass[$request['REQUEST_TYPE']]
               ];
    }
    
    public function adjustRequestType($request)
    {
        if($request['REQUEST_TYPE']==='Unexcused') {
            $request['REQUEST_TYPE'] = 'UnexcusedAbsence';
        }
        if($request['REQUEST_TYPE']==='Time Off Without Pay') {
            $request['REQUEST_TYPE'] = 'ApprovedNoPay';
        }
        return $request;
    }
    
    public function findTimeOffRequestData($employeeNumber = null, $calendarDates = null)
    {
        $approvedRequestsJson = [];
        $pendingRequestsJson = [];
        $allRequestsJson = [];
        
//        var_dump($calendarDates);exit();
        
        $approvedRequestsData = $this->findTimeOffRequestsByEmployeeAndStatus($employeeNumber, "A", $calendarDates['currentMonth']->format('Y-m-d'), $calendarDates['threeMonthsOut']->format('Y-m-t'));
        $pendingRequestsData = $this->findTimeOffRequestsByEmployeeAndStatus($employeeNumber, "P", $calendarDates['currentMonth']->format('Y-m-d'), $calendarDates['threeMonthsOut']->format('Y-m-t'));

        foreach($approvedRequestsData as $key => $approvedRequest) {
            $approvedRequest = $this->adjustRequestType($approvedRequest);
            $allRequestsJson[] = $this->appendAllRequestsJsonArray($approvedRequest);
            $approvedRequestsJson[] = $this->appendRequestJsonArray($approvedRequest);
//            if($approvedRequest['REQUEST_DATE'] > $calendarDates['currentMonth']->format('Y-m-d') and $approvedRequest['REQUEST_DATE'] < $calendarDates['threeMonthsOut']->format('Y-m-t')) {
//                $approvedRequestsJson[] = $this->appendAllRequestsJsonArray($approvedRequest);
//            }
        }
        foreach($pendingRequestsData as $key => $pendingRequest) {
            $pendingRequest = $this->adjustRequestType($pendingRequest);            
            $allRequestsJson[] = $this->appendAllRequestsJsonArray($pendingRequest);
            $pendingRequestsJson[] = $this->appendRequestJsonArray($pendingRequest);
//            if($pendingRequest['REQUEST_DATE'] >= $calendarDates['currentMonth']->format('Y-m-d') and $pendingRequest['REQUEST_DATE'] < $calendarDates['threeMonthsOut']->format('Y-m-d')) {
//                $pendingRequestsJson[] = $this->appendAllRequestsJsonArray($pendingRequest);
//            }
        }
        
        return ['json' => ['all' => $allRequestsJson,
                           'approved' => $approvedRequestsJson,
                           'pending' => $pendingRequestsJson,
                          ],
                'data' => ['approved' => $approvedRequestsData,
                           'pending' => $pendingRequestsData
                          ]
               ];
    }
    
    /**
     * Find time off calendar data by Employee Number lookup.
     * 
     */
    public function findTimeOffCalendarByEmployeeNumber($employeeNumber = null, $startDate = null, $endDate = null)
    {
        $rawSql = "SELECT entry.ENTRY_ID, entry.REQUEST_DATE, entry.REQUESTED_HOURS, requestcode.CALENDAR_DAY_CLASS
            FROM TIMEOFF_REQUEST_ENTRIES entry
            INNER JOIN TIMEOFF_REQUESTS AS request ON request.REQUEST_ID = entry.REQUEST_ID
            INNER JOIN TIMEOFF_REQUEST_CODES AS requestcode ON requestcode.REQUEST_CODE = entry.REQUEST_CODE
            INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
            WHERE
               request.REQUEST_STATUS = 'A' AND
               trim(employee.PREN) = '" . $employeeNumber . "' AND
               entry.REQUEST_DATE BETWEEN '" . $startDate . "' AND '" . $endDate . "'
            ORDER BY REQUEST_DATE ASC";
        die($rawSql);
        $statement = $this->adapter->query($rawSql);
        $result = $statement->execute();
        
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet;
            $resultSet->initialize($result);
            $calendarData = $resultSet->toArray();
        } else {
            $calendarData = [];
        }
        
        return $calendarData; //\Request\Helper\Format::trimData( $calendarData );
    }
    
    public function findTimeOffRequestsByEmployeeAndStatus($employeeNumber = null, $status = "A", $startDate = null, $endDate = null)
    {   
        $sql = new Sql($this->adapter);
        if($startDate==null || $endDate==null) {
            $select = $sql->select(['entry' => 'TIMEOFF_REQUEST_ENTRIES'])
                ->columns($this->timeoffRequestEntryColumns)
                ->join(['request' => 'TIMEOFF_REQUESTS'], 'request.REQUEST_ID = entry.REQUEST_ID', $this->timeoffRequestColumns)
                ->join(['requestcode' => 'TIMEOFF_REQUEST_CODES'], 'requestcode.REQUEST_CODE = entry.REQUEST_CODE', $this->timeoffRequestCodeColumns)
                ->where(['trim(request.EMPLOYEE_NUMBER)' => trim($employeeNumber),
                         'request.REQUEST_STATUS' => $status
                        ])
                ->order(['entry.REQUEST_DATE ASC']);
        } else {
            $select = $sql->select(['entry' => 'TIMEOFF_REQUEST_ENTRIES'])
                ->columns($this->timeoffRequestEntryColumns)
                ->join(['request' => 'TIMEOFF_REQUESTS'], 'request.REQUEST_ID = entry.REQUEST_ID', $this->timeoffRequestColumns)
                ->join(['requestcode' => 'TIMEOFF_REQUEST_CODES'], 'requestcode.REQUEST_CODE = entry.REQUEST_CODE', $this->timeoffRequestCodeColumns)
                ->where(['trim(request.EMPLOYEE_NUMBER)' => trim($employeeNumber),
                         'request.REQUEST_STATUS' => $status,
                         'entry.REQUEST_DATE >= ?' => $startDate,
                         'entry.REQUEST_DATE < ?' => $endDate
                        ])
                ->order(['entry.REQUEST_DATE ASC']);
        }
        
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
    
    public function submitRequestForApproval($employeeNumber = null, $requestData = [], $requestReason = null, $requesterEmployeeNumber = null)
    {
        $requestReturnData = ['request_id' => null];

        /** Insert record into TIMEOFF_REQUESTS * */
        $action = new Insert('timeoff_requests');
        $action->values([
            'EMPLOYEE_NUMBER' => \Request\Helper\Format::rightPad($employeeNumber),
            'REQUEST_STATUS' => self::$requestStatuses['pendingApproval'],
            'CREATE_USER' => \Request\Helper\Format::rightPad($requesterEmployeeNumber),
            'REQUEST_REASON' => $requestReason
        ]);
        $sql = new Sql($this->adapter);
        $stmt = $sql->prepareStatementForSqlObject($action);
        try {
            $result = $stmt->execute();
        } catch (Exception $e) {
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
            $sql = new Sql($this->adapter);
            $stmt = $sql->prepareStatementForSqlObject($action);
            try {
                $result = $stmt->execute();
            } catch (Exception $e) {
                throw new \Exception("Can't execute statement: " . $e->getMessage());
            }
        }
        $requestReturnData['request_id'] = $requestId;

        return $requestReturnData;
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