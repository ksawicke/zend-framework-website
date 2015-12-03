<?php
namespace Request\Model;

class Request implements RequestInterface
{

    /**
     *
     * @var int
     */
    protected $employeeId;

    /**
     *
     * @var string
     */
    protected $grandfatheredEarned;

    /**
     *
     * @var string
     */
    protected $grandfatheredTaken;

    /**
     *
     * @var string
     */
    protected $ptoEarned;

    /**
     *
     * @var string
     */
    protected $ptoTaken;

    /**
     *
     * @var string
     */
    protected $floatEarned;

    /**
     *
     * @var string
     */
    protected $floatTaken;

    /**
     *
     * @var string
     */
    protected $sickEarned;

    /**
     *
     * @var string
     */
    protected $sickTaken;

    /**
     *
     * @var string
     */
    protected $companyMandatedEarned;

    /**
     *
     * @var string
     */
    protected $companyMandatedTaken;

    /**
     *
     * @var string
     */
    protected $driverSickEarned;

    /**
     *
     * @var string
     */
    protected $driverSickTaken;

    /**
     *
     * @var string
     */
    protected $managerEmployeeId;

    /**
     *
     * @var string
     */
    protected $managerFirstName;

    /**
     *
     * @var string
     */
    protected $managerMiddleName;

    /**
     *
     * @var string
     */
    protected $managerLastName;

    /**
     *
     * @var string
     */
    protected $managerEmail;

    /**
     * {@inheritDoc}
     */
    public function getEmployeeId()
    {
        return $this->employeeId;
    }

    /**
     *
     * @param int $employeeId
     */
    public function setEmployeeId($employeeId)
    {
        $this->employeeId = $employeeId;
    }

    /**
     * {@inheritDoc}
     */
    public function getGrandfatheredEarned()
    {
        return $this->grandfatheredEarned;
    }

    /**
     *
     * @param int $grandfatheredEarned
     */
    public function setGrandfatheredEarned($grandfatheredEarned)
    {
        $this->grandfatheredEarned = $grandfatheredEarned;
    }

    /**
     * {@inheritDoc}
     */
    public function getGrandfatheredTaken()
    {
        return $this->grandfatheredTaken;
    }

    /**
     *
     * @param int $grandfatheredTaken
     */
    public function setGrandfatheredTaken($grandfatheredTaken)
    {
        $this->grandfatheredTaken = $grandfatheredTaken;
    }

    /**
     * {@inheritDoc}
     */
    public function getPtoEarned()
    {
        return $this->ptoEarned;
    }

    /**
     *
     * @param int $ptoEarned
     */
    public function setPtoEarned($ptoEarned)
    {
        $this->ptoEarned = $ptoEarned;
    }

    /**
     * {@inheritDoc}
     */
    public function getPtoTaken()
    {
        return $this->ptoTaken;
    }

    /**
     *
     * @param int $ptoTaken
     */
    public function setPtoTaken($ptoTaken)
    {
        $this->ptoTaken = $ptoTaken;
    }

    /**
     * {@inheritDoc}
     */
    public function getFloatEarned()
    {
        return $this->floatEarned;
    }

    /**
     *
     * @param int $floatEarned
     */
    public function setFloatEarned($floatEarned)
    {
        $this->floatEarned = $floatEarned;
    }

    /**
     * {@inheritDoc}
     */
    public function getFloatTaken()
    {
        return $this->floatTaken;
    }

    /**
     *
     * @param int $floatTaken
     */
    public function setFloatTaken($floatTaken)
    {
        $this->floatTaken = $floatTaken;
    }

    /**
     * {@inheritDoc}
     */
    public function getSickEarned()
    {
        return $this->sickEarned;
    }

    /**
     *
     * @param int $sickEarned
     */
    public function setSickEarned($sickEarned)
    {
        $this->sickEarned = $sickEarned;
    }

    /**
     * {@inheritDoc}
     */
    public function getSickTaken()
    {
        return $this->sickTaken;
    }

    /**
     *
     * @param int $sickTaken
     */
    public function setSickTaken($sickTaken)
    {
        $this->sickTaken = $sickTaken;
    }

    /**
     * {@inheritDoc}
     */
    public function getCompanyMandatedEarned()
    {
        return $this->companyMandatedEarned;
    }

    /**
     *
     * @param int $companyMandatedEarned
     */
    public function setCompanyMandatedEarned($companyMandatedEarned)
    {
        $this->companyMandatedEarned = $companyMandatedEarned;
    }

    /**
     * {@inheritDoc}
     */
    public function getCompanyMandatedTaken()
    {
        return $this->companyMandatedTaken;
    }

    /**
     *
     * @param int $companyMandatedTaken
     */
    public function setCompanyMandatedTaken($companyMandatedTaken)
    {
        $this->companyMandatedTaken = $companyMandatedTaken;
    }

    /**
     * {@inheritDoc}
     */
    public function getDriverSickEarned()
    {
        return $this->driverSickEarned;
    }

    /**
     *
     * @param int $driverSickEarned
     */
    public function setDriverSickEarned($driverSickEarned)
    {
        $this->driverSickEarned = $driverSickEarned;
    }

    /**
     * {@inheritDoc}
     */
    public function getDriverSickTaken()
    {
        return $this->driverSickTaken;
    }

    /**
     *
     * @param int $driverSickTaken
     */
    public function setDriverSickTaken($driverSickTaken)
    {
        $this->driverSickTaken = $driverSickTaken;
    }

    public function getManagerEmployeeId()
    {
        return $this->managerEmployeeId;
    }

    public function setManagerEmployeeId($managerEmployeeId)
    {
        $this->managerEmployeeId = $managerEmployeeId;
    }

    public function getManagerFirstName()
    {
        return $this->managerFirstName;
    }

    public function setManagerFirstName($managerFirstName)
    {
        $this->managerFirstName = $managerFirstName;
    }

    public function getManagerMiddleName()
    {
        return $this->managerMiddleName;
    }

    public function setManagerMiddleName($managerMiddleName)
    {
        $this->managerMiddleName = $managerMiddleName;
    }

    public function getManagerLastName()
    {
        return $this->managerLastName;
    }

    public function setManagerLastName($managerLastName)
    {
        $this->managerLastName = $managerLastName;
    }

    public function getManagerEmail()
    {
        return $this->managerEmail;
    }

    public function setManagerEmail($managerEmail)
    {
        $this->managerEmail = $managerEmail;
    }
}
