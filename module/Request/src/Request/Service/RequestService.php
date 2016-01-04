<?php

namespace Request\Service;

use Request\Mapper\RequestMapperInterface;
use Request\Model\RequestInterface;

class RequestService implements RequestServiceInterface
{
    /**
     * @var \Request\Mapper\RequestMapperInterface
     */
    protected $requestMapper;

    /**
     * @param RequestMapperInterface $requestMapper
     */
    public function __construct(RequestMapperInterface $requestMapper)
    {
        $this->requestMapper = $requestMapper;
    }

    /**
     * 
     * {@inheritDoc}
     * @see \Request\Service\RequestServiceInterface::findTimeOffRequestsByEmployeeAndStatus()
     */
    public function findTimeOffRequestsByEmployeeAndStatus($employeeNumber, $status)
    {
        return $this->requestMapper->findTimeOffRequestsByEmployeeAndStatus($employeeNumber, $status);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findTimeOffBalancesByEmployee($employeeNumber)
    {
        return $this->requestMapper->findTimeOffBalancesByEmployee($employeeNumber);
    }

    public function findTimeOffApprovedRequestsByEmployee($employeeNumber, $returnType)
    {
        return $this->requestMapper->findTimeOffApprovedRequestsByEmployee($employeeNumber, $returnType);
    }
    
    public function findTimeOffPendingRequestsByEmployee($employeeNumber, $returnType, $requestId)
    {
        return $this->requestMapper->findTimeOffPendingRequestsByEmployee($employeeNumber, $returnType, $requestId);
    }

    public function findTimeOffBalancesByManager($managerEmployeeNumber)
    {
        return $this->requestMapper->findTimeOffBalancesByManager($managerEmployeeNumber);
    }
    
    public function findManagerEmployees($managerEmployeeNumber, $search)
    {
        return $this->requestMapper->findManagerEmployees($managerEmployeeNumber, $search);
    }
    
    public function findQueuesByManager($managerEmployeeNumber)
    {
        return $this->requestMapper->findQueuesByManager($managerEmployeeNumber);
    }
    
    public function findTimeOffCalendarByManager($managerEmployeeNumber, $startDate, $endDate)
    {
        return $this->requestMapper->findTimeOffCalendarByManager($managerEmployeeNumber, $startDate, $endDate);
    }
    
    public function isManager($employeeNumber)
    {
        return $this->requestMapper->isManager($employeeNumber);
    }
    
    public function submitRequestForApproval($employeeNumber, $requestData, $requestReason)
    {
        return $this->requestMapper->submitRequestForApproval($employeeNumber, $requestData, $requestReason);
    }
    
    public function submitApprovalResponse($action, $requestId, $reviewRequestReason)
    {
        return $this->requestMapper->submitApprovalResponse($action, $requestId, $reviewRequestReason);
    }
}
