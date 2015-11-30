<?php
namespace Simpler\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Simpler\Controller\BlahController;

class BlahControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerServiceManager) {
        $serviceManager = $controllerServiceManager->getServiceLocator();
        $controller = new BlahController();

//        echo '@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@<br /><br />';
//        var_dump($serviceManager->get('post-model'));
//
//        echo '$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$<br /><br />';
//        var_dump($serviceManager->get('db-adapter'));
//
//        exit();

        $model = $serviceManager->get('post-model');
        $controller->setPostModel($model);

//        $dbAdapter = $serviceManager->get('adapter');
//        $controller->setDbAdapter($dbAdapter);

//        $form = $serviceManager->get('letter-group-search-form');
//        $controller->setLetterGroupForm($form);

        return $controller;
    }
}
