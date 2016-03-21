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
 * Description of TimeOffRequestLog
 *
 * @author sawik
 */
class TimeOffRequestLog extends BaseDB {

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

}
