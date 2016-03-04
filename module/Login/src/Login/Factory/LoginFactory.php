<?php
namespace Login\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Authentication\AuthenticationService;
use Login\Model\LoginModel;

class LoginFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $sl)
    {
        $service = new AuthenticationService();
        $adapter = new LoginModel();
        $service->setAdapter($adapter);
        return $service;
    }
}

?>