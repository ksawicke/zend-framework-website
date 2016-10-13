<?php
namespace Request\Service\UserSettings;

use Zend\ServiceManager\ServiceLocatorInterface;
use Request\DAO\UserSettings\UserSettingsDAO;

class UserSettingsService
{
    protected $serviceLocator;
    protected $userSettingsDAO;

    public function __construct(ServiceLocatorInterface $serviceLocator, UserSettingsDAO $userSettingsDAO)
    {
        $this->serviceLocator = $serviceLocator;
        $this->userSettingsDAO = $userSettingsDAO;
    }

    public function getUserSettings($employeeId)
    {
        $userSettingsDAOResult = $this->userSettingsDAO->getUserSettings($employeeId);

        return $userSettingsDAOResult;
    }

    public function setUserSettings($employeeId, $setting)
    {
        $this->userSettingsDAO->setUserSettings($employeeId, $setting);
    }
}

