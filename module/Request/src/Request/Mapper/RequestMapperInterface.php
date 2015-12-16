<?php
namespace Request\Mapper;

use Request\Model\RequestInterface;

interface RequestMapperInterface
{
    public function findTimeOffBalancesByEmployee($employeeNumber = null);

    public function findTimeOffApprovedRequestsByEmployee($employeeNumber = null, $returnType = "datesOnly");
    
    public function findTimeOffPendingRequestsByEmployee($employeeNumber = null, $returnType = "datesOnly", $requestId = null);

    public function findTimeOffBalancesByManager($managerEmployeeNumber = null);
    
    public function findQueuesByManager($managerEmployeeNumber = null);
    
    public function findTimeOffCalendarByManager($managerEmployeeNumber = null, $startDate = null, $endDate = null);
    
    public function submitRequestForApproval($employeeNumber = null, $requestData = [], $requestReason = null);
}
