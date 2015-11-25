<?php

namespace Request\Model;

interface RequestInterface
{
    public function getEmployeeId();

    public function getGrandfatheredBalance();

    public function getGrandfatheredTaken();

    public function getPtoBalance();

    public function getPtoTaken();

    public function getFloatBalance();

    public function getFloatTaken();

    public function getSickBalance();

    public function getSickTaken();

    public function getCompanyMandatedBalance();

    public function getCompanyMandatedTaken();

    public function getDriverSickBalance();

    public function getDriverSickTaken();
}
