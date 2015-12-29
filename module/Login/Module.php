<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Login;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container;
use Zend\Authentication\Adapter\DbTable as DbAuthAdapter;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Validator\Authentication as AuthenticationValidator;
use Login\Model\LoginModel;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $serviceManager = $e->getApplication()->getServiceManager();

        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array(
            $this,
            'beforeDispatch'
        ), 100);
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array(
            $this,
            'afterDispatch'
        ), -100);
    }

    function beforeDispatch(MvcEvent $event){

        $request = $event->getRequest();
        $response = $event->getResponse();
        $target = $event->getTarget ();

        /* Offline pages not needed authentication */
        $whiteList = array (
            'Login\Controller\Login-index',
//             'Login\Controller\Login-login',
        );

        $requestUri = $request->getRequestUri();
        $controller = $event->getRouteMatch ()->getParam ( 'controller' );
        $action = $event->getRouteMatch ()->getParam ( 'action' );

        $requestedResource = $controller . "-" . $action;

        $session = new Container('User');
        
//         echo '<pre>';
//         print_r($session);
//         echo '</pre>';
        
//         die("@@");

        if ($session->offsetExists ( 'email' )) {
            if ($requestedResource == 'Login\Controller\Login-index' || in_array ( $requestedResource, $whiteList )) {
                $url = '/sawik/timeoff/public/request/create';
                $response->setHeaders ( $response->getHeaders ()->addHeaderLine ( 'Location', $url ) );
                $response->setStatusCode ( 302 );
            }
        } else {

            if ($requestedResource != 'Login\Controller\Login-index' && ! in_array ( $requestedResource, $whiteList )) {
                $url = '/sawik/timeoff/public/login/index';
                $response->setHeaders ( $response->getHeaders ()->addHeaderLine ( 'Location', $url ) );
                $response->setStatusCode ( 302 );
            }
            $response->sendHeaders ();
        }

        //print "Called before any controller action called. Do any operation.";
    }

    function afterDispatch(MvcEvent $event){
        //print "Called after any controller action called. Do any operation.";
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'AuthService' => 'Login\Factory\LoginFactory'
            ),
        );
    }

}
