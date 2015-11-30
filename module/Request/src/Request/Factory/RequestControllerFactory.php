<?php
namespace Request\Factory;

use Request\Controller\RequestController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RequestControllerFactory implements FactoryInterface
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
        $requestService = $realServiceLocator->get('Request\Service\RequestServiceInterface');
        $requestInsertForm = $realServiceLocator->get('FormElementManager')->get('Request\Form\RequestForm');

        return new RequestController($requestService, $requestInsertForm);
    }
}
