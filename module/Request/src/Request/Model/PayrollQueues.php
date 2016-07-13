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
class PayrollQueues extends BaseDB {

    public $timePeriodToElapseBeforeWarningManagerToApproveRequests; 
    
    public function __construct() {
        parent::__construct();
        
        $this->timePeriodToElapseBeforeWarningManagerToApproveRequests = '-3 days';
    }
    
    /**
     * Get count of Denied Queue data
     * 
     * @param array $data   $data = [ 'employeeData' => 'xxxxxxxxx' ];
     * @return int
     */
    public function countDeniedQueueItems( $data = null, $isFiltered = false )
    {
        $rawSql = "SELECT COUNT(*) AS RCOUNT       
        FROM TIMEOFF_REQUESTS request
        INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
        INNER JOIN PRPSP manager ON employee.PREN = manager.SPEN
        INNER JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN";
        
        $where = [];
        $where[] = "request.REQUEST_STATUS = 'D'";
            
        if( $isFiltered ) {
            if( array_key_exists( 'search', $data ) && !empty( $data['search']['value'] ) ) {
                $where[] = "( employee.PREN LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
                              employee.PRFNM LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
                              employee.PRLNM LIKE '%" . strtoupper( $data['search']['value'] ) . "%' 
                            )";
            }
        }
        $rawSql .=  " WHERE " . implode( " AND ", $where );
        
        $queueData = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql( $this->adapter, $rawSql );

        return (int) $queueData['RCOUNT'];
    }

    /**
     * Get Manager Queue data to display in data table.
     * 
     * @param array $data   $data = [ 'employeeData' => 'xxxxxxxxx' ];
     * @return array
     */
    public function getDeniedQueue( $data = null ) {
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

                TRIM(employee.PRLNM) CONCAT ', ' CONCAT TRIM(employee.PRFNM) CONCAT ' (' CONCAT TRIM(employee.PREN) CONCAT ')' as EMPLOYEE_DESCRIPTION,
                TRIM(employee.PRFNM) AS EMPLOYEE_FIRST_NAME,
                TRIM(employee.PRLNM) AS EMPLOYEE_LAST_NAME,
		TRIM(manager_addons.PRLNM) CONCAT ', ' CONCAT TRIM(manager_addons.PRFNM) CONCAT ' (' CONCAT TRIM(manager_addons.PREN) CONCAT ')' as APPROVER_QUEUE,
                TRIM(manager_addons.PREML1) AS MANAGER_EMAIL_ADDRESS
            FROM TIMEOFF_REQUESTS request
            INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
            INNER JOIN PRPSP manager ON employee.PREN = manager.SPEN
            INNER JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN
            INNER JOIN TIMEOFF_REQUEST_STATUSES status ON status.REQUEST_STATUS = request.REQUEST_STATUS
            WHERE request.REQUEST_STATUS = 'D'
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
        
        $queueData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSql );

        return $queueData;
    }
    
    /**
     * Get count of Manager Queue data
     * 
     * @param array $data   $data = [ 'employeeData' => 'xxxxxxxxx' ];
     * @return int
     */
    public function countUpdateChecksQueueItems( $data = null, $isFiltered = false )
    {
        $where = [];
        if( $isFiltered ) {
            $where[] = ( ($data['columns'][0]['search']['value']!=="" && $data['columns'][0]['search']['value']!=="All") ?
                        "payroll_master_file.PYCYC = '" . $data['columns'][0]['search']['value'] . "'" : "payroll_master_file.PYCYC IS NOT NULL" );
        }
        
        if( array_key_exists( 'search', $data ) && !empty( $data['search']['value'] ) ) {
            $where[] = "( EMPLOYEE_NUMBER LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
                          EMPLOYEE_FIRST_NAME LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
                          EMPLOYEE_LAST_NAME LIKE '%" . strtoupper( $data['search']['value'] ) . "%' 
                        )";
        }
        $where[] = "request.REQUEST_STATUS = 'U'";
        
        $connector = "";
        if( count($where)==1 ) {
            $connector = "";
        } elseif( count($where)>1 ) {
            $connector = " AND ";
        }
        $whereStatement = " WHERE " . implode( $connector, $where );
        $rawSql = "       
        SELECT COUNT(*) AS RCOUNT FROM (
            SELECT
                ROW_NUMBER () OVER (ORDER BY CYCLE_CODE, EMPLOYEE_DESCRIPTION, REQUEST_ID) AS ROW_NUMBER,
                DATA.* FROM (
                SELECT
                request.REQUEST_ID AS REQUEST_ID,
                TRIM(request.EMPLOYEE_NUMBER) AS EMPLOYEE_NUMBER,
	        payroll_master_file.PYCYC AS CYCLE_CODE,
                (
		    SELECT CASE WHEN COMMENT IS NOT NULL THEN COMMENT ELSE '.....' END FROM timeoff_request_log log WHERE log.request_id = request.request_id AND
		    COMMENT_TYPE = 'P'
		    ORDER BY CREATE_TIMESTAMP DESC
		    FETCH FIRST 1 ROWS ONLY
		) AS LAST_PAYROLL_COMMENT,
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

                TRIM(employee.PRLNM) CONCAT ', ' CONCAT TRIM(employee.PRFNM) CONCAT ' (' CONCAT TRIM(employee.PREN) CONCAT ')' as EMPLOYEE_DESCRIPTION,
                TRIM(employee.PRFNM) AS EMPLOYEE_FIRST_NAME,
                TRIM(employee.PRLNM) AS EMPLOYEE_LAST_NAME,
		TRIM(manager_addons.PRLNM) CONCAT ', ' CONCAT TRIM(manager_addons.PRFNM) CONCAT ' (' CONCAT TRIM(manager_addons.PREN) CONCAT ')' as APPROVER_QUEUE,
                TRIM(manager_addons.PREML1) AS MANAGER_EMAIL_ADDRESS
            FROM TIMEOFF_REQUESTS request
            INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
            INNER JOIN PRPSP manager ON employee.PREN = manager.SPEN
            INNER JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN
	    INNER JOIN PYPMS payroll_master_file ON payroll_master_file.PYEN = request.EMPLOYEE_NUMBER
            INNER JOIN TIMEOFF_REQUEST_STATUSES status ON status.REQUEST_STATUS = request.REQUEST_STATUS
            " . $whereStatement . "
            ) AS DATA
        ) AS DATA2";
        
        $queueData = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql( $this->adapter, $rawSql );

        return (int) $queueData['RCOUNT'];
    }

    /**
     * Get Manager Queue data to display in data table.
     * 
     * @param array $data   $data = [ 'employeeData' => 'xxxxxxxxx' ];
     * @return array
     */
    public function getUpdateChecksQueue( $data = null ) {
        $where1 = ( ($data['columns'][0]['search']['value']!=="" && $data['columns'][0]['search']['value']!=="All") ?
                    "AND payroll_master_file.PYCYC = '" . $data['columns'][0]['search']['value'] . "'" : "" );
        
        $rawSql = "       
        SELECT DATA2.* FROM (
            SELECT
                ROW_NUMBER () OVER (ORDER BY CYCLE_CODE, EMPLOYEE_DESCRIPTION, REQUEST_ID) AS ROW_NUMBER,
                DATA.* FROM (
                SELECT
                request.REQUEST_ID AS REQUEST_ID,
                TRIM(request.EMPLOYEE_NUMBER) AS EMPLOYEE_NUMBER,
	        payroll_master_file.PYCYC AS CYCLE_CODE,
                (
		    SELECT CASE WHEN COMMENT IS NOT NULL THEN COMMENT ELSE '.....' END FROM timeoff_request_log log WHERE log.request_id = request.request_id AND
		    COMMENT_TYPE = 'P'
		    ORDER BY CREATE_TIMESTAMP DESC
		    FETCH FIRST 1 ROWS ONLY
		) AS LAST_PAYROLL_COMMENT,
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

                TRIM(employee.PRLNM) CONCAT ', ' CONCAT TRIM(employee.PRFNM) CONCAT ' (' CONCAT TRIM(employee.PREN) CONCAT ')' as EMPLOYEE_DESCRIPTION,
                TRIM(employee.PRFNM) AS EMPLOYEE_FIRST_NAME,
                TRIM(employee.PRLNM) AS EMPLOYEE_LAST_NAME,
		TRIM(manager_addons.PRLNM) CONCAT ', ' CONCAT TRIM(manager_addons.PRFNM) CONCAT ' (' CONCAT TRIM(manager_addons.PREN) CONCAT ')' as APPROVER_QUEUE,
                TRIM(manager_addons.PREML1) AS MANAGER_EMAIL_ADDRESS
            FROM TIMEOFF_REQUESTS request
            INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
            INNER JOIN PRPSP manager ON employee.PREN = manager.SPEN
            INNER JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN
	    INNER JOIN PYPMS payroll_master_file ON payroll_master_file.PYEN = request.EMPLOYEE_NUMBER
            INNER JOIN TIMEOFF_REQUEST_STATUSES status ON status.REQUEST_STATUS = request.REQUEST_STATUS
            WHERE request.REQUEST_STATUS = 'U' " . $where1 . "
	    ) AS DATA
        ) AS DATA2";

        $columns = [ "EMPLOYEE_DESCRIPTION",
                     "APPROVER_QUEUE",
                     "REQUEST_STATUS_DESCRIPTION",
                     "REQUESTED_HOURS",
                     "LAST_PAYROLL_COMMENT",
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
        
        $rawSql .=  " WHERE " . implode( " AND ", $where ) . " ORDER BY CYCLE_CODE, EMPLOYEE_DESCRIPTION, REQUEST_ID";
        
        $queueData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSql );

        return $queueData;
    }
    
    public function countPendingPayrollApprovalQueueItems( $data = null, $isFiltered = false )
    {
        $rawSql = "SELECT COUNT(*) AS RCOUNT       
        FROM TIMEOFF_REQUESTS request
        INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
        INNER JOIN PRPSP manager ON employee.PREN = manager.SPEN
        INNER JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN";
        
        $where = [];
        $where[] = "request.REQUEST_STATUS = 'Y'";
            
        if( $isFiltered ) {
            if( array_key_exists( 'search', $data ) && !empty( $data['search']['value'] ) ) {
                $where[] = "( employee.PREN LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
                              employee.PRFNM LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
                              employee.PRLNM LIKE '%" . strtoupper( $data['search']['value'] ) . "%' 
                            )";
            }
        }
        $rawSql .=  " WHERE " . implode( " AND ", $where );
        
        $queueData = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql( $this->adapter, $rawSql );

        return (int) $queueData['RCOUNT'];
    }
    
    public function getPendingPayrollApprovalQueue( $data = null )
    {
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

                TRIM(employee.PRLNM) CONCAT ', ' CONCAT TRIM(employee.PRFNM) CONCAT ' (' CONCAT TRIM(employee.PREN) CONCAT ')' as EMPLOYEE_DESCRIPTION,
                TRIM(employee.PRFNM) AS EMPLOYEE_FIRST_NAME,
                TRIM(employee.PRLNM) AS EMPLOYEE_LAST_NAME,
		'PAYROLL' as APPROVER_QUEUE,
                TRIM(manager_addons.PREML1) AS MANAGER_EMAIL_ADDRESS
            FROM TIMEOFF_REQUESTS request
            INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
            INNER JOIN PRPSP manager ON employee.PREN = manager.SPEN
            INNER JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN
            INNER JOIN TIMEOFF_REQUEST_STATUSES status ON status.REQUEST_STATUS = request.REQUEST_STATUS
            WHERE request.REQUEST_STATUS = 'Y'
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
        
        $queueData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSql );

        return $queueData;
    }
    
    public function countCompletedPAFsQueueItems( $data = null, $isFiltered = false )
    {
        $rawSql = "SELECT COUNT(*) AS RCOUNT       
        FROM TIMEOFF_REQUESTS request
        INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
        INNER JOIN PRPSP manager ON employee.PREN = manager.SPEN
        INNER JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN";
        
        $where = [];
        $where[] = "request.REQUEST_STATUS = 'F'";
            
        if( $isFiltered ) {
            if( array_key_exists( 'search', $data ) && !empty( $data['search']['value'] ) ) {
                $where[] = "( employee.PREN LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
                              employee.PRFNM LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
                              employee.PRLNM LIKE '%" . strtoupper( $data['search']['value'] ) . "%' 
                            )";
            }
        }
        $rawSql .=  " WHERE " . implode( " AND ", $where );
        
        $queueData = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql( $this->adapter, $rawSql );

        return (int) $queueData['RCOUNT'];
    }
    
    public function getCompletedPAFsQueue( $data = null )
    {
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

                TRIM(employee.PRLNM) CONCAT ', ' CONCAT TRIM(employee.PRFNM) CONCAT ' (' CONCAT TRIM(employee.PREN) CONCAT ')' as EMPLOYEE_DESCRIPTION,
                TRIM(employee.PRFNM) AS EMPLOYEE_FIRST_NAME,
                TRIM(employee.PRLNM) AS EMPLOYEE_LAST_NAME,
		TRIM(manager_addons.PRLNM) CONCAT ', ' CONCAT TRIM(manager_addons.PRFNM) CONCAT ' (' CONCAT TRIM(manager_addons.PREN) CONCAT ')' as APPROVER_QUEUE,
                TRIM(manager_addons.PREML1) AS MANAGER_EMAIL_ADDRESS
            FROM TIMEOFF_REQUESTS request
            INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
            INNER JOIN PRPSP manager ON employee.PREN = manager.SPEN
            INNER JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN
            INNER JOIN TIMEOFF_REQUEST_STATUSES status ON status.REQUEST_STATUS = request.REQUEST_STATUS
            WHERE request.REQUEST_STATUS = 'F'
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
        
        $queueData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSql );

        return $queueData;
    }
    
    public function countPendingAS400UploadQueueItems( $data = null, $isFiltered = false )
    {
        $rawSql = "SELECT COUNT(*) AS RCOUNT       
        FROM TIMEOFF_REQUESTS request
        INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
        INNER JOIN PRPSP manager ON employee.PREN = manager.SPEN
        INNER JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN";
        
        $where = [];
        $where[] = "request.REQUEST_STATUS = 'S'";
            
        if( $isFiltered ) {
            if( array_key_exists( 'search', $data ) && !empty( $data['search']['value'] ) ) {
                $where[] = "( employee.PREN LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
                              employee.PRFNM LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
                              employee.PRLNM LIKE '%" . strtoupper( $data['search']['value'] ) . "%' 
                            )";
            }
        }
        $rawSql .=  " WHERE " . implode( " AND ", $where );
        
        $queueData = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql( $this->adapter, $rawSql );

        return (int) $queueData['RCOUNT'];
    }
    
    public function getPendingAS400UploadQueue( $data = null )
    {
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

                TRIM(employee.PRLNM) CONCAT ', ' CONCAT TRIM(employee.PRFNM) CONCAT ' (' CONCAT TRIM(employee.PREN) CONCAT ')' as EMPLOYEE_DESCRIPTION,
                TRIM(employee.PRFNM) AS EMPLOYEE_FIRST_NAME,
                TRIM(employee.PRLNM) AS EMPLOYEE_LAST_NAME,
		TRIM(manager_addons.PRLNM) CONCAT ', ' CONCAT TRIM(manager_addons.PRFNM) CONCAT ' (' CONCAT TRIM(manager_addons.PREN) CONCAT ')' as APPROVER_QUEUE,
                TRIM(manager_addons.PREML1) AS MANAGER_EMAIL_ADDRESS
            FROM TIMEOFF_REQUESTS request
            INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
            INNER JOIN PRPSP manager ON employee.PREN = manager.SPEN
            INNER JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN
            INNER JOIN TIMEOFF_REQUEST_STATUSES status ON status.REQUEST_STATUS = request.REQUEST_STATUS
            WHERE request.REQUEST_STATUS = 'S'
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
        
        $queueData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSql );

        return $queueData;
    }
    
    public function countByStatusQueueItems( $data = null, $isFiltered = false )
    {
        $where1 = "";
        if( $isFiltered ) {
            $where1 = ( ($data['columns'][2]['search']['value']!=="" && $data['columns'][2]['search']['value']!=="All") ?
                    "WHERE status.DESCRIPTION = '" . $data['columns'][2]['search']['value'] . "'" : "" );
        }
        
        $rawSql = "SELECT COUNT(*) AS RCOUNT FROM (
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

                TRIM(employee.PRLNM) CONCAT ', ' CONCAT TRIM(employee.PRFNM) CONCAT ' (' CONCAT TRIM(employee.PREN) CONCAT ')' as EMPLOYEE_DESCRIPTION,
                TRIM(employee.PRFNM) AS EMPLOYEE_FIRST_NAME,
                TRIM(employee.PRLNM) AS EMPLOYEE_LAST_NAME,
		TRIM(manager_addons.PRLNM) CONCAT ', ' CONCAT TRIM(manager_addons.PRFNM) CONCAT ' (' CONCAT TRIM(manager_addons.PREN) CONCAT ')' as APPROVER_QUEUE,
                TRIM(manager_addons.PREML1) AS MANAGER_EMAIL_ADDRESS
            FROM TIMEOFF_REQUESTS request
            INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
            INNER JOIN PRPSP manager ON employee.PREN = manager.SPEN
            INNER JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN
            INNER JOIN TIMEOFF_REQUEST_STATUSES status ON status.REQUEST_STATUS = request.REQUEST_STATUS 
            " . $where1 . "
            ORDER BY REQUEST_STATUS_DESCRIPTION ASC, MIN_DATE_REQUESTED ASC, EMPLOYEE_LAST_NAME ASC) AS DATA
        ) AS DATA2";
        
        $where2 = [];
            
        if( $isFiltered ) {
            if( array_key_exists( 'search', $data ) && !empty( $data['search']['value'] ) ) {
                $where2[] = "( DATA2.EMPLOYEE_NUMBER LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
                          DATA2.EMPLOYEE_FIRST_NAME LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
                          DATA2.EMPLOYEE_LAST_NAME LIKE '%" . strtoupper( $data['search']['value'] ) . "%' 
                        )";
            }
            if( array_key_exists( 'startDate', $data) && !empty( $data['startDate'] ) ) {
                $where2[] = "DATA2.MIN_DATE_REQUESTED >= '" . $data['startDate'] . "'";
            }
            if( array_key_exists( 'endDate', $data) && !empty( $data['endDate'] ) ) {
                $where2[] = "DATA2.MAX_DATE_REQUESTED <= '" . $data['endDate'] . "'";
            }
        }
        $rawSql .=  ( !empty( $where2 ) ? " WHERE " . implode( " AND ", $where2 ) : "" );
        
        $queueData = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql( $this->adapter, $rawSql );
        
        return (int) $queueData['RCOUNT'];
    }
    
    public function getByStatusQueue( $data = null )
    {
        $where1 = ( ($data['columns'][2]['search']['value']!=="" && $data['columns'][2]['search']['value']!=="All") ?
                    "WHERE status.DESCRIPTION = '" . $data['columns'][2]['search']['value'] . "'" : "" );
        
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

                TRIM(employee.PRLNM) CONCAT ', ' CONCAT TRIM(employee.PRFNM) CONCAT ' (' CONCAT TRIM(employee.PREN) CONCAT ')' as EMPLOYEE_DESCRIPTION,
                TRIM(employee.PRFNM) AS EMPLOYEE_FIRST_NAME,
                TRIM(employee.PRLNM) AS EMPLOYEE_LAST_NAME,
		TRIM(manager_addons.PRLNM) CONCAT ', ' CONCAT TRIM(manager_addons.PRFNM) CONCAT ' (' CONCAT TRIM(manager_addons.PREN) CONCAT ')' as APPROVER_QUEUE,
                TRIM(manager_addons.PREML1) AS MANAGER_EMAIL_ADDRESS
            FROM TIMEOFF_REQUESTS request
            INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
            INNER JOIN PRPSP manager ON employee.PREN = manager.SPEN
            INNER JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN
            INNER JOIN TIMEOFF_REQUEST_STATUSES status ON status.REQUEST_STATUS = request.REQUEST_STATUS " . $where1 . "
            ORDER BY REQUEST_STATUS_DESCRIPTION ASC, MIN_DATE_REQUESTED ASC, EMPLOYEE_LAST_NAME ASC) AS DATA
        ) AS DATA2";

        $columns = [ "EMPLOYEE_DESCRIPTION",
                     "APPROVER_QUEUE",
                     "REQUEST_STATUS_DESCRIPTION",
                     "REQUESTED_HOURS",
                     "REQUEST_REASON",
                     "MIN_DATE_REQUESTED",
                     "ACTIONS"
                   ];
        
        $where2 = [];
        if( array_key_exists( 'search', $data ) && !empty( $data['search']['value'] ) ) {
            $where2[] = "( DATA2.EMPLOYEE_NUMBER LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
                          DATA2.EMPLOYEE_FIRST_NAME LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
                          DATA2.EMPLOYEE_LAST_NAME LIKE '%" . strtoupper( $data['search']['value'] ) . "%' 
                        )";
        }
        if( array_key_exists( 'startDate', $data) && !empty( $data['startDate'] ) ) {
            $where2[] = "MIN_DATE_REQUESTED >= '" . $data['startDate'] . "'";
        }
        if( array_key_exists( 'endDate', $data) && !empty( $data['endDate'] ) ) {
            $where2[] = "MAX_DATE_REQUESTED <= '" . $data['endDate'] . "'";
        }
        if( $data !== null ) {
            $where2[] = "ROW_NUMBER BETWEEN " . ( $data['start'] + 1 ) . " AND " . ( $data['start'] + $data['length'] );
        }
        
        $rawSql .=  ( !empty( $where2 ) ? " WHERE " . implode( " AND ", $where2 ) : "" );
        
        $queueData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSql );

        return $queueData;
    }
    
    public function countManagerActionQueueItems( $data = null, $isFiltered = false )
    {
        $rawSql = "SELECT COUNT(*) AS RCOUNT       
        FROM TIMEOFF_REQUESTS request
            INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
            INNER JOIN PRPSP manager ON employee.PREN = manager.SPEN
            INNER JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN
            INNER JOIN TIMEOFF_REQUEST_STATUSES status ON status.REQUEST_STATUS = request.REQUEST_STATUS";
        
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
        
        $where[] = "( date( CREATE_TIMESTAMP ) <= '" . $this->getManagerWarnDateToApproveRequests() . "' )";
        
        $rawSql .=  ( !empty( $where ) ? " WHERE " . implode( " AND ", $where ) : "" );
        
        $queueData = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql( $this->adapter, $rawSql );

        return (int) $queueData['RCOUNT'];
    }
    
    /**
     * Returns all requests where we need to send Manager a notification to take action.
     * 
     * @param type $data
     * @return type
     */
    public function getManagerActionEmailQueue( $data = null, $params = [] )
    {
        $singleManager = "";
        $warnType = "";
        if( array_key_exists( 'MANAGER_EMPLOYEE_NUMBER', $params ) ) {
            $singleManager = " AND TRIM(manager_addons.PREN) = " . $params['MANAGER_EMPLOYEE_NUMBER'] . " AND ";
        }
        if( array_key_exists( 'WARN_TYPE', $params ) ) {
            if( $params['WARN_TYPE'] === 'OLD_REQUESTS' ) {
                $warnType = " AND ( date( CREATE_TIMESTAMP ) <= '" . $this->getManagerWarnDateToApproveRequests() . "' )";
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
                    SELECT SUM(requested_hours) FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id
                ) AS REQUESTED_HOURS,
                (
                    SELECT MIN(REQUEST_DATE) FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id
                ) AS MIN_DATE_REQUESTED,
                (
                    SELECT MAX(REQUEST_DATE) FROM timeoff_request_entries entry WHERE entry.request_id = request.request_id
                ) AS MAX_DATE_REQUESTED,
                TRIM(employee.PRLNM) CONCAT ', ' CONCAT TRIM(employee.PRFNM) CONCAT ' (' CONCAT TRIM(employee.PREN) CONCAT ')' as EMPLOYEE_DESCRIPTION_ALT,
                TRIM(employee.PRFNM) AS EMPLOYEE_FIRST_NAME,
                TRIM(employee.PRLNM) AS EMPLOYEE_LAST_NAME,
		TRIM(manager_addons.PRLNM) CONCAT ', ' CONCAT TRIM(manager_addons.PRFNM) CONCAT ' (' CONCAT TRIM(manager_addons.PREN) CONCAT ')' as APPROVER_QUEUE,
                TRIM(manager_addons.PREN) AS MANAGER_EMPLOYEE_NUMBER,
                TRIM(manager_addons.PREML1) AS MANAGER_EMAIL_ADDRESS,
                CREATE_TIMESTAMP,
                date( CREATE_TIMESTAMP ) as CREATE_DATE
            FROM TIMEOFF_REQUESTS request
            INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER
            INNER JOIN PRPSP manager ON employee.PREN = manager.SPEN
            INNER JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN
            INNER JOIN TIMEOFF_REQUEST_STATUSES status ON status.REQUEST_STATUS = request.REQUEST_STATUS
            WHERE request.REQUEST_STATUS = 'P' 
                  " . $singleManager . "
                  " . $warnType . "
            ORDER BY
                MIN_DATE_REQUESTED ASC
            ) AS DATA
        ) AS DATA2";
        
        $where = [];
        if( array_key_exists( 'search', $data ) && !empty( $data['search']['value'] ) ) {
            $where[] = "( DATA2.EMPLOYEE_NUMBER LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
                          DATA2.EMPLOYEE_FIRST_NAME LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
                          DATA2.EMPLOYEE_LAST_NAME LIKE '%" . strtoupper( $data['search']['value'] ) . "%' 
                        )";
        }
        if( $data !== null && array_key_exists( 'start', $data ) && array_key_exists( 'length', $data ) &&
            !empty( $data['start'] ) && !empty( $data['length'] ) ) {
            $where[] = "ROW_NUMBER BETWEEN " . ( $data['start'] + 1 ) . " AND " . ( $data['start'] + $data['length'] );
        }
        
        $rawSql .= ( !empty( $where ) ? " WHERE " . implode( " AND ", $where ) : "" );
        
        $employeeData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSql );

        return $employeeData;
    }
    
    /**
     * Returns the date where we need to warn manager of requests in their queue they need to action.
     * 
     * @return type
     */
    private function getManagerWarnDateToApproveRequests()
    {
        return date( 'Y-m-d', strtotime( $this->timePeriodToElapseBeforeWarningManagerToApproveRequests, strtotime( date( "Y-m-d" ) ) ) );
    }
}