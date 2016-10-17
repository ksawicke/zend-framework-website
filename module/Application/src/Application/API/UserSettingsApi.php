<?php
namespace Application\API;

use Zend\View\Model\JsonModel;
use Request\Service\UserSettings\UserSettingsService;
use Request\Model\EmployeeId;
use Request\Model\UserSetting;

class UserSettingsApi extends ApiController
{

    /**
     * Updates user's settings.
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function updateUserSettingsAction()
    {
        $decodedJson = json_decode($this->getRequest()->getContent());

        $employeeId = new EmployeeId();
        $employeeId->setEmployeeId($decodedJson->employee->employeeId);

        $userSetting = new UserSetting();
        $userSetting->setUserSetting($decodedJson->setting);

        $userSettingsService = $this->serviceLocator->get('UserSettingsService');
        $userSettingsService->setUserSettings($employeeId, $userSetting);

        return new JsonModel([]);
    }

    /**
     * Gets a user's settings.
     */
    public function getUserSettingsAction()
    {
        $userSettingsData = [];
        $userSettingsResult = [];

        if (trim(\Login\Helper\UserSession::getUserSessionVariable('FIRST_NAME')) != '' and trim(\Login\Helper\UserSession::getUserSessionVariable('FIRST_NAME')) != null) {
            $decodedJson = json_decode($this->getRequest()->getContent());

            if ($decodedJson->employeeId != null) {
                $employeeId = new EmployeeId();
                $employeeId->setEmployeeId($decodedJson->employeeId);

                $userSettingsService = $this->serviceLocator->get('UserSettingsService');
                $userSettingsResult = $userSettingsService->getUserSettings($employeeId);

                foreach ($userSettingsResult as $setting) {
                    $subSetting = json_decode($setting['SYSTEM_VALUE']);
                    foreach ($subSetting as $key => $value) {
                        $userSettingsData[$key] = $value;
                    }
                }
            }
        }
        return new JsonModel($userSettingsData);
    }
}

