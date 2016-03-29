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
use \Request\Model\TimeOffRequestLog;
use \Request\Model\TimeOffRequests;
use \Request\Model\RequestEntry;
use \Request\Model\Papaatmp;
use \Request\Helper\OutlookHelper;
use \Request\Helper\ValidationHelper;
use \Login\Helper\UserSession;
use \Application\Factory\EmailFactory;

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
    
    /**
     * Array of email addresses to send all emails when running on SWIFT.
     * 
     * @var unknown
     */
    public $developmentEmailAddressList = null;
    
    public function __construct()
    {
        $this->testingEmailAddressList = [ 'kevin_sawicke@swifttrans.com',
                                           'sarah_koogle@swifttrans.com',
                                           'heather_baehr@swifttrans.com',
                                           'jessica_yanez@swifttrans.com'
        ];
        $this->developmentEmailAddressList = [ 'kevin_sawicke@swifttrans.com' ];
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
    
    /**
     * Submits the new Time Off Request for an employee.
     * 
     * @return JsonModel
     */
    public function submitTimeoffRequestAction()
    {
        $Employee = new Employee();
        $TimeOffRequests = new TimeOffRequests();
        $TimeOffRequestLog = new TimeOffRequestLog();
        
        /** Clean up / append data to the Request **/
        $post = $this->getRequest()->getPost();
        $post = $this->cleanUpRequestedDates( $post );
        $post = $this->addRequestForEmployeeData( $post );
        
        /** Ensure Employee has a default schedule created **/
        $Employee->ensureEmployeeScheduleIsDefined( $post->request['forEmployee']['EMPLOYEE_NUMBER'] );
        
        /** Submit the request to employee's manager; get the Request ID **/
        $requestReturnData = $TimeOffRequests->submitRequestForManagerApproval( $post );
        $requestId = $requestReturnData['request_id'];
        
        /** Log creation of this request **/
        $TimeOffRequestLog->logEntry(
            $requestId,
            $post->request['byEmployee']['EMPLOYEE_NUMBER'],
            'Created by ' . $post->request['byEmployee']['EMPLOYEE_DESCRIPTION_ALT'] );
        
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
     * Send the employee an email confirming their request.
     * 
     * @param integer $requestId
     */
    protected function emailRequestToEmployee( $requestId, $post )
    {
        $emailVariables = $this->getEmailRequestVariables( $requestId );
        $to = $post->request['forEmployee']['MANAGER_EMAIL_ADDRESS'];
        $cc = $post->request['forEmployee']['EMAIL_ADDRESS'];
        if( ENVIRONMENT==='development' ) {
            $to = $this->developmentEmailAddressList;
            $cc = '';
        }
        if( ENVIRONMENT==='testing' ) {
            $to = $this->testingEmailAddressList;
            $cc = '';
        }
        $Email = new EmailFactory(
            'Time off requested for ' . $post->request['forEmployee']['EMPLOYEE_DESCRIPTION_ALT'],
            'A total of ' . $emailVariables['totalHoursRequested'] . ' hours were requested off for ' .
                $post->request['forEmployee']['EMPLOYEE_DESCRIPTION_ALT'] . '<br /><br />' . 
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
        $cc = $post->request['forEmployee']['EMAIL_ADDRESS'];
        if( ENVIRONMENT==='development' ) {
            $to = $this->developmentEmailAddressList;
            $cc = '';
        }
        if( ENVIRONMENT==='testing' ) {
            $to = $this->testingEmailAddressList;
            $cc = '';
        }
        $Email = new EmailFactory(
            'Time off requested for ' . $post->request['forEmployee']['EMPLOYEE_DESCRIPTION_ALT'],
            'A total of ' . $emailVariables['totalHoursRequested'] . ' hours were requested off for ' .
                $post->request['forEmployee']['EMPLOYEE_DESCRIPTION_ALT'] . '<br /><br />' . 
                $emailVariables['hoursRequestedHtml'] . '<br /><br />' .
                'Please review this request at the following URL:<br /><br />' .
                '<a href="' . $reviewUrl . '">' . $reviewUrl . '</a>',
            $to,
            $cc
        );
        $Email->send();
    }
    
    /**
     * Handles the manager approval process.
     * 
     * @return JsonModel
     */
    public function submitManagerApprovedAction()
    {
        $post = $this->getRequest()->getPost();
        $Employee = new Employee();
        $TimeOffRequests = new TimeOffRequests();
        $TimeOffRequestLog = new TimeOffRequestLog();
        $validationHelper = new ValidationHelper();
        $requestData = $TimeOffRequests->findRequest( $post->request_id );
        
        $isPayrollReviewRequired = $validationHelper->isPayrollReviewRequired( $post->request_id, $requestData['EMPLOYEE_NUMBER'] ); // $validationHelper->isPayrollReviewRequired( $requestData, $employeeData );

        if ( $isPayrollReviewRequired === true ) {
            /** Log supervisor approval with comment **/
            $TimeOffRequestLog->logEntry(
                $post->request_id, UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ), 'Time off request approved by ' . UserSession::getFullUserInfo() .
                ' for ' . $requestData['EMPLOYEE_DATA']->EMPLOYEE_DESCRIPTION_ALT .
                ( (!empty( $post->review_request_reason )) ? ' with the comment: ' . $post->review_request_reason : '' ) );
            
            /** Change status to Approved */
            $requestReturnData = $TimeOffRequests->submitApprovalResponse(
                $TimeOffRequests->getRequestStatusCode( 'approved' ),
                $post->request_id,
                $post->review_request_reason );
            
            /** Log request as having insufficient hours **/
            $TimeOffRequestLog->logEntry(
                $post->request_id,
                UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ),
                'Payroll review required because of insufficient hours' );
            
            /** Change status to Pending Payroll Approval */
            $requestReturnData = $TimeOffRequests->submitApprovalResponse(
                $TimeOffRequests->getRequestStatusCode( 'pendingPayrollApproval' ),
                $post->request_id,
                $post->review_request_reason );
        } else {
            $OutlookHelper = new OutlookHelper();
            $RequestEntry = new RequestEntry();
            $calendarInviteData = $TimeOffRequests->findRequestCalendarInviteData( $post->request_id );
            $dateRequestBlocks = $RequestEntry->getRequestObject( $post->request_id );
            $employeeData = $Employee->findEmployeeTimeOffData( $dateRequestBlocks['for']['employee_number'], "Y", "EMPLOYER_NUMBER, EMPLOYEE_NUMBER, LEVEL_1, LEVEL_2, LEVEL_3, LEVEL_4, SALARY_TYPE" );
            
            /** Send calendar invites for this request **/
            $isSent = $OutlookHelper->addToCalendar( $calendarInviteData, $employeeData );

            /** Log supervisor approval with comment **/
            $TimeOffRequestLog->logEntry(
                $post->request_id,
                UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ),
                'Approved by ' . UserSession::getFullUserInfo() .
                (!empty( $post->review_request_reason ) ? ' with the comment: ' . $post->review_request_reason : '' ) );
            
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
            $Papaa->prepareToWritePapaatmpRecords( $employeeData, $dateRequestBlocks );
        }

        if ( $requestReturnData['request_id'] != null ) {
            $result = new JsonModel( [
                'success' => true,
                'request_id' => $requestReturnData['request_id'],
                'action' => 'A'
            ] );
        } else {
            $result = new JsonModel( [
                'success' => false,
                'message' => 'There was an error submitting your request. Please try again.'
            ] );
        }

        return $result;
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

        /** Log supervisor deny with comment **/
        $TimeOffRequestLog->logEntry(
            $post->request_id,
            UserSession::getUserSessionVariable('EMPLOYEE_NUMBER'),
            'Time off request denied by ' . UserSession::getFullUserInfo() .
            ' for ' . trim(ucwords(strtolower($employeeData['EMPLOYEE_NAME'])) . ' (' . $employeeData['EMPLOYEE_NUMBER'] . ')' . 
            ( !empty( $post->review_request_reason ) ? ' with the comment: ' . $post->review_request_reason : '' )));
        
        /** Change status to Denied */
        $requestReturnData = $TimeOffRequests->submitApprovalResponse(
            $TimeOffRequests->getRequestStatusCode( 'denied' ),
            $post->request_id,
            $post->review_request_reason );
        
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
     * Handles the Payroll approval process.
     * 
     * @return JsonModel
     */
    public function submitPayrollApprovedAction()
    {
        $post = $this->getRequest()->getPost();
        $Employee = new Employee();
        $TimeOffRequests = new TimeOffRequests();
        $TimeOffRequestLog = new TimeOffRequestLog();
        $validationHelper = new ValidationHelper();
        $requestData = $TimeOffRequests->findRequest( $post->request_id );
        $OutlookHelper = new OutlookHelper();
        $RequestEntry = new RequestEntry();
        $calendarInviteData = $TimeOffRequests->findRequestCalendarInviteData( $post->request_id );
        $dateRequestBlocks = $RequestEntry->getRequestObject( $post->request_id );
        $employeeData = $Employee->findEmployeeTimeOffData( $dateRequestBlocks['for']['employee_number'], "Y", "EMPLOYER_NUMBER, EMPLOYEE_NUMBER, LEVEL_1, LEVEL_2, LEVEL_3, LEVEL_4, SALARY_TYPE" );
        
        try {
            /** Log Payroll approval with comment **/
            $TimeOffRequestLog->logEntry(
                $post->request_id, UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ), 'Time off request Payroll approved by ' . UserSession::getFullUserInfo() .
                ' for ' . $requestData['EMPLOYEE_DATA']->EMPLOYEE_DESCRIPTION_ALT .
                ( (!empty( $post->review_request_reason )) ? ' with the comment: ' . $post->review_request_reason : '' ) );

            /** Change status to Completed PAFs */
            $requestReturnData = $TimeOffRequests->submitApprovalResponse(
                $TimeOffRequests->getRequestStatusCode( 'completedPAFs' ),
                $post->request_id,
                $post->review_request_reason );

            /** Log status change to Pending AS400 Upload **/
            $TimeOffRequestLog->logEntry(
                $post->request_id,
                UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ),
                'Status changed to Completed PAFs' );

            /** Send calendar invites for this request **/
            $isSent = $OutlookHelper->addToCalendar( $calendarInviteData, $employeeData );
            
            /** Log sending calendar invites **/
            $TimeOffRequestLog->logEntry(
                $post->request_id,
                UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ),
                'Sent calendar invites of the request' );
            
            $result = new JsonModel([
                'success' => true,
                'request_id' => $post->request_id
            ]);
        } catch ( Exception $ex ) {
            $result = new JsonModel([
                'success' => false,
                'message' => 'There was an error submitting your request. Please try again.'
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
        $result = new JsonModel([
            'success' => false,
            'error' => 'submitPayrollDenied'
        ]);
        
        return $result;
    }
    
    /**
     * Handles the Payroll upload process.
     * 
     * @return JsonModel
     */
    public function submitPayrollUploadAction()
    {
        $result = new JsonModel([
            'success' => false,
            'error' => 'submitPayrollUpload'
        ]);
        
        return $result;
    }
    
    /**
     * Handles the Payroll update checks process.
     * 
     * @return JsonModel
     */
    public function submitPayrollUpdateAction()
    {
        $result = new JsonModel([
            'success' => false,
            'error' => 'submitPayrollUpdate'
        ]);
        
        return $result;
    }
}
