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
 * All Database functions for employee proxies
 *
 * @author sawik
 *
 */
class EmployeeProxies extends BaseDB {

    public function __construct() {
        parent::__construct();
    }
    
    public function getProxies( $post )
    {
        $proxyData = [];
        $rawSql = "SELECT trim(p.PROXY_EMPLOYEE_NUMBER) as PROXY_EMPLOYEE_NUMBER,
            TRIM(employee.PRLNM) CONCAT ', ' CONCAT TRIM(employee.PRCOMN) CONCAT ' (' CONCAT TRIM(employee.PREN) CONCAT ')' as EMPLOYEE_DESCRIPTION
            FROM TIMEOFF_REQUEST_EMPLOYEE_PROXIES p
            LEFT JOIN PRPMS employee ON TRIM(employee.PREN) = trim(p.PROXY_EMPLOYEE_NUMBER)
            WHERE
               trim(p.EMPLOYEE_NUMBER) = '" . $post->EMPLOYEE_NUMBER . "'
            ORDER BY employee.PRLNM ASC";
        
        $statement = $this->adapter->query( $rawSql );
        $result = $statement->execute();

        if ( $result instanceof ResultInterface && $result->isQueryResult() ) {
            $resultSet = new ResultSet;
            $resultSet->initialize( $result );
            
            foreach( $resultSet as $field => $proxyEmployeeNumber ) {
                $proxyData[] = $proxyEmployeeNumber;
            }
        }

        return $proxyData;
    }
    
    /**
     * Adds a proxy to be able to submit time off requests for a designated employee.
     * 
     * @param type $post
     * @throws \Exception
     */
    public function addProxy( $post ) {
        $employeeProxy = new Insert( 'timeoff_request_employee_proxies' );
        $employeeProxy->values( [
            'EMPLOYEE_NUMBER' => \Request\Helper\Format::rightPadEmployeeNumber( $post->EMPLOYEE_NUMBER ),
            'PROXY_EMPLOYEE_NUMBER' => \Request\Helper\Format::rightPadEmployeeNumber( $post->PROXY_EMPLOYEE_NUMBER )
        ] );
        $sql = new Sql( $this->adapter );
        $stmt = $sql->prepareStatementForSqlObject( $employeeProxy );
        try {
            $result = $stmt->execute();
        } catch ( Exception $e ) {
            throw new \Exception( "Can't execute statement: " . $e->getMessage() );
        }
    }
    
    public function deleteProxy( $post ) {
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
        $delete->from('TIMEOFF_REQUEST_EMPLOYEE_PROXIES');

        /**
         * define sql WHERE
         */
        $delete->where(array(
            'EMPLOYEE_NUMBER' => \Request\Helper\Format::rightPadEmployeeNumber( $post->EMPLOYEE_NUMBER ),
            'PROXY_EMPLOYEE_NUMBER' => \Request\Helper\Format::rightPadEmployeeNumber( $post->PROXY_EMPLOYEE_NUMBER )
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
    
}
