<?php
namespace Request\Model;

class UserSetting
{
    protected $userSetting;

    /**
     * @return the $userSetting
     */
    public function getUserSetting()
    {
        return $this->userSetting;
    }

    /**
     * @param field_type $userSetting
     */
    public function setUserSetting($userSetting = [])
    {
        if ( !is_array($userSetting) && !is_object($userSetting)) {
            return;
        }

        $this->userSetting = $userSetting;

        return $this;
    }


}

