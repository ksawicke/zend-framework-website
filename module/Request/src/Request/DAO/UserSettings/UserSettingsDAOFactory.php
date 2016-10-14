<?php
namespace Request\DAO\UserSettings;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Request\DAO\UserSettings\UserSettingsDAO;

class UserSettingsDAOFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $databaseAdapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');

        $userSettingsDAO = new UserSettingsDAO($serviceLocator, $databaseAdapter);

        return $userSettingsDAO;
    }
}

