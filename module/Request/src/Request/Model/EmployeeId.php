<?php
namespace Request\Model;

use Request\Helper\Format;

class EmployeeId
{
    protected $employerId;

    protected $employeeId;

    public function __construct()
    {
        $this->setEmployerId('002');
    }

    /**
     * @return the $employerId
     */
    public function getEmployerId()
    {
        return $this->employerId;
    }

    /**
     * @return the $employeeId
     */
    public function getEmployeeId()
    {
        return $this->employeeId;
    }

    /**
     * @param field_type $employerId
     */
    public function setEmployerId($employerId)
    {
        $this->employerId = $employerId;
    }

    /**
     * @param field_type $employeeId
     */
    public function setEmployeeId($employeeId)
    {
        $this->employeeId = Format::rightPadEmployeeNumber($employeeId);
    }



}

