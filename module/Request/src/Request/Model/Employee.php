<?php

namespace Request\Model;

use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Request\Model\BaseDB;
use Zend\Db\Sql\Where;
use Login\Helper\UserSession;

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
        JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
        JOIN PRPMS manager_addons ON hierarchy.DIRECT_MANAGER_EMPLOYEE_NUMBER = trim(manager_addons.PREN)
        JOIN table (
            SELECT
                TRIM(EMPLOYEE_ID) AS EMPLOYEE_NUMBER,
                TRIM(DIRECT_MANAGER_EMPLOYEE_ID) AS DIRECT_MANAGER_EMPLOYEE_NUMBER,
                DIRECT_INDIRECT,
                MANAGER_LEVEL
            FROM table (
                GET_MANAGER_EMPLOYEES('002', '" . $data['employeeNumber'] . "', 'D')
            ) as data
        ) hierarchy
            ON hierarchy.EMPLOYEE_NUMBER = employee.PREN";

        $where = [];
        $where[] = "request.REQUEST_STATUS = 'P'";

        if( $isFiltered ) {
            if( array_key_exists( 'search', $data ) && !empty( $data['search']['value'] ) ) {
                $where[] = "( employee.PREN LIKE '" . strtoupper( $data['search']['value'] ) . "%' OR
                              employee.PRFNM LIKE '" . strtoupper( $data['search']['value'] ) . "%' OR
                              employee.PRLNM LIKE '" . strtoupper( $data['search']['value'] ) . "%'
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
        $rawSql = "SELECT TRIM(EMPLOYEE_NUMBER) AS EMPLOYEE_NUMBER, GET_EMPLOYEE_COMMON_NAME('002', EMPLOYEE_NUMBER) AS EMPLOYEE_NAME
            FROM TIMEOFF_REQUEST_EMPLOYEE_PROXIES p
            WHERE p.STATUS = '1' AND
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
            JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
            JOIN PRPMS manager_addons ON hierarchy.DIRECT_MANAGER_EMPLOYEE_NUMBER = trim(manager_addons.PREN)
            JOIN table (
                SELECT
                    TRIM(EMPLOYEE_ID) AS EMPLOYEE_NUMBER,
                    TRIM(DIRECT_MANAGER_EMPLOYEE_ID) AS DIRECT_MANAGER_EMPLOYEE_NUMBER,
                    DIRECT_INDIRECT,
                    MANAGER_LEVEL
                FROM table (
                    GET_MANAGER_EMPLOYEES('002', '" . $data['employeeNumber'] . "', 'D')
                ) as data
            ) hierarchy
                ON hierarchy.EMPLOYEE_NUMBER = employee.PREN
            JOIN TIMEOFF_REQUEST_STATUSES status ON status.REQUEST_STATUS = request.REQUEST_STATUS
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
            $where[] = "( EMPLOYEE_NUMBER LIKE '" . strtoupper( $data['search']['value'] ) . "%' OR
                          EMPLOYEE_FIRST_NAME LIKE '" . strtoupper( $data['search']['value'] ) . "%' OR
                          EMPLOYEE_LAST_NAME LIKE '" . strtoupper( $data['search']['value'] ) . "%'
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
     * Gets the start date for a request so we can determine which calendar
     * to use first for a request.
     *
     * @param integer $requestId
     * @return array
     */
    public function getStartDateDataFromRequest( $requestId = null ) {
        $rawSql = "SELECT
                        MIN(REQUEST_DATE) AS MIN_REQUEST_DATE,
                        CHAR(MIN(REQUEST_DATE),USA) AS USA_DATE_FORMAT,
                        MONTH(MIN(REQUEST_DATE)) AS START_MONTH,
                        YEAR(MIN(REQUEST_DATE)) AS START_YEAR
                   FROM timeoff_request_entries entry WHERE entry.request_id = " . $requestId;

        $employeeData = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql( $this->adapter, $rawSql );

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
        $isManagerData = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql( $this->adapter, $rawSql );

        return $isManagerData->IS_MANAGER;
    }

    /**
     * Returns whether employee is a Supervisor or not.
     *
     * @param type $employeeNumber  Integer, up to 9 places. Does not need to be justified.
     * @return type boolean  "Y" or "N"
     */
    public function isSupervisor( $employeeNumber = null ) {
        $rawSql = "select is_supervisor('002', '" . $employeeNumber . "') AS IS_SUPERVISOR FROM sysibm.sysdummy1";
        $isSupervisorData = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql( $this->adapter, $rawSql );

        return $isSupervisorData->IS_SUPERVISOR;
    }

    /**
     * Returns whether employee is Payroll Admin OR Assistant.
     *
     * @param integer $employeeNumber   Integer, up to 9 places. Does not need to be justified.
     * @return boolean   "Y" or "N"
     */
    public function isPayroll( $employeeNumber = null ) {
        return ( ( $this->isPayrollAdmin( $employeeNumber ) == "Y" ) ||
                 ( $this->isPayrollAssistant( $employeeNumber) == "Y" ) ? "Y" : "N" );
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
         *    If Level 2 = FIN (from file PPRMS, field PRL02) and
         *    Level 3 starts with PY (from file PRPMS, field PRL02) and
         *    Training group = MGR2 (from file PRPMS, field PRTGRP)
         * 08/23/16 sawik Add:
         *    If in new table TIMEOFF_PAYROLL
         */
        $rawSqlPRPMS = "SELECT (CASE WHEN (
                           PRL02 = 'FIN' AND
                           SUBSTRING(PRL03,0,3) = 'PY' AND
                           PRTGRP = 'MGR2' AND
                           PRTEDH = 0
                        ) THEN 'Y' ELSE 'N' END) AS IS_PAYROLL_ADMIN
                       FROM PRPMS
                       WHERE TRIM(PRPMS.PREN) = '" . $employeeNumber . "' and TRIM(PRPMS.PRER) = '002'";
        $dataPRPMS = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSqlPRPMS );

        /**
         * 2nd check to see if they are added in the db
         */
        $rawSqlTimeOffAdded = "SELECT COUNT(*) AS PAYROLL_ADMIN_ADDED_COUNT
                          FROM TIMEOFF_REQUESTS_PAYROLL_ADMINS
                          WHERE TRIM(EMPLOYEE_NUMBER) = '" . $employeeNumber . "' AND STATUS = 1";
        $dataTimeOffAdded = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSqlTimeOffAdded );

        $rawSqlTimeOffDisabled = "SELECT COUNT(*) AS PAYROLL_ADMIN_DISABLED_COUNT
                          FROM TIMEOFF_REQUESTS_PAYROLL_ADMINS
                          WHERE TRIM(EMPLOYEE_NUMBER) = '" . $employeeNumber . "' AND STATUS = 0";
        $dataTimeOffDisabled = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSqlTimeOffDisabled );

        $validation = [ 'IS_PAYROLL_ADMIN' => $dataPRPMS[0]->IS_PAYROLL_ADMIN,
                        'PAYROLL_ADMIN_ADDED_COUNT' => $dataTimeOffAdded[0]->PAYROLL_ADMIN_ADDED_COUNT,
                        'PAYROLL_ADMIN_DISABLED_COUNT' => $dataTimeOffDisabled[0]->PAYROLL_ADMIN_DISABLED_COUNT ];

        $isPayrollAdmin = "N";
        if( ( $validation['IS_PAYROLL_ADMIN']=="Y" && $validation['PAYROLL_ADMIN_ADDED_COUNT']==0 && $validation['PAYROLL_ADMIN_DISABLED_COUNT']==0 ) ||
            ( $validation['IS_PAYROLL_ADMIN']=="N" && $validation['PAYROLL_ADMIN_ADDED_COUNT']==1 && $validation['PAYROLL_ADMIN_DISABLED_COUNT']==0 ) ) {
            $isPayrollAdmin = "Y";
        }
        if( $validation['IS_PAYROLL_ADMIN']=="Y" && $validation['PAYROLL_ADMIN_ADDED_COUNT']==0 && $validation['PAYROLL_ADMIN_DISABLED_COUNT']==1 ) {
            $isPayrollAdmin = "N";
        }

//         die( "IS PAYROLL ADMIN: " . $isPayrollAdmin );

        return $isPayrollAdmin;
    }

    /**
     * Returns whether employee is Payroll or not.
     *
     * @param integer $employeeNumber   Integer, up to 9 places. Does not need to be justified.
     * @return boolean   "Y" or "N"
     */
    public function isPayrollAssistant( $employeeNumber = null ) {

        $employeeNumber = str_pad(trim($employeeNumber), 9, ' ', STR_PAD_LEFT);

        $sql = new Sql($this->adapter);

        $select = $sql->select();

        $select->from('TIMEOFF_REQUESTS_PAYROLL_ASSISTANTS');

        $select->columns(array('RCOUNT' => new \Zend\Db\Sql\Expression('COUNT(*)')));

        $select->where(array('STATUS = ?' => '1', 'EMPLOYEE_NUMBER = ?' => $employeeNumber));

        $statement = $sql->prepareStatementForSqlObject($select);

        $result = $statement->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet();
            $resultSet->initialize($result);
            $record = $resultSet->toArray();
            $rowCount = $record[0]['RCOUNT'];
            $returnValue = ($rowCount == 0) ? 'N' : 'Y';

            return $returnValue;
        }

        return "N";
    }

    public function isProxyForManager($employeeNumber = null)
    {
        $rawSql = "SELECT COUNT(*) AS RCOUNT FROM TIMEOFF_REQUEST_EMPLOYEE_PROXIES
                   WHERE TRIM(PROXY_EMPLOYEE_NUMBER) = '" . $employeeNumber . "' AND
                   STATUS = 1 AND (IS_MANAGER_MG('002', EMPLOYEE_NUMBER) = 'Y' OR IS_SUPERVISOR('002', EMPLOYEE_NUMBER) = 'Y' )";

        $data = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql($this->adapter, $rawSql);

        return ( $data[0]->RCOUNT >= 1 ? 'Y' : 'N' );
    }

    public function isProxyForManagerList($employeeNumber = null)
    {
        $rawSql = "SELECT EMPLOYEE_NUMBER AS RCOUNT FROM TIMEOFF_REQUEST_EMPLOYEE_PROXIES
                   WHERE TRIM(PROXY_EMPLOYEE_NUMBER) = '" . $employeeNumber . "' AND
                   STATUS = 1 AND (IS_MANAGER_MG('002', EMPLOYEE_NUMBER) = 'Y' OR IS_SUPERVISOR('002', EMPLOYEE_NUMBER) = 'Y' )";

        $data = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql($this->adapter, $rawSql);

        return $data;
    }

    public function isCreatorInEmployeeHierarchy( $creatorId, $employeeId )
    {
        $employeeHierarchy = $this->getEmployeeHierarchy($employeeId);
        foreach ($employeeHierarchy as $hierarchy) {
            if (trim($hierarchy["MANAGER_EMPLOYEE_ID"]) == trim($creatorId)) {
                return true;
            }
        }

        return false;
    }

    public function isCreatorProxyForManagerInHierarchy( $creatorId, $employeeId)
    {
        $employeeHierarchy = $this->getEmployeeHierarchy($employeeId);
        $proxyList = $this->findProxiesByEmployeeNumber( $creatorId );

        $proxyFor = [];
        foreach ($proxyList as $proxy) {
            $proxyFor[] = trim($proxy['EMPLOYEE_NUMBER']);
        }

        foreach ($employeeHierarchy as $hierarchy) {
            if (in_array(trim($hierarchy["MANAGER_EMPLOYEE_ID"]), $proxyFor)) {
                return true;
            }
        }
        return false;
    }

    public function getCreatorProxyForManagerInHierarchy( $creatorId, $employeeId)
    {
        $employeeHierarchy = $this->getEmployeeHierarchy($employeeId);
        $proxyList = $this->findProxiesByEmployeeNumber( $creatorId );

        $proxyFor = [];
        foreach ($proxyList as $proxy) {
            $proxyFor[] = trim($proxy['EMPLOYEE_NUMBER']);
        }

        foreach ($employeeHierarchy as $hierarchy) {
            if (in_array(trim($hierarchy["MANAGER_EMPLOYEE_ID"]), $proxyFor)) {
                return $this->getEmployeeAltDescription(trim($hierarchy["MANAGER_EMPLOYEE_ID"]));
            }
        }
        return false;
    }

    public function getEmployeeHierarchy( $employeeId )
    {
        $rawSql = "select mh.* from table (care_get_manager_hierarchy_for_employee('002','" . str_pad(trim($employeeId), 9, ' ', STR_PAD_LEFT) . "')) mh";

        $select = $this->adapter->query($rawSql);

        $result = $select->execute();
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet();
            $resultSet->initialize($result);
            return $resultSet->toArray();
        }

        return [];
    }

    /**
     * Adds a where clause to exclude employees in Level 2.
     *
     * @return string
     */
    public function getExcludedLevel2() {
        $where = '';
        foreach ( $this->excludeLevel2 as $excluded ) {
            $where .= "employee.PRL02 != '" . implode( ', ', $this->excludeLevel2 ) . "' and ";
        }
        return $where;
    }

    /**
     * Returns the requested hours per category for a request.
     * Note: Make sure that TIMEOFF_REQUEST_ENTRIES.IS_DELETED = 0.
     * This way we won't be including entries that a Manager or Payroll deleted from the request.
     *
     * @param type $requestId
     * @return array
     */
    public function checkHoursRequestedPerCategory( $requestId = null ) {
        $rawSql = "SELECT
            request.REQUEST_ID AS ID,
            request.EMPLOYEE_NUMBER,
            request.REQUEST_REASON AS REASON,
            ( SELECT SUM(requested_hours) FROM timeoff_request_entries entry
              WHERE entry.request_id = request.request_id AND
              entry.is_deleted = '0'
            ) AS TOTAL,
            ( SELECT CASE WHEN SUM(requested_hours) > 0 THEN SUM(requested_hours) ELSE 0 END FROM timeoff_request_entries entry
              WHERE entry.request_id = request.request_id AND
              entry.is_deleted = '0' AND
              entry.request_code = 'P'
            ) AS PTO,
            ( SELECT CASE WHEN SUM(requested_hours) > 0 THEN SUM(requested_hours) ELSE 0 END FROM timeoff_request_entries entry
              WHERE entry.request_id = request.request_id AND
              entry.is_deleted = '0' AND
              entry.request_code = 'K'
            ) AS FLOAT,
            ( SELECT CASE WHEN SUM(requested_hours) > 0 THEN SUM(requested_hours) ELSE 0 END FROM timeoff_request_entries entry
              WHERE entry.request_id = request.request_id AND
              entry.is_deleted = '0' AND
              entry.request_code = 'S'
            ) AS SICK,
            ( SELECT CASE WHEN SUM(requested_hours) > 0 THEN SUM(requested_hours) ELSE 0 END FROM timeoff_request_entries entry
              WHERE entry.request_id = request.request_id AND
              entry.is_deleted = '0' AND
              entry.request_code = 'R'
            ) AS GRANDFATHERED,
            ( SELECT CASE WHEN SUM(requested_hours) > 0 THEN SUM(requested_hours) ELSE 0 END FROM timeoff_request_entries entry
              WHERE entry.request_id = request.request_id AND
              entry.is_deleted = '0' AND
              entry.request_code = 'J'
            ) AS CIVIC_DUTY
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
            $proxyFor[$key] = "'" . trim($proxy) . "'";
        }
        $proxyForPartial = implode(",", $proxyFor);

        //$where .= " AND trim(employee.PREN) IN(" . $proxyForPartial . ")";
        $where .= " AND trim(manager.SPSPEN) IN(" . $proxyForPartial . ") AND employee.PRER = '002'" ;

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
              ON employee.PREN = manager.SPEN and employee.PRER = manager.SPER
        INNER JOIN PRPMS manager_addons
             ON manager_addons.PREN = manager.SPSPEN and manager_addons.PRER = manager.SPSPER
        " . $where . "
        ORDER BY employee.PRLNM ASC, employee.PRFNM ASC";
// var_dump($rawSql);
        return $rawSql;
    }

    public function getEmployeeSalaryType( $employee_id )
    {
        $sql = new Sql($this->adapter);
        
        $select = $sql->select();
        
        $select->from('PRPMS');
        
        $select->columns(['PRPAY']);
        
        $where = new Where();
        
        $where->equalTo( 'PREN', str_pad( trim( $employee_id ), 9, ' ', STR_PAD_LEFT ) )
              ->and->equalTo( 'PRER', '002' );
        
        $select->where($where);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        
        $result = $statement->execute();
        
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet();
            $resultSet->initialize($result);
            return $resultSet->toArray()[0]['PRPAY'];
        }
        return [];
    }
    
    public function getEmployeeAltDescription( $employee_id )
    {
        $sql = new Sql($this->adapter);

        $select = $sql->select();

        $select->from('PRPMS');

        $select->columns(['ALT_DESCRIPTION' => new Expression("TRIM ( PRFNM ) CONCAT ' ' CONCAT TRIM ( PRLNM ) CONCAT ' (' CONCAT TRIM ( PREN ) CONCAT ')'")]);

        $where = new Where();

        $where->equalTo('PREN', str_pad(trim($employee_id), 9, ' ', STR_PAD_LEFT))
              ->and->equalTo('PRER', '002');

        $select->where($where);

        $statement = $sql->prepareStatementForSqlObject($select);

        $result = $statement->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet();
            $resultSet->initialize($result);
            return $resultSet->toArray()[0]['ALT_DESCRIPTION'];
        }
        return [];
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
                          GET_MANAGER_EMPLOYEES('002', '" . $managerEmployeeNumber . "', '" . $directReportFilter . "')
                      ) as data
                ) hierarchy
                      ON hierarchy.EMPLOYEE_NUMBER = trim(employee.PREN)
                JOIN PRPMS manager_addons
                     ON hierarchy.DIRECT_MANAGER_EMPLOYEE_NUMBER = trim(manager_addons.PREN)
                " . $where . "
                ORDER BY EMPLOYEE_NAME";
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
        $isPayrollAdmin = \Login\Helper\UserSession::getUserSessionVariable( 'IS_PAYROLL_ADMIN' );
        $isPayrollAssistant = \Login\Helper\UserSession::getUserSessionVariable( 'IS_PAYROLL_ASSISTANT' );
        $where = "WHERE (
            " . $this->getExcludedLevel2() . "
            employee.PRER = '002' AND employee.PRTEDH = 0 AND
            ( TRIM(employee.PREN) LIKE '" . strtoupper( $search ) . "%' OR
              TRIM(employee.PRLNM) LIKE '" . strtoupper( $search ) . "%' OR
              TRIM(employee.PRFNM) LIKE '" . strtoupper( $search ) . "%'
            )
        )";

        if ( $isPayrollAdmin === "Y" ||
             $isPayrollAssistant === "Y" ) {
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
        if( empty( $this->findEmployeeSchedule( $employeeNumber ) ) ) {
            $this->makeDefaultEmployeeSchedule( $employeeNumber );
        }
    }

    public function isRequestToBeAutoApproved( $forEmployee = null, $byEmployee = null ) {
        $isRequestToBeAutoApproved = false;
        if( $forEmployee != $byEmployee ) {
            $rawSql = "select mh.* from table (care_get_manager_hierarchy_for_employee('002','" . $forEmployee . "')) mh";
            $employeeManagerData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSql );

            $proxyFor = [];
            if (\Login\Helper\UserSession::getUserSessionVariable( 'IS_PROXY_FOR_MANAGER' ) == 'Y') {
                $proxies = $this->isProxyForManagerList(UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ));
                foreach ($proxies as $key => $value) {
                    $proxyFor[] = $value->RCOUNT;
                }
            }

            foreach( $employeeManagerData as $ctr => $managerData ) {
                if( $managerData['MANAGER_EMPLOYEE_ID'] == $byEmployee ) {
                    $isRequestToBeAutoApproved = true;
                    break;
                }
                if (count($proxyFor) != 0) {
                    if( in_array($managerData['MANAGER_EMPLOYEE_ID'], $proxyFor)) {
                        $isRequestToBeAutoApproved = true;
                        break;
                    }
                }
            }
        }
        return $isRequestToBeAutoApproved;
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
        } catch ( \Exception $e ) {
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
    public function findTimeOffCalendarByEmployeeNumber( $employeeNumber = null, $startDate = null, $endDate = null, $requestId = null ) {
        $startDate = new \Datetime( $startDate );
        $startDate = $startDate->format( "Y-m-d" );
        $endDate = new \Datetime( $endDate );
        $endDate = $endDate->format( "Y-m-d" );
        $andRequestId = ( !is_null( $requestId ) ? " AND request.REQUEST_ID = " . $requestId : "" );

        $rawSql = "select entry.ENTRY_ID, entry.REQUEST_DATE, entry.REQUESTED_HOURS, requestcode.CALENDAR_DAY_CLASS, request.REQUEST_STATUS,
            CASE WHEN entry.REQUEST_DATE BETWEEN '" . $startDate . "' AND '" . $endDate . "' THEN
                1
            ELSE
                0
            END AS IS_ON_CURRENT_CALENDAR
            FROM TIMEOFF_REQUEST_ENTRIES entry
            INNER JOIN TIMEOFF_REQUESTS AS request ON request.REQUEST_ID = entry.REQUEST_ID
            INNER JOIN TIMEOFF_REQUEST_CODES AS requestcode ON requestcode.REQUEST_CODE = entry.REQUEST_CODE
            WHERE
              trim(request.EMPLOYEE_NUMBER)='" . $employeeNumber . "' AND
              IS_DELETED = 0 " . $andRequestId . "
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

    /**
     * Find time off calendar data by Manager. Handles Direct, Indirect, and Both reports.
     *
     */
    public function findTimeOffCalendarByManager( $employerNumber = '002', $managerEmployeeNumber = null,
                                                  $managerReportsType = 'D', $startDate = null, $endDate = null ) {
        $startDate = new \Datetime( $startDate );
        $startDate = $startDate->format( "Y-m-d" );
        $endDate = new \Datetime( $endDate );
        $endDate = $endDate->format( "Y-m-d" );

        $rawSql = "select report_requests.REQUEST_DATE, report_requests.EMPLOYEE_NUMBER,
            report_requests.PRCOMN, report_requests.PRLNM, report_requests.EMPLOYEE_NAME, report_requests.TOTAL_HOURS from table (
              SELECT
                EMPLOYEE_ID AS EMPLOYEE_NUMBER, TRIM(DIRECT_MANAGER_EMPLOYEE_ID) AS DIRECT_MANAGER_EMPLOYEE_NUMBER,
                DIRECT_INDIRECT,
                MANAGER_LEVEL FROM table (
                  GET_MANAGER_EMPLOYEES('" . $employerNumber . "', '" . $managerEmployeeNumber . "', '" . $managerReportsType . "')
                ) as data
            ) hierarchy
            INNER JOIN table(
              select
                entry.REQUEST_DATE, TRIM(employee.PRCOMN) AS PRCOMN, TRIM(employee.PRLNM) AS PRLNM, TRIM(employee.PRCOMN) CONCAT ' ' CONCAT TRIM(employee.PRLNM) as EMPLOYEE_NAME, request.EMPLOYEE_NUMBER, sum(entry.REQUESTED_HOURS) as TOTAL_HOURS
                FROM TIMEOFF_REQUEST_ENTRIES entry
                JOIN TIMEOFF_REQUESTS AS request ON request.REQUEST_ID = entry.REQUEST_ID
                JOIN TIMEOFF_REQUEST_CODES AS requestcode ON requestcode.REQUEST_CODE = entry.REQUEST_CODE
                JOIN PRPMS AS employee ON trim(employee.PREN) = trim(request.EMPLOYEE_NUMBER)
                WHERE
                  entry.REQUEST_DATE BETWEEN '" . $startDate . "' AND '" . $endDate . "' AND
                  IS_DELETED = 0 AND
                  request.REQUEST_STATUS IN('A','F','S','U')
                group by employee.PRCOMN, employee.PRLNM, request.EMPLOYEE_NUMBER, entry.request_date, request.REQUEST_STATUS
                ORDER BY entry.request_date, employee.PRCOMN, employee.PRLNM
            ) report_requests ON report_requests.EMPLOYEE_NUMBER = hierarchy.EMPLOYEE_NUMBER
            ORDER BY report_requests.REQUEST_DATE, hierarchy.EMPLOYEE_NUMBER";

        $statement = $this->adapter->query( $rawSql );
        $result = $statement->execute();

        if ( $result instanceof ResultInterface && $result->isQueryResult() ) {
            $resultSet = new ResultSet;
            $resultSet->initialize( $result );
            $data = $resultSet->toArray();
        } else {
            $data = [ ];
        }

        $calendarData = [];
        foreach( $data as $ctr => $request ) {
            $employeeAbbrName = substr( $request['PRCOMN'], 0, 1 ) . ". " . $request['PRLNM'];
            if( !array_key_exists( $request['REQUEST_DATE'], $calendarData ) ) {
                $counter = 0;
                $calendarData[$request['REQUEST_DATE']][$counter]['EMPLOYEE_NAME'] = $employeeAbbrName;
                $calendarData[$request['REQUEST_DATE']][$counter]['TOTAL_HOURS'] = $request['TOTAL_HOURS'];
            } else {
                $counter++;
                $calendarData[$request['REQUEST_DATE']][$counter]['EMPLOYEE_NAME'] = $employeeAbbrName;
                $calendarData[$request['REQUEST_DATE']][$counter]['TOTAL_HOURS'] = $request['TOTAL_HOURS'];
            }
        }

        return $calendarData;
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
        } catch ( \Exception $e ) {
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

    public function getEmployeeEmailAddress( $employeeId = null, $employerId = '002')
    {
        $employeeId = str_pad(trim($employeeId), 9, ' ', STR_PAD_LEFT);

        $sql = new Sql($this->adapter);

        $select = $sql->select();

        $select->from('PRPMS');

        $select->columns(['PREML1']);

        $where = new Where();

        $where->equalTo('PRER', $employerId)
              ->and->equalTo('PREN', $employeeId);

        $select->where($where);

        $statement = $sql->prepareStatementForSqlObject($select);

        $result = $statement->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet();
            $resultSet->initialize($result);
            return $resultSet->toArray()[0]['PREML1'];
        }

        return null;
    }

    public function getEmployeeManagerEmailAddress( $employeeId = null, $employerId = '002')
    {
        $employeeId = str_pad(trim($employeeId), 9, ' ', STR_PAD_LEFT);

        $employeeHierarchy = $this->getEmployeeHierarchy($employeeId);

        foreach ($employeeHierarchy as $hierarchyRecord) {
            if ($hierarchyRecord['MANAGER_SUPERVISOR'] == 'Manager') {
                $employeeId = $hierarchyRecord['MANAGER_EMPLOYEE_ID'];
                break;
            }
        }

        $sql = new Sql($this->adapter);

        $select = $sql->select();

        $select->from('PRPMS');

        $select->columns(['PREML1']);

        $where = new Where();

        $where->equalTo('PRER', $employerId)
              ->and->equalTo('PREN', $employeeId);

        $select->where($where);

        $statement = $sql->prepareStatementForSqlObject($select);

        $result = $statement->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet();
            $resultSet->initialize($result);
            return $resultSet->toArray()[0]['PREML1'];
        }

        return null;
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