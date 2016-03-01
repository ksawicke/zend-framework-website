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
class TimeoffRequests extends BaseDB {
    
    public $employeeColumns;
    
    public $supervisorAddonColumns;
    
    public $requesterAddonColumns;
    
    public $requestStatuses;
    
    public $requestStatusText;
    
    public $timeoffRequestColumns;
    
    public $timeoffRequestEntryColumns;
    
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
        
        $this->requestStatuses = [
            'draft' => 'D',
            'approved' => 'A',
            'cancelled' => 'C',
            'pendingApproval' => 'P',
            'beingReviewed' => 'R'
        ];
        $this->requestStatusText = [
            'D' => 'draft',
            'A' => 'approved',
            'C' => 'cancelled',
            'P' => 'pendingApproval',
            'R' => 'beingReviewed'
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
        
        $this->codesToKronos = [
            'P' => 'PTO',
            'R' => 'GFVAC',
            'B' => 'BR',
            'K' => 'FHP',
            'S' => 'SK',
            'V' => 'VA'
        ];
    }

    public function findRequest( $requestId ) {
        $sql = new Sql( $this->adapter );
        $select = $sql->select( [ 'request' => 'TIMEOFF_REQUESTS' ] )
                ->columns( [ 'REQUEST_ID' => 'REQUEST_ID', 'EMPLOYEE_NUMBER' => 'EMPLOYEE_NUMBER', 'REQUEST_STATUS' => 'REQUEST_STATUS', 'CREATE_USER' => 'CREATE_USER',
                    'REQUEST_REASON' => 'REQUEST_REASON', 'EMPLOYEE_DATA' => 'EMPLOYEE_DATA', 'CREATE_TIMESTAMP' => 'CREATE_TIMESTAMP' ] )
                ->join( [ 'employee' => 'PRPMS' ], 'employee.PREN = request.EMPLOYEE_NUMBER', [ 'LEVEL1' => 'PRL01', 'LEVEL2' => 'PRL02', 'LEVEL03' => 'PRL03', 'LEVEL4' => 'PRL04',
                    'POSITION' => 'PRPOS', 'EMAIL_ADDRESS' => 'PREML1',
                    'EMPLOYEE_HIRE_DATE' => 'PRDOHE', 'POSITION_TITLE' => 'PRTITL' ] )
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

//        echo '<pre>';
//        print_r( $request );
//        echo '</pre>';
//        die(".");

        $request['EMPLOYEE_DATA'] = json_decode( $request['EMPLOYEE_DATA'] );
        $request['ENTRIES'] = $this->findRequestEntries( $requestId );

        return $request;
    }

    public function findRequestEntries( $requestId = null ) {
        $sql = new Sql( $this->adapter );
        $select = $sql->select( [ 'entry' => 'TIMEOFF_REQUEST_ENTRIES' ] )
                ->columns( [ 'REQUEST_DATE' => 'REQUEST_DATE', 'REQUESTED_HOURS' => 'REQUESTED_HOURS', 'REQUEST_CODE' => 'REQUEST_CODE' ] )
                ->join( [ 'code' => 'TIMEOFF_REQUEST_CODES' ], 'code.REQUEST_CODE = entry.REQUEST_CODE', [ 'DESCRIPTION' => 'DESCRIPTION' ] )
                ->where( [ ' entry.REQUEST_ID' => $requestId ] );

        try {
            $entries = \Request\Helper\ResultSetOutput::getResultArray( $sql, $select );
        } catch ( Exception $e ) {
            var_dump( $e );
        }

        return $entries;
    }

    public function findRequestCalendarInviteData( $requestId = null ) {
        $sql = new Sql( $this->adapter );
        $select = $sql->select( ['entry' => 'TIMEOFF_REQUEST_ENTRIES' ] )
                ->columns( $this->timeoffRequestEntryColumns )
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

}
