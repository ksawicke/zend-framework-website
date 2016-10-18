<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @author  Modified by sawik Kevin Sawicke <kevin_sawicke@swifttrans.com> for Swift Transportation
 */

namespace Login;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container;

class Module
{

    public $loggedInTrueRedirectToUrl;
    public $loggedInFalseRedirectToUrl;

    public function __construct()
    {
        $fullPath = getcwd();

        if (isset($_SERVER['DOCUMENT_URI'])) {
            $fullPath = $_SERVER['DOCUMENT_URI'];
        }
        $fullPath = substr( $fullPath, 0, -10 );
        $this->loggedInTrueRedirectToUrl = $fullPath . '/request/view-my-requests';
        $this->loggedInFalseRedirectToUrl = $fullPath . '/login/index';
    }

    /**
     *
     *
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach( $eventManager );

        $serviceManager = $e->getApplication()->getServiceManager();

        $eventManager->attach(MvcEvent::EVENT_DISPATCH, [ $this,
                                                          'beforeDispatch'
                                                        ], 100);
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, [ $this,
                                                          'afterDispatch'
                                                        ], -100);
    }

    /**
     * Do something before any controller action is taken.
     *
     * @param MvcEvent $event
     */
    public function beforeDispatch( MvcEvent $event )
    {
        /* @var $response type */
        $response = $event->getResponse();
        $controller = $event->getRouteMatch ()->getParam ( 'controller' );
        $action = $event->getRouteMatch ()->getParam ( 'action' );
        $requestedResource = $controller . "-" . $action;
        $session = new Container('Timeoff_'.ENVIRONMENT);

        /* Pages that are excluded from requiring authentication */
        $whiteList = [ 'Login\Controller\Login-index',
                       'Login\Controller\Login-logout',
                       'Login\Controller\Login-sso',
                       'API\Scheduler\Controller-sendThreeDayReminderEmailToSupervisor',
                       'API\CLI\Controller-setRequestsToCompleted'
        ];

        $redirectUrl = $event->getRequest()->getQuery('q');

        if (trim($redirectUrl) != '') {
            $session->redirect = $redirectUrl;
        }

        if( $session->offsetExists ( 'EMPLOYEE_NUMBER' ) ) {
            if ( in_array( $requestedResource, $whiteList ) ) {
                $response->setHeaders ( $response->getHeaders ()->addHeaderLine ( 'Location', $this->loggedInTrueRedirectToUrl ) );
                $response->setStatusCode ( 302 );
            }
        } else {
            /** Redirect back to login! **/
            if ($requestedResource != 'Login\Controller\Login-index' && ! in_array ( $requestedResource, $whiteList )) {
                /** Detect if we need to send user to a specific URL after they successfully login. **/
                $redirectUrl = '?q=' . $event->getRequest()->getURI()->getPath();
                $response->setHeaders( $response->getHeaders()->addHeaderLine( 'Location', $this->loggedInFalseRedirectToUrl . $redirectUrl ) );
                $response->setStatusCode( 302 );
            }
            if (isset($_SERVER['DOCUMENT_URI'])) {
                $response->sendHeaders();
            }
        }
    }

    /**
     * Do something after any controller action is taken.
     *
     * @param MvcEvent $event
     */
    public function afterDispatch( MvcEvent $event ){
        //
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Gets the Autoloader Configuration from Zend Framework.
     *
     * @return type
     */
    public function getAutoloaderConfig()
    {
        return [ 'Zend\Loader\StandardAutoloader' => [ 'namespaces' => [ __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__ ] ] ];
    }

    /**
     * Gets the Service Configuration from Zend Framework.
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return [ 'factories' => [ 'AuthService' => 'Login\Factory\LoginFactory' ] ];
    }

}
