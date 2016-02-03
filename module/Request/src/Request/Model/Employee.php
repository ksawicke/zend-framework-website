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
    
    public function appendAllRequestsJsonArray($request)
    {
        return ['date' => date("m/d/Y", strtotime($request['REQUEST_DATE'])),
                'dateYmd' => date("Y-m-d", strtotime($request['REQUEST_DATE'])),
                'hours' => $request['REQUESTED_HOURS'],
                'category' => self::$categoryToClass[$request['REQUEST_TYPE']],
                'status' => 'A'
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
        
        $approvedRequestsData = $this->findTimeOffRequestsByEmployeeAndStatus($employeeNumber, "A");
        $pendingRequestsData = $this->findTimeOffRequestsByEmployeeAndStatus($employeeNumber, "P");

        foreach($approvedRequestsData as $key => $approvedRequest) {
            $approvedRequest = $this->adjustRequestType($approvedRequest);
            $approvedRequestsJson[] = $this->appendRequestJsonArray($approvedRequest);
            if($approvedRequest['REQUEST_DATE'] > $calendarDates['currentMonth']->format('Y-m-d') and $approvedRequest['REQUEST_DATE'] < $calendarDates['threeMonthsOut']->format('Y-m-01')) {
                $allRequestsJson[] = $this->appendAllRequestsJsonArray($approvedRequest);
            }
        }
        foreach($pendingRequestsData as $key => $pendingRequest) {
            $pendingRequest = $this->adjustRequestType($pendingRequest);            
            $pendingRequestJson[] = $this->appendRequestJsonArray($pendingRequest);
            if($pendingRequest['REQUEST_DATE'] > $calendarDates['currentMonth']->format('Y-m-d') and $pendingRequest['REQUEST_DATE'] < $calendarDates['threeMonthsOut']->format('Y-m-01')) {
                $allRequestsJson[] = $this->appendAllRequestsJsonArray($pendingRequest);
            }
        }
        
        return ['json' => ['all' => $allRequestsJson,
                           'approved' => $approvedRequestsJson,
                           'pending' => $pendingRequestsData,
                          ],
                'data' => ['approved' => $approvedRequestsData,
                           'pending' => $pendingRequestsData
                          ]
               ];
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