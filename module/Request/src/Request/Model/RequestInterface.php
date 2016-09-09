<?php
namespace Request\Model;

interface RequestInterface
{

    public function getEmployeeId();

    public function getGrandfatheredEarned();

    public function getGrandfatheredTaken();

    public function getPtoEarned();

    public function getPtoTaken();

    public function getFloatEarned();

    public function getFloatTaken();

    public function getSickEarned();

    public function getSickTaken();

    public function getCompanyMandatedEarned();

    public function getCompanyMandatedTaken();

    public function getDriverSickEarned();

    public function getDriverSickTaken();

    public function getManagerEmployeeId();

    public function getManagerFirstName();

    public function getManagerMiddleName();

    public function getManagerLastName();

    public function getManagerEmail();
}
