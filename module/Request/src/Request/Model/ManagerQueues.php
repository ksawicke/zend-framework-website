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
class ManagerQueues extends BaseDB {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get count of Pending Manager Approval Queue data
     *
     * @param array $data   $data = [ 'employeeData' => 'xxxxxxxxx' ];
     * @return int
     */
    public function countManagerEmployeeRequestItems( $data = null, $isFiltered = false, $proxyFor = null, $statuses = [] )
    {
        $whereStatusStatement = ( ($data['columns'][2]['search']['value']!=="" && $data['columns'][2]['search']['value']!=="All") ?
            "WHERE status.DESCRIPTION = '" . $data['columns'][2]['search']['value'] . "'" : "" );

        if( !empty( $statuses ) ) {
            $whereStatusStatement .= " AND request.REQUEST_STATUS IN('" . implode( "','", $statuses ) . "') ";
        }

        $careGetManagerEmployees = "SELECT
        EMPLOYEE_ID AS EMPLOYEE_NUMBER,
        TRIM(DIRECT_MANAGER_EMPLOYEE_ID) AS DIRECT_MANAGER_EMPLOYEE_NUMBER,
        DIRECT_INDIRECT,
        MANAGER_LEVEL
        FROM table (
            CARE_GET_MANAGER_EMPLOYEES('002', '" . $data['employeeNumber'] . "', 'D')
            ) as data";


        if ($proxyFor !== null && is_array($proxyFor) && count($proxyFor) > 0) {
            foreach ($proxyFor as $proxy) {
                $careGetManagerEmployees .= " UNION ALL " .
                    "SELECT " .
                    "EMPLOYEE_ID AS EMPLOYEE_NUMBER, " .
                    "TRIM(DIRECT_MANAGER_EMPLOYEE_ID) AS DIRECT_MANAGER_EMPLOYEE_NUMBER, " .
                    "DIRECT_INDIRECT, " .
                    "MANAGER_LEVEL " .
                    "FROM table ( " .
                    "CARE_GET_MANAGER_EMPLOYEES('002', '" . $proxy . "', 'D') " .
                    ") as data";
            }
        }

        $rawSql = "
        SELECT COUNT(*) AS RCOUNT FROM (
            SELECT
                ROW_NUMBER () OVER (ORDER BY MIN_DATE_REQUESTED ASC, EMPLOYEE_LAST_NAME ASC) AS ROW_NUMBER,
                DATA.* FROM (
                SELECT
                request.REQUEST_ID AS REQUEST_ID,
                TRIM(request.EMPLOYEE_NUMBER) AS EMPLOYEE_NUMBER,
                request.REQUEST_REASON AS REQUEST_REASON,
                status.DESCRIPTION AS REQUEST_STATUS_DESCRIPTION,
                (
                    SELECT SUM(requested_hours) FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id AND IS_DELETED = '0'
                ) AS REQUESTED_HOURS,
                (
                    SELECT MIN(REQUEST_DATE) FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id
                ) AS MIN_DATE_REQUESTED,
                (
                    SELECT MAX(REQUEST_DATE) FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id
                ) AS MAX_DATE_REQUESTED,

                TRIM(employee.PRLNM) CONCAT ', ' CONCAT TRIM(employee.PRFNM) CONCAT ' (' CONCAT TRIM(employee.PREN) CONCAT ')' as EMPLOYEE_DESCRIPTION,
                TRIM(employee.PRFNM) AS EMPLOYEE_FIRST_NAME,
                TRIM(employee.PRLNM) AS EMPLOYEE_LAST_NAME,
                TRIM(manager_addons.PRLNM) CONCAT ', ' CONCAT TRIM(manager_addons.PRFNM) CONCAT ' (' CONCAT TRIM(manager_addons.PREN) CONCAT ')' as APPROVER_QUEUE,
                TRIM(manager_addons.PREML1) AS MANAGER_EMAIL_ADDRESS
            FROM TIMEOFF_REQUESTS request
            INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
            INNER JOIN PRPSP manager ON employee.PREN = manager.SPEN
            INNER JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN
            INNER JOIN table (" .
            $careGetManagerEmployees .
            ") hierarchy
                ON hierarchy.EMPLOYEE_NUMBER = employee.PREN
            INNER JOIN TIMEOFF_REQUEST_STATUSES status ON status.REQUEST_STATUS = request.REQUEST_STATUS
            " . $whereStatusStatement . "
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

        if( !empty( $where ) ) {
            $rawSql .=  " WHERE " . implode( " AND ", $where );
        }

        $queueData = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql( $this->adapter, $rawSql );

        return (int) $queueData['RCOUNT'];
    }

    /**
     * Get Pending Manager Approval Queue data to display in data table.
     *
     * @param array $data   $data = [ 'employeeData' => 'xxxxxxxxx' ];
     * @return array
     */
    public function getManagerEmployeeRequests( $data = null, $proxyFor = null, $statuses = [] ) {
        $whereStatusStatement = "";
        if( array_key_exists( 'columns', $data ) ) {
            $whereStatusStatement = ( ($data['columns'][2]['search']['value']!=="" && $data['columns'][2]['search']['value']!=="All") ?
                "WHERE status.DESCRIPTION = '" . $data['columns'][2]['search']['value'] . "'" : "" );
        }
        if( !empty( $statuses ) ) {
            $whereStatusStatement .= ( !empty( $whereStatusStatement ) ? " AND " : " WHERE " ) . "request.REQUEST_STATUS IN('" . implode( "','", $statuses ) . "') ";
        }

        $careGetManagerEmployees = "SELECT
        EMPLOYEE_ID AS EMPLOYEE_NUMBER,
        TRIM(DIRECT_MANAGER_EMPLOYEE_ID) AS DIRECT_MANAGER_EMPLOYEE_NUMBER,
        DIRECT_INDIRECT,
        MANAGER_LEVEL
        FROM table (
            CARE_GET_MANAGER_EMPLOYEES('002', '" . $data['employeeNumber'] . "', 'D')
            ) as data";


        if ($proxyFor !== null && is_array($proxyFor) && count($proxyFor) > 0) {
            foreach ($proxyFor as $proxy) {
                $careGetManagerEmployees .= " UNION ALL " .
                    "SELECT " .
                    "EMPLOYEE_ID AS EMPLOYEE_NUMBER, " .
                    "TRIM(DIRECT_MANAGER_EMPLOYEE_ID) AS DIRECT_MANAGER_EMPLOYEE_NUMBER, " .
                    "DIRECT_INDIRECT, " .
                    "MANAGER_LEVEL " .
                    "FROM table ( " .
                    "CARE_GET_MANAGER_EMPLOYEES('002', '" . $proxy . "', 'D') " .
                    ") as data";
            }
        }

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
                    SELECT SUM(requested_hours) FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id AND IS_DELETED = '0'
                ) AS REQUESTED_HOURS,
                (
                    SELECT MIN(REQUEST_DATE) FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id
                ) AS MIN_DATE_REQUESTED,
                (
                    SELECT MAX(REQUEST_DATE) FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id
                ) AS MAX_DATE_REQUESTED,

                TRIM(employee.PRLNM) CONCAT ', ' CONCAT TRIM(employee.PRFNM) CONCAT ' (' CONCAT TRIM(employee.PREN) CONCAT ')' as EMPLOYEE_DESCRIPTION,
                TRIM(employee.PRFNM) AS EMPLOYEE_FIRST_NAME,
                TRIM(employee.PRLNM) AS EMPLOYEE_LAST_NAME,
                CASE WHEN status.DESCRIPTION IN('Pending Manager Approval') THEN
                    TRIM(manager_addons.PRLNM) CONCAT ', ' CONCAT TRIM(manager_addons.PRFNM) CONCAT ' (' CONCAT TRIM(manager_addons.PREN) CONCAT ')'
                ELSE
                    CASE WHEN status.DESCRIPTION IN('Pending Payroll Approval') THEN 'PAYROLL' ELSE '' END
                END
                as APPROVER_QUEUE,
                TRIM(manager_addons.PREML1) AS MANAGER_EMAIL_ADDRESS
            FROM TIMEOFF_REQUESTS request
            INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
            INNER JOIN PRPSP manager ON employee.PREN = manager.SPEN
            INNER JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN
            INNER JOIN table (" .
            $careGetManagerEmployees .
            ") hierarchy
                ON hierarchy.EMPLOYEE_NUMBER = employee.PREN
            INNER JOIN TIMEOFF_REQUEST_STATUSES status ON status.REQUEST_STATUS = request.REQUEST_STATUS
            " . $whereStatusStatement . "
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
        if( array_key_exists( 'start', $data ) && array_key_exists( 'length', $data ) ) {
            $where[] = "ROW_NUMBER BETWEEN " . ( $data['start'] + 1 ) . " AND " . ( $data['start'] + $data['length'] );
        }

        if( !empty( $where ) ) {
            $rawSql .=  " WHERE " . implode( " AND ", $where );
        }

        $employeeData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSql );

        return $employeeData;
    }
}