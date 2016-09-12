<?php
namespace Login\Mapper;

use Login\Model\LoginInterface;

interface LoginMapperInterface
{
    public function authenticateUser($username = null, $password = null);

    public function isManager($employeeNumber = null);

    public function isPayroll($employeeNumber = null);

    public function isPayrollAdmin($employeeNumber = null);

    public function isPayrollAssistant($employeeNumber = null);

    public function isProxy($employeeNumber = null);

    public function isProxyForManager($employeeNumber = null);
}