<?php

namespace Simpler\Model;

class Example extends AbstractAdapterAware
{

    public function exampleMethod()
    {
        $sql    = new Sql($this->db);
        $select = $sql->select('posts');
        $select->where(['id = ?' => 5]);

        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        $currentResult = $result->current();

        var_dump($currentResult);exit();

        $resultIsArray = true;
        if( is_array($currentResult) === false ) {
            $resultIsArray = false;
        }

        if ($result instanceof ResultInterface && $result->isQueryResult() && $result->getAffectedRows() &&
            $resultIsArray
           ) {
            return $this->hydrator->hydrate($currentResult, $this->postPrototype);
        }
    }

}
