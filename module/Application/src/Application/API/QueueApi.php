<?php

/**
 * QueueApi.php
 *
 * Queue API
 *
 * API Handler for queue data
 *
 * PHP version 5
 *
 * @package    Application\API\HotelApi
 * @author     Guido Faecke <guido_faecke@swifttrans.com>
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
 * @author faecg
 *
 */
class QueueApi extends ApiController {

    /**
     * POST request from datatable UI
     *
     * @api
     * @return \Zend\View\Model\JsonModel
     */
    public function getPendingManagerApprovalQueueAction()
    {
        return new JsonModel( $this->getPendingManagerApprovalQueueDatatable( $_POST ) );
    }
    
    public function getPayrollUpdateChecksQueueAction()
    {
        return new JsonModel( $this->getPayrollUpdateChecksQueueDatatable( $_POST ) );
    }

    /**
     * Get data for the Ma
     * 
     * @param type $data
     * @return type
     */
    public function getPendingManagerApprovalQueueDatatable( $data = null ) {
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

        $ManagerQueues = new \Request\Model\ManagerQueues();
        $queueData = $ManagerQueues->getManagerQueue( $_POST );
        $data = [];
        foreach ( $queueData as $ctr => $request ) {
            $viewLinkUrl = 'review-request/' . $request['REQUEST_ID'];
            
            $data[] = [
                'EMPLOYEE_DESCRIPTION' => $request['EMPLOYEE_DESCRIPTION'],
                'APPROVER_QUEUE' => $request['APPROVER_QUEUE'],
                'REQUEST_STATUS_DESCRIPTION' => $request['REQUEST_STATUS_DESCRIPTION'],
                'REQUESTED_HOURS' => $request['REQUESTED_HOURS'],
                'REQUEST_REASON' => $request['REQUEST_REASON'],
                'MIN_DATE_REQUESTED' => $request['MIN_DATE_REQUESTED'],
                'ACTIONS' => '<a href="' . $viewLinkUrl . '"><button type="button" class="btn btn-form-primary btn-xs">View</button></a>'
            ];
        }

        $recordsTotal = $ManagerQueues->countManagerQueueItems( $_POST, false );
        $recordsFiltered = $ManagerQueues->countManagerQueueItems( $_POST, true );

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
    
    public function getPayrollUpdateChecksQueueDatatable( $data = null )
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

        $PayrollQueues = new \Request\Model\PayrollQueues();
        $queueData = $PayrollQueues->getUpdateChecksQueue( $_POST );
        
        $data = [];
        foreach ( $queueData as $ctr => $request ) {
            $viewLinkUrl = '#'; // 'review-request/' . $request['REQUEST_ID'];
            
            $data[] = [
                'EMPLOYEE_DESCRIPTION' => $request['EMPLOYEE_DESCRIPTION'],
                'APPROVER_QUEUE' => $request['APPROVER_QUEUE'],
                'REQUEST_STATUS_DESCRIPTION' => $request['REQUEST_STATUS_DESCRIPTION'],
                'REQUESTED_HOURS' => $request['REQUESTED_HOURS'],
                'REQUEST_REASON' => $request['REQUEST_REASON'],
                'MIN_DATE_REQUESTED' => $request['MIN_DATE_REQUESTED'],
                'ACTIONS' => '<a href="' . $viewLinkUrl . '"><button type="button" class="btn btn-form-primary btn-xs">View</button></a>'
            ];
        }

        $recordsTotal = 0;
        $recordsFiltered = 0;
        
        $recordsTotal = $PayrollQueues->countUpdateChecksQueueItems( $_POST, false );
        $recordsFiltered = $PayrollQueues->countUpdateChecksQueueItems( $_POST, true );

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

}