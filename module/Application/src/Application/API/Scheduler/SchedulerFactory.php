<?php
namespace Application\API\Scheduler;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SchedulerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $scheduler = new Scheduler();
        $scheduler->setServiceLocator($serviceLocator);

        return $scheduler;
    }
}

