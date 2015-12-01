<?php
namespace Request\Mapper;

use Request\Model\RequestInterface;

interface RequestMapperInterface
{
    public function findTimeOffBalancesByEmployee($employeeId = null);

    public function findTimeOffApprovedRequestsByEmployee($employeeId = null);

    public function findTimeOffBalancesByManager($managerEmployeeId = null);
}
