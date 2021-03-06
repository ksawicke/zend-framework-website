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

    public function findTimeOffEmployeeData($employeeNumber, $includeHourTotals)
    {
        return $this->requestMapper->findTimeOffEmployeeData($employeeNumber, $includeHourTotals);
    }

    public function findTimeOffApprovedRequestsByEmployee($employeeNumber, $returnType)
    {
        return $this->requestMapper->findTimeOffApprovedRequestsByEmployee($employeeNumber, $returnType);
    }

    public function findRequestCalendarInviteData($requestId)
    {
        return $this->requestMapper->findRequestCalendarInviteData($requestId);
    }

    public function findTimeOffPendingRequestsByEmployee($employeeNumber, $returnType, $requestId)
    {
        return $this->requestMapper->findTimeOffPendingRequestsByEmployee($employeeNumber, $returnType, $requestId);
    }

    public function findTimeOffBalancesByManager($managerEmployeeNumber)
    {
        return $this->requestMapper->findTimeOffBalancesByManager($managerEmployeeNumber);
    }

    public function findEmployeeSchedule($employeeNumber)
    {
        return $this->requestMapper->findEmployeeSchedule($employeeNumber);
    }

    public function makeDefaultEmployeeSchedule($employeeNumber)
    {
        return $this->requestMapper->makeDefaultEmployeeSchedule($employeeNumber);
    }

    public function findManagerEmployees($managerEmployeeNumber, $search, $directReportFilter)
    {
        return $this->requestMapper->findManagerEmployees($managerEmployeeNumber, $search, $directReportFilter);
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

    public function isPayroll($employeeNumber)
    {
        return $this->requestMapper->isPayroll($employeeNumber);
    }

    public function submitRequestForApproval($employeeNumber, $requestData, $requestReason, $requesterEmployeeNumber)
    {
        return $this->requestMapper->submitRequestForApproval($employeeNumber, $requestData, $requestReason, $requesterEmployeeNumber);
    }

    public function submitApprovalResponse($action, $requestId, $reviewRequestReason)
    {
        return $this->requestMapper->submitApprovalResponse($action, $requestId, $reviewRequestReason);
    }

    public function checkHoursRequestedPerCategory($requestId)
    {
        return $this->requestMapper->checkHoursRequestedPerCategory($requestId);
    }

    public function logEntry($requestId, $employeeNumber, $comment)
    {
        return $this->requestMapper->logEntry($requestId, $employeeNumber, $comment);
    }

}
