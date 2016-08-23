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
 * All Database functions for Payroll Admins
 *
 * @author sawik
 *
 */
class PayrollAdmins extends BaseDB {

    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get count of Payroll Admin data
     * 
     * @param array $data   $data = [ 'employeeData' => 'xxxxxxxxx' ];
     * @return int
     */
    public function countPayrollAdminItems( $data = null, $isFiltered = false )
    {
        $rawSql = "SELECT COUNT(*) AS RCOUNT       
        FROM TIMEOFF_REQUESTS_PAYROLL_ADMINS";
        // INNER JOIN HRDBFA.PRPMS employee ON employee.PREN = p.PROXY_EMPLOYEE_NUMBER
        
//        $where = [];
//        $where[] = "trim(p.EMPLOYEE_NUMBER) = '" . $data['employeeNumber'] . "'";
//            
//        if( $isFiltered ) {
//            if( array_key_exists( 'search', $data ) && !empty( $data['search']['value'] ) ) {
//                $where[] = "( employee.PREN LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
//                              employee.PRFNM LIKE '%" . strtoupper( $data['search']['value'] ) . "%' OR
//                              employee.PRLNM LIKE '%" . strtoupper( $data['search']['value'] ) . "%' 
//                            )";
//            }
//        }
//        $rawSql .=  " WHERE " . implode( " AND ", $where );
        
        $payrollAdminData = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql( $this->adapter, $rawSql );

        return (int) $payrollAdminData['RCOUNT'];
    }
    
    public function getPayrollAdmins( $post )
    {
        $payrollAdminData = [];
        $rawSql = "SELECT trim(pa.EMPLOYEE_NUMBER) as EMPLOYEE_NUMBER,
            TRIM(employee.PRLNM) CONCAT ', ' CONCAT TRIM(employee.PRCOMN) CONCAT ' (' CONCAT TRIM(employee.PREN) CONCAT ')' as EMPLOYEE_DESCRIPTION,
            pa.STATUS
            FROM TIMEOFF_REQUESTS_PAYROLL_ADMINS pa
            LEFT JOIN PRPMS employee ON TRIM(employee.PREN) = trim(pa.EMPLOYEE_NUMBER)
            ORDER BY employee.PRLNM ASC";
                
        $statement = $this->adapter->query( $rawSql );
        $result = $statement->execute();

        if ( $result instanceof ResultInterface && $result->isQueryResult() ) {
            $resultSet = new ResultSet;
            $resultSet->initialize( $result );
            
            foreach( $resultSet as $field => $proxyEmployeeNumber ) {
                $payrollAdminData[] = $proxyEmployeeNumber;
            }
        }

        return $payrollAdminData;
    }
    
    /**
     * Adds a Payroll Admin to be able to submit time off requests for a designated employee.
     * 
     * @param type $post
     * @throws \Exception
     */
    public function addPayrollAdmin( $post ) {
        $payrollAdmin = new Insert( 'timeoff_requests_payroll_assistants' );
        $payrollAdmin->values( [
            'EMPLOYEE_NUMBER' => \Request\Helper\Format::rightPadEmployeeNumber( $post->EMPLOYEE_NUMBER ),
            'CREATED_BY' => \Request\Helper\Format::rightPadEmployeeNumber( $post->CREATED_BY )
        ] );
        $sql = new Sql( $this->adapter );
        $stmt = $sql->prepareStatementForSqlObject( $payrollAdmin );
        try {
            $result = $stmt->execute();
        } catch ( Exception $e ) {
            throw new \Exception( "Can't execute statement: " . $e->getMessage() );
        }
    }
    
    /**
     * Deletes a proxy for a designated employee.
     * 
     * @param type $post
     * @return type
     */
    public function deletePayrollAdmin( $post ) {
        /**
         * instantiate new SQL adapter
         */
        $sql = new Sql($this->adapter);

        /**
         * prepare new sql DELETE
         */
        $delete = $sql->delete();

        /**
         * define sql FROM
         */
        $delete->from('TIMEOFF_REQUESTS_PAYROLL_ADMINS');

        /**
         * define sql WHERE
         */
        $delete->where(array(
            'EMPLOYEE_NUMBER' => \Request\Helper\Format::rightPadEmployeeNumber( $post->PAYROLLADMIN_EMPLOYEE_NUMBER )
        ));

        /**
         * prepare SQL execution
         */
        $statement = $sql->prepareStatementForSqlObject($delete);

        /**
         * execute SQL
         */
        $result = $statement->execute();

        /**
         * analyze result set
         */
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet();
            $resultSet->initialize($result);
            return $resultSet->toArray();
        }

        return array();
    }
    
    public function togglePayrollAdmin( $post )
    {
        $rawSql = "UPDATE TIMEOFF_REQUESTS_PAYROLL_ADMINS SET STATUS = '" . $post->STATUS . "' WHERE " .
                  "TRIM(EMPLOYEE_NUMBER) = " . $post->PAYROLLADMIN_EMPLOYEE_NUMBER;

//        die( $rawSql );
        
        $payrollAdminData = \Request\Helper\ResultSetOutput::executeRawSql($this->adapter, $rawSql);        

        return $payrollAdminData;
    }
    
}
