<?php

/**
 * EmployeeApi.php
 *
 * Employee API
 *
 * API Handler for searches
 *
 * PHP version 5
 *
 * @package    Application\API\SearchApi
 * @author     Kevin Sawicke <kevin_sawicke@swifttrans.com>
 * @copyright  2016 Swift Transportation
 * @version    GIT: $Id$ In development
 */

namespace Application\API;

use Zend\View\Model\JsonModel;
use Request\Model\Employee;

//use Application\Model\HotelTable;
//use Application\Model\RoomTable;

/**
 *
 * @author sawik
 *
 */
class EmployeeApi extends ApiController {

    public function getEmployeeSearchAction()
    {
        die("TEST");
//        $return = [];
//        $Employee = new \Request\Model\Employee();
//        $managerEmployees = $Employee->findManagerEmployees($this->employeeNumber, $request->getPost()->search, $request->getPost()->directReportFilter);
//        foreach($managerEmployees as $id => $data) {
//            $return[] = [ 'id' => $data->EMPLOYEE_NUMBER,
//                          'text' => $data->EMPLOYEE_NAME . ' (' . $data->EMPLOYEE_NUMBER . ') - ' . $data->POSITION_TITLE
//                        ];
//        }
//        return new JsonModel($return);
    }
    
}