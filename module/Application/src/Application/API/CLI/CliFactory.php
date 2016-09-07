<?php
namespace Application\API\CLI;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CliFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cli = new Cli();
        $cli->setServiceLocator($serviceLocator);

        return $cli;
    }
}

