<?php
namespace Login\Mapper;

use Login\Model\LoginInterface;

interface LoginMapperInterface
{
    public function authenticateUser($username = null, $password = null);
    
    public function isManager($employeeNumber = null);
    
    public function isPayroll($employeeNumber = null);
    
    public function isProxy($employeeNumber = null);
}