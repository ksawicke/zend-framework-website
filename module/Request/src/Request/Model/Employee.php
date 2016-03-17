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
class Employee extends BaseDB {

    /**
     * @var array
     */
    public $employeeData = [ ]; //['BEREAVEMENT_PENDING' => '.00',
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
    public $excludeLevel2 = [ ];
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

    public function __construct() {
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
        $this->excludeLevel2 = ['DRV' ];
    }
    
    /**
     * Get count of Manager Queue data
     * 
     * @param array $data   $data = [ 'employeeData' => 'xxxxxxxxx' ];
     * @return int
     */
    public function countManagerQueueItems( $data = null, $isFiltered = false )
    {
        $rawSql = "SELECT COUNT(*) AS RCOUNT       
        FROM TIMEOFF_REQUESTS request
        INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
        INNER JOIN PRPSP manager ON employee.PREN = manager.SPEN
        INNER JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN
        INNER JOIN table (
            SELECT
                EMPLOYEE_ID AS EMPLOYEE_NUMBER,
                TRIM(DIRECT_MANAGER_EMPLOYEE_ID) AS DIRECT_MANAGER_EMPLOYEE_NUMBER,
                DIRECT_INDIRECT,
                MANAGER_LEVEL
            FROM table (
                CARE_GET_MANAGER_EMPLOYEES('002', '" . $data['employeeNumber'] . "', 'D')
            ) as data
        ) hierarchy
            ON hierarchy.EMPLOYEE_NUMBER = employee.PREN";
        
        $where = [];
        $where[] = "request.REQUEST_STATUS = 'P'";
            
        if( $isFiltered ) {
            if( array_key_exists( 'search', $data ) && !empty( $data['search']['value'] ) ) {
                $where[] = "( employee.PREN LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
                              employee.PRFNM LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
                              employee.PRLNM LIKE '%" . strtoupper( $data['search']['value'] ) . "%' 
                            )";
            }
        }
        $rawSql .=  " WHERE " . implode( " AND ", $where );
        
        $employeeData = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql( $this->adapter, $rawSql );

        return (int) $employeeData['RCOUNT'];
    }

    /**
     * Get Manager Queue data to display in data table.
     * 
     * @param array $data   $data = [ 'employeeData' => 'xxxxxxxxx' ];
     * @return array
     */
    public function getManagerQueue( $data = null ) {
        $rawSql = "
        SELECT DATA2.* FROM (
            SELECT
                ROW_NUMBER () OVER (ORDER BY MIN_DATE_REQUESTED ASC, EMPLOYEE_LAST_NAME ASC) AS ROW_NUMBER,
                DATA.* FROM (
                SELECT
                request.REQUEST_ID AS REQUEST_ID,
                TRIM(request.EMPLOYEE_NUMBER) AS EMPLOYEE_NUMBER,
                request.REQUEST_REASON AS REQUEST_REASON,
                status.DESCRIPTION AS REQUEST_STATUS_DESCRIPTION,
                (
                    SELECT SUM(requested_hours) FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id
                ) AS REQUESTED_HOURS,
                (
                    SELECT MIN(REQUEST_DATE) FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id
                ) AS MIN_DATE_REQUESTED,
                (
                    SELECT MAX(REQUEST_DATE) FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id
                ) AS MAX_DATE_REQUESTED,

                TRIM(employee.PRLNM) CONCAT ', ' CONCAT TRIM(employee.PRCOMN) CONCAT ' (' CONCAT TRIM(employee.PREN) CONCAT ')' as EMPLOYEE_DESCRIPTION,
                TRIM(employee.PRFNM) AS EMPLOYEE_FIRST_NAME,
                TRIM(employee.PRLNM) AS EMPLOYEE_LAST_NAME,
		TRIM(manager_addons.PRLNM) CONCAT ', ' CONCAT TRIM(manager_addons.PRFNM) CONCAT ' (' CONCAT TRIM(manager_addons.PREN) CONCAT ')' as APPROVER_QUEUE,
                TRIM(manager_addons.PREML1) AS MANAGER_EMAIL_ADDRESS
            FROM TIMEOFF_REQUESTS request
            INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
            INNER JOIN PRPSP manager ON employee.PREN = manager.SPEN
            INNER JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN
            INNER JOIN table (
                SELECT
                    EMPLOYEE_ID AS EMPLOYEE_NUMBER,
                    TRIM(DIRECT_MANAGER_EMPLOYEE_ID) AS DIRECT_MANAGER_EMPLOYEE_NUMBER,
                    DIRECT_INDIRECT,
                    MANAGER_LEVEL
                FROM table (
                    CARE_GET_MANAGER_EMPLOYEES('002', '" . $data['employeeNumber'] . "', 'D')
                ) as data
            ) hierarchy
                ON hierarchy.EMPLOYEE_NUMBER = employee.PREN
            INNER JOIN TIMEOFF_REQUEST_STATUSES status ON status.REQUEST_STATUS = request.REQUEST_STATUS
            WHERE request.REQUEST_STATUS = 'P'
            ORDER BY MIN_DATE_REQUESTED ASC, EMPLOYEE_LAST_NAME ASC) AS DATA
        ) AS DATA2";

        $columns = [ "EMPLOYEE_DESCRIPTION",
                     "APPROVER_QUEUE",
                     "REQUEST_STATUS_DESCRIPTION",
                     "REQUESTED_HOURS",
                     "REQUEST_REASON",
                     "MIN_DATE_REQUESTED",
                     "ACTIONS"
                   ];
        
        $where = [];
        if( array_key_exists( 'search', $data ) && !empty( $data['search']['value'] ) ) {
            $where[] = "( EMPLOYEE_NUMBER LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
                          EMPLOYEE_FIRST_NAME LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
                          EMPLOYEE_LAST_NAME LIKE '%" . strtoupper( $data['search']['value'] ) . "%' 
                        )";
        }
        if( $data !== null ) {
            $where[] = "ROW_NUMBER BETWEEN " . ( $data['start'] + 1 ) . " AND " . ( $data['start'] + $data['length'] );
        }
        
        $rawSql .=  " WHERE " . implode( " AND ", $where );
        
        $employeeData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSql );

        return $employeeData;
    }
    
    /**
     * SELECT * FROM ( SELECT "HOTEL_MANAGER_GUESTS".*, "HOTEL_MANAGER_ROOMS"."ROOM_NUMBER" AS "ROOM_NUMBER", "HOTEL_MANAGER_HOTELS"."HOTEL_SHORT_NAME" AS "HOTEL_SHORT_NAME", ROW_NUMBER() OVER (ORDER BY "GUEST_LAST_NAME" ASC, "GUEST_FIRST_NAME" ASC) AS ZEND_DB_ROWNUM FROM "HOTEL_MANAGER_GUESTS" INNER JOIN "HOTEL_MANAGER_ROOMS"  ON "HOTEL_MANAGER_ROOMS"."IDENTITY_ID" = "GUEST_HOTEL_ROOM" INNER JOIN "HOTEL_MANAGER_HOTELS"  ON "HOTEL_MANAGER_HOTELS"."IDENTITY_ID" = "HOTEL_IDENTITY_ID" ) AS ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION WHERE ZEND_IBMDB2_SERVER_LIMIT_OFFSET_EMULATION.ZEND_DB_ROWNUM BETWEEN ? AND ?
     */
    
    // @see: http://gitlab.swift.com/HotelManager/HotelManager/blob/guido_dev_2/module/Application/src/Application/Model/HotelTable.php
    /***public function findQueuesByManager( $managerEmployeeNumber = null, $limit = 1, $offset = 10 ) {
        $rawSql = "SELECT DATA2.* FROM (
            SELECT
            ROW_NUMBER () OVER (ORDER BY MIN_REQUEST_DATE ASC, EMPLOYEE_LAST_NAME ASC) AS ROWNUM,
            DATA.* FROM (
            SELECT
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
        INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
        INNER JOIN PRPSP manager ON employee.PREN = manager.SPEN
        INNER JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN
        INNER JOIN table (
            SELECT
                EMPLOYEE_ID AS EMPLOYEE_NUMBER,
                TRIM(DIRECT_MANAGER_EMPLOYEE_ID) AS DIRECT_MANAGER_EMPLOYEE_NUMBER,
                DIRECT_INDIRECT,
                MANAGER_LEVEL
            FROM table (
                CARE_GET_MANAGER_EMPLOYEES('002', '" . $managerEmployeeNumber . "', 'D')
            ) as data
        ) hierarchy
            ON hierarchy.EMPLOYEE_NUMBER = employee.PREN
        WHERE request.REQUEST_STATUS = 'P'
        ORDER BY MIN_REQUEST_DATE ASC, EMPLOYEE_LAST_NAME ASC) AS DATA
        ) AS DATA2
        WHERE ROWNUM BETWEEN " . $limit . " AND " . ($limit + $offset - 1);

        $employeeData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSql );

        return $employeeData;
    }***/
    
//    public function buildQueueWhereClause( $data )
//    {
//        $where = [];
//        if( array_key_exists( 'search', $data ) && !empty( $data['search']['value'] ) ) {
//            $where[] = "( TRIM(request.EMPLOYEE_NUMBER) LIKE '" . $data['search']['value'] . "%' OR
//                          TRIM(employee.PRFNM) LIKE '" . $data['search']['value'] . "%' OR
//                          TRIM(employee.PRLNM) LIKE '" . $data['search']['value'] . "%' 
//                        )";
//        }
//        if( $data !== null ) {
//            $where[] = "ROW_NUMBER BETWEEN " . ( $data['start'] + 1 ) . " AND " . ( $data['start'] + $data['length'] );
//        }
//        
//        return " WHERE " . implode( " AND ", $where );
//    }

    public function isManager( $employeeNumber = null ) {
        $rawSql = "select is_manager_mg('002', '" . $employeeNumber . "') AS IS_MANAGER FROM sysibm.sysdummy1";
        $isSupervisorData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSql );

        return $isSupervisorData[0]->IS_MANAGER;
    }

    public function isPayroll( $employeeNumber = null ) {
        $rawSql = "SELECT
            (CASE WHEN (SUBSTRING(PRL03,0,3) = 'PY' AND PRTEDH = 0) THEN 'Y' ELSE 'N' END) AS IS_PAYROLL
            FROM PRPMS
            WHERE TRIM(PRPMS.PREN) = '" . $employeeNumber . "'";

        $isSupervisorData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSql );
        
        return $isSupervisorData[0]->IS_PAYROLL;
    }

    public function getExcludedLevel2() {
        $where = '';
        foreach ( $this->excludeLevel2 as $excluded ) {
            $where .= "employee.PRL02 <> '" . implode( ', ', $this->excludeLevel2 ) . "' and ";
        }
        return $where;
    }

    public function checkHoursRequestedPerCategory( $requestId = null ) {
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

        $requestData = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql( $this->adapter, $rawSql );

        return $requestData;
    }

    public function findEmployeeTimeOffData( $employeeNumber = null, $includeHourTotals = "Y", $includeOnlyFields = "*" ) {
        $rawSql = "select data.*, sch.schedule_mon, sch.schedule_tue, sch.schedule_wed,
                   sch.schedule_thu, sch.schedule_fri, sch.schedule_sat, sch.schedule_sun
                   from table(timeoff_get_employee_data('002', '" . $employeeNumber . "', '" . $includeHourTotals . "')) as data
                   left join (select * from timeoff_request_employee_schedules sch where sch.employee_number = refactor_employee_id('" . $employeeNumber . "')) sch
                   on sch.employee_number = data.employee_number";
        
//        echo '<pre>';
//        print_r( $rawSql );
//        echo '</pre>';
//        die("?");
        
        $statement = $this->adapter->query( $rawSql );
        $result = $statement->execute();

        if ( $result instanceof ResultInterface && $result->isQueryResult() ) {
            $resultSet = new ResultSet;
            $resultSet->initialize( $result );
            $this->employeeData = $resultSet->current();
        } else {
            $this->employeeData = [ ];
        }
        
        $this->employeeData = \Request\Helper\Format::trimData( $this->employeeData );

        return $this->employeeData;
    }

    public function submitApprovalResponse( $action = null, $requestId = null, $reviewRequestReason = null, $employeeData = null ) {
        $requestReturnData = ['request_id' => null ];
        $rawSql = "UPDATE timeoff_requests SET REQUEST_STATUS = '" . $action . "' WHERE REQUEST_ID = '" . $requestId . "'";
        $employeeData = \Request\Helper\ResultSetOutput::executeRawSql( $this->adapter, $rawSql );
        $requestReturnData['request_id'] = $requestId;

        return $requestReturnData;
    }

    public function findManagerEmployees( $managerEmployeeNumber = null, $search = null, $directReportFilter = null ) {
        $isPayroll = \Login\Helper\UserSession::getUserSessionVariable( 'IS_PAYROLL' );
        $where = "WHERE (
            " . $this->getExcludedLevel2() . "
            employee.PRER = '002' AND employee.PRTEDH = 0 AND
            ( trim(employee.PREN) LIKE '%" . strtoupper( $search ) . "%' OR
              trim(employee.PRLNM) LIKE '%" . strtoupper( $search ) . "%' OR
              trim(employee.PRFNM) LIKE '%" . strtoupper( $search ) . "%'
            )
        )";

        if ( $isPayroll === "N" ) {
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

        $employeeData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSql );

        return $employeeData;
    }
    
    /**
     * Ensures the employee has a schedule saved.
     * 
     * @param type $employeeNumber
     */
    public function ensureEmployeeScheduleIsDefined( $employeeNumber = null ) {
        if( $this->findEmployeeSchedule( $employeeNumber )===false ) {
            $this->makeDefaultEmployeeSchedule( $employeeNumber );
        }
    }

    /**
     * Finds the employee schedule.
     * 
     * @param type $employeeNumber
     * @return type
     */
    public function findEmployeeSchedule( $employeeNumber = null ) {
        $sql = new Sql( $this->adapter );
        $select = $sql->select( ['schedule' => 'TIMEOFF_REQUEST_EMPLOYEE_SCHEDULES' ] )
                ->columns( ['SCHEDULE_MON' => 'SCHEDULE_MON',
                    'SCHEDULE_TUE' => 'SCHEDULE_TUE',
                    'SCHEDULE_WED' => 'SCHEDULE_WED',
                    'SCHEDULE_THU' => 'SCHEDULE_THU',
                    'SCHEDULE_FRI' => 'SCHEDULE_FRI',
                    'SCHEDULE_SAT' => 'SCHEDULE_SAT',
                    'SCHEDULE_SUN' => 'SCHEDULE_SUN'
                ] )
                ->where( ['schedule.EMPLOYEE_NUMBER' => \Request\Helper\Format::rightPad( $employeeNumber ) ] );

        $scheduleData = \Request\Helper\ResultSetOutput::getResultRecord( $sql, $select );
        
        return $scheduleData;
    }

    public function makeDefaultEmployeeSchedule( $employeeNumber = null ) {
        $action = new Insert( 'timeoff_request_employee_schedules' );
        $action->values( [
            'EMPLOYEE_NUMBER' => \Request\Helper\Format::rightPad( $employeeNumber ),
            'SCHEDULE_MON' => '8.00',
            'SCHEDULE_TUE' => '8.00',
            'SCHEDULE_WED' => '8.00',
            'SCHEDULE_THU' => '8.00',
            'SCHEDULE_FRI' => '8.00',
            'SCHEDULE_SAT' => '0.00',
            'SCHEDULE_SUN' => '0.00'
        ] );
        $sql = new Sql( $this->adapter );
        $stmt = $sql->prepareStatementForSqlObject( $action );
        try {
            $result = $stmt->execute();
        } catch ( Exception $e ) {
            throw new \Exception( "Can't execute statement: " . $e->getMessage() );
        }

        if ( $result ) {
            return true;
        }
        return false;
    }

    public function appendAllRequestsJsonArray( $request ) {
        return ['date' => date( "m/d/Y", strtotime( $request['REQUEST_DATE'] ) ),
            'dateYmd' => date( "Y-m-d", strtotime( $request['REQUEST_DATE'] ) ),
            'hours' => $request['REQUESTED_HOURS'],
            'category' => self::$categoryToClass[$request['REQUEST_TYPE']],
            'status' => $request['REQUEST_STATUS']
        ];
    }

    public function appendRequestJsonArray( $request ) {
        return ['REQUEST_DATE' => date( "m/d/Y", strtotime( $request['REQUEST_DATE'] ) ),
            'REQUESTED_HOURS' => $request['REQUESTED_HOURS'],
            'REQUEST_TYPE' => self::$categoryToClass[$request['REQUEST_TYPE']]
        ];
    }

    public function adjustRequestType( $request ) {
        if ( $request['REQUEST_TYPE'] === 'Unexcused' ) {
            $request['REQUEST_TYPE'] = 'UnexcusedAbsence';
        }
        if ( $request['REQUEST_TYPE'] === 'Time Off Without Pay' ) {
            $request['REQUEST_TYPE'] = 'ApprovedNoPay';
        }
        return $request;
    }

    public function findTimeOffRequestData( $employeeNumber = null, $calendarDates = null ) {
        $approvedRequestsJson = [ ];
        $pendingRequestsJson = [ ];
        $allRequestsJson = [ ];

//        var_dump($calendarDates);exit();

        $approvedRequestsData = $this->findTimeOffRequestsByEmployeeAndStatus( $employeeNumber, "A", $calendarDates['currentMonth']->format( 'Y-m-d' ), $calendarDates['threeMonthsOut']->format( 'Y-m-t' ) );
        $pendingRequestsData = $this->findTimeOffRequestsByEmployeeAndStatus( $employeeNumber, "P", $calendarDates['currentMonth']->format( 'Y-m-d' ), $calendarDates['threeMonthsOut']->format( 'Y-m-t' ) );

        foreach ( $approvedRequestsData as $key => $approvedRequest ) {
            $approvedRequest = $this->adjustRequestType( $approvedRequest );
            $allRequestsJson[] = $this->appendAllRequestsJsonArray( $approvedRequest );
            $approvedRequestsJson[] = $this->appendRequestJsonArray( $approvedRequest );
//            if($approvedRequest['REQUEST_DATE'] > $calendarDates['currentMonth']->format('Y-m-d') and $approvedRequest['REQUEST_DATE'] < $calendarDates['threeMonthsOut']->format('Y-m-t')) {
//                $approvedRequestsJson[] = $this->appendAllRequestsJsonArray($approvedRequest);
//            }
        }
        foreach ( $pendingRequestsData as $key => $pendingRequest ) {
            $pendingRequest = $this->adjustRequestType( $pendingRequest );
            $allRequestsJson[] = $this->appendAllRequestsJsonArray( $pendingRequest );
            $pendingRequestsJson[] = $this->appendRequestJsonArray( $pendingRequest );
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
    public function findTimeOffCalendarByEmployeeNumber( $employeeNumber = null, $startDate = null, $endDate = null ) {
        $startDate = new \Datetime( $startDate );
        $startDate = $startDate->format( "Y-m-d" );
        $endDate = new \Datetime( $endDate );
        $endDate = $endDate->format( "Y-m-d" );
        
        $rawSql = "SELECT entry.ENTRY_ID, entry.REQUEST_DATE, entry.REQUESTED_HOURS, requestcode.CALENDAR_DAY_CLASS, request.REQUEST_STATUS
            FROM TIMEOFF_REQUEST_ENTRIES entry
            INNER JOIN TIMEOFF_REQUESTS AS request ON request.REQUEST_ID = entry.REQUEST_ID
            INNER JOIN TIMEOFF_REQUEST_CODES AS requestcode ON requestcode.REQUEST_CODE = entry.REQUEST_CODE
            INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
            WHERE
               request.REQUEST_STATUS IN('A', 'P') AND
               trim(employee.PREN) = '" . $employeeNumber . "' AND
               entry.REQUEST_DATE BETWEEN '" . $startDate . "' AND '" . $endDate . "'
            ORDER BY REQUEST_DATE ASC";
//        die( $rawSql );
        $statement = $this->adapter->query( $rawSql );
        $result = $statement->execute();

        if ( $result instanceof ResultInterface && $result->isQueryResult() ) {
            $resultSet = new ResultSet;
            $resultSet->initialize( $result );
            $calendarData = $resultSet->toArray();
        } else {
            $calendarData = [ ];
        }

        return $calendarData; //\Request\Helper\Format::trimData( $calendarData );
    }

    public function findTimeOffRequestsByEmployeeAndStatus( $employeeNumber = null, $status = "A", $startDate = null, $endDate = null ) {
        $sql = new Sql( $this->adapter );
        if ( $startDate == null || $endDate == null ) {
            $select = $sql->select( ['entry' => 'TIMEOFF_REQUEST_ENTRIES' ] )
                    ->columns( ['EMPLOYEE_NUMBER' => 'PREN' ] ) // $this->timeoffRequestEntryColumns
                    ->join( ['request' => 'TIMEOFF_REQUESTS' ], 'request.REQUEST_ID = entry.REQUEST_ID', $this->timeoffRequestColumns )
                    ->join( ['requestcode' => 'TIMEOFF_REQUEST_CODES' ], 'requestcode.REQUEST_CODE = entry.REQUEST_CODE', $this->timeoffRequestCodeColumns )
                    ->where( ['trim(request.EMPLOYEE_NUMBER)' => trim( $employeeNumber ),
                        'request.REQUEST_STATUS' => $status
                    ] )
                    ->order( ['entry.REQUEST_DATE ASC' ] );
        } else {
            $select = $sql->select( ['entry' => 'TIMEOFF_REQUEST_ENTRIES' ] )
                    ->columns( $this->timeoffRequestEntryColumns )
                    ->join( ['request' => 'TIMEOFF_REQUESTS' ], 'request.REQUEST_ID = entry.REQUEST_ID', $this->timeoffRequestColumns )
                    ->join( ['requestcode' => 'TIMEOFF_REQUEST_CODES' ], 'requestcode.REQUEST_CODE = entry.REQUEST_CODE', $this->timeoffRequestCodeColumns )
                    ->where( ['trim(request.EMPLOYEE_NUMBER)' => trim( $employeeNumber ),
                        'request.REQUEST_STATUS' => $status,
                        'entry.REQUEST_DATE >= ?' => $startDate,
                        'entry.REQUEST_DATE < ?' => $endDate
                    ] )
                    ->order( ['entry.REQUEST_DATE ASC' ] );
        }

        try {
            $statement = $sql->prepareStatementForSqlObject( $select );
        } catch ( Exception $e ) {
            var_dump( $e );
        }

        $result = $statement->execute();

        $resultSet = new ResultSet();
        $resultSet->initialize( $result );

        $array = [ ];
        foreach ( $resultSet as $row ) {
            $array[] = $row;
        }

        return $array;
    }

    public function submitRequestForApproval( $employeeNumber = null, $requestData = [ ], $requestReason = null, $requesterEmployeeNumber = null, $employeeData = null ) {
        $requestReturnData = ['request_id' => null ];

        /** Insert record into TIMEOFF_REQUESTS * */
        $action = new Insert( 'timeoff_requests' );
        $action->values( [
            'EMPLOYEE_NUMBER' => \Request\Helper\Format::rightPad( $employeeNumber ),
            'REQUEST_STATUS' => self::$requestStatuses['pendingApproval'],
            'CREATE_USER' => \Request\Helper\Format::rightPad( $requesterEmployeeNumber ),
            'REQUEST_REASON' => $requestReason,
            'EMPLOYEE_DATA' => $employeeData
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
        foreach ( $requestData as $key => $request ) {
            $action = new Insert( 'timeoff_request_entries' );
            $action->values( [
                'REQUEST_ID' => $requestId,
                'REQUEST_DATE' => $request['date'],
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

    public function logEntry( $requestId = null, $employeeNumber = null, $comment = null ) {
        $logEntry = new Insert( 'timeoff_request_log' );
        $logEntry->values( [
            'REQUEST_ID' => $requestId,
            'EMPLOYEE_NUMBER' => \Request\Helper\Format::rightPadEmployeeNumber( $employeeNumber ),
            'COMMENT' => $comment
        ] );
        $sql = new Sql( $this->adapter );
        $stmt = $sql->prepareStatementForSqlObject( $logEntry );
        try {
            $result = $stmt->execute();
        } catch ( Exception $e ) {
            throw new \Exception( "Can't execute statement: " . $e->getMessage() );
        }
    }

    public function trimData( $object ) {
        array_walk_recursive( $object, function( &$value, $key ) {
            /**
             * Value is of type string
             */
            if ( is_string( $value ) ) {
                $value = trim( $value );
            }
        } );

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
    /**
     * Do a subquery
     * 
     * /**
     * instantiate new SQL adapter
     */
//        $sql = new Sql($this->adapter);

    /**
     * prepare new SQL Select
     */
//        $select = $sql->select();

    /**
     * define sql FROM
     */
//        $select->from('HOTEL_MANAGER_ROOMS');

    /**
     * create sub-query
     */
//        $subQry = $sql->select()
//                      ->from('HOTEL_MANAGER_GUESTS')
//                      ->columns(array('OCCUPIED_BEDS' => new \Zend\Db\Sql\Expression('COUNT(*)')))
//                      ->where('GUEST_HOTEL_ROOM = HOTEL_MANAGER_ROOMS.IDENTITY_ID AND');
//        $select->columns(array('IDENTITY_ID', 'ROOM_NUMBER', 'ROOM_CAPACITY', 'ROOMS_OCCUPIED' => new \Zend\Db\Sql\Expression('?', array($subQry))));
}
