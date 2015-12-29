<?php
namespace Login\Mapper;

use Login\Model\LoginInterface;

interface LoginMapperInterface
{
    public function authenticateUser($username = null, $password = null);
}