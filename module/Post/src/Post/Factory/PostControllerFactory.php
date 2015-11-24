<?php
namespace Post\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Post\Controller\PostController;

class PostControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerServiceManager) {
        $serviceManager = $controllerServiceManager->getServiceLocator();
        $controller = new PostController();

        $model = $serviceManager->get('post-model');
        $controller->setPostModel($model);

        return $controller;
    }
}
