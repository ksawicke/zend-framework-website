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
    public function getManagerQueueAction() {
        return new JsonModel( $this->getManagerQueueDatatable( $_POST ) );
    }

    public function getManagerQueueDatatable( $data = null ) {
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

        $Employee = new \Request\Model\Employee();
        $managerQueueData = $Employee->getManagerQueue( $_POST );
        $data = [];
        foreach ( $managerQueueData as $ctr => $request ) {
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

        $recordsTotal = $Employee->countManagerQueueItems( $_POST, false );
        $recordsFiltered = $Employee->countManagerQueueItems( $_POST, true );

        /**
         * prepare return result
         */
        $result = array(
            "status" => "success",
            "message" => "data loaded",
            "draw" => $draw,
            "data" => $data,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsTotal // count of what is actually being searched on
        );

        /**
         * return result
         */
        return $result;
    }

}