<?php
namespace Request\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Request\Service\EmailService;
use Request\Model\TimeOffRequestSettings;

class EmailServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $timeOffRequestSettings = new TimeOffRequestSettings();

        $emailService = new EmailService($timeOffRequestSettings);

        return $emailService;
    }
}
