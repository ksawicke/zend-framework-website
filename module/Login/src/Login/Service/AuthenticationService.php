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
    
    public function isPayroll($employeeNumber)
    {
        return $this->loginMapper->isPayroll($employeeNumber);
    }
}