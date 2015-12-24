<?php
namespace Request\Helper;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;
use Zend\Db\ResultSet\ResultSet;

class ResultSetOutput
{

    public static function getResultRecord($sql, $select)
    {
        $stmt = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        $resultSet = new ResultSet();
        $resultSet->initialize($result);

        return $resultSet->current();
    }

    public static function getResultArray($sql, $select)
    {
        try {
            $stmt = $sql->prepareStatementForSqlObject($select);
        } catch(Exception $e) {
            var_dump($e);
        }
//         var_dump($stmt);exit();
        $result = $stmt->execute();

        $resultSet = new ResultSet();
        $resultSet->initialize($result);

        $array = [];
        foreach($resultSet as $row) {
            $array[] = $row;
        }

        return $array;
    }
}
