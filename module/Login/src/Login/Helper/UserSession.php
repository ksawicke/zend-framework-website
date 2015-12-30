<?php
namespace Login\Helper;

use Zend\Session\Container;

class UserSession
{
    /**
     * Gets the User session namespace.
     */
    public static function getUserSessionNamespace()
    {
        return 'Timeoff_'.ENVIRONMENT;
    }
    
    /**
     * Builds the user session variables.
     * @param unknown $session
     * @param unknown $result
     */
    public static function createUserSession($result)
    {
        $session = new Container(self::getUserSessionNamespace());
        
        $session->offsetSet('EMPLOYEE_NUMBER', trim($result[0]->EMPLOYEE_NUMBER));
        $session->offsetSet('EMAIL_ADDRESS', strtolower(trim($result[0]->EMAIL_ADDRESS)));
        $session->offsetSet('COMMON_NAME', ucwords(strtolower(trim($result[0]->COMMON_NAME))));
        $session->offsetSet('FIRST_NAME', ucwords(strtolower(trim($result[0]->FIRST_NAME))));
        $session->offsetSet('LAST_NAME', ucwords(strtolower(trim($result[0]->LAST_NAME))));
        $session->offsetSet('USERNAME', strtolower(trim($result[0]->USERNAME)));
        $session->offsetSet('POSITION_TITLE', trim($result[0]->POSITION_TITLE));
        
        $session->offsetSet('MANAGER_EMPLOYEE_NUMBER', trim($result[0]->MANAGER_EMPLOYEE_NUMBER));
        $session->offsetSet('MANAGER_FIRST_NAME', ucwords(strtolower(trim($result[0]->MANAGER_FIRST_NAME))));
        $session->offsetSet('MANAGER_LAST_NAME', ucwords(strtolower(trim($result[0]->MANAGER_LAST_NAME))));
        $session->offsetSet('MANAGER_EMAIL_ADDRESS', strtolower(trim($result[0]->MANAGER_EMAIL_ADDRESS)));
        
        return $session;
    }
    
    /**
     * Ends a user session.
     */
    public static function endUserSession()
    {
        $userSessionNamespace = self::getUserSessionNamespace();
        $session = new Container($userSessionNamespace);
        $session->getManager()->getStorage()->clear($userSessionNamespace);
    }
    
    /**
     * Gets a user session variable.
     * @param unknown $variable
     */
    public static function getUserSessionVariable($variable)
    {
        $userSessionNamespace = self::getUserSessionNamespace();
        $session = new Container($userSessionNamespace);
        return $session->offsetGet($variable);
    }
}
