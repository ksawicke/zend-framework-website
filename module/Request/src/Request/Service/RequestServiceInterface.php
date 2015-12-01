<?php

namespace Request\Service;

use Request\Model\RequestInterface;

interface RequestServiceInterface
{
    public function findTimeOffBalancesByEmployee($employeeId);

    public function findTimeOffBalancesByManager($managerEmployeeId);
}
