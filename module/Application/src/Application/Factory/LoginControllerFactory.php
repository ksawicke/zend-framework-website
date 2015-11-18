<?php
namespace Application\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Controller\LoginController;

class LoginControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerServiceManager)
    {
        $serviceManager = $controllerServiceManager->getServiceLocator();
        $controller = new LoginController();

        $form = $serviceManager->get('login-form');
        $controller->setLoginForm($form);

//        $authService = $serviceManager->get('authentication-service');
//        $controller->setAuthenticationService($authService);

        return $controller;
    }
}
