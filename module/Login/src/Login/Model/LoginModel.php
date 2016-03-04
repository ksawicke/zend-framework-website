<?php
namespace Login\Model;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;

class LoginModel implements AdapterInterface
{
    protected $username;
    protected $password;

    public function setIdentity($username)
    {
        $this->username = $username;
        return $this;
    }

    public function getIdentity()
    {
        return $this->username;
    }

    public function setCredential($password)
    {
        $this->password = $password;
        return $this;
    }

    public function getCredential()
    {
        return $this->password;
    }

    public function isValid()
    {
        return true;
    }

    public function authenticate()
    {
        return Result::SUCCESS;
    }
}

?>