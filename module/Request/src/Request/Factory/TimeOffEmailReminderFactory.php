<?php
namespace Request\Factory;

use Zend\ServiceManager\FactoryInterface;
use Request\Model\TimeOffEmailReminder;
use Zend\ServiceManager\ServiceLocatorInterface;

class TimeOffEmailReminderFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $timeOffEmailReminder = new TimeOffEmailReminder();
        return $timeOffEmailReminder;
    }
}

