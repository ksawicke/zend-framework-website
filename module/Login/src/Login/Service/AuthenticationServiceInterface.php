<?php

namespace Login\Service;

use Login\Model\LoginInterface;

interface AuthenticationServiceInterface
{
    public function authenticateUser($username, $password);
}