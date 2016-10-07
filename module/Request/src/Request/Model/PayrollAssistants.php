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
 * All Database functions for Payroll Assistants
 *
 * @author sawik
 *
 */
class PayrollAssistants extends BaseDB {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get count of Payroll Assistant data
     *
     * @param array $data   $data = [ 'employeeData' => 'xxxxxxxxx' ];
     * @return int
     */
    public function countPayrollAssistantItems( $data = null, $isFiltered = false )
    {
        $rawSql = "SELECT COUNT(*) AS RCOUNT
        FROM TIMEOFF_REQUESTS_PAYROLL_ASSISTANTS";
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

        $payrollAssistantData = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql( $this->adapter, $rawSql );

        return (int) $payrollAssistantData['RCOUNT'];
    }

    public function getPayrollAssistants( $post )
    {
        $payrollAssistantData = [];
        $rawSql = "SELECT trim(pa.EMPLOYEE_NUMBER) as EMPLOYEE_NUMBER,
            TRIM(employee.PRLNM) CONCAT ', ' CONCAT TRIM(employee.PRCOMN) CONCAT ' (' CONCAT TRIM(employee.PREN) CONCAT ')' as EMPLOYEE_DESCRIPTION,
            pa.STATUS
            FROM TIMEOFF_REQUESTS_PAYROLL_ASSISTANTS pa
            LEFT JOIN PRPMS employee ON TRIM(employee.PREN) = trim(pa.EMPLOYEE_NUMBER) and TRIM(employee.PRER) = '002'
            ORDER BY employee.PRLNM ASC";

        $statement = $this->adapter->query( $rawSql );
        $result = $statement->execute();

        if ( $result instanceof ResultInterface && $result->isQueryResult() ) {
            $resultSet = new ResultSet;
            $resultSet->initialize( $result );

            foreach( $resultSet as $field => $proxyEmployeeNumber ) {
                $payrollAssistantData[] = $proxyEmployeeNumber;
            }
        }

        return $payrollAssistantData;
    }

    /**
     * Adds a Payroll Assistant to be able to submit time off requests for a designated employee.
     *
     * @param type $post
     * @throws \Exception
     */
    public function addPayrollAssistant( $post ) {
        $payrollAssistant = new Insert( 'timeoff_requests_payroll_assistants' );
        $payrollAssistant->values( [
            'EMPLOYEE_NUMBER' => \Request\Helper\Format::rightPadEmployeeNumber( $post->EMPLOYEE_NUMBER ),
            'CREATED_BY' => \Request\Helper\Format::rightPadEmployeeNumber( $post->CREATED_BY )
        ] );
        $sql = new Sql( $this->adapter );
        $stmt = $sql->prepareStatementForSqlObject( $payrollAssistant );
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
    public function deletePayrollAssistant( $post ) {
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
        $delete->from('TIMEOFF_REQUESTS_PAYROLL_ASSISTANTS');

        /**
         * define sql WHERE
         */
        $delete->where(array(
            'EMPLOYEE_NUMBER' => \Request\Helper\Format::rightPadEmployeeNumber( $post->PAYROLLASSISTANT_EMPLOYEE_NUMBER )
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

    public function togglePayrollAssistant( $post )
    {
        $rawSql = "UPDATE TIMEOFF_REQUESTS_PAYROLL_ASSISTANTS SET STATUS = '" . $post->STATUS . "' WHERE " .
                  "TRIM(EMPLOYEE_NUMBER) = " . $post->PAYROLLASSISTANT_EMPLOYEE_NUMBER;

//        die( $rawSql );

        $payrollAssistantData = \Request\Helper\ResultSetOutput::executeRawSql($this->adapter, $rawSql);

        return $payrollAssistantData;
    }

}
