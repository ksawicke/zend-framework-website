<?php

/**
 * PayrollAssistantApi.php
 *
 * PayrollAssistant API
 *
 * API Handler for proxy submissions and actions
 *
 * PHP version 5
 *
 * @package    Application\API\PayrollAssistantApi
 * @author     Kevin Sawicke <kevin_sawicke@swifttrans.com>
 * @copyright  2016 Swift Transportation
 * @version    GIT: $Id$ In development
 */

namespace Application\API;

use Zend\View\Model\JsonModel;
use \Request\Model\PayrollAssistants;
// use \Login\Helper\UserSession;
// use \Application\Factory\EmailFactory;

/**
 * Handles Payroll Assistant API requests for the Time Off application.
 *
 * @author sawik
 *
 */
class PayrollAssistantApi extends ApiController {

    public function loadPayrollAssistantsAction()
    {
        return new JsonModel( $this->getPayrollAssistantDatatable( $_POST ) );
    }

    /**
     * Get data for the PayrollAssistant datatable.
     *
     * @param array $data
     * @return array
     */
    public function getPayrollAssistantDatatable( $data )
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
        $PayrollAssistants = new PayrollAssistants();
        $payrollAssistantData = $PayrollAssistants->getPayrollAssistants( $data );
        $data = [];

//        echo '<pre>';
//        var_dump( $payrollAssistantData );
//        echo '</pre>';
//        exit();

        foreach ( $payrollAssistantData as $ctr => $request ) {
            $viewLinkUrl = "#";
            $checked = ( $request['STATUS']==1 ? ' checked="checked"' : '' );

            $data[] = [
                'EMPLOYEE_DESCRIPTION' => $request['EMPLOYEE_DESCRIPTION'],
                'STATUS' => '<div class="switch">' .
                            '<input id="cmn-toggle-' . $ctr . '" class="cmn-toggle cmn-toggle-round-flat" type="checkbox"' . $checked .
                            ' data-payroll-assistant-employee-number="' . $request['EMPLOYEE_NUMBER'] . '"' .
                            ' data-status="' . $request['STATUS'] . '">' .
                            '<label for="cmn-toggle-' . $ctr . '"></label>' .
                            '</div>',
                'ACTIONS' => '<a href="' . $viewLinkUrl . '">' .
                             '<button type="button" class="btn btn-form-primary btn-xs remove-payroll-assistant" data-payroll-assistant-employee-number="' .
                             $request['EMPLOYEE_NUMBER'] . '">Remove</button></a>'
            ];
        }

        $recordsTotal = $PayrollAssistants->countPayrollAssistantItems( $_POST, false );
        $recordsFiltered = $PayrollAssistants->countPayrollAssistantItems( $_POST, true );

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
     * Submits new Payroll Assistant request for an employee.
     */
    public function submitPayrollAssistantRequestAction()
    {
        $post = $this->getRequest()->getPost();
        $PayrollAssistants = new PayrollAssistants();

        try {
            $PayrollAssistants->addPayrollAssistant( $post );
            /**
             * 201: Created success code, for POST request.
             */
            $this->getResponse()->setStatusCode( 201 );
            return new JsonModel([
                'success' => true,
                'employeeNumber' => $post->EMPLOYEE_NUMBER
            ]);
        } catch ( \Exception $ex ) {
            /**
             * 500: An error has occurred so the request couldn't be completed.
             */
            $this->getResponse()->setStatusCode( 500 );
            return new JsonModel([
                'success' => false,
                'message' => 'There was an error adding a Payroll Assistant for this employee number. Please try again.'
            ]);
        }
    }

    /**
     * Deletes a proxy for an employee.
     *
     * @return JsonModel
     */
    public function deletePayrollAssistantAction()
    {
        $post = $this->getRequest()->getPost();
        $PayrollAssistants = new PayrollAssistants();

        try {
            $PayrollAssistants->deletePayrollAssistant( $post );

            /**
             * 204: No Content success code, for DELETE request.
             */
            $this->getResponse()->setStatusCode( 204 );
        } catch ( \Exception $ex ) {
             /**
             * 500: An error has occurred so the request couldn't be completed.
             */
            $this->getResponse()->setStatusCode( 500 );
            return new JsonModel([
                'success' => false,
                'message' => 'There was an error deleting a Payroll Assistant for this employee number. Please try again.'
            ]);
        }
    }

    /**
     * Toggles a Payroll Assistant status from active to non-active and vice versa for an employee.
     *
     * @return JsonModel
     */
    public function togglePayrollAssistantAction()
    {
        $post = $this->getRequest()->getPost();
        $PayrollAssistants = new PayrollAssistants();

        try {
            $PayrollAssistants->togglePayrollAssistant( $post );

            /**
             * 200: Success.
             */
            $this->getResponse()->setStatusCode( 200 );
            return new JsonModel([
                'success' => true,
                'employeeNumber' => $post->PAYROLLASSISTANT_EMPLOYEE_NUMBER
            ]);
        } catch ( \Exception $ex ) {
             /**
             * 500: An error has occurred so the request couldn't be completed.
             */
            $this->getResponse()->setStatusCode( 500 );
            return new JsonModel([
                'success' => false,
                'message' => 'There was an error deleting a Payroll Assistant for this employee number. Please try again.'
            ]);
        }
    }

}