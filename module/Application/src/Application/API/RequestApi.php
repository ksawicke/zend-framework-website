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
use Request\Model\Employee;

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
    
    public $developmentEmailAddressList = null;
    
    public function __construct()
    {
        $this->developmentEmailAddressList = 'kevin_sawicke@swifttrans.com';
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
        $Employee = new \Request\Model\Employee();
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
        $Employee = new \Request\Model\Employee();
        
        /** Clean up / append data to the Request **/
        $post = $this->getRequest()->getPost();
        $post = $this->cleanUpRequestedDates( $post );
        $post = $this->addRequestForEmployeeData( $post );
        
        /** Ensure Employee has a default schedule created **/
        $Employee->ensureEmployeeScheduleIsDefined( $post->request['forEmployee']['EMPLOYEE_NUMBER'] );
        
        /** Submit the request to employee's manager; get the Request ID **/
        $requestReturnData = $Employee->submitRequestForManagerApproval( $post );
        $requestId = $requestReturnData['request_id'];
        
        /** Log creation of this request **/
        $Employee->logEntry( $requestId,
                             $post->request['byEmployee']['EMPLOYEE_NUMBER'],
                             'Created by ' . $post->request['byEmployee']['EMPLOYEE_DESCRIPTION_ALT'] );
        
        /** Log setting request to Pending Manager Approval **/
        $Employee->logEntry( $requestId,
                             $post->request['byEmployee']['EMPLOYEE_NUMBER'],
                             'Sent for manager approval to ' . $post->request['forEmployee']['MANAGER_DESCRIPTION_ALT'] );
        
        /** Send email to employee and manager **/
        /** Grab data to email out **/
        $this->emailRequestToEmployee( $requestId );
        $this->emailRequestToManager( $requestId );
                
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
        $TimeoffRequests = new \Request\Model\TimeoffRequests();
        $timeoffRequestData = $TimeoffRequests->findRequest( $requestId );
        
        return [ 'totalHoursRequested' => $TimeoffRequests->countTimeoffRequested( $requestId ),
                 'hoursRequestedHtml' => $TimeoffRequests->drawHoursRequested( $timeoffRequestData['ENTRIES'] )
               ];
    }
    
    /**
     * Send the employee an email confirming their request.
     * 
     * @param integer $requestId
     */
    protected function emailRequestToEmployee( $requestId )
    {
        $emailVariables = $this->getEmailRequestVariables( $requestId );
        $Email = new \Application\Factory\EmailFactory(
            'Time off requested for ' . $post->request['forEmployee']['EMPLOYEE_DESCRIPTION_ALT'],
            'A total of ' . $emailVariables['totalHoursRequested'] . ' hours were requested off for ' .
                $post->request['forEmployee']['EMPLOYEE_DESCRIPTION_ALT'] . '<br /><br />' . 
                $emailVariables['hoursRequestedHtml'] . '<br /><br />' .
                'If you are a Supervisor, please review this request at the following URL:<br /><br />' .
                '<a href="http://swift:10080/sawik/timeoff/public/request/review-request/' . $requestId .
                '">http://swift:10080/sawik/timeoff/public/request/review-request/' . $requestId . '</a>',
            ( ( ENVIRONMENT==='development' ) ? $this->developmentEmailAddressList : $post->request['forEmployee']['MANAGER_EMAIL_ADDRESS'] ),
            ( ( ENVIRONMENT==='development' ) ? $this->developmentEmailAddressList : $post->request['forEmployee']['EMAIL_ADDRESS'] )
        );
        $Email->send();
    }
    
    /**
     * Send 
     * 
     * @param integer $requestId
     */
    protected function emailRequestToManager( $requestId )
    {
        $emailVariables = $this->getEmailRequestVariables( $requestId );
        $Email = new \Application\Factory\EmailFactory(
            'Time off requested for ' . $post->request['forEmployee']['EMPLOYEE_DESCRIPTION_ALT'],
            'A total of ' . $emailVariables['totalHoursRequested'] . ' hours were requested off for ' .
                $post->request['forEmployee']['EMPLOYEE_DESCRIPTION_ALT'] . '<br /><br />' . 
                $emailVariables['hoursRequestedHtml'] . '<br /><br />' .
                'Please review this request at the following URL:<br /><br />' .
                '<a href="http://swift:10080/sawik/timeoff/public/request/review-request/' . $requestId .
                '">http://swift:10080/sawik/timeoff/public/request/review-request/' . $requestId . '</a>',
            ( ( ENVIRONMENT==='development' ) ? $this->developmentEmailAddressList : $post->request['forEmployee']['MANAGER_EMAIL_ADDRESS'] ),
            ( ( ENVIRONMENT==='development' ) ? $this->developmentEmailAddressList : $post->request['forEmployee']['EMAIL_ADDRESS'] )
        );
        $Email->send();
    }
    
    /**
     * Handles the manager approval process.
     * 
     * @return JsonModel
     */
    public function submitApprovalResponseAction()
    {
        $request = $this->getRequest();
        $Employee = new \Request\Model\Employee();
        $TimeoffRequests = new \Request\Model\TimeOffRequests();
        $TimeoffRequestLog = new \Request\Model\TimeoffRequestLog();
        $requestData = $Employee->checkHoursRequestedPerCategory($request->getPost()->request_id);
        $employeeData = $Employee->findTimeOffEmployeeData($requestData['EMPLOYEE_NUMBER']);

        $validationHelper = new \Request\Helper\ValidationHelper();
        $payrollReviewRequired = $validationHelper->isPayrollReviewRequired($requestData, $employeeData);
                
        if($payrollReviewRequired===true) {
            $TimeoffRequestLog->logEntry(
                $request->getPost()->request_id,
                \Login\Helper\UserSession::getUserSessionVariable('EMPLOYEE_NUMBER'),
                'Time off request approved by ' . \Login\Helper\UserSession::getFullUserInfo() .
                ' for ' . trim(ucwords(strtolower($employeeData['EMPLOYEE_NAME'])) . '(' . trim($employeeData['EMPLOYEE_NUMBER']) . ')') .
                ( (!empty($request->getPost()->review_request_reason)) ? ' with the comment: ' . $request->getPost()->review_request_reason : '') );

            $requestReturnData = $Employee->submitApprovalResponse('A', $request->getPost()->request_id, $request->getPost()->review_request_reason);

            $TimeoffRequestLog->logEntry($request->getPost()->request_id, \Login\Helper\UserSession::getUserSessionVariable('EMPLOYEE_NUMBER'), 'Payroll review required because of insufficient hours');
            $requestReturnData = $Employee->submitApprovalResponse('Y', $request->getPost()->request_id, $request->getPost()->review_request_reason);
        } else {
            $OutlookHelper = new \Request\Helper\OutlookHelper();
            $RequestEntry = new \Request\Model\RequestEntry();
            $Papaa = new \Request\Model\Papaa();
            
            $calendarInviteData = $TimeoffRequests->findRequestCalendarInviteData($request->getPost()->request_id);
            
            $isSent = $OutlookHelper->addToCalendar( $calendarInviteData, $employeeData );

            $TimeoffRequestLog->logEntry(
                $request->getPost()->request_id,
                \Login\Helper\UserSession::getUserSessionVariable('EMPLOYEE_NUMBER'),
                'Time off request approved by ' . \Login\Helper\UserSession::getFullUserInfo() .
                ' for ' . trim(ucwords(strtolower($employeeData['EMPLOYEE_NAME'])) . ' (' . $employeeData['EMPLOYEE_NUMBER'] . ')' .
                ( !empty($request->getPost()->review_request_reason) ? ' with the comment: ' . $request->getPost()->review_request_reason : '' )));

            $requestReturnData = $Employee->submitApprovalResponse('A', $request->getPost()->request_id, $request->getPost()->review_request_reason);

            $dateRequestBlocks = $RequestEntry->getRequestObject( $request->getPost()->request_id );
            $employeeData = $Employee->findTimeOffEmployeeData( $dateRequestBlocks['for']['employee_number'], "Y",
                "EMPLOYER_NUMBER, EMPLOYEE_NUMBER, LEVEL_1, LEVEL_2, LEVEL_3, LEVEL_4, SALARY_TYPE" );

            $dateRequestBlocks['for']['employer_number'] = $employeeData['EMPLOYER_NUMBER'];
            $dateRequestBlocks['for']['level1'] = $employeeData['LEVEL_1'];
            $dateRequestBlocks['for']['level2'] = $employeeData['LEVEL_2'];
            $dateRequestBlocks['for']['level3'] = $employeeData['LEVEL_3'];
            $dateRequestBlocks['for']['level4'] = $employeeData['LEVEL_4'];
            $dateRequestBlocks['for']['salary_type'] = $employeeData['SALARY_TYPE'];

            foreach( $dateRequestBlocks['dates'] as $ctr => $dateCollection ) {
                $Papaa->SaveDates( $dateRequestBlocks['for'], $dateRequestBlocks['reason'], $dateCollection );
            }
        }
        
        if($requestReturnData['request_id']!=null) {
            $result = new JsonModel([
                'success' => true,
                'request_id' => $requestReturnData['request_id'],
                'action' => 'A'
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
     * Handles the manager deny process.
     * 
     * @return JsonModel
     */
    public function submitDenyResponseAction()
    {
        $request = $this->getRequest();
        $Employee = new \Request\Model\Employee();
        $TimeoffRequestLog = new \Request\Model\TimeoffRequestLog();
        $requestData = $Employee->checkHoursRequestedPerCategory($request->getPost()->request_id);
        $employeeData = $Employee->findTimeOffEmployeeData($requestData['EMPLOYEE_NUMBER']);
        
//        die( 'Time off request denied by ' . \Login\Helper\UserSession::getFullUserInfo() .
//            ' for ' . trim(ucwords(strtolower($employeeData['EMPLOYEE_NAME'])) . ' (' . $employeeData['EMPLOYEE_NUMBER'] . ')' . 
//            ( !empty($request->getPost()->review_request_reason) ? ' with the comment: ' . $request->getPost()->review_request_reason : '' )) );
        
        $TimeoffRequestLog->logEntry(
            $request->getPost()->request_id,
            \Login\Helper\UserSession::getUserSessionVariable('EMPLOYEE_NUMBER'),
            'Time off request denied by ' . \Login\Helper\UserSession::getFullUserInfo() .
            ' for ' . trim(ucwords(strtolower($employeeData['EMPLOYEE_NAME'])) . ' (' . $employeeData['EMPLOYEE_NUMBER'] . ')' . 
            ( !empty($request->getPost()->review_request_reason) ? ' with the comment: ' . $request->getPost()->review_request_reason : '' )));
        
        $requestReturnData = $Employee->submitApprovalResponse('D', $request->getPost()->request_id, $request->getPost()->review_request_reason);
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
}
