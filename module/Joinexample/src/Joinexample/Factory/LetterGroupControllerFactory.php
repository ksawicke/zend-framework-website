<?php
namespace Joinexample\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Joinexample\Controller\LetterGroupController;

class LetterGroupControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerServiceManager) {
        $serviceManager = $controllerServiceManager->getServiceLocator();
        $controller = new LetterGroupController();

        $model = $serviceManager->get('letter-group-model');
        $controller->setLetterGroupModel($model);

        $form = $serviceManager->get('letter-group-search-form');
        $controller->setLetterGroupForm($form);

        return $controller;
    }
}
