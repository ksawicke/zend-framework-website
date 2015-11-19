<?php
namespace Joinexample\Model;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql\Select;

class LetterGroupModel extends TableGateway
{
    public function fetchAll()
    {
        return $this->select()->toArray();
    }

    /**
     * Placeholder for manual creation of raw SLQ query string
     * @param string $group
     * @param string $section
     */
    public function sqlFetchJoinLetterMonitor($group = null, $section = null)
    {
        $sql = '';
        $this->getAdapter()->getDriver()->getConnection()->execute($sql);
    }

    public function fetchJoinLetterMonitor($group = null, $section = null)
    {
        $sql = $this->getSql();
        $select = $sql->select()
//            ->from($this->table)
            ->columns(['Group' => 'LGGRP', 'Section' => 'LGSEC', 'Option' => 'LGOPT','Amount' => 'LGAMT'])
            ->join('CHOICE.LTRMON', 'POPDTALIB.LTRGRP.LGGRP = CHOICE.LTRMON.LMGRP and POPDTALIB.LTRGRP.LGSEC = CHOICE.LTRMON.LMSEC',
                   ['Letter_1' => 'LML1DT', 'Letter_2' => 'LML2DT' , 'Letter_3' => 'LML3DT', 'Reminder' => 'LMRDT', 'Suspended' => 'LMSDT']);
// con't experimenting            ->join('CHOICE/LTRMON', 'POPDTALIB/LTRGRP.LGGRP = CHOICE/LTRMON.LMGRP and POPDTALIB/LTRGRP.LGSEC = CHOICE/LTRMON.LMSEC'/*, ['LMSEC']*/);
        //            ->where(['POPDTALIB.LTRGRP.LGGRP' => $group, 'POPDTALIB.LTRGRP.LGSEC' => $section]); // need to ensure these user input values are sanitized

        if (! is_null($group)) {
            $select->where(['CHOICE.LTRMON.LMGRP' => $group]);
        }

        if (! is_null($section)) {
            $select->where(['CHOICE.LTRMON.LMSEC' => $section]);
        }

        $statement = $sql->prepareStatementForSqlObject($select);

        return $statement->execute();
    }
}
