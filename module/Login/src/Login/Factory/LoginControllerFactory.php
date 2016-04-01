<?php
namespace Login\Factory;

use Login\Controller\LoginController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LoginControllerFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $realServiceLocator = $serviceLocator->getServiceLocator();
        $authenticationService = $realServiceLocator->get('Login\Service\AuthenticationServiceInterface');
        $loginInsertForm = $realServiceLocator->get('FormElementManager')->get('Login\Form\LoginForm');

        $loginController = new LoginController($authenticationService, $loginInsertForm);
        $loginController->setServiceLocator( $serviceLocator );
        
        return $loginController;
    }
}
