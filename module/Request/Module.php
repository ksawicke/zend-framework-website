<?php
namespace Request;

//use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
//use Zend\ModuleManager\Feature\ConfigProviderInterface;

use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Application;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface {

    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();

        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        // tell the ServiceManager to get the adapter, as configured in global.php
        $serviceManager = $e->getApplication()->getServiceManager();
        $serviceManager->get('Zend\Db\Adapter\Adapter');

        $eventManager->attach(MvcEvent::EVENT_DISPATCH, [ $this,
            'beforeDispatch'
        ], 100);
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, [ $this,
            'afterDispatch'
        ], -100);

//        $Employee = $serviceManager->get('Request\Service\Employee');
//        $viewModel->employeeData = $Employee->findTimeOffEmployeeData("49499", "Y");
    }

    /**
     * Do something before any controller action is taken.
     *
     * @param MvcEvent $event
     */
    public function beforeDispatch( MvcEvent $event )
    {
//         $headers = new \Zend\Http\PhpEnvironment\Request;
//         $headers->getServer('HTTP_REFERER');

//         echo '<pre>';
//         var_dump( $headers );
//         echo '</pre>';
//         die();


//         /* @var $response type */
//         $response = $event->getResponse();
//         $controller = $event->getRouteMatch ()->getParam ( 'controller' );
//         $action = $event->getRouteMatch ()->getParam ( 'action' );
//         $requestedResource = $controller . "-" . $action;
//         $session = new Container('Timeoff_'.ENVIRONMENT);

//         /* Pages that are excluded from requiring authentication */
//         $whiteList = [ 'Login\Controller\Login-index',
//             'Login\Controller\Login-logout',
//             'Login\Controller\Login-sso',
//             'API\Scheduler\Controller-sendThreeDayReminderEmailToSupervisor',
//             'API\CLI\Controller-setRequestsToCompleted'
//         ];

//         $redirectUrl = $event->getRequest()->getQuery('q');

//         if (trim($redirectUrl) != '') {
//             $session->redirect = $redirectUrl;
//         }

//         if( $session->offsetExists ( 'EMPLOYEE_NUMBER' ) ) {
//             if ( in_array( $requestedResource, $whiteList ) ) {
//                 $response->setHeaders ( $response->getHeaders ()->addHeaderLine ( 'Location', $this->loggedInTrueRedirectToUrl ) );
//                 $response->setStatusCode ( 302 );
//             }
//         } else {
//             /** Redirect back to login! **/
//             if ($requestedResource != 'Login\Controller\Login-index' && ! in_array ( $requestedResource, $whiteList )) {
//                 $response->setHeaders( $response->getHeaders ()->addHeaderLine ( 'Location', $this->loggedInFalseRedirectToUrl ) );
//                 $response->setStatusCode( 302 );
//             }
//             if (isset($_SERVER['DOCUMENT_URI'])) {
//                 $response->sendHeaders();
//             }
//         }
    }

    /**
     * Do something after any controller action is taken.
     *
     * @param MvcEvent $event
     */
    public function afterDispatch( MvcEvent $event ){
        //
    }

    /**
     * Returns configuration to merge with application configuration
     *
     * @return array|\Traversable
     */
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
}
