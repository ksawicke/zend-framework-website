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
use \Application\Factory\EmailFactory;
use Request\Model\Employee;

/**
 *
 * @author sawik
 *
 */
class QueueApi extends ApiController {

    /**
     * Array of email addresses to send all emails when running on SWIFT.
     *
     * @var unknown
     */
    public $testingEmailAddressList = null;
    public $developmentEmailAddressList = null;

    public function __construct()
    {
        $TimeOffRequestSettings = new \Request\Model\TimeOffRequestSettings();
        $emailOverrideList = $TimeOffRequestSettings->getEmailOverrideList();
        $this->overrideEmails = $TimeOffRequestSettings->getOverrideEmailsSetting();
        $this->emailOverrideList = ( ( ENVIRONMENT=='testing' || ENVIRONMENT=='development' ) ?
            $emailOverrideList : '' );
    }

    /**
     * POST request from datatable UI to load Manager Queue.
     *
     * @api
     * @return \Zend\View\Model\JsonModel
     */
    public function getManagerQueueAction()
    {
        switch( $this->params()->fromRoute('manager-queue') ) {
            case 'my-employee-requests':
                return new JsonModel( $this->getManagerEmployeesRequestsDatatable( $_POST, [] ) );
                break;

            case 'pending-manager-approval':
            default:
                return new JsonModel( $this->getManagerEmployeesRequestsDatatable( $_POST, ['P'] ) );
                break;
        }
    }

    /**
     * Adjusts dates to YYYY-mm-dd format.
     *
     * @param type $data
     * @return type
     */
    public function adjustStartAndEndDates( $data ) {
        if( !empty( $data['startDate'] ) ) {
            $startDate = new \DateTime( $data['startDate'] );
            $data['startDate'] = date_format( $startDate, 'Y-m-d' );
        }
        if( !empty( $data['endDate'] ) ) {
            $endDate = new \DateTime( $_POST['endDate'] );
            $data['endDate'] = date_format( $endDate, 'Y-m-d' );
        }

        return $data;
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
                $_POST = $this->adjustStartAndEndDates( $_POST );
                return new JsonModel( $this->getPayrollByStatusQueueDatatable( $_POST ) );
                break;

            case 'manager-action':
                return new JsonModel( $this->getManagerActionQueueDatatable( $_POST ) );
                break;
        }
    }

    /**
     * Get data for the Pending Manager Approval Queue datatable.
     *
     * @param array $data
     * @return array
     */
    public function getManagerEmployeesRequestsDatatable( $data = null, $statuses = [] ) {
        $redirectUrl = ( empty( $statuses ) ? '/request/view-manager-queue/my-employee-requests' : '/request/view-manager-queue/pending-manager-approval' );

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
        $Employee = new Employee();

        $proxyFor = [];

        /* is a proxy manager selected ? */
        if (isset($data['currentProxyManagerSelected']) && '' == trim($data['currentProxyManagerSelected'])) {
            /* load all employees we have access to */
            $proxyForEntries = $Employee->findProxiesByEmployeeNumber( $_POST['employeeNumber']);
        } else {
            if (isset($data['currentProxyManagerSelected'])) {
                /* choose only one manager */
                $proxyForEntries[] = ['EMPLOYEE_NUMBER' => $data['currentProxyManagerSelected']];
            } else {
                $proxyForEntries[] = ['EMPLOYEE_NUMBER' => $data['employeeNumber']];
            }
        }

        /* extract employee number */
        foreach ( $proxyForEntries as $proxy) {
            $proxyFor[] = $proxy['EMPLOYEE_NUMBER'];
        }

        /* retrieve all data */
        $queueData = $ManagerQueues->getProxyEmployeeRequests( $data, $proxyFor, $statuses);

        $data = [];

        foreach ( $queueData as $ctr => $request ) {
            $viewLinkUrl = $this->getRequest()->getBasePath() . '/request/review-request/' . $request['REQUEST_ID'] . '?q=' . $redirectUrl;

            $data[] = [
                'EMPLOYEE_DESCRIPTION' => $request['EMPLOYEE_DESCRIPTION'],
                'APPROVER_QUEUE' => $request['APPROVER_QUEUE'],
                'REQUEST_STATUS_DESCRIPTION' => $request['REQUEST_STATUS_DESCRIPTION'],
                'REQUESTED_HOURS' => $request['REQUESTED_HOURS'],
                'REQUEST_REASON' => trim( $request['REQUEST_REASON'] ),
                'MIN_DATE_REQUESTED' => ($request['REQUEST_STATUS_DESCRIPTION'] == 'Pending Manager Approval' ? $this->showFirstDayRequested( $request['MIN_DATE_REQUESTED'], '- 3 days' ) : date_format(date_create($request['MIN_DATE_REQUESTED']), 'm/d/Y')),
                'ACTIONS' => '<a href="' . $viewLinkUrl . '"><button type="button" class="btn btn-form-primary btn-xs">View</button></a>',
                'MANAGER_EMPLOYEE_ID' => $request['MANAGER_EMPLOYEE_ID']
            ];
        }

        $recordsTotal = $ManagerQueues->getProxyEmployeeRequestsCount( $_POST, false, $proxyFor, $statuses );
        $recordsFiltered = $ManagerQueues->getProxyEmployeeRequestsCount( $_POST, true, $proxyFor, $statuses );

        /**
         * prepare return result
         */
        $result = array(
            "status" => "success",
            "message" => "data loaded",
            "draw" => $draw,
            "data" => $data,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered, // count of what is actually being searched on
            "blah" => 223942
        );

        /**
         * return result
         */
        return $result;
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
                'MIN_DATE_REQUESTED' => $this->showFirstDayRequested( $request['MIN_DATE_REQUESTED'] ),
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
                'CYCLE_CODE' => $request['CYCLE_CODE'],
                'EMPLOYEE_DESCRIPTION' => $request['EMPLOYEE_DESCRIPTION'],
                'APPROVER_QUEUE' => $request['APPROVER_QUEUE'],
                'REQUEST_STATUS_DESCRIPTION' => $request['REQUEST_STATUS_DESCRIPTION'],
                'REQUESTED_HOURS' => $request['REQUESTED_HOURS'],
                'LAST_PAYROLL_COMMENT' => $request['LAST_PAYROLL_COMMENT'],
                'MIN_DATE_REQUESTED' => $this->showFirstDayRequested( $request['MIN_DATE_REQUESTED'] ),
                'ACTIONS' => '<a href="' . $viewLinkUrl . '"><button type="button" class="btn btn-form-primary btn-xs">View</button></a>&nbsp;' .
                             '<a href="#"><button type="button" class="btn btn-form-primary btn-xs apiRequest"' .
                             ' data-request-id="' . $request['REQUEST_ID'] . '" data-apiaction="payrollActionCompleteRequest">Approve</button></a>'
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
                'MIN_DATE_REQUESTED' => $this->showFirstDayRequested( $request['MIN_DATE_REQUESTED'], '- 14 days' ),
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
                'MIN_DATE_REQUESTED' => $this->showFirstDayRequested( $request['MIN_DATE_REQUESTED'] ),
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
                'MIN_DATE_REQUESTED' => $this->showFirstDayRequested( $request['MIN_DATE_REQUESTED'] ),
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
                'MIN_DATE_REQUESTED' => $this->showFirstDayRequested( $request['MIN_DATE_REQUESTED'], '- 3 days' ),
                'ACTIONS' => '<a href="' . $viewLinkUrl . '/by-status"><button type="button" class="btn btn-form-primary btn-xs">View</button></a>'
            ];
//             var_dump($viewLinkUrl);
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

    /**
     * Get data for the Manager Action Queue datatable.
     *
     * @param array $data
     * @return array
     */
    public function getManagerActionQueueDatatable( $data = null )
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
        $queueData = $PayrollQueues->getManagerActionEmailQueue( $_POST, [ 'WARN_TYPE' => 'OLD_REQUESTS' ]);

        $data = [];
        foreach ( $queueData as $ctr => $request ) {
            $viewLinkUrl = $this->getRequest()->getBasePath() . '/request/review-request/' . $request['REQUEST_ID'];

            $data[] = [
                'EMPLOYEE_DESCRIPTION' => $request['EMPLOYEE_DESCRIPTION_ALT'],
                'APPROVER_QUEUE' => $request['APPROVER_QUEUE'],
                'REQUEST_STATUS_DESCRIPTION' => $request['REQUEST_STATUS_DESCRIPTION'],
                'REQUESTED_HOURS' => $request['REQUESTED_HOURS'],
                'REQUEST_REASON' => $request['REQUEST_REASON'],
                'MIN_DATE_REQUESTED' => $this->showFirstDayRequested( $request['MIN_DATE_REQUESTED'], '- 3 days' ),
                'ACTIONS' => '<a href="' . $viewLinkUrl . '"><button type="button" class="btn btn-form-primary btn-xs">View</button></a>'
            ];
        }

        $recordsTotal = 0;
        $recordsFiltered = 0;

        $recordsTotal = $PayrollQueues->countManagerActionQueueItems( $_POST, false, [ 'WARN_TYPE' => 'OLD_REQUESTS' ] );
        $recordsFiltered = $PayrollQueues->countManagerActionQueueItems( $_POST, true, [ 'WARN_TYPE' => 'OLD_REQUESTS' ] );

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

//        echo '<pre>';
//        var_dump( $result );
//        echo '</pre>';
//        exit();

        /**
         * return result
         */
        return $result;
    }

    public function getManagerActionEmailDataAction( $data = [] )
    {
        $warnType = 'OLD_REQUESTS';
        $warnTypeBody = "It has been more than 3 days since this request was made and requires your approval.";
        $PayrollQueues = new \Request\Model\PayrollQueues();
        $queueData = $PayrollQueues->getManagerActionEmailQueue( $data, [ 'WARN_TYPE' => $warnType ]);
        $renderer = $this->serviceLocator->get( 'Zend\View\Renderer\RendererInterface' );
        if( $warnType == 'BEFORE_PAYROLL_RUN' ) {
            $warnTypeBody = "We are about to do a Payroll run, and this request requires your approval.";
        }

        foreach( $queueData as $key => $queue ) {
            $reviewUrl = ( ( ENVIRONMENT==='development' || ENVIRONMENT==='testing' ) ? 'http://swift:10080' : 'http://aswift:10080' ) .
                $renderer->basePath( '/request/review-request/' . $queue['REQUEST_ID'] );
            $to = $queue['MANAGER_EMAIL_ADDRESS'];
            if( ENVIRONMENT=='development' ) {
                $to = $this->developmentEmailAddressList;
                $cc = '';
            }
            if( ENVIRONMENT=='testing' ) {
                $to = $this->testingEmailAddressList;
                $cc = '';
            }
            $Email = new EmailFactory(
                'Time off request for ' . $queue['EMPLOYEE_DESCRIPTION_ALT'] . ' requires approval',
                'A total of ' . $queue['REQUESTED_HOURS'] . ' hours were requested off for ' .
                    $queue['EMPLOYEE_DESCRIPTION_ALT'] . '<br /><br />' .
                    $warnTypeBody . '<br /><br />' .
                    'Please review this request at the following URL:<br /><br />' .
                    '<a href="' . $reviewUrl . '">' . $reviewUrl . '</a>',
                $to,
                $cc
            );
            $Email->send();
        }

        die('...EMAIL(S) SENT...');

//        echo '<pre>';
//        print_r( $queueData );
//        echo '</pre>';
//        die( "...." );
    }

    /**
     * Returns first day requested. Highlights if older than days passed in.
     *
     * @param date $minDateRequested
     * @param string $dateDiff
     * @return string
     */
    private function showFirstDayRequested( $minDateRequested = null, $dateDiff = null )
    {
        $minDateRequestedNewFormat = date_create( $minDateRequested );
        $minDateRequestedNewFormat = date_format( $minDateRequestedNewFormat, "m/d/Y") ;

        if( is_null( $dateDiff ) ) {
            return $minDateRequestedNewFormat;
        }

        return ( $minDateRequested < date( 'Y-m-d', strtotime( $dateDiff, strtotime( date( "Y-m-d" ) ) ) ) ?
                 '<span class="warnFirstDayRequestedTooOld">' . $minDateRequestedNewFormat . '</span>' :
                 $minDateRequestedNewFormat );
    }

}