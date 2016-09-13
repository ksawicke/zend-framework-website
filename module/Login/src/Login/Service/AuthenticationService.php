<?php

namespace Login\Service;

use Login\Mapper\LoginMapperInterface;
use Login\Model\LoginInterface;

class AuthenticationService implements AuthenticationServiceInterface
{
    /**
     * @var \Login\Mapper\LoginMapperInterface
     */
    protected $loginMapper;

    /**
     * @param LoginMapperInterface $loginMapper
     */
    public function __construct(LoginMapperInterface $loginMapper)
    {
        $this->loginMapper = $loginMapper;
    }

    /**
     *
     * {@inheritDoc}
     * @see \Login\Service\LoginServiceInterface::authenticateUser($username, $password)
     */
    public function authenticateUser($username, $password)
    {
        return $this->loginMapper->authenticateUser($username, $password);
    }

    public function isManager($employeeNumber)
    {
        return $this->loginMapper->isManager($employeeNumber);
    }

    public function isSupervisor($employeeNumber)
    {
        return $this->loginMapper->isSupervisor($employeeNumber);
    }

    public function isPayroll($employeeNumber)
    {
        return $this->loginMapper->isPayroll($employeeNumber);
    }

    public function isPayrollAdmin($employeeNumber)
    {
        return $this->loginMapper->isPayrollAdmin($employeeNumber);
    }

    public function isPayrollAssistant($employeeNumber)
    {
        return $this->loginMapper->isPayrollAssistant($employeeNumber);
    }

    public function isProxy($employeeNumber)
    {
        return $this->loginMapper->isProxy($employeeNumber);
    }

    public function isProxyForManager($employeeNumber)
    {
        return $this->loginMapper->isProxyForManager($employeeNumber);
    }

}