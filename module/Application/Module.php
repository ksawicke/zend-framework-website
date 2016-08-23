<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\MvcEvent;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface {

    public function onBootstrap( MvcEvent $e ) {
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach( 'dispatch', [ $this, 'loadTimeOffRequestsConfiguration' ] );
        
        /** Set up a listener for every route event so when routing occurs, this method is run - checkUserAuthenticated.
         *  -100 = priority queue. This means run at a very low priority. **/
//        $eventManager->attach( 'route', [ $this, 'checkUserAuthenticated' ], -100 );
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach( $eventManager );
    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    /**
     * Sets variables to be used in the application template.
     * Example, setting $controller->layout()->MY_VARIABLE_NAME here will allow you to do something like
     * echo $this->MY_VARIABLE_NAME .... in a view or partial.
     * 
     * @param MvcEvent $e
     */
    public function loadTimeOffRequestsConfiguration( MvcEvent $e ) {
        $controller = $e->getTarget();        
        $controller->layout()->welcomeMessage = '';
        $controller->layout()->employeeNumber = '';
        $controller->layout()->isLoggedIn = 'N';
        $controller->layout()->isManager = 'N';
        $controller->layout()->isSupervisor = 'N';
        $controller->layout()->isPayrollAdmin = 'N';
        $controller->layout()->isPayrollAssistant = 'N';
        $controller->layout()->countStaleManagerRequests = 0;
        
        if ( isset( $_SESSION['Timeoff_' . ENVIRONMENT] ) ) {
            $controller->layout()->welcomeMessage = '&nbsp;&nbsp;&nbsp;<span style="font-weight:normal;font-size:75%">Welcome, ' . $_SESSION['Timeoff_' . ENVIRONMENT]['COMMON_NAME'] . ' ' . $_SESSION['Timeoff_' . ENVIRONMENT]['LAST_NAME'];
            $controller->layout()->employeeNumber = \Login\Helper\UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' );
            $controller->layout()->isLoggedIn = 'Y';
            $controller->layout()->isManager = \Login\Helper\UserSession::getUserSessionVariable( 'IS_MANAGER' );
            $controller->layout()->isSupervisor = \Login\Helper\UserSession::getUserSessionVariable( 'IS_SUPERVISOR' );
            $controller->layout()->isPayrollAdmin = \Login\Helper\UserSession::getUserSessionVariable( 'IS_PAYROLL_ADMIN' );
            $controller->layout()->isPayrollAssistant = \Login\Helper\UserSession::getUserSessionVariable( 'IS_PAYROLL_ASSISTANT' );
            $PayrollQueues = new \Request\Model\PayrollQueues();
            /** Check the count here. If the manager approves the "stale" requests, the error message will
             *  no longer appear for them.
             */
            $controller->layout()->countStaleManagerRequests = $PayrollQueues->countManagerActionQueueItems( null, false,
                [ 'MANAGER_EMPLOYEE_NUMBER' => $controller->layout()->employeeNumber, 'WARN_TYPE' => 'OLD_REQUESTS' ] );
        }
    }
    
    /**
     * Check if the user is authenticated or not.
     * 
     * @param MvcEvent $event
     */
//    public function checkUserAuthenticated( MvcEvent $event )
//    {
//        $match = $event->getRouteMatch();
//        
//        if( ! $match ) {
//            return;
//        }
//        
//        // No authentication adapter is needed to merely display the Login Form,
//        // or to logout, or to check if the user is already authenticated
//        if( 'login' === $match->getMatchedRouteName() && $event->getRequest()->isPost() ) {
//            $event->getApplication()->getServiceManager()->setService( 'authAdapterNeeded', [ 'authAdapterNeeded' => true ] );
//        }
//        
//        if( $match->getParam( 'authentication-required', true ) ) {
//            $authService = $event->getApplication()->getServiceManager()->get( 'authentication-service' );
//            if( !$authService->hasIdentity() ) {
//                $match->setParam( 'controller', 'Login\\Controller\\Login' );
//                $match->setParam( 'action', 'index' );
//            }
//        }
//    }

}
