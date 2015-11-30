<?php
namespace Request\Mapper;

use Request\Model\RequestInterface;

interface RequestMapperInterface
{
    public function findTimeOffBalances($employeeId = null);

    public function findDirectReports($managerEmployeeId = null);
}
