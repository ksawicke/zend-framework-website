<?php
namespace Request\Helper;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;
use Zend\Db\ResultSet\ResultSet;

class ResultSetOutput
{

    public static function getResultRecord($sql, $select)
    {
        try {
            $statement = $sql->prepareStatementForSqlObject($select);
        } catch(Exception $e) {
            var_dump($e);
        }
//         echo $select->getSqlString();exit();
//         var_dump($stmt);exit();
        $result = $statement->execute();

        $resultSet = new ResultSet();
        $resultSet->initialize($result);

        return $resultSet->current();
    }

    public static function getResultArray($sql, $select)
    {
        try {
            $statement = $sql->prepareStatementForSqlObject($select);
        } catch(Exception $e) {
            var_dump($e);
        }
        
        $result = $statement->execute();

        $resultSet = new ResultSet();
        $resultSet->initialize($result);

        $array = [];
        foreach($resultSet as $row) {
            $array[] = $row;
        }

        return $array;
    }
    
    public static function getResultArrayFromRawSql($dbAdapter, $rawSql)
    {
        try {
            $statement = $dbAdapter->createStatement($rawSql);
        } catch(Exception $e) {
            var_dump($e);
        }
        
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        
        $array = [];
        foreach($resultSet as $row) {
            $array[] = $row;
        }
        
        return $array;
    }
    
    public static function executeRawSql($dbAdapter, $rawSql)
    {
        try {
            $statement = $dbAdapter->createStatement($rawSql);
        } catch(Exception $e) {
            var_dump($e);
        }
    
        $result = $statement->execute();
    
        return $result;
    }
}
