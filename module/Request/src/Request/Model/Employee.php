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

    public $employeeData = [ ];
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
     * Returns the count of items in Pending Manager Approval. May be filtered based on a search.
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
     * Returns the employee numbers that the passed in employee number can submit time off on their behalf.
     * 
     * @param type $employeeNumber
     * @return type
     */
    public function findProxiesByEmployeeNumber( $employeeNumber = null )
    {
        $rawSql = "SELECT TRIM(EMPLOYEE_NUMBER) AS EMPLOYEE_NUMBER
            FROM TIMEOFF_REQUEST_EMPLOYEE_PROXIES p
            WHERE
               TRIM(p.PROXY_EMPLOYEE_NUMBER) = " . $employeeNumber;
        
        $statement = $this->adapter->query( $rawSql );
        $result = $statement->execute();

        if ( $result instanceof ResultInterface && $result->isQueryResult() ) {
            $resultSet = new ResultSet;
            $resultSet->initialize( $result );
            $proxyData = $resultSet->toArray();
        } else {
            $proxyData = [ ];
        }
        
        return $proxyData;
    }

    /**
     * Get data for Datatables for the Pending Manager Approval queue.
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
     * Returns whether employee is a Manager or not.
     * 
     * @param type $employeeNumber  Integer, up to 9 places. Does not need to be justified.
     * @return boolean  "Y" or "N"
     */
    public function isManager( $employeeNumber = null ) {
        $rawSql = "select is_manager_mg('002', '" . $employeeNumber . "') AS IS_MANAGER FROM sysibm.sysdummy1";
        $isSupervisorData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSql );

        return $isSupervisorData[0]->IS_MANAGER;
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
         * If Level 2 = FIN (from file PPRMS, field PRL02) and
         *    Level 3 starts with PY (from file PRPMS, field PRL02) and
         *    Training group = MGR2 (from file PRPMS, field PRTGRP)
         */
        $rawSql = "SELECT
                   (CASE WHEN (
                      PRL02 = 'FIN' AND
                      SUBSTRING(PRL03,0,3) = 'PY' AND
                      PRTGRP = 'MGR2' AND
                      PRTEDH = 0
                   ) THEN 'Y' ELSE 'N' END) AS IS_PAYROLL_ADMIN
                   FROM PRPMS
                   WHERE TRIM(PRPMS.PREN) = '" . $employeeNumber . "'";
//die($rawSql);
        $data = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSql );
        
        return $data[0]->IS_PAYROLL_ADMIN;
    }
    
    /**
     * Returns whether employee is Payroll or not.
     * 
     * @param integer $employeeNumber   Integer, up to 9 places. Does not need to be justified.
     * @return boolean   "Y" or "N"
     */
    public function isPayrollAssistant( $employeeNumber = null ) {
        /**
         * 05/06/16 sawik TODO: Add query on new table to see if they were added as a Payroll Assistant
         */
        return "N";
    }

    /**
     * Adds a where clause to exclude employees in Level 2.
     * 
     * @return string
     */
    public function getExcludedLevel2() {
        $where = '';
        foreach ( $this->excludeLevel2 as $excluded ) {
            $where .= "employee.PRL02 <> '" . implode( ', ', $this->excludeLevel2 ) . "' and ";
        }
        return $where;
    }

    /**
     * Returns the requested hours per category for a request.
     * 
     * @param type $requestId
     * @return array
     */
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

    /**
     * Returns the Time Off and Employee Schedule for an employee.
     * 
     * @param integer $employeeNumber
     * @param string $includeUnapprovedRequests Default "Y" returns pending manager approval time as well.
     * @param string $includeOnlyFields Default "*" returns all fields from the function timeoff_get_employee_data.
     * @return array
     */
    public function findEmployeeTimeOffData( $employeeNumber = null, $includeUnapprovedRequests = "Y", $includeOnlyFields = "*" ) {
        $rawSql = "select data.*, sch.schedule_mon, sch.schedule_tue, sch.schedule_wed,
                   sch.schedule_thu, sch.schedule_fri, sch.schedule_sat, sch.schedule_sun
                   from table(timeoff_get_employee_data('002', '" . $employeeNumber . "', '" . $includeUnapprovedRequests . "')) as data
                   left join (select * from timeoff_request_employee_schedules sch where sch.employee_number = refactor_employee_id('" . $employeeNumber . "')) sch
                   on sch.employee_number = data.employee_number";

//        var_dump( $rawSql );
//        die();
        
        $statement = $this->adapter->query( $rawSql );
        $result = $statement->execute();

        if ( $result instanceof ResultInterface && $result->isQueryResult() ) {
            $resultSet = new ResultSet;
            $resultSet->initialize( $result );
            $this->employeeData = $resultSet->current();
        } else {
            $this->employeeData = [ ];
        }
        
//        \Request\Helper\Format::setFieldsAsFloat( [
//            'SCHEDULE_MON', 'SCHEDULE_TUE', 'SCHEDULE_WED', 'SCHEDULE_THU', 'SCHEDULE_FRI',
//            'SCHEDULE_SAT', 'SCHEDULE_SUN'
//        ] );
        $this->employeeData = \Request\Helper\Format::trimData( $this->employeeData );
//        $this->employeeData['SCHEDULE_MON'] = (float) $this->employeeData['SCHEDULE_MON'];
        
        
//        exit();
        

        return $this->employeeData;
    }
    
    public function findProxyEmployees( $managerEmployeeNumber = null, $search = null ) {
        $where = "WHERE (
            " . $this->getExcludedLevel2() . "
            employee.PRER = '002' AND employee.PRTEDH = 0 AND
            ( trim(employee.PREN) LIKE '%" . strtoupper( $search ) . "%' OR
              trim(employee.PRLNM) LIKE '%" . strtoupper( $search ) . "%' OR
              trim(employee.PRFNM) LIKE '%" . strtoupper( $search ) . "%'
            )
        )";
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

        $employeeData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSql );

        return $employeeData;
    }
    
    /**
     * Returns the Payroll Employee Search
     * 
     * @param type $where
     * @return type
     */
    public function getPayrollEmployeeSearchStatement( $where = null )
    {
        return "SELECT
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
    
    /**
     * Returns the Proxy Employee Search
     * 
     * @param type $where
     * @return type
     */
    public function getProxyEmployeeSearchStatement( $where = null, $proxyFor = [] )
    {
        foreach( $proxyFor as $key => $proxy ) {
            $proxyFor[$key] = "'" . $proxy . "'";
        }
        $proxyForPartial = implode(",", $proxyFor);

        $where .= " AND trim(employee.PREN) IN(" . $proxyForPartial . ")";
        
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
            '',
            '',
            trim(employee.PRPOS) AS POSITION,
            trim(employee.PREML1) AS EMAIL_ADDRESS,
            employee.PRDOHE AS EMPLOYEE_HIRE_DATE,
            trim(employee.PRTITL) AS POSITION_TITLE,
            '000' AS DIRECT_MANAGER_EMPLOYEE_NUMBER,
            'BLAH' AS DIRECT_MANAGER_NAME,
            manager_addons.PREML1 AS DIRECT_MANAGER_EMAIL_ADDRESS
        FROM PRPMS employee
        INNER JOIN PRPSP manager
              ON employee.PREN = manager.SPEN
        INNER JOIN PRPMS manager_addons
             ON manager_addons.PREN = manager.SPSPEN
        " . $where . "
        ORDER BY employee.PRLNM ASC, employee.PRFNM ASC";
        
        return $rawSql;
    }
    
    public function getManagerEmployeeSearchStatement( $where = null, $managerEmployeeNumber = null, $directReportFilter = null )
    {
        return "SELECT
                CASE
                    when trim(employee.PRCOMN) IS NOT NULL then trim(employee.PRLNM) || ', ' || trim(employee.PRCOMN)
                    else trim(employee.PRLNM) || ', ' || trim(employee.PRFNM)
                END AS EMPLOYEE_NAME,
                trim(employee.PREN) AS EMPLOYEE_NUMBER,
                employee.PRL01 AS LEVEL_1, employee.PRL02 AS LEVEL_2, employee.PRL03 AS LEVEL_3, employee.PRL04 AS LEVEL_4,
                hierarchy.DIRECT_INDIRECT, hierarchy.MANAGER_LEVEL, trim(employee.PRPOS) AS POSITION,
                trim(employee.PREML1) AS EMAIL_ADDRESS, employee.PRDOHE AS EMPLOYEE_HIRE_DATE,
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
    }

    /**
     * Find manager's employees
     * 
     * @param integer $managerEmployeeNumber
     * @param string $search
     * @param string $directReportFilter
     * @return \Zend\Db\ResultSet\ResultSet[]
     */
    public function findManagerEmployees( $managerEmployeeNumber = null, $search = null, $directReportFilter = null,
            $isProxy = null, $proxyFor = [] ) {        
        $isPayroll = \Login\Helper\UserSession::getUserSessionVariable( 'IS_PAYROLL_ADMIN' );
        $where = "WHERE (
            " . $this->getExcludedLevel2() . "
            employee.PRER = '002' AND employee.PRTEDH = 0 AND
            ( trim(employee.PREN) LIKE '%" . strtoupper( $search ) . "%' OR
              trim(employee.PRLNM) LIKE '%" . strtoupper( $search ) . "%' OR
              trim(employee.PRFNM) LIKE '%" . strtoupper( $search ) . "%'
            )
        )";
        
        if ( $isPayroll === "Y" ) {
            $rawSql = $this->getPayrollEmployeeSearchStatement( $where );
        } else {
            switch( $directReportFilter ) {
                case 'P':
                    $rawSql = $this->getProxyEmployeeSearchStatement( $where, $proxyFor );
                    break;
                
                case 'B':
                case 'D':
                case 'I':
                    $rawSql = $this->getManagerEmployeeSearchStatement( $where, $managerEmployeeNumber, $directReportFilter );
                    break;
            }
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
     * Returns schedule for an employee.
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

    /**
     * Establishes a default Employee Schedule for a given employee.
     * 
     * @param type $employeeNumber
     * @return boolean
     * @throws \Exception
     */
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
        if ( $request['REQUEST_TYPE'] === 'Civic Duty' ) {
            $request['REQUEST_TYPE'] = 'CivicDuty';
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
