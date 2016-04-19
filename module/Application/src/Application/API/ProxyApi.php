<?php

/**
 * ProxyApi.php
 *
 * Proxy API
 *
 * API Handler for proxy submissions and actions
 *
 * PHP version 5
 *
 * @package    Application\API\ProxyApi
 * @author     Kevin Sawicke <kevin_sawicke@swifttrans.com>
 * @copyright  2016 Swift Transportation
 * @version    GIT: $Id$ In development
 */

namespace Application\API;

use Zend\View\Model\JsonModel;
use \Request\Model\EmployeeProxies;
use \Login\Helper\UserSession;
use \Application\Factory\EmailFactory;

/**
 * Handles API requests for the Time Off application.
 * 
 * @author sawik
 *
 */
class ProxyApi extends ApiController {
    
    public function loadProxiesAction()
    {
        return new JsonModel( $this->getProxyDatatable( $_POST ) );
    }
    
    /**
     * Get data for the Proxy datatable.
     * 
     * @param array $data
     * @return array
     */
    public function getProxyDatatable( $data )
    {
        /**
         * return empty result if not called by Datatable
         */
        if ( !array_key_exists( 'draw', $data ) ) {
            return [ ];
        }

        /**
         * increase draw counter for adatatable
         */
        $draw = $data['draw'] ++;
        $EmployeeProxies = new EmployeeProxies();
        $proxyData = $EmployeeProxies->getProxies( $data );
        $data = [];
        foreach ( $proxyData as $ctr => $request ) {
            $viewLinkUrl = "#";
            $checked = ( $request['STATUS']==1 ? ' checked="checked"' : '' );
            
            $data[] = [
                'EMPLOYEE_DESCRIPTION' => $request['EMPLOYEE_DESCRIPTION'],
                'STATUS' => '<div class="switch">' .
                            '<input id="cmn-toggle-' . $ctr . '" class="cmn-toggle cmn-toggle-round-flat" type="checkbox"' . $checked .
                            ' data-proxy-employee-number="' . $request['PROXY_EMPLOYEE_NUMBER'] . '"' .
                            ' data-status="' . $request['STATUS'] . '">' .
                            '<label for="cmn-toggle-' . $ctr . '"></label>' .
                            '</div>',
                'ACTIONS' => '<a href="' . $viewLinkUrl . '">' .
                             '<button type="button" class="btn btn-form-primary btn-xs remove-proxy" data-proxy-employee-number="' .
                             $request['PROXY_EMPLOYEE_NUMBER'] . '">Remove</button></a>'
            ];
        }

        $recordsTotal = $EmployeeProxies->countProxyItems( $_POST, false );
        $recordsFiltered = $EmployeeProxies->countProxyItems( $_POST, true );

        /**
         * prepare return result
         */
        $result = array(
            "status" => "success",
            "message" => "data loaded",
            "draw" => $draw,
            "data" => $data,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered // count of what is actually being searched on
        );

        /**
         * return result
         */
        return $result;
    }
    
    /**
     * Submits new proxy request for an employee.
     */
    public function submitProxyRequestAction()
    {
        $post = $this->getRequest()->getPost();
        $EmployeeProxies = new EmployeeProxies();
        
        try {
            $EmployeeProxies->addProxy( $post );
            /**
             * 201: Created success code, for POST request.
             */
            $this->getResponse()->setStatusCode( 201 );
            return new JsonModel([
                'success' => true,
                'employeeNumber' => $post->EMPLOYEE_NUMBER
            ]);
        } catch ( Exception $ex ) {
            /**
             * 500: An error has occurred so the request couldn't be completed.
             */
            $this->getResponse()->setStatusCode( 500 );
            return new JsonModel([
                'success' => false,
                'message' => 'There was an error adding a proxy for this employee number. Please try again.'
            ]);
        }
    }
    
    /**
     * Deletes a proxy for an employee.
     * 
     * @return JsonModel
     */
    public function deleteProxyAction()
    {
        $post = $this->getRequest()->getPost();
        $EmployeeProxies = new EmployeeProxies();
        
        try {
            $EmployeeProxies->deleteProxy( $post );
        
            /**
             * 204: No Content success code, for DELETE request.
             */
            $this->getResponse()->setStatusCode( 204 );
        } catch ( Exception $ex ) {
             /**
             * 500: An error has occurred so the request couldn't be completed.
             */
            $this->getResponse()->setStatusCode( 500 );
            return new JsonModel([
                'success' => false,
                'message' => 'There was an error deleting a proxy for this employee number. Please try again.'
            ]);
        }
    }
    
    /**
     * Toggles a proxy status from active to non-active and vice versa for an employee.
     * 
     * @return JsonModel
     */
    public function toggleProxyAction()
    {
        $post = $this->getRequest()->getPost();
        $EmployeeProxies = new EmployeeProxies();
        
        try {
            $EmployeeProxies->toggleProxy( $post );
        
            /**
             * 200: Success.
             */
            $this->getResponse()->setStatusCode( 200 );
            return new JsonModel([
                'success' => true,
                'employeeNumber' => $post->EMPLOYEE_NUMBER
            ]);
        } catch ( Exception $ex ) {
             /**
             * 500: An error has occurred so the request couldn't be completed.
             */
            $this->getResponse()->setStatusCode( 500 );
            return new JsonModel([
                'success' => false,
                'message' => 'There was an error deleting a proxy for this employee number. Please try again.'
            ]);
        }
    }
    
}