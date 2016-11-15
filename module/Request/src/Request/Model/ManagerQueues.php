<?php

namespace Request\Model;


use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Where;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

/**
 * All Database functions for employees
 *
 * @author sawik
 *
 */
class ManagerQueues extends BaseDB
{

    private $sql;

    private $proxyFor = [];

    private $managerReportType = 'B';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     * @param array $data
     * @param array $proxyFor
     * @param array $statuses
     */
    public function getProxyEmployeeRequests( $data = null, $proxyFor = null, $statuses = [] )
    {
        /* define a new sql object */
        $this->sql = new Sql($this->adapter);

        /* set attribute */
        $this->proxyFor = $proxyFor;

        /* set sql to select */
        $select = $this->sql->select();

        $select->from(['request' => 'timeoff_requests']);

        /* sub query for requested hours */
        $requestedHours = $this->prepareRequestedHoursSql();

        /* sub query for first day */
        $minDateRequested = $this->prepareMinDateRequestedSql();

        /* sub query for last day */
        $maxDateRequested = $this->prepareMaxDateRequestedSql();

        /* prepare case statement to simplify the sql statement */
        $approverQueue = new Expression("case when status.description in('Pending Manager Approval') then TRIM(manager_addons.PRLNM) CONCAT ', ' CONCAT TRIM(manager_addons.PRFNM) CONCAT ' (' CONCAT TRIM(manager_addons.PREN) CONCAT ')' else CASE WHEN status.DESCRIPTION IN('Pending Payroll Approval','Update Checks') THEN 'PAYROLL' ELSE '' END end");

        /* define columns and integrate sub queries */
        $select->columns(
            [
                'REQUEST_ID'            => 'request_id',
                'EMPLOYEE_NUMBER'       => new Expression("trim(request.EMPLOYEE_NUMBER)"),
                'REQUEST_REASON'        => 'request_reason',
                'REQUEST_STATUS_DESCRIPTION' => new Expression('status.description'),
                'REQUESTED_HOURS'       => $requestedHours,
                'MIN_DATE_REQUESTED'    => $minDateRequested,
                'MAX_DATE_REQUESTED'    => $maxDateRequested,
                'EMPLOYEE_DESCRIPTION'  => new Expression("TRIM(employee.PRLNM) CONCAT ', ' CONCAT TRIM(employee.PRFNM) CONCAT ' (' CONCAT TRIM(employee.PREN) CONCAT ')'"),
                'EMPLOYEE_FIRST_NAME'   => new Expression("trim(employee.prfnm)"),
                'EMPLOYEE_LAST_NAME'    => new Expression("trim(employee.prlnm)"),
                'APPROVER_QUEUE'        => $approverQueue,
                'MANAGER_EMAIL_ADDRESS' => new Expression('trim(manager_addons.preml1)'),
                'MANAGER_EMPLOYEE_ID'   => new Expression('hierarchy.manager_employee_id_complete')
            ]);

        $this->managerReportsType = 'P';

        if (array_key_exists( 'columns', $data ) && $data['columns'][0]['search']['value'] !== "P" ) {
            $this->managerReportType = trim($data['columns'][0]['search']['value']) != '' ? $data['columns'][0]['search']['value'] : 'B';
            $this->proxyFor = [$data['employeeNumber']];
        }

        /* do we have proxies provided */
        if (!empty($this->proxyFor)) {
            $subProxy = $this->prepareProxySql();

            /* in case we have more proxies */
            foreach ($this->proxyFor as $proxy) {
                $subProxy->combine($this->prepareProxyUnionSql( $proxy ), 'UNION ALL');
            }
        }

        /* add joins to the select */
        $select->join(['employee'       => 'prpms'], "employee.PREN = request.EMPLOYEE_NUMBER and employee.PRER = '002'", ['prlnm', 'prfnm', 'pren']);
        $select->join(['manager'        => 'prpsp'], "employee.PREN = manager.SPEN and employee.PRER = manager.SPER", ['sper', 'spen', 'spspen']);
        $select->join(['manager_addons' => 'prpms'], "manager_addons.PREN = manager.SPSPEN and manager_addons.PRER = manager.SPSPER", ['preml1', 'prlnm', 'prfnm', 'pren']);
        $select->join(['hierarchy'      => new TableIdentifier("table(" . $subProxy->getSqlString($this->sql->getAdapter()->platform) . ")")], "hierarchy.EMPLOYEE_NUMBER = employee.PREN and '002' = employee.PRER", [/*'EMPLOYEE_NUMBER',*/ 'DIRECT_MANAGER_EMPLOYEE_NUMBER', 'DIRECT_INDIRECT', 'MANAGER_LEVEL']);
        $select->join(['status'         => 'timeoff_request_statuses'], "status.REQUEST_STATUS = request.REQUEST_STATUS", ['description']);

        $where = new Where();

        /* create outer select to incorporate row_number */
        $outerSelect = $this->sql->select();

        /* define inner select as table identifier */
        $outerSelect->from(['outerselect' => new TableIdentifier("(" . $select->getSqlString($this->sql->getAdapter()->platform) . ")")]);

        /* implement status filter if selected */
        if (array_key_exists('columns', $data) &&
            array_key_exists('2', $data['columns']) &&
            trim($data['columns'][2]['search']['value']) != "" &&
            trim($data['columns'][2]['search']['value']) !== "All" ) {
            $where->equalTo('DESCRIPTION', $data['columns'][2]['search']['value']);
        }

        /* filter by search */
        if (array_key_exists('search', $data) && trim($data['search']['value']) !== '') {
            $where->and->nest->like('EMPLOYEE_NUMBER', '%' . $data['search']['value'] . '%')
                  ->or->like('EMPLOYEE_FIRST_NAME', '%' . strtoupper($data['search']['value']) . '%')
                  ->or->like('EMPLOYEE_LAST_NAME', '%' . strtoupper($data['search']['value']) . '%')
                  ->or->like('APPROVER_QUEUE', '%' . strtoupper($data['search']['value']) . '%')
                  ->or->like('REQUEST_STATUS_DESCRIPTION', '%' . strtoupper($data['search']['value']) . '%')
                  ->or->like('requested_hours', '%' . strtoupper($data['search']['value']) . '%')
                  ->or->like('upper(request_reason)', '%' . strtoupper($data['search']['value']) . '%')
                  ->or->like("convert_from_date(min_date_requested, 'mm/dd/yyyy')", '%' . strtoupper($data['search']['value']) . '%')
                  ->unnest;
        }

        /* attach where clause */
        $outerSelect->where($where);

        /* set starting point for pagination */
        if (array_key_exists('start', $data)) {
            $outerSelect->offset($data['start']);
        }

        /* set length of pagination */
        if (array_key_exists('length', $data)) {
            $outerSelect->limit($data['length']);
        }

        /* define sort order and direction */
        if (array_key_exists('order', $data)) {
            if ('P' == $data['columns'][0]['search']['value']) {
                $outerSelect->order([
                    $data['columns'][7]['data'] . (trim($data['columns'][3]['search']['value']) == '' ? " ASC" : " " . $data['columns'][3]['search']['value']),
                    $data['columns'][$data['order'][0]['column']]['data'] . " " . $data['order'][0]['dir']
                ]);
            } else {
                $outerSelect->order($data['columns'][$data['order'][0]['column']]['data'] . " " . $data['order'][0]['dir']);
            }
        }

        /* prepare the sql statement for execution */
        $statement = $this->sql->prepareStatementForSqlObject($outerSelect);

        /* execute the sql statement */
        $result = $statement->execute();

        /* check for results and return */
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            /* define new result set */
            $resultSet = new ResultSet();

            /* initialize result set */
            $resultSet->initialize($result);

            /* resurn result as array */
            return $resultSet->toArray();
        }

        /* return empty array */
        return [];

    }

    /**
     *
     * @param array $data
     * @param array $proxyFor
     * @param array $statuses
     */
    public function getProxyEmployeeRequestsCount( $data = null, $filtered, $proxyFor = null, $statuses = [] )
    {
        /* define a new sql object */
        $this->sql = new Sql($this->adapter);

        /* set attribute */
        $this->proxyFor = $proxyFor;

        /* set sql to select */
        $select = $this->sql->select();

        $select->from(['request' => 'timeoff_requests']);

        /* sub query for requested hours */
        $requestedHours = $this->prepareRequestedHoursSql();

        /* sub query for first day */
        $minDateRequested = $this->prepareMinDateRequestedSql();

        /* sub query for last day */
        $maxDateRequested = $this->prepareMaxDateRequestedSql();

        /* prepare case statement to simplify the sql statement */
        $approverQueue = new Expression("case when status.description in('Pending Manager Approval') then TRIM(manager_addons.PRLNM) CONCAT ', ' CONCAT TRIM(manager_addons.PRFNM) CONCAT ' (' CONCAT TRIM(manager_addons.PREN) CONCAT ')' else CASE WHEN status.DESCRIPTION IN('Pending Payroll Approval','Update Checks') THEN 'PAYROLL' ELSE '' END end");

        /* define columns and integrate sub queries */
        $select->columns(
            [
                'REQUEST_ID'            => 'request_id',
                'EMPLOYEE_NUMBER'       => new Expression("trim(request.EMPLOYEE_NUMBER)"),
                'REQUEST_REASON'        => 'request_reason',
                'REQUEST_STATUS_DESCRIPTION' => new Expression('status.description'),
                'REQUESTED_HOURS'       => $requestedHours,
                'MIN_DATE_REQUESTED'    => $minDateRequested,
                'MAX_DATE_REQUESTED'    => $maxDateRequested,
                'EMPLOYEE_DESCRIPTION'  => new Expression("TRIM(employee.PRLNM) CONCAT ', ' CONCAT TRIM(employee.PRFNM) CONCAT ' (' CONCAT TRIM(employee.PREN) CONCAT ')'"),
                'EMPLOYEE_FIRST_NAME'   => new Expression("trim(employee.prfnm)"),
                'EMPLOYEE_LAST_NAME'    => new Expression("trim(employee.prlnm)"),
                'APPROVER_QUEUE'        => $approverQueue,
                'MANAGER_EMAIL_ADDRESS' => new Expression('trim(manager_addons.preml1)')
            ]);

        $this->managerReportsType = 'P';

        if (array_key_exists( 'columns', $data ) && $data['columns'][0]['search']['value'] !== "P" ) {
            $this->managerReportType = trim($data['columns'][0]['search']['value']) != '' ? $data['columns'][0]['search']['value'] : 'B';
            $this->proxyFor = [$data['employeeNumber']];
        }

        /* do we have proxies provided */
        if (!empty($this->proxyFor)) {
            $subProxy = $this->prepareProxySql();

            /* in case we have more proxies */
            foreach ($this->proxyFor as $proxy) {
                $subProxy->combine($this->prepareProxyUnionSql( $proxy ), 'UNION ALL');
            }
        }

        /* add joins to the select */
        $select->join(['employee'       => 'prpms'], "employee.PREN = request.EMPLOYEE_NUMBER and employee.PRER = '002'", ['prlnm', 'prfnm', 'pren']);
        $select->join(['manager'        => 'prpsp'], "employee.PREN = manager.SPEN and employee.PRER = manager.SPER", ['sper', 'spen', 'spspen']);
        $select->join(['manager_addons' => 'prpms'], "manager_addons.PREN = manager.SPSPEN and manager_addons.PRER = manager.SPSPER", ['preml1', 'prlnm', 'prfnm', 'pren']);
        $select->join(['hierarchy'      => new TableIdentifier("table(" . $subProxy->getSqlString($this->sql->getAdapter()->platform) . ")")], "hierarchy.EMPLOYEE_NUMBER = employee.PREN and '002' = employee.PRER", [/*'EMPLOYEE_NUMBER',*/ 'DIRECT_MANAGER_EMPLOYEE_NUMBER', 'DIRECT_INDIRECT', 'MANAGER_LEVEL']);
        $select->join(['status'         => 'timeoff_request_statuses'], "status.REQUEST_STATUS = request.REQUEST_STATUS", ['description']);

        /* create outer select to incorporate row_number */
        $outerSelect = $this->sql->select();

        /* define inner select as table identifier */
        $outerSelect->from(['outerselect' => new TableIdentifier("(" . $select->getSqlString($this->sql->getAdapter()->platform) . ")")]);

        $outerSelect->columns(['RCOUNT' => new Expression('count(*)')]);

        /* define new where clause */
        $where = new Where();

        /* filter by status if selected */
        if (array_key_exists('columns', $data) &&
            array_key_exists('2', $data['columns']) &&
            trim($data['columns'][2]['search']['value']) != "" &&
            trim($data['columns'][2]['search']['value']) !== "All" ) {
            $where->equalTo('DESCRIPTION', $data['columns'][2]['search']['value']);
        }

        /* implement text search if provided */
        if (array_key_exists('search', $data) && trim($data['search']['value']) !== '' && $filtered == true) {
            $where->and->nest->like('EMPLOYEE_NUMBER', '%' . $data['search']['value'] . '%')
                  ->or->like('EMPLOYEE_FIRST_NAME', '%' . strtoupper($data['search']['value']) . '%')
                  ->or->like('EMPLOYEE_LAST_NAME', '%' . strtoupper($data['search']['value']) . '%')
                  ->or->like('APPROVER_QUEUE', '%' . strtoupper($data['search']['value']) . '%')
                  ->or->like('REQUEST_STATUS_DESCRIPTION', '%' . strtoupper($data['search']['value']) . '%')
                  ->or->like('requested_hours', '%' . strtoupper($data['search']['value']) . '%')
                  ->or->like('upper(request_reason)', '%' . strtoupper($data['search']['value']) . '%')
                  ->or->like("convert_from_date(min_date_requested, 'mm/dd/yyyy')", '%' . strtoupper($data['search']['value']) . '%')
                  ->unnest;
        }

        /* attach where clause */
        $outerSelect->where($where);

        /* prepare the sql statement for execution */
        $statement = $this->sql->prepareStatementForSqlObject($outerSelect);

        /* execute the sql statement */
        $result = $statement->execute();

        /* check for results and return */
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            /* define new result set */
            $resultSet = new ResultSet();

            /* initialize result set */
            $resultSet->initialize($result);

            /* resurn result as array */
            return $resultSet->toArray()[0]['RCOUNT'];
        }

        /* return empty array */
        return [];

    }

    private function prepareProxySql()
    {
        $proxy = array_shift($this->proxyFor);

        $subProxy = $this->sql->select();

        $subProxy->from(['data' => new TableIdentifier("table(GET_MANAGER_EMPLOYEES('002', '" . $proxy . "', '" . $this->managerReportType . "'))")]);

        $subProxy->columns(
            [
                'EMPLOYEE_NUMBER' => 'EMPLOYEE_ID',
                'DIRECT_MANAGER_EMPLOYEE_NUMBER' => new Expression('TRIM(DIRECT_MANAGER_EMPLOYEE_ID)'),
                'DIRECT_INDIRECT',
                'MANAGER_LEVEL',
                'MANAGER_EMPLOYEE_ID_COMPLETE' => new Expression("GET_EMPLOYEE_COMMON_NAME('002', MANAGER_EMPLOYEE_ID)")
            ]);

        return $subProxy;
    }

    private function prepareProxyUnionSql( $proxy )
    {
        $union = $this->sql->select();

        $union->from(['data' => new TableIdentifier("table(GET_MANAGER_EMPLOYEES('002', '" . $proxy . "', '" . $this->managerReportType . "'))")]);

        $union->columns(
            [
                'EMPLOYEE_NUMBER' => 'EMPLOYEE_ID',
                'DIRECT_MANAGER_EMPLOYEE_NUMBER' => new Expression('TRIM(DIRECT_MANAGER_EMPLOYEE_ID)'),
                'DIRECT_INDIRECT',
                'MANAGER_LEVEL',
                'MANAGER_EMPLOYEE_ID_COMPLETE' => new Expression("GET_EMPLOYEE_COMMON_NAME('002', MANAGER_EMPLOYEE_ID)")
            ]);

        return $union;
    }

    /**
     *
     * @return \Zend\Db\Sql\Select
     */
    private function prepareRequestedHoursSql()
    {
        $requestedHours = $this->sql->select();
        $requestedHours->from(['entry' => 'timeoff_request_entries']);
        $requestedHours->columns(['sum_requested_hours' => new Expression('sum(requested_hours)')]);
        $requestedHoursWhere = new Where();
        $requestedHoursWhere->equalTo('entry.request_id', new Expression('request.request_id'))
                            ->and->equalTo('IS_DELETED', '0');
        $requestedHours->where($requestedHoursWhere);

        return $requestedHours;
    }

    /**
     *
     * @return \Zend\Db\Sql\Select
     */
    private function prepareMinDateRequestedSql()
    {
        $minDateRequested = $this->sql->select();
        $minDateRequested->from(['entry' => 'timeoff_request_entries']);
        $minDateRequested->columns(['min_request_date' => new Expression('min(request_date)')]);
        $minDateRequestedWhere = new Where();
        $minDateRequestedWhere->equalTo('entry.request_id', new Expression('request.request_id'))
                              ->and->equalTo('IS_DELETED', '0');
        $minDateRequested->where($minDateRequestedWhere);

        return $minDateRequested;
    }

    /**
     *
     * @return \Zend\Db\Sql\Select
     */
    private function prepareMaxDateRequestedSql()
    {
        $maxDateRequested = $this->sql->select();
        $maxDateRequested->from(['entry' => 'timeoff_request_entries']);
        $maxDateRequested->columns(['max_request_date' => new Expression('max(request_date)')]);
        $maxDateRequestedWhere = new Where();
        $maxDateRequestedWhere->equalTo('entry.request_id', new Expression('request.request_id'))
                              ->and->equalTo('IS_DELETED', '0');
        $maxDateRequested->where($maxDateRequestedWhere);

        return $maxDateRequested;
    }

    /**
     * Get Pending Manager Approval Queue data to display in data table.
     *
     * @param array $data   $data = [ 'employeeData' => 'xxxxxxxxx' ];
     * @return array
     */
    public function getManagerEmployeeRequests( $data = null, $proxyFor = null, $statuses = [] ) {
        $managerReportsType = ( array_key_exists( 'columns', $data ) && $data['columns'][0]['search']['value']!="" ? $data['columns'][0]['search']['value'] : "D" );
        $whereStatusStatement = "";
        $whereStatusStatement = ( ( array_key_exists( 'columns', $data ) && array_key_exists( '2', $data['columns'] ) &&
                                    $data['columns'][2]['search']['value']!="" && $data['columns'][2]['search']['value']!="All" ) ?
                                  "WHERE status.DESCRIPTION = '" . $data['columns'][2]['search']['value'] . "'" : "" );
        if( !empty( $statuses ) ) {
            $whereStatusStatement .= ( !empty( $whereStatusStatement ) ? " AND " : " WHERE " ) . "request.REQUEST_STATUS IN('" . implode( "','", $statuses ) . "') ";
        }

        $careGetManagerEmployees = "SELECT
        EMPLOYEE_ID AS EMPLOYEE_NUMBER,
        TRIM(DIRECT_MANAGER_EMPLOYEE_ID) AS DIRECT_MANAGER_EMPLOYEE_NUMBER,
        DIRECT_INDIRECT,
        MANAGER_LEVEL
        FROM table (
            GET_MANAGER_EMPLOYEES('002', '" . $data['employeeNumber'] . "', '" . $managerReportsType . "')
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
                    "GET_MANAGER_EMPLOYEES('002', '" . $proxy . "', '" . $managerReportsType . "') " .
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
                    CASE WHEN status.DESCRIPTION IN('Pending Payroll Approval','Update Checks') THEN 'PAYROLL' ELSE '' END
                END
                as APPROVER_QUEUE,
                TRIM(manager_addons.PREML1) AS MANAGER_EMAIL_ADDRESS
            FROM TIMEOFF_REQUESTS request
            INNER JOIN PRPMS employee ON employee.PREN = request.EMPLOYEE_NUMBER and employee.PRER = '002'
            INNER JOIN PRPSP manager ON employee.PREN = manager.SPEN and employee.PRER = manager.SPER
            INNER JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN and manager_addons.PRER = manager.SPSPER
            INNER JOIN table (" .
            $careGetManagerEmployees .
            ") hierarchy
                ON hierarchy.EMPLOYEE_NUMBER = employee.PREN and '002' = employee.PRER
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
// var_dump($rawSql);
        $employeeData = \Request\Helper\ResultSetOutput::getResultArrayFromRawSql( $this->adapter, $rawSql );

        return $employeeData;
    }
}