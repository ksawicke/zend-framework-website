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
 * @package    Application\API\QueueApi
 * @author     Guido Faecke <guido_faecke@swifttrans.com>
 * @author     Kevin Sawicke <kevin_sawicke@swifttrans.com>
 * @copyright  2016 Swift Transportation
 * @version    GIT: $Id$ In development
 */

namespace Application\API;

use Zend\View\Model\JsonModel;

/**
 *
 * @author sawik
 *
 */
class QueueApi extends ApiController {

    /**
     * POST request from datatable UI to load Manager Queue.
     *
     * @api
     * @return \Zend\View\Model\JsonModel
     */
    public function getManagerQueueAction()
    {
        switch( $this->params()->fromRoute('manager-queue') ) {
            case 'pending-manager-approval':
            default:
                return new JsonModel( $this->getPendingManagerApprovalQueueDatatable( $_POST ) );
                break;
        }
    }
    
    /**
     * POST request from datatable UI to load Payroll Queue.
     *
     * @api
     * @return \Zend\View\Model\JsonModel
     */
    public function getPayrollQueueAction()
    {
        switch( $this->params()->fromRoute('payroll-queue') ) {
            case 'denied':
                return new JsonModel( $this->getPayrollDeniedQueueDatatable( $_POST ) );
                break;
            
            case 'update-checks':
                return new JsonModel( $this->getPayrollUpdateChecksQueueDatatable( $_POST ) );
                break;
            
            case 'pending-payroll-approval':
                return new JsonModel( $this->getPayrollPendingPayrollApprovalQueueDatatable( $_POST ) );
                break;
            
            case 'completed-pafs':
                return new JsonModel( $this->getPayrollCompletedPAFsQueueDatatable( $_POST ) );
                break;
            
            case 'pending-as400-upload':
                return new JsonModel( $this->getPayrollPendingAS400UploadQueueDatatable( $_POST ) );
                break;
            
            case 'by-status':
                return new JsonModel( $this->getPayrollByStatusQueueDatatable( $_POST ) );
                break;
        }
    }

    /**
     * Get data for the Pending Manager Approval Queue datatable.
     * 
     * @param array $data
     * @return array
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
            $viewLinkUrl = $this->getRequest()->getBasePath() . '/request/review-request/' . $request['REQUEST_ID'];
            
            $data[] = [
                'EMPLOYEE_DESCRIPTION' => $request['EMPLOYEE_DESCRIPTION'],
                'APPROVER_QUEUE' => $request['APPROVER_QUEUE'],
                'REQUEST_STATUS_DESCRIPTION' => $request['REQUEST_STATUS_DESCRIPTION'],
                'REQUESTED_HOURS' => $request['REQUESTED_HOURS'],
                'REQUEST_REASON' => $request['REQUEST_REASON'],
                'MIN_DATE_REQUESTED' => $this->showFirstDayRequested( $request['MIN_DATE_REQUESTED'], '- 6 days' ),
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
    
    /**
     * Returns first day requested. Highlights if older than days passed in.
     * 
     * @param date $minDateRequested
     * @param string $dateDiff
     * @return string
     */
    protected function showFirstDayRequested( $minDateRequested = null, $dateDiff = '- 6 days' )
    {
        $minDateRequestedNewFormat = date_create( $minDateRequested );
        $minDateRequestedNewFormat = date_format( $minDateRequestedNewFormat, "m/d/Y") ;

        return ( $minDateRequested < date( 'Y-m-d', strtotime( $dateDiff, strtotime( date( "Y-m-d" ) ) ) ) ?
                 '<span class="warnFirstDayRequestedTooOld">' . $minDateRequestedNewFormat . '</span>' :
                 $minDateRequestedNewFormat );
    }
    
    /**
     * Get data for the Denied Queue datatable.
     * 
     * @param array $data
     * @return array
     */
    public function getPayrollDeniedQueueDatatable( $data = null )
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
        $queueData = $PayrollQueues->getDeniedQueue( $_POST );
        
        $data = [];
        foreach ( $queueData as $ctr => $request ) {
            $viewLinkUrl = $this->getRequest()->getBasePath() . '/request/review-request/' . $request['REQUEST_ID'];
            
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
        
        $recordsTotal = $PayrollQueues->countDeniedQueueItems( $_POST, false );
        $recordsFiltered = $PayrollQueues->countDeniedQueueItems( $_POST, true );

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
     * Get data for the Update Checks Queue datatable.
     * 
     * @param array $data
     * @return array
     */
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
            $viewLinkUrl = $this->getRequest()->getBasePath() . '/request/review-request/' . $request['REQUEST_ID'];
            
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
    
    /**
     * Get data for the Pending Payroll Approval Queue datatable.
     * 
     * @param array $data
     * @return array
     */
    public function getPayrollPendingPayrollApprovalQueueDatatable( $data = null )
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
        $queueData = $PayrollQueues->getPendingPayrollApprovalQueue( $_POST );
        
        $data = [];
        foreach ( $queueData as $ctr => $request ) {
            $viewLinkUrl = $this->getRequest()->getBasePath() . '/request/review-request/' . $request['REQUEST_ID'];
            
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
        
        $recordsTotal = $PayrollQueues->countPendingPayrollApprovalQueueItems( $_POST, false );
        $recordsFiltered = $PayrollQueues->countPendingPayrollApprovalQueueItems( $_POST, true );

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
     * Get data for the Completed PAFs Queue datatable.
     * 
     * @param array $data
     * @return array
     */
    public function getPayrollCompletedPAFsQueueDatatable( $data = null )
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
        $queueData = $PayrollQueues->getCompletedPAFsQueue( $_POST );
        
        $data = [];
        foreach ( $queueData as $ctr => $request ) {
            $viewLinkUrl = $this->getRequest()->getBasePath() . '/request/review-request/' . $request['REQUEST_ID'];
            
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
        
        $recordsTotal = $PayrollQueues->countCompletedPAFsQueueItems( $_POST, false );
        $recordsFiltered = $PayrollQueues->countCompletedPAFsQueueItems( $_POST, true );

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
     * Get data for the Pending AS400 Upload Queue datatable.
     * 
     * @param array $data
     * @return array
     */
    public function getPayrollPendingAS400UploadQueueDatatable( $data = null )
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
        $queueData = $PayrollQueues->getPendingAS400UploadQueue( $_POST );
        
        $data = [];
        foreach ( $queueData as $ctr => $request ) {
            $viewLinkUrl = $this->getRequest()->getBasePath() . '/request/review-request/' . $request['REQUEST_ID'];
            
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
        
        $recordsTotal = $PayrollQueues->countPendingAS400UploadQueueItems( $_POST, false );
        $recordsFiltered = $PayrollQueues->countPendingAS400UploadQueueItems( $_POST, true );

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
     * Get data for the Update Checks Queue datatable.
     * 
     * @param array $data
     * @return array
     */
    public function getPayrollByStatusQueueDatatable( $data = null )
    {
        /**
         * return empty result if not called by Datatable
         */
        if ( !array_key_exists( 'draw', $data ) ) {
            return [ ];
        }

        /**
         * increase draw counter for datatable
         */
        $draw = $data['draw'] ++;

        $PayrollQueues = new \Request\Model\PayrollQueues();
        $queueData = $PayrollQueues->getByStatusQueue( $_POST );
        
        $data = [];
        foreach ( $queueData as $ctr => $request ) {
            $viewLinkUrl = $this->getRequest()->getBasePath() . '/request/review-request/' . $request['REQUEST_ID'];
            
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
        
        $recordsTotal = $PayrollQueues->countByStatusQueueItems( $_POST, false );
        $recordsFiltered = $PayrollQueues->countByStatusQueueItems( $_POST, true );

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