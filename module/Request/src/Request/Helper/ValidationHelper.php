<?php

namespace Request\Helper;

use \Request\Model\Employee;
use \Request\Model\TimeOffRequests;

class ValidationHelper {
    
    /**
     * Checks if a request exceeds employee's remaining PTO, Float, Sick, or Grandfathered time.
     * Also check if a request contains more than 24 hours of Civic Duty.
     * 
     * @param integer $requestId
     * @param integer $employeeNumber
     * @return boolean
     */
    public function isPayrollReviewRequired( $requestId = null, $employeeNumber = null )
    {
        $Employee = new Employee();
        $TimeOffRequests = new TimeOffRequests();
        
        $hoursRequestedData = $Employee->checkHoursRequestedPerCategory( $requestId );
        $employeeData = $Employee->findEmployeeTimeOffData( $employeeNumber );
        $request = $TimeOffRequests->findRequest( $requestId );

        return ( ( $hoursRequestedData['PTO'] > $request['EMPLOYEE_DATA']->PTO_REMAINING ||
                   $hoursRequestedData['FLOAT'] > $request['EMPLOYEE_DATA']->FLOAT_REMAINING ||
                   $hoursRequestedData['SICK'] > $request['EMPLOYEE_DATA']->SICK_REMAINING ||
                   $hoursRequestedData['GRANDFATHERED'] > $request['EMPLOYEE_DATA']->GF_REMAINING ||
                   $hoursRequestedData['CIVIC_DUTY'] > 0
                 ) ? true : false
               );
    }
    
}