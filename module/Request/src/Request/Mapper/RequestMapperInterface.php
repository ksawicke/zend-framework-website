<?php
namespace Request\Mapper;

use Request\Model\RequestInterface;

interface RequestMapperInterface
{
    public function findTimeOffBalancesByEmployee($employeeNumber = null);

    public function findTimeOffRequestsByEmployeeAndStatus($employeeNumber = null, $status = "A");
    
    public function findTimeOffApprovedRequestsByEmployee($employeeNumber = null, $returnType = "datesOnly");
    
    public function findTimeOffPendingRequestsByEmployee($employeeNumber = null, $returnType = "datesOnly", $requestId = null);

    public function findTimeOffBalancesByManager($managerEmployeeNumber = null);
    
    public function findManagerEmployees($managerEmployeeNumber = null, $search = null);
    
    public function findQueuesByManager($managerEmployeeNumber = null);
    
    public function findTimeOffCalendarByManager($managerEmployeeNumber = null, $startDate = null, $endDate = null);
    
    public function isManager($employeeNumber = null);
    
    public function isPayroll($employeeNumber = null);
    
    public function submitRequestForApproval($employeeNumber = null, $requestData = [], $requestReason = null, $requesterEmployeeNumber = null);
    
    public function submitApprovalResponse($action = null, $requestId = null, $reviewRequestReason = null);
}
