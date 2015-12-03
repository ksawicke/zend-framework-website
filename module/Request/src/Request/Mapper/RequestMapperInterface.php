<?php
namespace Request\Mapper;

use Request\Model\RequestInterface;

interface RequestMapperInterface
{
    public function findTimeOffBalancesByEmployee($employeeNumber = null);

    public function findTimeOffApprovedRequestsByEmployee($employeeNumber = null);

    public function findTimeOffBalancesByManager($managerEmployeeNumber = null);
    
    public function findTimeOffCalendarByManager($managerEmployeeNumber = null, $startDate = null, $endDate = null);
}
