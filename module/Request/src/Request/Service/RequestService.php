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
     * {@inheritDoc}
     */
    public function findTimeOffBalancesByEmployee($employeeNumber)
    {
        return $this->requestMapper->findTimeOffBalancesByEmployee($employeeNumber);
    }

    public function findTimeOffApprovedRequestsByEmployee($employeeNumber, $returnType)
    {
        return $this->requestMapper->findTimeOffApprovedRequestsByEmployee($employeeNumber);
    }
    
    public function findTimeOffPendingRequestsByEmployee($employeeNumber, $returnType)
    {
        return $this->requestMapper->findTimeOffPendingRequestsByEmployee($employeeNumber);
    }

    public function findTimeOffBalancesByManager($managerEmployeeNumber)
    {
        return $this->requestMapper->findTimeOffBalancesByManager($managerEmployeeNumber);
    }
    
    public function findQueuesByManager($managerEmployeeNumber)
    {
        return $this->requestMapper->findQueuesByManager($managerEmployeeNumber);
    }
    
    public function findTimeOffCalendarByManager($managerEmployeeNumber, $startDate, $endDate)
    {
        return $this->requestMapper->findTimeOffCalendarByManager($managerEmployeeNumber, $startDate, $endDate);
    }
    
    public function submitRequestForApproval($employeeNumber, $requestData, $requestReason)
    {
        return $this->requestMapper->submitRequestForApproval($employeeNumber, $requestData, $requestReason);
    }
}
