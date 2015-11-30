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
    public function findTimeOffBalances($employeeId)
    {
        return $this->requestMapper->findTimeOffBalances($employeeId);
    }

    public function findDirectReports($managerEmployeeId)
    {
        return $this->requestMapper->findDirectReports($managerEmployeeId);
    }
}
