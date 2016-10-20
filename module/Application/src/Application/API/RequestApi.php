<?php

/**
 * RequestApi.php
 *
 * Request API
 *
 * API Handler for request submissions and actions
 *
 * PHP version 5
 *
 * @package    Application\API\RequestApi
 * @author     Kevin Sawicke <kevin_sawicke@swifttrans.com>
 * @copyright  2016 Swift Transportation
 * @version    GIT: $Id$ In development
 */

namespace Application\API;

use Zend\View\Model\JsonModel;
use \Request\Model\Employee;
use \Request\Model\EmployeeSchedules;
use \Request\Model\TimeOffRequestLog;
use \Request\Model\TimeOffRequests;
use \Request\Model\RequestEntry;
use \Request\Model\Papaatmp;
use \Request\Helper\OutlookHelper;
use \Request\Helper\ValidationHelper;
use \Login\Helper\UserSession;
use \Application\Factory\EmailFactory;
use \Request\Service\CalendarInviteService;

/**
 * Handles API requests for the Time Off application.
 *
 * @author sawik
 *
 */
class RequestApi extends ApiController {

    protected static $typesToCodes = [
        'timeOffPTO' => 'P',
        'timeOffFloat' => 'K',
        'timeOffSick' => 'S',
        'timeOffUnexcusedAbsence' => 'X',
        'timeOffBereavement' => 'B',
        'timeOffCivicDuty' => 'J',
        'timeOffGrandfathered' => 'R',
        'timeOffApprovedNoPay' => 'A'
    ];

    protected static $codesToTypes = [
        'P' => 'timeOffPTO',
        'K' => 'timeOffFloat',
        'S' => 'timeOffSick',
        'X' => 'timeOffUnexcusedAbsence',
        'B' => 'timeOffBereavement',
        'J' => 'timeOffCivicDuty',
        'R' => 'timeOffGrandfathered',
        'A' => 'timeOffApprovedNoPay'
    ];

    /**
     * Array of email addresses to send all emails when running on SWIFT.
     *
     * @var unknown
     */
    public $emailOverrideList = '';

    /**
     * Setting to allow us to override the actual email address(es) used to send emails and calendar invites.
     * If set to 1 then we will use those email address(es).
     * This feature will only work in DEV or UAT environments.
     *
     * @var integer
     */
    public $overrideEmails = 0;

    public function __construct()
    {
        $TimeOffRequestSettings = new \Request\Model\TimeOffRequestSettings();
        $emailOverrideList = $TimeOffRequestSettings->getEmailOverrideList();
        $this->overrideEmails = $TimeOffRequestSettings->getOverrideEmailsSetting();
        $this->emailOverrideList = ( ( ENVIRONMENT=='testing' || ENVIRONMENT=='development' ) ?
            $emailOverrideList : '' );
    }

    /**
     * Cleans up the POST from a new Time Off request.
     *
     * @param array $post
     * @return array
     */
    protected function cleanUpRequestedDates( $post )
    {
        $selectedDatesNew = [];

        foreach( $post->request['dates'] as $key => $data ) {
            $date = \DateTime::createFromFormat( 'm/d/Y', $post->request['dates'][$key]['date'] );
            $post->request['dates'][$key] = [
                'date' => $date->format('Y-m-d'),
                'day_of_week' => strtoupper( $date->format('D') ),
                'type' => self::$typesToCodes[$post->request['dates'][$key]['category']],
                'hours' => number_format( $post->request['dates'][$key]['hours'], 2 )
            ];
        }

        return $post;
    }

    public function getEmailOverrideSettingsAction()
    {
        $emailOverrideList = implode( ",", $this->emailOverrideList );
        $result = new JsonModel([
            'success' => true,
            'emailOverrideList' => $emailOverrideList,
            'overrideEmails' => $this->overrideEmails
        ]);

        return $result;
    }

    public function editEmailOverrideSettingsAction()
    {
        $post = $this->getRequest()->getPost();

        $newEmailOverrideList = explode( ",", $post->NEW_EMAIL_OVERRIDE_LIST );
        $emailsValidate = true;
        foreach( $newEmailOverrideList as $ctr => $email ) {
            if( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
                $emailsValidate = false;
                break;
            }
        }

        if( $emailsValidate ) {
            sleep( 1 ); // Wait 1 second so the user sees the button showing the saving process taking place.
            $TimeOffRequestSettings = new \Request\Model\TimeOffRequestSettings();
            $TimeOffRequestSettings->editEmailOverrideList( $newEmailOverrideList );
            $TimeOffRequestSettings->editEmailOverride( $post->OVERRIDE_EMAILS );

            $this->getResponse()->setStatusCode( 200 );
            $result = new JsonModel([
                'success' => true,
                'post' => $post,
                'emailOverrideList' => $post->NEW_EMAIL_OVERRIDE_LIST,
                'overrideEmails' => $post->OVERRIDE_EMAILS
            ]);
        } else {
            sleep( 1 ); // Wait 1 second so the user sees the button showing the saving process taking place.
            $this->getResponse()->setStatusCode( 500 );
            $result = new JsonModel([
                'success' => false,
                'message' => 'There was an error saving the email override list. Please make sure all email addresses are in a valid format, and separated by a comma.'
            ]);
        }

        return $result;
    }

    public function getCompanyHolidaysAction()
    {
        return new JsonModel( $this->getCompanyHolidaysDatatable( $_POST ) );
    }

    public function addCompanyHolidayAction()
    {
        $post = $this->getRequest()->getPost();
        $data['date'] = $post['request']['date'];
        $TimeOffRequestSettings = new \Request\Model\TimeOffRequestSettings();
        $TimeOffRequestSettings->addCompanyHoliday( $data );

        $this->getResponse()->setStatusCode( 200 );
        $result = new JsonModel([
            'success' => true,
            'date' => $post['request']['date']
        ]);

        return $result;
    }

    public function deleteCompanyHolidayAction()
    {
        $post = $this->getRequest()->getPost();
        $data['date'] = $post['request']['date'];
        $TimeOffRequestSettings = new \Request\Model\TimeOffRequestSettings();
        $TimeOffRequestSettings->deleteCompanyHoliday( $data );

        $this->getResponse()->setStatusCode( 200 );
        $result = new JsonModel([
            'success' => true,
            'date' => $post['request']['date']
        ]);

        return $result;
    }

    public function getCompanyHolidaysDatatable( $data = null )
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

        $TimeOffRequestSettings = new \Request\Model\TimeOffRequestSettings();
        $companyHolidays = $TimeOffRequestSettings->getCompanyHolidays();

        $data = [];
        foreach ( $companyHolidays as $ctr => $holiday ) {
            //$viewLinkUrl = $this->getRequest()->getBasePath() . '/request/review-request/' . $request['REQUEST_ID'];
            $viewLinkUrl = '--';

            $data[] = [
                'DATE' => $holiday,
                'ACTIONS' => '<a class="btn btn-form-primary btn-xs submitDeleteCompanyHoliday" data-date="' . $holiday . '">Delete</a>'
            ];
        }

        $recordsTotal = 0;
        $recordsFiltered = 0;

        $recordsTotal = count( $companyHolidays );
        $recordsFiltered = count( $companyHolidays );

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
     * Handle an API request to approve a request in the Update Checks queue.
     *
     * @return JsonModel
     */
    public function approveUpdateChecksRequestAction()
    {
        $post = $this->getRequest()->getPost();
        $TimeOffRequests = new TimeOffRequests();
        $TimeOffRequestLog = new TimeOffRequestLog();
        $requestData = $TimeOffRequests->findRequest( $post->request_id );

        // Check if there were any updates to the form
        $updatesToFormMade = $this->checkForUpdatesMadeToForm( $post, $requestData['ENTRIES'] );
        if( $updatesToFormMade ) {
            $this->logChangesMadeToRequest( $post );
        }

        if( $requestData['REQUEST_STATUS_DESCRIPTION']=="Update Checks" ) {
            /** Log Approval **/
            $TimeOffRequestLog->logEntry( $post->request_id, UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ),
                'Payroll approved by ' . UserSession::getFullUserInfo() );

            /** Change status to Completed PAFs */
            $requestReturnData = $TimeOffRequests->submitApprovalResponse(
                $TimeOffRequests->getRequestStatusCode( 'completedPAFs' ),
                $post->request_id );

            /** Log status change to Pending AS400 Upload **/
            $TimeOffRequestLog->logEntry(
                $post->request_id,
                UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ),
                'Status changed to Completed PAFs' );

            $result = new JsonModel( [
                'success' => true,
                'request_id' => $requestReturnData['request_id'],
                'action' => 'A',
                'request_id' => $post->request_id
            ] );
        } else {
            $result = new JsonModel( [
                'success' => false,
                'message' => 'There was an error submitting your request. Please try again.',
                'request_id' => $post->request_id
            ] );
        }

        return $result;
    }

    /**
     * Handle an API request to edit a Payroll Comment
     */
    public function submitPayrollModifyCommentAction() {
        $posted = true;
        if( empty( $data ) ) {
            $post = $this->getRequest()->getPost();
        } else {
            $posted = false;
            $post = (object) $data;
        }

        $TimeOffRequestLog = new TimeOffRequestLog();
        $TimeOffRequestLog->editLogEntry( $post );

        $result = new JsonModel( [
            'success' => true
        ] );

        return $result;
    }

    /**
     * Handle an API request to allow Payroll to edit a request in Completed PAFs queue.
     *
     * @return JsonModel
     */
    public function submitPayrollModifyCompletedPAFsAction() {
        $posted = true;
        if( empty( $data ) ) {
            $post = $this->getRequest()->getPost();
        } else {
            $posted = false;
            $post = (object) $data;
        }

        $Employee = new Employee();
        $TimeOffRequests = new TimeOffRequests();
        $TimeOffRequestLog = new TimeOffRequestLog();
        $validationHelper = new ValidationHelper();
        $requestData = $TimeOffRequests->findRequest( $post->request_id );
        $employeeData = (array) $requestData['EMPLOYEE_DATA'];

        // Check if there were any updates to the form
        $updatesToFormMade = $this->checkForUpdatesMadeToForm( $post, $requestData['ENTRIES'] );
        if( $updatesToFormMade ) {
            $this->logChangesMadeToRequest( $post );
        }

        if( $updatesToFormMade ) {
            $TimeOffRequestLog->logEntry(
                $post->request_id,
                UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ),
                'Request modified by ' . UserSession::getFullUserInfo() );
        }

        /** Log Payroll approval with comment **/
        $TimeOffRequestLog->logEntry(
            $post->request_id, UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ), 'Time off request Payroll modified by ' . UserSession::getFullUserInfo() .
            ' for ' . $requestData['EMPLOYEE_DATA']->EMPLOYEE_DESCRIPTION_ALT .
            ( (!empty( $post->payroll_comment )) ? ' with the comment: ' . $post->payroll_comment : '' ) );

        if ( $post->request_id != null ) {
            $result = new JsonModel( [
                'success' => true,
                'request_id' => $post->request_id,
                'posted' => $posted
            ] );
        } else {
            $result = new JsonModel( [
                'success' => false,
                'message' => 'There was an error submitting your request. Please try again.',
                'posted' => $posted
            ] );
        }

        return $result;
    }

    /**
     * Toggles option to receive calendar invites.
     *
     * @return JsonModel
     */
    protected function toggleCalendarInviteAction()
    {
        $EmployeeSchedules = new EmployeeSchedules();
        $post = $this->getRequest()->getPost();

        try {
            $EmployeeSchedules->toggleCalendarInvites( $post );

            /**
             * 200: Success.
             */
            $this->getResponse()->setStatusCode( 200 );
            return new JsonModel([
                'success' => true
            ]);
        } catch ( \Exception $ex ) {
             /**
             * 500: An error has occurred so the request couldn't be completed.
             */
            $this->getResponse()->setStatusCode( 500 );
            return new JsonModel([
                'success' => false,
                'message' => 'There was an error changing the calendar invite setting. Please try again.'
            ]);
        }
    }

    /**
     * Appends request data.
     *
     * @param array $post
     * @return array
     */
    protected function appendRequestData( $post )
    {
        $TimeOffRequests = new TimeOffRequests();
        $request = $TimeOffRequests->findRequest( $post->request_id );

        foreach( $request['ENTRIES'] as $key => $data ) {
            $date = \DateTime::createFromFormat( 'Y-m-d', $request['ENTRIES'][$key]['REQUEST_DATE'] );
            $post->request['dates'][$key] = [
                'date' => $date->format('Y-m-d'),
                'day_of_week' => strtoupper( $date->format('D') ),
                'type' => $request['ENTRIES'][$key]['REQUEST_CODE'],
                'hours' => number_format( $request['ENTRIES'][$key]['REQUESTED_HOURS'], 2 )
            ];
        }

        $post->request['forEmployee'] = (array) $request['EMPLOYEE_DATA'];

        return $post;
    }

    /**
     * Adds the current Employee Time Off information to the POST so we can save it with the request.
     *
     * @param type $post
     * @return type
     */
    protected function addRequestForEmployeeData( $post )
    {
        $Employee = new Employee();
        $post->request['forEmployee'] = (array) $Employee->findEmployeeTimeOffData( $post->request['forEmployee']['EMPLOYEE_NUMBER'], "Y",
            "EMPLOYEE_NUMBER, EMPLOYEE_NAME, EMAIL_ADDRESS, " .
            "MANAGER_EMPLOYEE_NUMBER, MANAGER_NAME, MANAGER_EMAIL_ADDRESS, " .
            "PTO_EARNED, PTO_TAKEN, PTO_UNAPPROVED, " .
            "PTO_PENDING, PTO_PENDING_TMP, PTO_PENDING_TOTAL, PTO_REMAINING, " .
            "FLOAT_EARNED, FLOAT_TAKEN, FLOAT_UNAPPROVED, " .
            "FLOAT_PENDING, FLOAT_PENDING_TMP, FLOAT_PENDING_TOTAL, FLOAT_REMAINING, " .
            "SICK_EARNED, SICK_TAKEN, SICK_UNAPPROVED, SICK_PENDING, SICK_PENDING_TMP, SICK_PENDING_TOTAL, " .
            "SICK_REMAINING, GF_EARNED, GF_TAKEN, GF_UNAPPROVED, GF_PENDING, GF_PENDING_TMP, " .
            "GF_PENDING_TOTAL, GF_REMAINING, UNEXCUSED_UNAPPROVED, UNEXCUSED_PENDING, " .
            "UNEXCUSED_PENDING_TMP, UNEXCUSED_PENDING_TOTAL, BEREAVEMENT_UNAPPROVED, " .
            "BEREAVEMENT_PENDING, BEREAVEMENT_PENDING_TMP, BEREAVEMENT_PENDING_TOTAL, CIVIC_DUTY_UNAPPROVED, ".
            "CIVIC_DUTY_PENDING, CIVIC_DUTY_PENDING_TMP, CIVIC_DUTY_PENDING_TOTAL, UNPAID_UNAPPROVED, " .
            "UNPAID_PENDING, UNPAID_PENDING_TMP, UNPAID_PENDING_TOTAL");

        return $post;
    }

    protected function addRequestByEmployeeData( $post )
    {
        $Employee = new Employee();
        $employeeNumber = \Login\Helper\UserSession::getUserSessionVariable('EMPLOYEE_NUMBER');
        $post->request['byEmployee'] = (array) $Employee->findEmployeeTimeOffData( $employeeNumber, "Y",
            "EMPLOYEE_NUMBER, EMPLOYEE_NAME, EMAIL_ADDRESS");

        return $post;
    }

    public function getEmployeeProfileAction()
    {
        $EmployeeSchedules = new EmployeeSchedules();
        $post = $this->getRequest()->getPost();

        try {
            $employeeData = $EmployeeSchedules->getEmployeeProfile( $post->employeeNumber );

            $result = new JsonModel([
                'success' => true,
                'sendInvitationsForMyself' => $employeeData['SEND_CALENDAR_INVITATIONS_TO_EMPLOYEE'],
                'sendInvitationsForMyReports' => $employeeData['SEND_CALENDAR_INVITATIONS_TO_MY_REPORTS']
            ]);
        } catch ( \Exception $ex ) {
            $result = new JsonModel([
                'success' => false,
                'message' => 'There was an error submitting your request. Please try again.'
            ]);
        }

        return $result;
    }

    public function submitEmployeeScheduleRequestAction()
    {
        $EmployeeSchedules = new EmployeeSchedules();
        $post = $this->getRequest()->getPost();

        try {
            $EmployeeSchedules->updateEmployeeSchedule( $post );

            $result = new JsonModel([
                'success' => true,
                'byEmployee' => $post->request['byEmployee']
            ]);
        } catch ( \Exception $ex ) {
            $result = new JsonModel([
                'success' => false,
                'message' => 'There was an error submitting your request. Please try again.'
            ]);
        }

        return $result;
    }

    /**
     * Checks if any date in the array of dates is 14 or greater days.
     *
     * @param type $dates
     * @return boolean
     */
    public function isFirstDateRequestedTooOld( $dates = [] )
    {
        $isFirstDateRequestedTooOld = false;
        $counter = 0;
        $compareToDate = date( 'Y-m-d', strtotime('-14 days') );

        foreach( $dates as $index => $selectedDateObject ) {
            if( $selectedDateObject['date'] <= $compareToDate ) {
                $counter++;
            }
        }
        if( $counter>0 ) {
            $isFirstDateRequestedTooOld = true;
        }

        return $isFirstDateRequestedTooOld;
    }

    /**
     * Submits the new Time Off Request for an employee.
     *
     * @return JsonModel
     */
    public function submitTimeoffRequestAction()
    {
        $calendarInviteService = $this->serviceLocator->get('CalendarInviteService');
        $calendarInviteService->setRequestId( '100036' );
        $calendarInviteService->send();


        die( "stoping here...from RequestApi.php" );

        $Employee = new Employee();
        $TimeOffRequests = new TimeOffRequests();
        $TimeOffRequestLog = new TimeOffRequestLog();

        /** Clean up / append data to the Request **/
        $post = $this->getRequest()->getPost();
        $post = $this->cleanUpRequestedDates( $post );
        $post = $this->addRequestForEmployeeData( $post );
        $post = $this->addRequestByEmployeeData( $post );

        $isCreatorInEmployeeHierarchy = $Employee->isCreatorInEmployeeHierarchy($post->request['byEmployee']['EMPLOYEE_NUMBER'], $post->request['forEmployee']['EMPLOYEE_NUMBER']);
        $isCreatorProxyForManagerInHierarchy = $Employee->isCreatorProxyForManagerInHierarchy($post->request['byEmployee']['EMPLOYEE_NUMBER'], $post->request['forEmployee']['EMPLOYEE_NUMBER']);

        $proxyLogText = '';
        if ($isCreatorInEmployeeHierarchy == false && $isCreatorProxyForManagerInHierarchy == true) {
            $proxyForEmployee = $Employee->getCreatorProxyForManagerInHierarchy($post->request['byEmployee']['EMPLOYEE_NUMBER'], $post->request['forEmployee']['EMPLOYEE_NUMBER']);
            $proxyLogText = ' as proxy for ' . $proxyForEmployee;
        }

        $isRequestToBeAutoApproved = $Employee->isRequestToBeAutoApproved( $post->request['forEmployee']['EMPLOYEE_NUMBER'],
                                                                           $post->request['byEmployee']['EMPLOYEE_NUMBER'] );

        /** Ensure Employee has a default schedule created **/
        $Employee->ensureEmployeeScheduleIsDefined( $post->request['forEmployee']['EMPLOYEE_NUMBER'] );

        /** Submit the request to employee's manager; get the Request ID **/
        $requestReturnData = $TimeOffRequests->submitRequestForManagerApproval( $post );
        $requestId = $requestReturnData['request_id'];

        /** Log creation of this request **/
        $TimeOffRequestLog->logEntry(
            $requestId,
            $post->request['byEmployee']['EMPLOYEE_NUMBER'],
            'Created by ' . $post->request['byEmployee']['EMPLOYEE_DESCRIPTION_ALT'] . $proxyLogText);

        if( $isRequestToBeAutoApproved || (
                $isCreatorInEmployeeHierarchy == false &&
                $Employee->isPayroll(trim($post->request['forEmployee']['EMPLOYEE_NUMBER'])) == 'N' &&
                \Login\Helper\UserSession::getUserSessionVariable('IS_PAYROLL') == 'Y'))
        {
            $post['request_id'] = $requestId;
            $this->emailRequestToEmployee( $requestId, $post );
            $this->sendCalendarInvitationsForRequestToEnabledUsers( $post );

            $return = $this->submitManagerApprovedAction( [ 'request_id' => $requestId,
                'review_request_reason' => 'System auto-approved request because requester is in manager or supervisor hierarchy of ' .
                $post->request['forEmployee']['EMPLOYEE_DESCRIPTION_ALT'] . "." ] );

            return $return;
        } else {
            /** Log change in status to Pending Manager Approval **/
            $TimeOffRequestLog->logEntry(
                $requestId,
                $post->request['byEmployee']['EMPLOYEE_NUMBER'],
                'Sent for manager approval to ' . $post->request['forEmployee']['MANAGER_DESCRIPTION_ALT'] );

            /** Send email to employee and manager; grab data to email out **/
            $this->emailRequestToEmployee( $requestId, $post );
            $this->emailRequestToManager( $requestId, $post );

            if( $requestReturnData['request_id']!=null ) {
                $result = new JsonModel([
                    'success' => true,
                    'request_id' => $requestReturnData['request_id']
                ]);
            } else {
                $result = new JsonModel([
                    'success' => false,
                    'message' => 'There was an error submitting your request. Please try again.'
                ]);
            }

            return $result;
        }
    }

    /**
     * Get the total hours requested and block of HTML to insert into emails regarding the request.
     *
     * @param integer $requestId
     * @return array
     */
    protected function getEmailRequestVariables( $requestId )
    {
        $TimeOffRequests = new TimeoffRequests();
        $timeoffRequestData = $TimeOffRequests->findRequest( $requestId );

        return [ 'totalHoursRequested' => $TimeOffRequests->countTimeoffRequested( $requestId ),
                 'hoursRequestedHtml' => $TimeOffRequests->drawHoursRequested( $timeoffRequestData['ENTRIES'] )
               ];
    }

    /**
     * Get HTML blocks for changes made to a request.
     *
     * @param type $requestId
     * @return type
     */
    protected function getEmailRequestChangesVariables( $requestId )
    {
        $TimeOffRequests = new TimeoffRequests();
        $timeoffRequestData = $TimeOffRequests->findRequest( $requestId );

        return [ 'forName' => $timeoffRequestData->EMPLOYEE_DATA->EMPLOYEE_NAME,
                 'forEmail' => $timeoffRequestData->EMPLOYEE_DATA->EMAIL_ADDRESS,
                 'managerName' => $timeoffRequestData->EMPLOYEE_DATA->MANAGER_NAME,
                 'managerEmail' => $timeoffRequestData->EMPLOYEE_DATA->MANAGER_EMAIL_ADDRESS,
                 'oldHoursRequestedHtml' => $TimeOffRequests->drawHoursRequested( $timeoffRequestData->CHANGES_MADE->UPDATE_DETAIL->old ),
                 'newHoursRequestedHtml' => $TimeOffRequests->drawHoursRequested( $timeoffRequestData->CHANGES_MADE->UPDATE_DETAIL->new )
               ];
    }

    protected function getArrayOfRequestChanges( $requestId )
    {
        $TimeOffRequests = new TimeoffRequests();
        $timeoffRequestData = $TimeOffRequests->findRequest( $requestId );

        return [ 'old' => $TimeOffRequests->drawHoursRequested( $timeoffRequestData->CHANGES_MADE->UPDATE_DETAIL->old, "array" ),
                 'new' => $TimeOffRequests->drawHoursRequested( $timeoffRequestData->CHANGES_MADE->UPDATE_DETAIL->new, "array" )
               ];
    }

    /**
     * Send the employee an email confirming their request.
     *
     * @param integer $requestId
     */
    protected function emailRequestToEmployee( $requestId, $post )
    {
        $emailVariables = $this->getEmailRequestVariables( $requestId );
        $to = $post->request['forEmployee']['EMAIL_ADDRESS'];
        $cc = '';
        if( $this->overrideEmails==1 && !empty( $this->emailOverrideList ) ) {
            $to = $this->emailOverrideList;
        }
        $Email = new EmailFactory(
            'Time off requested for ' . $post->request['forEmployee']['EMPLOYEE_DESCRIPTION_ALT'],
            'A total of ' . $emailVariables['totalHoursRequested'] . ' hours were requested off for ' .
                $post->request['forEmployee']['EMPLOYEE_DESCRIPTION_ALT'] . ' by ' .
                $post->request['byEmployee']['EMPLOYEE_DESCRIPTION_ALT'] . '<br /><br />' .
                $emailVariables['hoursRequestedHtml'],
            $to,
            $cc
        );
        $Email->send();
    }

    /**
     * Send the employee's manager an email confriming the request.
     *
     * @param integer $requestId
     */
    protected function emailRequestToManager( $requestId, $post )
    {
        $renderer = $this->serviceLocator->get( 'Zend\View\Renderer\RendererInterface' );
        $reviewUrl = ( ( ENVIRONMENT==='development' || ENVIRONMENT==='testing' ) ? 'http://swift:10080' : 'http://aswift:10080' ) .
            $renderer->basePath( '/request/review-request/' . $requestId );
        $emailVariables = $this->getEmailRequestVariables( $requestId );
        $to = $post->request['forEmployee']['MANAGER_EMAIL_ADDRESS'];
        $cc = '';
        if( $this->overrideEmails==1 && !empty( $this->emailOverrideList ) ) {
            $to = $this->emailOverrideList;
        }
        $Email = new EmailFactory(
            'Time off requested for ' . $post->request['forEmployee']['EMPLOYEE_DESCRIPTION_ALT'],
            'A total of ' . $emailVariables['totalHoursRequested'] . ' hours were requested off for ' .
                $post->request['forEmployee']['EMPLOYEE_DESCRIPTION_ALT'] . ' by ' .
                $post->request['byEmployee']['EMPLOYEE_DESCRIPTION_ALT'] . '<br /><br />' .
                $emailVariables['hoursRequestedHtml'] . '<br /><br />' .
                'Please review this request at the following URL:<br /><br />' .
                '<a href="' . $reviewUrl . '">' . $reviewUrl . '</a>',
            $to,
            $cc
        );
        $Email->send();
    }

    /**
     * Send the employee an email about their request being denied.
     *
     * @param integer $requestId
     */
    protected function emailDeniedNoticeToEmployee( $post, $deniedBy )
    {
        $renderer = $this->serviceLocator->get( 'Zend\View\Renderer\RendererInterface' );
        $reviewUrl = ( ( ENVIRONMENT==='development' || ENVIRONMENT==='testing' ) ? 'http://swift:10080' : 'http://aswift:10080' ) .
            $renderer->basePath( '/request/review-request/' . $post->request_id );
        $emailVariables = $this->getEmailRequestVariables( $post->request_id );
        $to = $post->request['forEmployee']['EMAIL_ADDRESS'];
        $cc = ''; //$post->request['forEmployee']['MANAGER_EMAIL_ADDRESS'];
        if( $this->overrideEmails==1 && !empty( $this->emailOverrideList ) ) {
            $to = $this->emailOverrideList;
            $cc = '';
        }

        $Email = new EmailFactory(
            'Time off request for ' . $post->request['forEmployee']['EMPLOYEE_DESCRIPTION_ALT'] . ' was denied',
            'The request for ' .
                $post->request['forEmployee']['EMPLOYEE_DESCRIPTION_ALT'] . ' has been denied by ' . $deniedBy .
                ( !empty( $post->review_request_reason ) ? ' with the reason: ' . $post->review_request_reason : '' ) . '<br /><br />' .
                $emailVariables['hoursRequestedHtml'] . '<br /><br />' .
                'For details please visit the following URL:<br /><br />' .
                '<a href="' . $reviewUrl . '">' . $reviewUrl . '</a>',
            $to,
            $cc
        );
        $Email->send();
    }

    /**
     * Emails an upload notice to Payroll of Status change to Upload
     *
     * @param type $post
     */
    protected function emailUploadNoticeToPayroll( $post )
    {
        $renderer = $this->serviceLocator->get( 'Zend\View\Renderer\RendererInterface' );
        $reviewUrl = ( ( ENVIRONMENT==='development' || ENVIRONMENT==='testing' ) ? 'http://swift:10080' : 'http://aswift:10080' ) .
            $renderer->basePath( '/request/review-request/' . $post->request_id );
        $emailVariables = $this->getEmailRequestVariables( $post->request_id );
        $to = $post->request['forEmployee']['EMAIL_ADDRESS'];
        $cc = $post->request['forEmployee']['MANAGER_EMAIL_ADDRESS'];
        if( $this->overrideEmails==1 && !empty( $this->emailOverrideList ) ) {
            $to = $this->emailOverrideList;
            $cc = '';
        }

        $Email = new EmailFactory(
            'Time off request for ' . $post->request['forEmployee']['EMPLOYEE_DESCRIPTION_ALT'] . ' status changed to Upload',
            'The request for ' .
                $post->request['forEmployee']['EMPLOYEE_DESCRIPTION_ALT'] . ' has changed status to Upload' .
                ( !empty( $post->review_request_reason ) ? ' with the reason: ' . $post->review_request_reason : '' ) . '<br /><br />' .
                $emailVariables['hoursRequestedHtml'] . '<br /><br />' .
                'For details please visit the following URL:<br /><br />' .
                '<a href="' . $reviewUrl . '">' . $reviewUrl . '</a>',
            $to,
            $cc
        );
        $Email->send();
    }

    /**
     * Send email of changes made to request.
     *
     * @param type $post
     */
    protected function emailChangesToRequestMade( $post )
    {
        $renderer = $this->serviceLocator->get( 'Zend\View\Renderer\RendererInterface' );
        $reviewUrl = ( ( ENVIRONMENT==='development' || ENVIRONMENT==='testing' ) ? 'http://swift:10080' : 'http://aswift:10080' ) .
            $renderer->basePath( '/request/review-request/' . $post->request_id );
        //$emailVariables = $this->getEmailRequestVariables( $post->request_id );
        $emailVariables = $this->getEmailRequestChangesVariables( $post->request_id );

        $to = $emailVariables['forEmail'];
        $cc = $emailVariables['managerEmail'];
        if( $this->overrideEmails==1 && !empty( $this->emailOverrideList ) ) {
            $to = $this->emailOverrideList;
            $cc = '';
        }

        $Email = new EmailFactory(
            'Time off request has been modified',
            'The request for ' .
                $emailVariables['forName'] . ' has been modified' .
                '<br /><br />' .
                '<strong>Original request:<br /><br />' .
                $emailVariables['oldHoursRequestedHtml'] . '<br /><br />' .
                '<strong>Modified request:<br /><br />' .
                $emailVariables['newHoursRequestedHtml'] . '<br /><br />' .
                'For details please visit the following URL:<br /><br />' .
                '<a href="' . $reviewUrl . '">' . $reviewUrl . '</a>',
            $to,
            $cc
        );
        $Email->send();
    }

    /**
     * Returns boolean if request entry was marked as edited.
     *
     * @param type $request
     * @return type
     */
    private function requestEntryIsUpdated( $entry )
    {
        return ( ( $this->requestEntryIsDeleted( $entry ) ||  $this->requestEntryIsAdded( $entry ) ||
                   $this->requestEntryIsEdited( $entry )
                 ) ? true : false
               );
    }

    /**
     * Returns boolean if request entry was marked to be deleted.
     *
     * @param type $request
     * @return type
     */
    private function requestEntryIsDeleted( $entry )
    {
        return ( ( array_key_exists( 'entryId', $entry ) &&
                   array_key_exists( 'isDeleted', $entry ) &&
                   $entry['isDeleted']=="true"
                 ) ? true : false );
    }

    /**
     * Returns boolean if request entry was added.
     *
     * @param type $request
     * @return type
     */
    private function requestEntryIsAdded( $entry )
    {
        return ( ( !array_key_exists( 'entryId', $entry ) &&
                   array_key_exists( 'isAdded', $entry ) &&
                   $entry['isAdded']=="true"
                 ) ? true : false );
    }

    /**
     * Returns boolean if request entry was marked to be deleted.
     *
     * @param type $request
     * @return type
     */
    private function requestEntryIsEdited( $entry )
    {
        $isEdited = false;
        if( array_key_exists("entryId", $entry) ) {
            $entryId = (int) $entry["entryId"];
            $RequestEntry = new RequestEntry();
            $originalEntryIdData = $RequestEntry->getRequestEntry( $entryId );

//             var_dump( $originalEntryIdData );
//             var_dump( $entry );
//             die();

            if( $isEdited===false &&
                ( $entry['category']!=self::$codesToTypes[$originalEntryIdData->REQUEST_CODE] ||
                  $entry['hours']!=$originalEntryIdData->REQUESTED_HOURS
                ) ) {
                $isEdited = true;
            }
        }

        return $isEdited;
    }

    /**
     * Checks if updates have been made to original request submitted.
     *
     * @param type $post
     * @param type $requestedDatesOld
     * @return boolean
     */
    public function checkForUpdatesMadeToForm( $post, $requestedDatesOld )
    {
        $updatesMadeToForm = false;
        if( property_exists( $post, "selectedDatesNew" )===false ) {
            return $updatesMadeToForm;
        }

        foreach( $post->selectedDatesNew as $ctr => $entry ) {
            if( $updatesMadeToForm===false && $this->requestEntryIsUpdated( $entry ) ) {
                $updatesMadeToForm = true;
            }
        }

        if( $updatesMadeToForm ) {
            $TimeOffRequests = new TimeOffRequests();
            foreach( $post->selectedDatesNew as $ctr => $entry ) {
                if( $this->requestEntryIsEdited( $entry ) ) {
                    $data = [ 'ENTRY_ID' => $entry['entryId'],
                              'REQUEST_ID' => $post->request_id,
                              'REQUEST_DATE' => $entry['date'],
                              'REQUESTED_HOURS' => $entry['hours'],
                              'REQUEST_CATEGORY' => $entry['category'],
                              'REQUEST_DAY_OF_WEEK' => $entry['dow']
                            ];

                    $TimeOffRequests->copyRequestEntriesToArchive( $post->request_id );
                    $TimeOffRequests->updateRequestEntry( $data );
                }
                if( $this->requestEntryIsDeleted( $entry ) ) {
                    $TimeOffRequests->copyRequestEntriesToArchive( $post->request_id );
                    $TimeOffRequests->markRequestEntryAsDeleted( $entry['entryId'] );
                }
                if( $this->requestEntryIsAdded( $entry ) ) {
                    $data = [ 'REQUEST_ID' => $post->request_id,
                              'REQUEST_DATE' => $entry['date'],
                              'REQUESTED_HOURS' => $entry['hours'],
                              'REQUEST_CATEGORY' => $entry['category'],
                              'REQUEST_DAY_OF_WEEK' => $entry['dow']
                            ];

                    $TimeOffRequests->addRequestEntry( $data );
                }

            }

            $TimeOffRequests = new TimeOffRequests();
            $newRequest = $TimeOffRequests->findRequest( $post->request_id );

            $update_detail = [ 'old' => $requestedDatesOld,
                               'new' => $newRequest['ENTRIES']
            ];

            $TimeOffRequests->addRequestUpdate( $post->loggedInUserEmployeeNumber, $post->request_id, $update_detail );
        }

        return $updatesMadeToForm;
    }

    /**
     * Handles the manager approval process.
     *
     * @return JsonModel
     */
    public function submitManagerApprovedAction( $data = [] )
    {
        $posted = true;
        if( empty( $data ) ) {
            $post = $this->getRequest()->getPost();
        } else {
            $posted = false;
            $post = (object) $data;
        }

        $Employee = new Employee();
        $TimeOffRequests = new TimeOffRequests();
        $TimeOffRequestLog = new TimeOffRequestLog();
        $validationHelper = new ValidationHelper();
        $requestData = $TimeOffRequests->findRequest( $post->request_id );

        // Check if there were any updates to the form
        $updatesToFormMade = $this->checkForUpdatesMadeToForm( $post, $requestData['ENTRIES'] );
        if( $updatesToFormMade ) {
            $this->logChangesMadeToRequest( $post );
            // Re-grab requestData since there may have been updates
            $requestData = $TimeOffRequests->findRequest( $post->request_id );
        }

        $employeeData = (array) $requestData['EMPLOYEE_DATA'];

        $dates = [];
        foreach( $requestData['ENTRIES'] as $ctr => $requestObject ) {
            $dates[]['date'] = $requestObject['REQUEST_DATE'];
        }

        $isFirstDateRequestedTooOld = $this->isFirstDateRequestedTooOld( $dates );
        $isPayrollReviewRequired = $validationHelper->isPayrollReviewRequired( $post->request_id, $requestData['EMPLOYEE_NUMBER'] ); // $validationHelper->isPayrollReviewRequired( $requestData, $employeeData );

        $proxyLogText = '';
        if ($requestData['EMPLOYEE_NUMBER'] != UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' )) {
            $proxyLogText = ' as proxy for ' . $requestData['EMPLOYEE_DATA']->MANAGER_DESCRIPTION_ALT;
        }

        if ( $isPayrollReviewRequired === true || $isFirstDateRequestedTooOld === true ) {
            $payrollReviewRequiredReason = '';
            if( $isPayrollReviewRequired ) {
                $payrollReviewRequiredReason = 'Payroll review required because of insufficient hours in one or more categories, and/or Civic Duty requested.';
            }
            if( $isFirstDateRequestedTooOld ) {
                $payrollReviewRequiredReason = 'Payroll review required because one or more days requested is at least 14 days old.';
            }
            /** Log supervisor approval with comment **/
            $TimeOffRequestLog->logEntry(
                $post->request_id, UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ), 'Time off request approved by ' . UserSession::getFullUserInfo() .
                $proxyLogText . ' for ' . $requestData['EMPLOYEE_DATA']->EMPLOYEE_DESCRIPTION_ALT .
                ( (!empty( $post->manager_comment )) ? ' with the comment: ' . $post->manager_comment : '' ) );

            /** Change status to Approved */
            $requestReturnData = $TimeOffRequests->submitApprovalResponse(
                $TimeOffRequests->getRequestStatusCode( 'approved' ),
                $post->request_id,
                $post->review_request_reason );

            /** Log request with payroll review required reason **/
            $TimeOffRequestLog->logEntry(
                $post->request_id,
                UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ),
                $payrollReviewRequiredReason );

            /** Change status to Pending Payroll Approval */
            $requestReturnData = $TimeOffRequests->submitApprovalResponse(
                $TimeOffRequests->getRequestStatusCode( 'pendingPayrollApproval' ),
                $post->request_id,
                $post->review_request_reason );
        } else {
            $this->sendCalendarInvitationsForRequestToEnabledUsers( $post );
            $RequestEntry = new RequestEntry();
            $dateRequestBlocks = $RequestEntry->getRequestObject( $post->request_id );

            /** Log supervisor approval with comment **/
            $supervisorApprovalComment = 'Approved by ' . UserSession::getFullUserInfo() .
                $proxyLogText . (!empty( $post->manager_comment ) ? ' with the comment: ' . $post->manager_comment : '' );
            if( property_exists( $post, "review_request_reason" ) ) { // In case this is an auto-approval
                $supervisorApprovalComment = $post->review_request_reason;
            }
            $TimeOffRequestLog->logEntry(
                $post->request_id,
                UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ),
                $supervisorApprovalComment );

            /** Change status to Approved */
            $requestReturnData = $TimeOffRequests->submitApprovalResponse(
                $TimeOffRequests->getRequestStatusCode( 'approved' ),
                $post->request_id,
                $post->review_request_reason );

            /** Log status change to Pending AS400 Upload **/
            $TimeOffRequestLog->logEntry(
                $post->request_id,
                UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ),
                'Status changed to Pending AS400 Upload' );

            /** Change status to Pending AS400 Upload */
            $requestReturnData = $TimeOffRequests->submitApprovalResponse(
                $TimeOffRequests->getRequestStatusCode( 'pendingAS400Upload' ),
                $post->request_id,
                $post->review_request_reason );

            /** Write record(s) to HPAPAATMP or PAPAATMP **/
            $Papaa = new Papaatmp();
            $Papaa->prepareToWritePapaatmpRecords( $employeeData, $dateRequestBlocks, $post->request_id );
        }

        if ( $requestReturnData['request_id'] != null ) {
            $result = new JsonModel( [
                'success' => true,
                'request_id' => $requestReturnData['request_id'],
                'action' => 'A',
                'posted' => $posted
            ] );
        } else {
            $result = new JsonModel( [
                'success' => false,
                'message' => 'There was an error submitting your request. Please try again.',
                'posted' => $posted
            ] );
        }

        return $result;
    }

    public function sendCalendarInvitationsForRequestToEnabledUsers( $post )
    {
        $forEmployeeNumber = null;
        $post = (array) $post;
        if( array_key_exists( 'request', $post ) ) {
            $forEmployeeNumber = $post['request']['forEmployee']['EMPLOYEE_NUMBER'];
        } elseif( array_key_exists( 'loggedInUserEmployeeNumber', $post ) ) {
            $forEmployeeNumber = $post['loggedInUserEmployeeNumber'];
        }

        if( !is_null( $forEmployeeNumber ) ) {
            $EmployeeSchedules = new EmployeeSchedules();
            $employeeProfile = $EmployeeSchedules->getEmployeeProfile( $forEmployeeNumber );

            $OutlookHelper = new OutlookHelper();
            $RequestEntry = new RequestEntry();
            $TimeOffRequests = new TimeOffRequests();
            $Employee = new Employee();
            $calendarInviteData = $TimeOffRequests->findRequestCalendarInviteData( $post['request_id'] );
            $dateRequestBlocks = $RequestEntry->getRequestObject( $post['request_id'] );
            $employeeData = $Employee->findEmployeeTimeOffData( $dateRequestBlocks['for']['employee_number'], "Y", "EMPLOYER_NUMBER, EMPLOYEE_NUMBER, LEVEL_1, LEVEL_2, LEVEL_3, LEVEL_4, SALARY_TYPE" );

            if( $employeeProfile['SEND_CALENDAR_INVITATIONS_TO_EMPLOYEE'] || $employeeProfile['SEND_CALENDAR_INVITATIONS_TO_MANAGER'] ) {
                $OutlookHelper->addToCalendar( $calendarInviteData, $employeeData, $employeeProfile['SEND_CALENDAR_INVITATIONS_TO_EMPLOYEE'],
                    $employeeProfile['SEND_CALENDAR_INVITATIONS_TO_MANAGER'] );
            }
        }
    }

    /**
     * Handles the manager denied process.
     *
     * @return JsonModel
     */
    public function submitManagerDeniedAction()
    {
        $post = $this->getRequest()->getPost();

        $Employee = new Employee();
        $TimeOffRequests = new TimeOffRequests();
        $TimeOffRequestLog = new TimeOffRequestLog();
        $requestData = $TimeOffRequests->findRequest( $post->request_id );
        $employeeData = $Employee->findEmployeeTimeOffData( $requestData['EMPLOYEE_NUMBER'] );

        $post->review_request_reason = $post->manager_comment;

        $post->request['forEmployee']['EMAIL_ADDRESS'] = $requestData['EMPLOYEE_DATA']->EMAIL_ADDRESS;
        $post->request['forEmployee']['MANAGER_EMAIL_ADDRESS'] = $requestData['EMPLOYEE_DATA']->MANAGER_EMAIL_ADDRESS;
        $post->request['forEmployee']['EMPLOYEE_DESCRIPTION_ALT'] = $requestData['EMPLOYEE_DATA']->EMPLOYEE_DESCRIPTION_ALT;

        $this->emailDeniedNoticeToEmployee( $post, UserSession::getFullUserInfo() );

        /** Log supervisor deny with comment **/
        $TimeOffRequestLog->logEntry(
            $post->request_id,
            UserSession::getUserSessionVariable('EMPLOYEE_NUMBER'),
            'Time off request denied by ' . UserSession::getFullUserInfo() .
            ' for ' . $post->request['forEmployee']['EMPLOYEE_DESCRIPTION_ALT'] .
            ( !empty( $post->review_request_reason ) ? ' with the comment: ' . $post->review_request_reason : '' ));

        /** Change status to Denied */
        $requestReturnData = $TimeOffRequests->submitApprovalResponse(
            $TimeOffRequests->getRequestStatusCode( 'denied' ),
            $post->request_id,
            $post->review_request_reason );

        /* mark entries as deleted */
        $TimeOffRequests->markRequestEntryAsDeletedByRequest($post->request_id);

        if($requestReturnData['request_id']!=null) {
            $result = new JsonModel([
                'success' => true,
                'request_id' => $requestReturnData['request_id'],
                'action' => 'D'
            ]);
        } else {
            $result = new JsonModel([
                'success' => false,
                'message' => 'There was an error submitting your request. Please try again.'
            ]);
        }

        return $result;
    }

    /**
     * Adds log entries to show:
     * 1. Who modified the requested dates
     * 2. What the original request dates looked like
     * 3. What the changes are.
     * s
     * @param unknown $post
     */
    public function logChangesMadeToRequest( $post )
    {
        $TimeOffRequestLog = new TimeOffRequestLog();
        $arrayOfRequestChangesMade = $this->getArrayOfRequestChanges( $post->request_id );
        $oldText = implode( ", ", $arrayOfRequestChangesMade['old'] );
        $newText = implode( ", ", $arrayOfRequestChangesMade['new'] );

        $TimeOffRequestLog->logEntry(
            $post->request_id,
            UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ),
            'Request modified by ' . UserSession::getFullUserInfo() );

        $TimeOffRequestLog->logEntry(
            $post->request_id,
            UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ),
            'Request changed from: ' . $oldText );

        $TimeOffRequestLog->logEntry(
            $post->request_id,
            UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ),
            'Request changed to: ' . $newText );

        $this->emailChangesToRequestMade( $post );
    }

    /**
     * Handles the Payroll approval process.
     *
     * @return JsonModel
     */
    public function submitPayrollApprovedAction()
    {
        $post = $this->getRequest()->getPost();

//         echo '<pre>POST';
//         var_dump( $post );
//         echo '</pre>';

        $Employee = new Employee();
        $TimeOffRequests = new TimeOffRequests();
        $TimeOffRequestLog = new TimeOffRequestLog();
        $validationHelper = new ValidationHelper();
        $requestData = $TimeOffRequests->findRequest( $post->request_id );
        $OutlookHelper = new OutlookHelper();
        $RequestEntry = new RequestEntry();
        $calendarInviteData = $TimeOffRequests->findRequestCalendarInviteData( $post->request_id );
        $dateRequestBlocks = $RequestEntry->getRequestObject( $post->request_id );

        $requestData = $TimeOffRequests->findRequest( $post->request_id );

        // Check if there were any updates to the form
        $updatesToFormMade = $this->checkForUpdatesMadeToForm( $post, $requestData['ENTRIES'] );
        if( $updatesToFormMade ) {
            $this->logChangesMadeToRequest( $post );
            // Re-grab requestData since there may have been updates
            $requestData = $TimeOffRequests->findRequest( $post->request_id );
        }

        $employeeData = (array) $requestData['EMPLOYEE_DATA'];

//         // Check if there were any updates to the form
//         $updatesToFormMade = $this->checkForUpdatesMadeToForm( $post, $requestData['ENTRIES'] );
//         if( $updatesToFormMade ) {
//             $this->logChangesMadeToRequest( $post );
//             // Re-grab $dateRequestBlocks since there may have been updates
//             $dateRequestBlocks = $RequestEntry->getRequestObject( $post->request_id );
//         }
//         $employeeData = $Employee->findEmployeeTimeOffData( $dateRequestBlocks['for']['employee_number'], "Y", "EMPLOYER_NUMBER, EMPLOYEE_NUMBER, LEVEL_1, LEVEL_2, LEVEL_3, LEVEL_4, SALARY_TYPE" );

        try {
            $dateRequestBlocks = $RequestEntry->getRequestObject( $post->request_id );

            /** Log Payroll approval with comment **/
            $TimeOffRequestLog->logEntry(
                $post->request_id, UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ), 'Time off request Payroll approved by ' . UserSession::getFullUserInfo() .
                ' for ' . $requestData['EMPLOYEE_DATA']->EMPLOYEE_DESCRIPTION_ALT .
                ( (!empty( $post->payroll_comment )) ? ' with the comment: ' . $post->payroll_comment : '' ) );

            /** Change status to Pending AS400 Upload */
            $requestReturnData = $TimeOffRequests->submitApprovalResponse(
                $TimeOffRequests->getRequestStatusCode( 'pendingAS400Upload' ),
                $post->request_id,
                $post->review_request_reason );

            /** Log status change to Pending AS400 Upload **/
            $TimeOffRequestLog->logEntry(
                $post->request_id,
                UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ),
                'Status changed to Pending AS400 Upload' );

            /** Send calendar invites for this request **/
            $isSent = $this->sendCalendarInvitationsForRequestToEnabledUsers( $post );

            /** Log sending calendar invites **/
            $TimeOffRequestLog->logEntry(
                $post->request_id,
                UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ),
                'Sent calendar invites of the request' );

            /** Write record(s) to HPAPAATMP or PAPAATMP **/
            $Papaa = new Papaatmp();
            $Papaa->prepareToWritePapaatmpRecords( $employeeData, $dateRequestBlocks, $post->request_id );

            $result = new JsonModel([
                'success' => true,
                'request_id' => $post->request_id
            ]);
        } catch ( \Exception $ex ) {
            $result = new JsonModel([
                'success' => false,
                'message' => 'There was an error submitting your request. Please try again.',
                'ex' => $ex
            ]);
        }

        return $result;
    }

    /**
     * Handles the Payroll denied process.
     *
     * @return JsonModel
     */
    public function submitPayrollDeniedAction()
    {
        /** Clean up / append data to the Request **/
        $post = $this->getRequest()->getPost();
        $post = $this->appendRequestData( $post );
        $post = $this->addRequestForEmployeeData( $post );

        $Employee = new Employee();
        $TimeOffRequests = new TimeOffRequests();
        $TimeOffRequestLog = new TimeOffRequestLog();
        $requestData = $TimeOffRequests->findRequest( $post->request_id );

        try {
            $deniedBy = ( $requestData['REQUEST_STATUS_DESCRIPTION']=='Pending Payroll Approval' ? 'Payroll' : UserSession::getFullUserInfo() );
            $this->emailDeniedNoticeToEmployee( $post, $deniedBy );

            /** Log Payroll denied with comment **/
            $TimeOffRequestLog->logEntry(
                $post->request_id, UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ), 'Time off request Payroll denied by ' . UserSession::getFullUserInfo() .
                ( (!empty( $post->review_request_reason )) ? ' with the comment: ' . $post->review_request_reason : '' ) );

            /** Change status to Denied */
            $requestReturnData = $TimeOffRequests->submitApprovalResponse(
                $TimeOffRequests->getRequestStatusCode( 'denied' ),
                $post->request_id,
                $post->review_request_reason );

            /** Log status change to Denied **/
            $TimeOffRequestLog->logEntry(
                $post->request_id,
                UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ),
                'Status changed to Denied' );

            /* mark entries as deleted */
            $TimeOffRequests->markRequestEntryAsDeletedByRequest($post->request_id);

            $result = new JsonModel([
                'success' => true,
                'request_id' => $post->request_id
            ]);
        } catch ( \Exception $ex ) {
            $result = new JsonModel([
                'success' => false,
                'message' => 'There was an error submitting your request. Please try again.'
            ]);
        }

        return $result;
    }

    /**
     * Handles the Payroll upload process.
     *
     * @return JsonModel
     */
    public function submitPayrollUploadAction()
    {
        /** Clean up / append data to the Request **/
        $post = $this->getRequest()->getPost();
        $post = $this->appendRequestData( $post );
        $post = $this->addRequestForEmployeeData( $post );
        $Employee = new Employee();
        $TimeOffRequests = new TimeOffRequests();
        $TimeOffRequestLog = new TimeOffRequestLog();
        $RequestEntry = new RequestEntry();
        $requestData = $TimeOffRequests->findRequest( $post->request_id );

        // Check if there were any updates to the form
        $updatesToFormMade = $this->checkForUpdatesMadeToForm( $post, $requestData['ENTRIES'] );
        if( $updatesToFormMade ) {
            $this->logChangesMadeToRequest( $post );
            // Re-grab requestData since there may have been updates
            $requestData = $TimeOffRequests->findRequest( $post->request_id );
        }

        try {
            /** Change status to Upload */
            $requestReturnData = $TimeOffRequests->submitApprovalResponse(
                $TimeOffRequests->getRequestStatusCode( 'pendingAS400Upload' ),
                $post->request_id,
                $post->review_request_reason );

            /** Log status change to Upload **/
            $TimeOffRequestLog->logEntry(
                $post->request_id, UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ), 'Status changed to Pending AS400 Upload by ' . UserSession::getFullUserInfo() .
                ( (!empty( $post->review_request_reason )) ? ' with the comment: ' . $post->review_request_reason : '' ) );

            $dateRequestBlocks = $RequestEntry->getRequestObject( $post->request_id );
            $employeeData = $Employee->findEmployeeTimeOffData( $dateRequestBlocks['for']['employee_number'], "Y", "EMPLOYER_NUMBER, EMPLOYEE_NUMBER, LEVEL_1, LEVEL_2, LEVEL_3, LEVEL_4, SALARY_TYPE" );

            /** Write record(s) to HPAPAATMP or PAPAATMP **/
            $Papaa = new Papaatmp();
            $Papaa->prepareToWritePapaatmpRecords( $employeeData, $dateRequestBlocks, $post->request_id );

            $result = new JsonModel([
                'success' => true,
                'request_id' => $post->request_id
            ]);
        } catch ( \Exception $ex ) {
            $result = new JsonModel([
                'success' => false,
                'message' => 'There was an error submitting your request. Please try again.'
            ]);
        }

        return $result;
    }

    /**
     * Handles the Payroll update checks process.
     *
     * @return JsonModel
     */
    public function submitPayrollUpdateChecksAction()
    {
        /** Clean up / append data to the Request **/
        $post = $this->getRequest()->getPost();
        $post = $this->appendRequestData( $post );
        $post = $this->addRequestForEmployeeData( $post );
        $Employee = new Employee();
        $TimeOffRequests = new TimeOffRequests();
        $TimeOffRequestLog = new TimeOffRequestLog();
        $requestData = $TimeOffRequests->findRequest( $post->request_id );

        // Check if there were any updates to the form
        $updatesToFormMade = $this->checkForUpdatesMadeToForm( $post, $requestData['ENTRIES'] );
        if( $updatesToFormMade ) {
            $this->logChangesMadeToRequest( $post );
            // Re-grab requestData since there may have been updates
            $requestData = $TimeOffRequests->findRequest( $post->request_id );
        }

        try {
            $this->emailUploadNoticeToPayroll( $post );

            /** Change status to Update Checks */
            $requestReturnData = $TimeOffRequests->submitApprovalResponse(
                $TimeOffRequests->getRequestStatusCode( 'updateChecks' ),
                $post->request_id,
                $post->review_request_reason );

            /** Log status change to Update Checks **/
            $TimeOffRequestLog->logEntry(
                $post->request_id, UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ), 'Status changed to Update Checks by ' . UserSession::getFullUserInfo() .
                ( (!empty( $post->review_request_reason )) ? ' with the comment: ' . $post->review_request_reason : '' ) );

            if( !empty( $post->payroll_comment ) ) {
                $TimeOffRequestLog->logEntry(
                    $post->request_id, UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ), $post->payroll_comment,
                    UserSession::getUserSessionVariable( 'IS_PAYROLL' ) );
            }

            $result = new JsonModel([
                'success' => true,
                'request_id' => $post->request_id
            ]);
        } catch ( \Exception $ex ) {
            $result = new JsonModel([
                'success' => false,
                'message' => 'There was an error submitting your request. Please try again.'
            ]);
        }

        return $result;
    }
}