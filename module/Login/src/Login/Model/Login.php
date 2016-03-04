<?php
namespace Login\Model;

class Login implements LoginInterface
{

    /**
     *
     * @var int
     */
    protected $username;

    /**
     * {@inheritDoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     *
     * @param int $employeeId
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }
}
