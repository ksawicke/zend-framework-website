<?php
namespace Request\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Request\Service\TimeOffEmailReminderService;

class TimeOffEmailReminderServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $timeOffEmailReminderService = new TimeOffEmailReminderService();
        $timeOffEmailReminderService->setServiceLocator($serviceLocator);

        return $timeOffEmailReminderService;
    }
}

