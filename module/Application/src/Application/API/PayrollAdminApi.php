<?php

/**
 * PayrollAdminApi.php
 *
 * PayrollAdmin API
 *
 * API Handler for Payroll Admin and actions
 *
 * PHP version 5
 *
 * @package    Application\API\PayrollAdminApi
 * @author     Kevin Sawicke <kevin_sawicke@swifttrans.com>
 * @copyright  2016 Swift Transportation
 * @version    GIT: $Id$ In development
 */

namespace Application\API;

use Zend\View\Model\JsonModel;
use \Request\Model\PayrollAdmins;
use \Login\Helper\UserSession;
use \Application\Factory\EmailFactory;

/**
 * Handles Payroll Admin API requests for the Time Off application.
 * 
 * @author sawik
 *
 */
class PayrollAdminApi extends ApiController {
    
    public function loadPayrollAdminsAction()
    {
        return new JsonModel( $this->getPayrollAdminDatatable( $_POST ) );
    }
    
    /**
     * Get data for the PayrollAdmin datatable.
     * 
     * @param array $data
     * @return array
     */
    public function getPayrollAdminDatatable( $data )
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
        $PayrollAdmins = new PayrollAdmins();
        $payrollAdminData = $PayrollAdmins->getPayrollAdmins( $data );
        $data = [];
        
//        echo '<pre>';
//        var_dump( $payrollAdminData );
//        echo '</pre>';
//        exit();
        
        foreach ( $payrollAdminData as $ctr => $request ) {
            $viewLinkUrl = "#";
            $checked = ( $request['STATUS']==1 ? ' checked="checked"' : '' );
            
            $data[] = [
                'EMPLOYEE_DESCRIPTION' => $request['EMPLOYEE_DESCRIPTION'],
                'STATUS' => '<div class="switch">' .
                            '<input id="cmn-toggle-' . $ctr . '" class="cmn-toggle cmn-toggle-round-flat" type="checkbox"' . $checked .
                            ' data-payroll-admin-employee-number="' . $request['EMPLOYEE_NUMBER'] . '"' .
                            ' data-status="' . $request['STATUS'] . '">' .
                            '<label for="cmn-toggle-' . $ctr . '"></label>' .
                            '</div>',
                'ACTIONS' => '<a href="' . $viewLinkUrl . '">' .
                             '<button type="button" class="btn btn-form-primary btn-xs remove-payroll-admin" data-payroll-admin-employee-number="' .
                             $request['EMPLOYEE_NUMBER'] . '">Remove</button></a>'
            ];
        }

        $recordsTotal = $PayrollAdmins->countPayrollAdminItems( $_POST, false );
        $recordsFiltered = $PayrollAdmins->countPayrollAdminItems( $_POST, true );

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
     * Submits new Payroll Admin request for an employee.
     */
    public function submitPayrollAdminRequestAction()
    {
        $post = $this->getRequest()->getPost();
        $PayrollAdmins = new PayrollAdmins();
        
        try {
            $PayrollAdmins->addPayrollAdmin( $post );
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
                'message' => 'There was an error adding a Payroll Admin for this employee number. Please try again.'
            ]);
        }
    }
    
    /**
     * Deletes a proxy for an employee.
     * 
     * @return JsonModel
     */
    public function deletePayrollAdminAction()
    {
        $post = $this->getRequest()->getPost();
        $PayrollAdmins = new PayrollAdmins();
        
        try {
            $PayrollAdmins->deletePayrollAdmin( $post );
        
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
                'message' => 'There was an error deleting a Payroll Admin for this employee number. Please try again.'
            ]);
        }
    }
    
    /**
     * Toggles a Payroll Admin status from active to non-active and vice versa for an employee.
     * 
     * @return JsonModel
     */
    public function togglePayrollAdminAction()
    {
        $post = $this->getRequest()->getPost();
        $PayrollAdmins = new PayrollAdmins();
        
        try {
            $PayrollAdmins->togglePayrollAdmin( $post );
        
            /**
             * 200: Success.
             */
            $this->getResponse()->setStatusCode( 200 );
            return new JsonModel([
                'success' => true,
                'employeeNumber' => $post->PAYROLLASSISTANT_EMPLOYEE_NUMBER
            ]);
        } catch ( Exception $ex ) {
             /**
             * 500: An error has occurred so the request couldn't be completed.
             */
            $this->getResponse()->setStatusCode( 500 );
            return new JsonModel([
                'success' => false,
                'message' => 'There was an error deleting a Payroll Admin for this employee number. Please try again.'
            ]);
        }
    }
    
}