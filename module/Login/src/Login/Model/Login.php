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

    public function verifyLogin( $username, $password )
    {
        $db2Conn = db2_connect( 'SWIFTDB', $username, $password );
        if ( $db2Conn !== false ) {
            // connection succeeded
            db2_close( $db2Conn );
            return true;
        } else {
            // connection failed
            return false;
        }
    }

    public function getUserDataByUsername( $username = null )
    {

    }
}
