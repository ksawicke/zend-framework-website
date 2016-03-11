<?php

namespace Login\Helper;

use Zend\Session\Container;

class UserSession {

    /**
     * Gets the User session namespace.
     */
    public static function getUserSessionNamespace() {
        return 'Timeoff_' . ENVIRONMENT;
    }

    /**
     * Builds the user session variables.
     * @param unknown $session
     * @param unknown $result
     */
    public static function createUserSession( $result ) {
        $session = new Container( self::getUserSessionNamespace() );

        $session->offsetSet( 'EMPLOYEE_NUMBER', trim( $result['EMPLOYEE_NUMBER'] ) );
        $session->offsetSet( 'EMAIL_ADDRESS', strtolower( trim( $result['EMAIL_ADDRESS'] ) ) );
        $session->offsetSet( 'COMMON_NAME', ucwords( strtolower( trim( $result['COMMON_NAME'] ) ) ) );
        $session->offsetSet( 'FIRST_NAME', ucwords( strtolower( trim( $result['FIRST_NAME'] ) ) ) );
        $session->offsetSet( 'LAST_NAME', ucwords( strtolower( trim( $result['LAST_NAME'] ) ) ) );
        $session->offsetSet( 'USERNAME', strtolower( trim( $result['USERNAME'] ) ) );
        $session->offsetSet( 'POSITION_TITLE', trim( $result['POSITION_TITLE'] ) );

        $session->offsetSet( 'MANAGER_EMPLOYEE_NUMBER', trim( $result['MANAGER_EMPLOYEE_NUMBER'] ) );
        $session->offsetSet( 'MANAGER_FIRST_NAME', ucwords( strtolower( trim( $result['MANAGER_FIRST_NAME'] ) ) ) );
        $session->offsetSet( 'MANAGER_LAST_NAME', ucwords( strtolower( trim( $result['MANAGER_LAST_NAME'] ) ) ) );
        $session->offsetSet( 'MANAGER_EMAIL_ADDRESS', strtolower( trim( $result['MANAGER_EMAIL_ADDRESS'] ) ) );

        return $session;
    }

    public static function getFullUserInfo()
    {
        return self::getUserSessionVariable('LAST_NAME') . ', '  . \Login\Helper\UserSession::getUserSessionVariable('FIRST_NAME') .
               ' (' . self::getUserSessionVariable('EMPLOYEE_NUMBER') . ')';
    }
    
    /**
     * Ends a user session.
     */
    public static function endUserSession() {
        $userSessionNamespace = self::getUserSessionNamespace();
        $session = new Container( $userSessionNamespace );
        $session->getManager()->getStorage()->clear( $userSessionNamespace );
    }

    /**
     * Gets a user session variable.
     * @param unknown $variable
     */
    public static function getUserSessionVariable( $variable ) {
        $userSessionNamespace = self::getUserSessionNamespace();
        $session = new Container( $userSessionNamespace );
        return $session->offsetGet( $variable );
    }

    public static function setUserSessionVariable( $variable, $value ) {
        $userSessionNamespace = self::getUserSessionNamespace();
        $session = new Container( $userSessionNamespace );
        $session->offsetSet( $variable, $value );
    }

}
