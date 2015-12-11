<?php

namespace Request\Service;

use Request\Model\RequestInterface;

interface RequestServiceInterface
{
    public function findTimeOffBalancesByEmployee($employeeNumber);

    public function findTimeOffApprovedRequestsByEmployee($employeeNumber);
    
    public function findTimeOffPendingRequestsByEmployee($employeeNumber);

    public function findTimeOffBalancesByManager($managerEmployeeNumber);
    
    public function findTimeOffCalendarByManager($managerEmployeeNumber, $startDate, $endDate);
    
    public function submitRequestForApproval($employeeNumber, $requestData);
}
