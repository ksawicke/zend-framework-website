<?php

namespace Request\Helper;

class ValidationHelper {
    
    public function isPayrollReviewRequired($requestData, $employeeData)
    {
//        echo '<pre>';
//        print_r($requestData);
//        echo '</pre>';
//        
//        echo '<pre>';
//        print_r($employeeData);
//        echo '</pre>';
        
        if($requestData['PTO'] > ($employeeData['PTO_EARNED'] - $employeeData['PTO_TAKEN'])) {
            return true;
        }
        if($requestData['FLOAT'] > ($employeeData['FLOAT_EARNED'] - $employeeData['FLOAT_TAKEN'])) {
            return true;
        }
        if($requestData['SICK'] > ($employeeData['SICK_EARNED'] - $employeeData['SICK_TAKEN'])) {
            return true;
        }
        if($requestData['GRANDFATHERED'] > ($employeeData['GRANDFATHERED_EARNED'] - $employeeData['GRANDFATHERED_TAKEN'])) {
            return true;
        }
        
        return false;
    }
    
}