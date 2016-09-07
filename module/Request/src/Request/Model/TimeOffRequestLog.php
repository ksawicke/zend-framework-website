<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Request\Model;

use Request\Model\RequestInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Expression;
use Zend\Db\ResultSet\ResultSet;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\NamingStrategy\ArrayMapNamingStrategy;

/**
 * Manages log entries for Time Off Requests.
 *
 * @author sawik
 */
class TimeoffRequestLog extends BaseDB {

    public function logEntry( $requestId = null, $employeeNumber = null, $comment = null, $isPayroll = "N" ) {
        $commentType = ( $isPayroll=="Y" ? "P" : "S" );
        $logEntry = new Insert( 'timeoff_request_log' );
        $logEntry->values( [
            'REQUEST_ID' => $requestId,
            'EMPLOYEE_NUMBER' => \Request\Helper\Format::rightPadEmployeeNumber( $employeeNumber ),
            'COMMENT' => $comment,
            'COMMENT_TYPE' => $commentType
        ] );
        $sql = new Sql( $this->adapter );
        $stmt = $sql->prepareStatementForSqlObject( $logEntry );
        try {
            $result = $stmt->execute();
        } catch ( Exception $e ) {
            throw new \Exception( "Can't execute statement: " . $e->getMessage() );
        }
    }
    
    public function editLogEntry( $post ) {
        $rawSql = "UPDATE TIMEOFF_REQUEST_LOG SET STATUS = '" . $post->STATUS . "' WHERE " .
            "TRIM(EMPLOYEE_NUMBER) = " . $post->PAYROLLASSISTANT_EMPLOYEE_NUMBER;        
        $payrollAssistantData = \Request\Helper\ResultSetOutput::executeRawSql($this->adapter, $rawSql);
        
        return $payrollAssistantData;
    }

}
