<?php

/**
 * SearchApi.php
 *
 * Search API
 *
 * API Handler for queue data
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

/**
 *
 * @author sawik
 *
 */
class SearchApi extends ApiController {

    /**
     * POST request from timeoff request screen to search empoyees.
     *
     * @api
     * @return \Zend\View\Model\JsonModel
     */
    public function getSearchResultsAction() {
        switch ( $this->params()->fromRoute( 'search-type' ) ) {
            case 'proxies':
                return new JsonModel( $this->getEmployeeProxySearchResults() );
                break;

            case 'employees':
            default:
                return new JsonModel( $this->getEmployeeSearchResults() );
                break;
        }
    }

    /**
     * Returns an array of employees the logged in user may put in a request for.
     *
     * @return array
     */
    private function getEmployeeSearchResults() {
        $request = $this->getRequest();
        $return = [ ];
        $Employee = new \Request\Model\Employee();
        $managerEmployees = $Employee->findManagerEmployees( $request->getPost()->employeeNumber,
            $request->getPost()->search, $request->getPost()->directReportFilter, $request->getPost()->isProxy,
            $request->getPost()->proxyFor );
        foreach ( $managerEmployees as $id => $data ) {
            $return[] = [ 'id' => $data->EMPLOYEE_NUMBER,
                'text' => $data->EMPLOYEE_NAME . ' (' . $data->EMPLOYEE_NUMBER . ') - ' . $data->POSITION_TITLE
            ];
        }

        return $return;
    }

    /**
     * Returns an array of employees the logged in user may use as a proxy.
     *
     * @return array
     */
    private function getEmployeeProxySearchResults() {
        $request = $this->getRequest();
        $return = [ ];
        $Employee = new \Request\Model\Employee();
        $managerEmployees = $Employee->findProxyEmployees( $request->getPost()->employeeNumber,
            $request->getPost()->search, $request->getPost()->directReportFilter );
        foreach ( $managerEmployees as $id => $data ) {
            $return[] = [ 'id' => $data->EMPLOYEE_NUMBER,
                'text' => $data->EMPLOYEE_NAME . ' (' . $data->EMPLOYEE_NUMBER . ') - ' . $data->POSITION_TITLE
            ];
        }

        return $return;
    }

}
