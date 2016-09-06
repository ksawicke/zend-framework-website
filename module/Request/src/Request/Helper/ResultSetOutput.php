<?php
namespace Request\Helper;

use Zend\Db\Sql\Sql;
// use Zend\Db\Sql\Update;
use Zend\Db\ResultSet\ResultSet;

class ResultSetOutput
{

    public static function getResultRecord($sql, $select)
    {
        try {
            $statement = $sql->prepareStatementForSqlObject($select);
        } catch(\Exception $e) {
            throw new \Exception( "The following error has occurred: " . $e->getMessage() );
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
        } catch(\Exception $e) {
            throw new \Exception( "The following error has occurred: " . $e->getMessage() );
        }

        $result = $statement->execute();

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        return $resultSet->toArray();

//        $array = [];
//        foreach($resultSet as $row) {
//            $array[] = $row;
//        }
//
//        return $array;
    }

    public static function getResultArrayFromRawSql( $dbAdapter, $rawSql )
    {
        try {
            $statement = $dbAdapter->createStatement( $rawSql );
            $result = $statement->execute();
            $resultSet = new ResultSet;
            $resultSet->initialize( $result );
        } catch( \Exception $ex ) {
            throw new \Exception( "The following error has occurred: " . $e->getMessage() );
        }

        $array = [];
        foreach($resultSet as $row) {
            $array[] = $row;
        }

        return $array;
    }

    public static function getResultRecordFromRawSql($dbAdapter, $rawSql)
    {
        try {
            $statement = $dbAdapter->createStatement($rawSql);
        } catch(\Exception $e) {
            throw new \Exception( "The following error has occurred: " . $e->getMessage() );
        }

        $result = $statement->execute();

        $resultSet = new ResultSet;
        $resultSet->initialize($result);
//        return $resultSet->toArray();
        return $resultSet->current();
    }

    public static function executeRawSql($dbAdapter, $rawSql)
    {
        try {
            $statement = $dbAdapter->createStatement($rawSql);
        } catch(\Exception $e) {
            throw new \Exception( "The following error has occurred: " . $e->getMessage() );
        }

        $result = $statement->execute();

        return $result;
    }
}
