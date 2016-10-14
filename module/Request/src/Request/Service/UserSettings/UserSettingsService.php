<?php
namespace Request\Service\UserSettings;

use Zend\ServiceManager\ServiceLocatorInterface;
use Request\DAO\UserSettings\UserSettingsDAO;
use Request\Model\EmployeeId;
use Request\Model\UserSetting;

class UserSettingsService
{
    protected $serviceLocator;
    protected $userSettingsDAO;

    public function __construct(ServiceLocatorInterface $serviceLocator, UserSettingsDAO $userSettingsDAO)
    {
        $this->serviceLocator = $serviceLocator;
        $this->userSettingsDAO = $userSettingsDAO;
    }

    public function getUserSettings(EmployeeId $employeeId)
    {
        $userSettingsDAOResult = $this->userSettingsDAO->getUserSettings($employeeId);

        return $userSettingsDAOResult;
    }

    public function setUserSettings(EmployeeId $employeeId, UserSetting $setting)
    {
        $this->userSettingsDAO->setUserSettings($employeeId, $setting);
    }
}

