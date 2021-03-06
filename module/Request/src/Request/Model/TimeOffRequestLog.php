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
        /** 1. Grab original data **/
        $sql = new Sql( $this->adapter );
        $select = $sql->select(['log' => 'TIMEOFF_REQUEST_LOG'])
            ->columns(['EMPLOYEE_NUMBER' => 'EMPLOYEE_NUMBER', 'COMMENT' => 'COMMENT' ])
            ->where(['log.REQUEST_LOG_ID' => $post->requestLogId]);
        $originalEntryData = \Request\Helper\ResultSetOutput::getResultRecord($sql, $select);
        
        /** 2. Archive the original comment **/
        $logArchiveEntry = new Insert( 'timeoff_request_log_archive' );
        $logArchiveEntry->values( [
            'EMPLOYEE_NUMBER' => $originalEntryData->EMPLOYEE_NUMBER,
            'COMMENT' => $originalEntryData->COMMENT,
            'REQUEST_LOG_ID' => $post->requestLogId
        ] );
        $sql = new Sql( $this->adapter );
        $stmt = $sql->prepareStatementForSqlObject( $logArchiveEntry );
        try {
            $result = $stmt->execute();
        } catch ( Exception $e ) {
            throw new \Exception( "Can't execute statement: " . $e->getMessage() );
        }
        
        /** 3. Edit the comment **/
        $rawSql = "UPDATE timeoff_request_log SET " .
            "COMMENT = '" . $post->updatedCommentText . "' WHERE REQUEST_LOG_ID = '" . $post->requestLogId . "'";
        \Request\Helper\ResultSetOutput::executeRawSql( $this->adapter, $rawSql );
    }

}
