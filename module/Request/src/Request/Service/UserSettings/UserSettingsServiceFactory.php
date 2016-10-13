<?php
namespace Request\Service\UserSettings;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserSettingsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $userSettingsDAO = $serviceLocator->get('UserSettingsDAO');
        $userSettingsService = new UserSettingsService($serviceLocator, $userSettingsDAO);

        return $userSettingsService;
    }
}

