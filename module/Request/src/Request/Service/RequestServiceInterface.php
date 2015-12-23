<?php

namespace Request\Service;

use Request\Model\RequestInterface;

interface RequestServiceInterface
{
    public function findTimeOffBalancesByEmployee($employeeNumber);

    public function findTimeOffRequestsByEmployeeAndStatus($employeeNumber, $status);
    
    public function findTimeOffApprovedRequestsByEmployee($employeeNumber, $returnType);
    
    public function findTimeOffPendingRequestsByEmployee($employeeNumber, $returnType, $requestId);

    public function findTimeOffBalancesByManager($managerEmployeeNumber);
    
    public function findManagerEmployees($managerEmployeeNumber);
    
    public function findQueuesByManager($managerEmployeeNumber);
    
    public function findTimeOffCalendarByManager($managerEmployeeNumber, $startDate, $endDate);
    
    public function submitRequestForApproval($employeeNumber, $requestData, $requestReason);
}
