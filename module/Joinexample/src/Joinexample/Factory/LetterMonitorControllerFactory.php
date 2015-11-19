<?php
namespace Joinexample\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Joinexample\Controller\LetterMonitorController;

class LetterMonitorControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerServiceManager) {
        $serviceManager = $controllerServiceManager->getServiceLocator();
        $controller = new LetterMonitorController();

        $model = $serviceManager->get('letter-monitor-model');
        $controller->setLetterMonitorModel($model);

        $form = $serviceManager->get('letter-monitor-search-form');
        $controller->setLetterMonitorForm($form);

        return $controller;
    }
}
