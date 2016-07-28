<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Request\Model;

// use Request\Model\RequestInterface;
// use Zend\Db\Adapter\AdapterInterface;
// use Zend\Db\Adapter\Driver\ResultInterface;
// use Zend\Db\ResultSet\HydratingResultSet;
// use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
// use Zend\Db\Sql\Update;
// use Zend\Db\Sql\Expression;
// use Zend\Db\ResultSet\ResultSet;
// use Zend\Stdlib\Hydrator\HydratorInterface;
// use Zend\Stdlib\Hydrator\NamingStrategy\ArrayMapNamingStrategy;

/**
 * Manages log entries for Time Off Requests.
 *
 * @author sawik
 */
class TimeoffRequestLog extends BaseDB {

    protected $requestId;
    protected $employeeNumber;
    protected $comment;
    protected $isPayroll;

    public function logEntry( $requestId = null, $employeeNumber = null, $comment = null, $isPayroll = "N" ) {

        if ($this->requestId !== null) { $requestId = $this->requestId; }
        if ($this->employeeNumber !== null) { $employeeNumber = $this->employeeNumber; }
        if ($this->comment !== null) { $comment = $this->comment; }
        if ($this->isPayroll !== null) { $isPayroll = $this->isPayroll; }

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

    /**
     * @param field_type $requestId
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
    }

    /**
     * @param field_type $employeeNumber
     */
    public function setEmployeeNumber($employeeNumber)
    {
        $this->employeeNumber = $employeeNumber;
    }

    /**
     * @param field_type $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @param field_type $isPayroll
     */
    public function setIsPayroll($isPayroll)
    {
        $this->isPayroll = $isPayroll;
    }

}
