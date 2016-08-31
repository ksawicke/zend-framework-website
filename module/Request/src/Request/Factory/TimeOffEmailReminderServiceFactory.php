<?php
namespace Request\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Request\Service\TimeOffEmailReminderService;
use Request\Model\TimeOffRequests;
use Request\Model\TimeOffEmailReminder;

class TimeOffEmailReminderServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $timeOffRequests = new TimeOffRequests();
        $timeOffEmailReminder = $serviceLocator->get('TimeOffEmailReminder');
        $emailService = $serviceLocator->get('EmailService');

        $timeOffEmailReminderService = new TimeOffEmailReminderService($timeOffRequests, $timeOffEmailReminder, $emailService);

        return $timeOffEmailReminderService;
    }
}

