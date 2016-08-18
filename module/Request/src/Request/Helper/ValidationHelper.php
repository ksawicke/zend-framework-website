<?php

namespace Request\Helper;

use \Request\Model\Employee;
use \Request\Model\TimeOffRequests;

class ValidationHelper {
    
    /**
     * Checks if a request exceeds employee's remaining PTO, Float, Sick, or Grandfathered time.
     * Also check if a request contains Civic Duty.
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
        
//         echo '<pre>';
//         var_dump( $hoursRequestedData );
//         var_dump( $employeeData );
//         var_dump( $request );
//         echo '</pre>';
        
//         echo '<br /><br />';
        
//         echo $request['EMPLOYEE_DATA']->PTO_REMAINING;
        
//         die( '..stopping..' );
        
        // 8/18/16 changed from $employeeData['PTO_REMAINING']
        if( $hoursRequestedData['PTO'] > $request['EMPLOYEE_DATA']->PTO_REMAINING ) {
            return true;
        }
        if( $hoursRequestedData['FLOAT'] > $request['EMPLOYEE_DATA']->FLOAT_REMAINING ) {
            return true;
        }
        if( $hoursRequestedData['SICK'] > $request['EMPLOYEE_DATA']->SICK_REMAINING ) {
            return true;
        }
        if( $hoursRequestedData['GRANDFATHERED'] > $request['EMPLOYEE_DATA']->GF_REMAINING ) {
            return true;
        }
        if( $hoursRequestedData['CIVIC_DUTY'] > 24 ) {
            return true;
        }
        
        return false;
    }
    
}