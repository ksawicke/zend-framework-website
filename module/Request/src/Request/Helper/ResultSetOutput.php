<?php
namespace Request\Helper;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;
use Zend\Db\ResultSet\ResultSet;

class ResultSetOutput
{

    public static function getResultObject($sql, $select)
    {
        $stmt = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        $resultSet = new ResultSet();
        $resultSet->initialize($result);

        return $resultSet->current();
    }
}
