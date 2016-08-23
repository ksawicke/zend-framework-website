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
use Zend\Mvc\MvcEvent;

class Module {

    public function onBootstrap( MvcEvent $e ) {
        $eventManager = $e->getApplication()->getEventManager();
        
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
