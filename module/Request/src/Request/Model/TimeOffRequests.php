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
 * Common Request arrays
 *
 * @author sawik
 *
 */
class TimeOffRequests extends BaseDB {
    
    public $employeeColumns;
    
    public $supervisorAddonColumns;
    
    public $requesterAddonColumns;
    
    public $requestStatuses;
    
    public $requestStatusText;
    
    public $timeOffRequestColumns;
    
    public $timeOffRequestEntryColumns;
    
    protected $typesToCodes;
    
    protected $categoryToClass;
    
    protected $codesToKronos;

    public function __construct() {
        parent::__construct();
        
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
        
        $this->timeOffRequestColumns = [
            'REQUEST_ID' => 'REQUEST_ID',
            'REQUEST_REASON' => 'REQUEST_REASON',
            'CREATE_TIMESTAMP' => 'CREATE_TIMESTAMP',
            'REQUEST_STATUS' => 'REQUEST_STATUS',
            'REQUESTER_EMPLOYEE_ID' => 'CREATE_USER'
        ];
        
        $this->timeOffRequestEntryColumns = [
            'REQUEST_DATE' => 'REQUEST_DATE',
            'REQUESTED_HOURS' => 'REQUESTED_HOURS',
            'REQUEST_CODE' => 'REQUEST_CODE'
        ];
        
        $this->requestStatuses = [
            'denied' => 'D',
            'approved' => 'A',
            'cancelled' => 'C',
            'pendingManagerApproval' => 'P',
            'completedPAFs' => 'F',
            'pendingAS400Upload' => 'S',
            'pendingPayrollApproval' => 'Y',
            'updateChecks' => 'U'
        ];
        
        $this->requestStatusText = [
            'D' => 'denied',
            'A' => 'approved',
            'C' => 'cancelled',
            'P' => 'pendingManagerApproval',
            'F' => 'completedPAFs',
            'S' => 'pendingAS400Upload',
            'Y' => 'pendingPayrollApproval',
            'U' => 'updateChecks'
        ];
        
        $this->typesToCodes = [
            'timeOffPTO' => 'P',
            'timeOffFloat' => 'K',
            'timeOffSick' => 'S',
            'timeOffUnexcusedAbsence' => 'X',
            'timeOffBereavement' => 'B',
            'timeOffCivicDuty' => 'J',
            'timeOffGrandfathered' => 'R',
            'timeOffApprovedNoPay' => 'A'
        ];
        
        $this->categoryToClass = [
            'PTO' => 'timeOffPTO',
            'Float' => 'timeOffFloat',
            'Sick' => 'timeOffSick',
            'UnexcusedAbsence' => 'timeOffUnexcusedAbsence',
            'Bereavement' => 'timeOffBereavement',
            'CivicDuty' => 'timeOffCivicDuty',
            'Grandfathered' => 'timeOffGrandfathered',
            'ApprovedNoPay' => 'timeOffApprovedNoPay'
        ];
        
        $this->codesToClass = [
            'P' => 'timeOffPTO',
            'K' => 'timeOffFloat',
            'S' => 'timeOffSick',
            'X' => 'timeOffUnexcusedAbsence',
            'B' => 'timeOffBereavement',
            'J' => 'timeOffCivicDuty',
            'R' => 'timeOffGrandfathered',
            'A' => 'timeOffApprovedNoPay'
        ];
        
        $this->codesToCategory = [
            'P' => 'PTO',
            'K' => 'Float',
            'S' => 'Sick',
            'X' => 'Unexcused',
            'B' => 'Bereavement',
            'J' => 'Civic Duty',
            'R' => 'Grandfathered',
            'A' => 'Unpaid Time Off'
        ];
        
        $this->codesToKronos = [
            'P' => 'PTO',
            'R' => 'GFVAC',
            'B' => 'BR',
            'K' => 'FHP',
            'S' => 'SK',
            'V' => 'VA'
        ];
    }
    
    /**
     * Draws a nicely formatted table of the requested days to display on the review request screen.
     * 
     * @param array $entries    Array of requested days.
     * @return string
     */
    public function drawHoursRequested( $entries )
    {        
        $htmlData = '<table class="hoursRequested"><thead><tr><th>Day</th><th>Date</th><th>Hours</th><th>Type</th></tr></thead></tbody>';
        foreach( $entries as $ctr => $data ) {
            $code = $data['REQUEST_CODE'];
            $date = new \DateTime( $data['REQUEST_DATE'] );
            $date = $date->format( "m/d/Y" );
            $htmlData .= '<tr>' .
                '<td>' . $data['REQUEST_DAY_OF_WEEK'] . '</td>' . 
                '<td>' . $date . '</td>' . 
                '<td>' . $data['REQUESTED_HOURS'] . '</td>' .
                '<td><span class="badge ' . $this->codesToClass[$code] . '">' . $this->codesToCategory[$code] . '</span></td>' .
                '</tr>';
        }
        $htmlData .= '</tbody></table>';
        
        return $htmlData;
    }
    
    /**
     * Records a new Time Off request for an employee.
     * 
     * @param array $post
     * @return array    Return the Request ID generated.
     * @throws \Exception
     */
    public function submitRequestForManagerApproval( $post = [] ) {
        $requestReturnData = ['request_id' => null ];

        /** Insert record into TIMEOFF_REQUESTS * */
        $action = new Insert( 'timeoff_requests' );
        $action->values( [
            'EMPLOYEE_NUMBER' => \Request\Helper\Format::rightPad( $post->request['forEmployee']['EMPLOYEE_NUMBER'] ),
            'REQUEST_STATUS' => $this->requestStatuses['pendingManagerApproval'],
            'CREATE_USER' => \Request\Helper\Format::rightPad( $post->request['byEmployee']['EMPLOYEE_NUMBER'] ),
            'REQUEST_REASON' => $post->request['reason'],
            'EMPLOYEE_DATA' => json_encode( $post->request['forEmployee'] )
        ] );
        $sql = new Sql( $this->adapter );
        $stmt = $sql->prepareStatementForSqlObject( $action );
        try {
            $result = $stmt->execute();
        } catch ( Exception $e ) {
            throw new \Exception( "Can't execute statement: " . $e->getMessage() );
        }

        $requestId = $result->getGeneratedValue();

        /** Insert record(s) into TIMEOFF_REQUEST_ENTRIES * */
        foreach ( $post->request['dates'] as $key => $request ) {
            $action = new Insert( 'timeoff_request_entries' );
            $action->values( [
                'REQUEST_ID' => $requestId,
                'REQUEST_DATE' => $request['date'],
                'REQUEST_DAY_OF_WEEK' => $request['day_of_week'],
                'REQUESTED_HOURS' => $request['hours'],
                'REQUEST_CODE' => $request['type']
            ] );
            $sql = new Sql( $this->adapter );
            $stmt = $sql->prepareStatementForSqlObject( $action );
            try {
                $result = $stmt->execute();
            } catch ( Exception $e ) {
                throw new \Exception( "Can't execute statement: " . $e->getMessage() );
            }
        }
        $requestReturnData['request_id'] = $requestId;

        return $requestReturnData;
    }

    /**
     * Returns the data associated with a single request for time off.
     * 
     * @param integer $requestId    Request ID
     * @return array
     */
    public function findRequest( $requestId ) {
        $sql = new Sql( $this->adapter );
        $select = $sql->select( [ 'request' => 'TIMEOFF_REQUESTS' ] )
                ->columns( [ 'REQUEST_ID' => 'REQUEST_ID', 'EMPLOYEE_NUMBER' => 'EMPLOYEE_NUMBER', 'REQUEST_STATUS' => 'REQUEST_STATUS', 'CREATE_USER' => 'CREATE_USER',
                    'REQUEST_REASON' => 'REQUEST_REASON', 'EMPLOYEE_DATA' => 'EMPLOYEE_DATA', 'CREATE_TIMESTAMP' => 'CREATE_TIMESTAMP' ] )
                ->join( [ 'employee' => 'PRPMS' ], 'employee.PREN = request.EMPLOYEE_NUMBER', [ 'LEVEL1' => 'PRL01', 'LEVEL2' => 'PRL02', 'LEVEL03' => 'PRL03', 'LEVEL4' => 'PRL04',
                    'POSITION' => 'PRPOS', 'EMAIL_ADDRESS' => 'PREML1',
                    'EMPLOYEE_HIRE_DATE' => 'PRDOHE', 'POSITION_TITLE' => 'PRTITL' ] )
                ->join( [ 'creator' => 'PRPMS' ], 'creator.PREN = request.CREATE_USER', [ 'CREATOR_POSITION' => 'PRPOS', 'CREATOR_EMAIL_ADDRESS' => 'PREML1', 'CREATOR_POSITION_TITLE' => 'PRTITL',
                    'CREATOR_LAST_NAME' => 'PRLNM', 'CREATOR_FIRST_NAME' => 'PRFNM' ] ) // employee.CREATOR_LAST_NAME CONCAT "," CONCAT employee.CREATOR_FIRST_NAME CONCAT " (" CONCAT employee.CREATE_USER CONCAT ") - " CONCAT employee.PRTITL
                ->join( [ 'status' => 'TIMEOFF_REQUEST_STATUSES' ], 'status.REQUEST_STATUS = request.REQUEST_STATUS', [ 'REQUEST_STATUS_DESCRIPTION' => 'DESCRIPTION' ] )
                ->join( [ 'schedule' => 'TIMEOFF_REQUEST_EMPLOYEE_SCHEDULES' ], 'schedule.EMPLOYEE_NUMBER = request.EMPLOYEE_NUMBER', [ 'SCHEDULE_MON' => 'SCHEDULE_MON', 'SCHEDULE_TUE' => 'SCHEDULE_TUE', 'SCHEDULE_WED' => 'SCHEDULE_WED',
                    'SCHEDULE_THU' => 'SCHEDULE_THU', 'SCHEDULE_FRI' => 'SCHEDULE_FRI', 'SCHEDULE_SAT' => 'SCHEDULE_SAT',
                    'SCHEDULE_SUN' => 'SCHEDULE_SUN' ] )
                ->where( [ 'request.REQUEST_ID' => $requestId ] );

        try {
            $request = \Request\Helper\ResultSetOutput::getResultRecord( $sql, $select );
        } catch ( Exception $e ) {
            var_dump( $e );
        }
        
        $request['EMPLOYEE_DATA'] = json_decode( $request['EMPLOYEE_DATA'] );
        $request['ENTRIES'] = $this->findRequestEntries( $requestId );
        $request['LOG_ENTRIES'] = $this->findRequestLogEntries( $requestId );
        $doh = new \DateTime( $request['EMPLOYEE_HIRE_DATE'] );
        $request['EMPLOYEE_HIRE_DATE'] = $doh->format( "m/d/Y" );
        
        $this->employeeData = \Request\Helper\Format::trimData( $request );
                
        return $request;
    }
    
    /**
     * Submits approval for Time Off request.
     * 
     * @param string $action
     * @param integer $requestId
     * @param string $reviewRequestReason
     * @param array $employeeData
     * @return array
     */
    public function submitApprovalResponse( $status = null, $requestId = null, $reviewRequestReason = null, $employeeData = null ) {
        $requestReturnData = ['request_id' => null ];
        $rawSql = "UPDATE timeoff_requests SET REQUEST_STATUS = '" . $status . "' WHERE REQUEST_ID = '" . $requestId . "'";
        $employeeData = \Request\Helper\ResultSetOutput::executeRawSql( $this->adapter, $rawSql );
        $requestReturnData['request_id'] = $requestId;

        return $requestReturnData;
    }

    /**
     * Returns the individual entries associated with a single request.
     * 
     * @param integer $requestId    Request ID
     * @return array
     */
    public function findRequestEntries( $requestId = null ) {
        $sql = new Sql( $this->adapter );
        $select = $sql->select( [ 'entry' => 'TIMEOFF_REQUEST_ENTRIES' ] )
                ->columns( [ 'REQUEST_DATE' => 'REQUEST_DATE', 'REQUEST_DAY_OF_WEEK' => 'REQUEST_DAY_OF_WEEK',
                             'REQUESTED_HOURS' => 'REQUESTED_HOURS', 'REQUEST_CODE' => 'REQUEST_CODE'
                           ] )
                ->join( [ 'code' => 'TIMEOFF_REQUEST_CODES' ], 'code.REQUEST_CODE = entry.REQUEST_CODE', [ 'DESCRIPTION' => 'DESCRIPTION' ] )
                ->where( [ ' entry.REQUEST_ID' => $requestId ] )
                ->order( ['entry.REQUEST_DATE ASC' ] );

        try {
            $entries = \Request\Helper\ResultSetOutput::getResultArray( $sql, $select );
        } catch ( Exception $e ) {
            var_dump( $e );
        }

        return $entries;
    }
    
    /**
     * Returns the log entries associated with a single request.
     * 
     * @param integer $requestId   Request ID
     * @return array
     */
    public function findRequestLogEntries( $requestId = null ) {
        $rawSql = "SELECT COMMENT, varchar_format (CREATE_TIMESTAMP, 'mm/dd/yyyy HH12:MI:SS PM') AS CREATE_TIMESTAMP FROM
                   TIMEOFF_REQUEST_LOG log WHERE log.REQUEST_ID = " . $requestId . " ORDER
                   BY log.CREATE_TIMESTAMP DESC";
        
        $logEntries = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSql );

        return $logEntries;
    }
    
    /**
     * Get count of Entries by Request ID.
     * 
     * @param array $data   $data = [ 'employeeData' => 'xxxxxxxxx' ];
     * @return integer
     */
    public function countTimeoffRequested( $requestId = null )
    {
        $rawSql = "SELECT SUM(REQUESTED_HOURS) AS TOTAL_REQUESTED_HOURS       
        FROM TIMEOFF_REQUEST_ENTRIES entry WHERE entry.REQUEST_ID = " . $requestId;
        
        $timeOffData = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql( $this->adapter, $rawSql );
        
        return $timeOffData['TOTAL_REQUESTED_HOURS'];
    }

    public function findRequestCalendarInviteData( $requestId = null ) {
        $sql = new Sql( $this->adapter );
        $select = $sql->select( ['entry' => 'TIMEOFF_REQUEST_ENTRIES' ] )
                ->columns( $this->timeOffRequestEntryColumns )
                ->join( ['request' => 'TIMEOFF_REQUESTS' ], 'request.REQUEST_ID = entry.REQUEST_ID', [ ] )
                ->join( ['code' => 'TIMEOFF_REQUEST_CODES' ], 'entry.REQUEST_CODE = code.REQUEST_CODE', ['DESCRIPTION' => 'DESCRIPTION' ] )
                ->where( ['request.REQUEST_ID' => $requestId ] )
                ->order( ['entry.REQUEST_DATE ASC' ] );
        $result = \Request\Helper\ResultSetOutput::getResultArray( $sql, $select );

        $sql = new Sql( $this->adapter );
        $select = $sql->select( ['employee' => 'PRPMS' ] )
                ->columns( $this->employeeColumns )
                ->join( ['request' => 'TIMEOFF_REQUESTS' ], 'trim(request.EMPLOYEE_NUMBER) = trim(employee.PREN)', [ ] )
                ->join( ['manager' => 'PRPSP' ], 'employee.PREN = manager.SPEN', [ ] )
                ->join( ['manager_addons' => 'PRPMS' ], 'manager_addons.PREN = manager.SPSPEN', $this->supervisorAddonColumns )
                ->where( ['request.REQUEST_ID' => $requestId ] );
        $result2 = \Request\Helper\ResultSetOutput::getResultRecord( $sql, $select );

        $datesRequested = [ ];
        if ( count( $result ) > 0 ) {
            $datesRequested[] = [ 'start' => $result[0]['REQUEST_DATE'],
                'end' => $result[0]['REQUEST_DATE'],
                'type' => $result[0]['DESCRIPTION'],
                'hours' => $result[0]['REQUESTED_HOURS']
            ];
        }
        $group = 0;

        for ( $ctr = 1; $ctr <= (count( $result ) - 1); $ctr++ ) {
            if ( $result[$ctr]['REQUEST_DATE'] !== $datesRequested[$group]['end'] &&
                    $result[$ctr]['REQUEST_DATE'] === date( "Y-m-d", strtotime( "+1 day", strtotime( $datesRequested[$group]['end'] ) ) ) &&
                    $result[$ctr]['DESCRIPTION'] === $datesRequested[$group]['type'] &&
                    $result[$ctr]['REQUESTED_HOURS'] === $datesRequested[$group]['hours']
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

        return [ 'datesRequested' => $datesRequested, 'for' => $result2 ];
    }
    
    /**
     * Get the one character abbreviated Status
     * 
     * @param string $shortname Camelcase queue name (i.e. pendingManagerApproval)
     * @return NULL|string
     */
    public function getRequestStatusCode( $shortname = null )
    {
        return ( array_key_exists( $shortname, $this->requestStatuses ) ? $this->requestStatuses[$shortname] : null );
    }

}