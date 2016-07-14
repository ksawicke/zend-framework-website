<?php

namespace Request\Helper;

use \Request\Model\Employee;

class ValidationHelper {
    
    /**
     * Checks if a request exceeds employee's remaining PTO, Float, Sick, or Grandfathered time.
     * Also check if a request contains Bereavement.
     * 
     * @param integer $requestId
     * @param integer $employeeNumber
     * @return boolean
     */
    public function isPayrollReviewRequired( $requestId = null, $employeeNumber = null )
    {
        $Employee = new Employee();
        
        $requestData = $Employee->checkHoursRequestedPerCategory( $requestId );
        $employeeData = $Employee->findEmployeeTimeOffData( $employeeNumber );
        
        if($requestData['PTO'] > $employeeData['PTO_REMAINING']) {
            return true;
        }
        if($requestData['FLOAT'] > $employeeData['FLOAT_REMAINING']) {
            return true;
        }
        if($requestData['SICK'] > $employeeData['SICK_REMAINING']) {
            return true;
        }
        if($requestData['GRANDFATHERED'] > $employeeData['GF_REMAINING']) {
            return true;
        }
        if($requestData['BEREAVEMENT'] > 0) {
            return true;
        }
        
        return false;
    }
    
}