<?php

namespace Request\Model;

class Request implements RequestInterface
{
    /**
     * @var int
     */
    protected $employeeId;

    /**
     * @var string
     */
    protected $grandfatheredBalance;

    /**
     * @var string
     */
    protected $grandfatheredTaken;

    /**
     * @var string
     */
    protected $ptoBalance;

    /**
     * @var string
     */
    protected $ptoTaken;

    /**
     * @var string
     */
    protected $floatBalance;

    /**
     * @var string
     */
    protected $floatTaken;

    /**
     * @var string
     */
    protected $sickBalance;

    /**
     * @var string
     */
    protected $sickTaken;

    /**
     * @var string
     */
    protected $companyMandatedBalance;

    /**
     * @var string
     */
    protected $companyMandatedTaken;

    /**
     * @var string
     */
    protected $driverSickBalance;

    /**
     * @var string
     */
    protected $driverSickTaken;

    /**
     * {@inheritDoc}
     */
    public function getEmployeeId()
    {
        return $this->employeeId;
    }

    /**
     * @param int $employeeId
     */
    public function setEmployeeId($employeeId)
    {
        $this->employeeId = $employeeId;
    }

    /**
     * {@inheritDoc}
     */
    public function getGrandfatheredBalance()
    {
        return $this->grandfatheredBalance;
    }

    /**
     * @param int $grandfatheredBalance
     */
    public function setGrandfatheredBalance($grandfatheredBalance)
    {
        $this->grandfatheredBalance = $grandfatheredBalance;
    }

    /**
     * {@inheritDoc}
     */
    public function getGrandfatheredTaken()
    {
        return $this->grandfatheredTaken;
    }

    /**
     * @param int $grandfatheredTaken
     */
    public function setGrandfatheredTaken($grandfatheredTaken)
    {
        $this->grandfatheredTaken = $grandfatheredTaken;
    }

    /**
     * {@inheritDoc}
     */
    public function getPtoBalance()
    {
        return $this->ptoBalance;
    }

    /**
     * @param int $ptoBalance
     */
    public function setPtoBalance($ptoBalance)
    {
        $this->ptoBalance = $ptoBalance;
    }

    /**
     * {@inheritDoc}
     */
    public function getPtoTaken()
    {
        return $this->ptoTaken;
    }

    /**
     * @param int $ptoTaken
     */
    public function setPtoTaken($ptoTaken)
    {
        $this->ptoTaken = $ptoTaken;
    }

    /**
     * {@inheritDoc}
     */
    public function getFloatBalance()
    {
        return $this->floatBalance;
    }

    /**
     * @param int $floatBalance
     */
    public function setFloatBalance($floatBalance)
    {
        $this->floatBalance = $floatBalance;
    }

    /**
     * {@inheritDoc}
     */
    public function getFloatTaken()
    {
        return $this->floatTaken;
    }

    /**
     * @param int $floatTaken
     */
    public function setFloatTaken($floatTaken)
    {
        $this->floatTaken = $floatTaken;
    }

    /**
     * {@inheritDoc}
     */
    public function getSickBalance()
    {
        return $this->sickBalance;
    }

    /**
     * @param int $sickBalance
     */
    public function setSickBalance($sickBalance)
    {
        $this->sickBalance = $sickBalance;
    }

    /**
     * {@inheritDoc}
     */
    public function getSickTaken()
    {
        return $this->sickTaken;
    }

    /**
     * @param int $sickTaken
     */
    public function setSickTaken($sickTaken)
    {
        $this->sickTaken = $sickTaken;
    }

    /**
     * {@inheritDoc}
     */
    public function getCompanyMandatedBalance()
    {
        return $this->companyMandatedBalance;
    }

    /**
     * @param int $companyMandatedBalance
     */
    public function setCompanyMandatedBalance($companyMandatedBalance)
    {
        $this->companyMandatedBalance = $companyMandatedBalance;
    }

    /**
     * {@inheritDoc}
     */
    public function getCompanyMandatedTaken()
    {
        return $this->companyMandatedTaken;
    }

    /**
     * @param int $companyMandatedTaken
     */
    public function setCompanyMandatedTaken($companyMandatedTaken)
    {
        $this->companyMandatedTaken = $companyMandatedTaken;
    }

    /**
     * {@inheritDoc}
     */
    public function getDriverSickBalance()
    {
        return $this->driverSickBalance;
    }

    /**
     * @param int $driverSickBalance
     */
    public function setDriverSickBalance($driverSickBalance)
    {
        $this->driverSickBalance = $driverSickBalance;
    }

    /**
     * {@inheritDoc}
     */
    public function getDriverSickTaken()
    {
        return $this->driverSickTaken;
    }

    /**
     * @param int $driverSickTaken
     */
    public function setDriverSickTaken($driverSickTaken)
    {
        $this->driverSickTaken = $driverSickTaken;
    }
}
