<?php
namespace Joinexample\Model;
use Zend\Db\TableGateway\TableGateway;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

class LetterMonitorModel extends TableGateway
{
    public function fetchAll()
    {
        // inject using the factory?
        $paginator = new Paginator(new DbSelect($this->sql->select(), $this->getAdapter()));

        return $paginator;

//        return $this->select(['LMGRP' => '55038'])->toArray();
    }

}
