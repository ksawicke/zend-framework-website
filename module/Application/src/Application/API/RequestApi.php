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
    
    public $collection;
    
    public function __construct()
    {
        $this->collection = [];
    }
    
    protected function cleanUpRequestDates( $post )
    {
        $selectedDatesNew = [];
        foreach( $post->selectedDatesNew as $key => $data ) {
            $date = \DateTime::createFromFormat('m/d/Y', $post->selectedDatesNew[$key]['date']);
            $selectedDatesNew[] = [
                'date' => $date->format('Y-m-d'),
                'dow' => strtoupper( $date->format('D') ),
                'type' => self::$typesToCodes[$post->selectedDatesNew[$key]['category']],
                'hours' => number_format( $post->selectedDatesNew[$key]['hours'], 2 )
            ];
        }
        $post->selectedDatesNew = $selectedDatesNew;
        
        return $post;
    }
    
    public function submitTimeoffRequestAction()
    {
        $Employee = new \Request\Model\Employee();
        $post = $this->getRequest()->getPost();
        $post = $this->cleanUpRequestDates( $post );
        $employeeNumber = $post->employeeNumber;
        $employeeSchedule = $Employee->ensureEmployeeScheduleIsDefined( $employeeNumber );
        $requesterEmployeeNumber = trim( $post->loggedInUserData['EMPLOYEE_NUMBER'] );
        
        /**
         * Zend\Stdlib\Parameters Object
            (
                [storage:ArrayObject:private] => Array
                    (
                        //[action] => submitTimeoffRequest
                        [selectedDatesNew] => Array
                            (
                                [0] => Array
                                    (
                                        [category] => timeOffPTO
                                        [date] => 04/25/2016
                                        [hours] => 8.00
                                    )

                                [1] => Array
                                    (
                                        [category] => timeOffPTO
                                        [date] => 04/26/2016
                                        [hours] => 8.00
                                    )

                                [2] => Array
                                    (
                                        [category] => timeOffPTO
                                        [date] => 04/27/2016
                                        [hours] => 8.00
                                    )

                                [3] => Array
                                    (
                                        [category] => timeOffPTO
                                        [date] => 04/28/2016
                                        [hours] => 8.00
                                    )

                                [4] => Array
                                    (
                                        [category] => timeOffPTO
                                        [date] => 04/29/2016
                                        [hours] => 8.00
                                    )

                            )

                        [requestReason] => Test submit request
                        [employeeNumber] => 49499
                        [loggedInUserData] => Array
                            (
                                [EMPLOYER_NUMBER] => 002
                                [EMPLOYEE_NUMBER] => 49499
                                [EMPLOYEE_NAME] => GASIOR, JAMES
                                [EMAIL_ADDRESS] => James_Gasior@Swifttrans.com
                                [LEVEL_1] => 10100
                                [LEVEL_2] => IT
                                [LEVEL_3] => DV00X
                                [LEVEL_4] => 92510
                                [POSITION] => AZSDA3
                                [POSITION_TITLE] => PHOAZ-SOFTWARE DEV/ANALYST III
                                [EMPLOYEE_HIRE_DATE] => 8/06/1999
                                [SALARY_TYPE] => S
                                [MANAGER_EMPLOYEE_NUMBER] => 229589
                                [MANAGER_POSITION] => MESITP
                                [MANAGER_POSITION_TITLE] => SAVTN-SR IT PROJECT LDR
                                [MANAGER_NAME] => JACKSON, MARY
                                [MANAGER_EMAIL_ADDRESS] => Mary_Jackson@swifttrans.com
                                [PTO_EARNED] => 1993.33
                                [PTO_TAKEN] => 1592.00
                                [PTO_UNAPPROVED] => .00
                                [PTO_PENDING] => 248.00
                                [PTO_PENDING_TMP] => .00
                                [PTO_PENDING_TOTAL] => 248.00
                                [PTO_AVAILABLE] => 153.33
                                [FLOAT_EARNED] => 152.00
                                [FLOAT_TAKEN] => 152.00
                                [FLOAT_UNAPPROVED] => .00
                                [FLOAT_PENDING] => .00
                                [FLOAT_PENDING_TMP] => .00
                                [FLOAT_PENDING_TOTAL] => .00
                                [FLOAT_AVAILABLE] => .00
                                [SICK_EARNED] => 513.33
                                [SICK_TAKEN] => 424.00
                                [SICK_UNAPPROVED] => .00
                                [SICK_PENDING] => 24.00
                                [SICK_PENDING_TMP] => .00
                                [SICK_PENDING_TOTAL] => 24.00
                                [SICK_AVAILABLE] => 65.33
                                [GF_EARNED] => .00
                                [GF_TAKEN] => .00
                                [GF_UNAPPROVED] => .00
                                [GF_PENDING] => .00
                                [GF_PENDING_TMP] => .00
                                [GF_PENDING_TOTAL] => .00
                                [GF_AVAILABLE] => .00
                                [UNEXCUSED_UNAPPROVED] => .00
                                [UNEXCUSED_PENDING] => .00
                                [UNEXCUSED_PENDING_TMP] => .00
                                [UNEXCUSED_PENDING_TOTAL] => .00
                                [BEREAVEMENT_UNAPPROVED] => .00
                                [BEREAVEMENT_PENDING] => .00
                                [BEREAVEMENT_PENDING_TMP] => .00
                                [BEREAVEMENT_PENDING_TOTAL] => .00
                                [CIVIC_DUTY_UNAPPROVED] => .00
                                [CIVIC_DUTY_PENDING] => .00
                                [CIVIC_DUTY_PENDING_TMP] => .00
                                [CIVIC_DUTY_PENDING_TOTAL] => .00
                                [UNPAID_UNAPPROVED] => .00
                                [UNPAID_PENDING] => .00
                                [UNPAID_PENDING_TMP] => .00
                                [UNPAID_PENDING_TOTAL] => .00
                                [SCHEDULE_MON] => 8.00
                                [SCHEDULE_TUE] => 8.00
                                [SCHEDULE_WED] => 8.00
                                [SCHEDULE_THU] => 8.00
                                [SCHEDULE_FRI] => 8.00
                                [SCHEDULE_SAT] => .00
                                [SCHEDULE_SUN] => .00
                                [IS_LOGGED_IN_USER_MANAGER] => N
                                [IS_LOGGED_IN_USER_PAYROLL] => N
                            )

                    )

            )
         */
        
        // Massage selectedDatesNew:
        //      from: [ 'date' => 'dd/mm/YYYY', 'category' => 'C', 'hours' => '8.00' ]
        //      to:   [ 'date' => 'YYYY-mm-dd', 'type' => 'C', 'hours' => '8.00' ]
        
        
        echo '<pre>';
        print_r( $post );
        echo '</pre>';
        die("....");
        
//        $request = $this->getRequest();
//        $employeeNumber = $request->getPost()->employeeNumber;
        

        $requestData = [];

        

        
        

//        if( $employeeSchedule===false ) {
//            $Employee->makeDefaultEmployeeSchedule( $employeeNumber );
//            $employeeSchedule = $Employee->findEmployeeSchedule( $employeeNumber );
//        }

        $employeeTimeOffData = $Employee->findEmployeeTimeOffData($employeeNumber, "Y",
            "EMPLOYEE_NUMBER, EMPLOYEE_NAME, EMAIL_ADDRESS, " .
            "MANAGER_EMPLOYEE_NUMBER, MANAGER_NAME, MANAGER_EMAIL_ADDRESS, " .
            "PTO_EARNED, PTO_TAKEN, PTO_UNAPPROVED, " .
            "PTO_PENDING, PTO_PENDING_TMP, PTO_PENDING_TOTAL, PTO_AVAILABLE, " .
            "FLOAT_EARNED, FLOAT_TAKEN, FLOAT_UNAPPROVED, " .
            "FLOAT_PENDING, FLOAT_PENDING_TMP, FLOAT_PENDING_TOTAL, FLOAT_AVAILABLE, " .
            "SICK_EARNED, SICK_TAKEN, SICK_UNAPPROVED, SICK_PENDING, SICK_PENDING_TMP, SICK_PENDING_TOTAL, " .
            "SICK_AVAILABLE, GF_EARNED, GF_TAKEN, GF_UNAPPROVED, GF_PENDING, GF_PENDING_TMP, " . 
            "GF_PENDING_TOTAL, GF_AVAILABLE, UNEXCUSED_UNAPPROVED, UNEXCUSED_PENDING, " .
            "UNEXCUSED_PENDING_TMP, UNEXCUSED_PENDING_TOTAL, BEREAVEMENT_UNAPPROVED, " .
            "BEREAVEMENT_PENDING, BEREAVEMENT_PENDING_TMP, BEREAVEMENT_PENDING_TOTAL, CIVIC_DUTY_UNAPPROVED, ".
            "CIVIC_DUTY_PENDING, CIVIC_DUTY_PENDING_TMP, CIVIC_DUTY_PENDING_TOTAL, UNPAID_UNAPPROVED, " .
            "UNPAID_PENDING, UNPAID_PENDING_TMP, UNPAID_PENDING_TOTAL");

        $requestReturnData = $Employee->submitRequestForApproval($employeeNumber, $requestData, $request->getPost()->requestReason, $requesterEmployeeNumber, json_encode($employeeTimeOffData));
        $requestId = $requestReturnData['request_id'];
        $comment = 'Created by ' . \Login\Helper\UserSession::getFullUserInfo();
        $Employee->logEntry($requestId, $requesterEmployeeNumber, $comment);

        $Employee->logEntry(
            $requestId,
            $requesterEmployeeNumber,
            'Sent for manager approval to ' . trim(ucwords(strtolower($employeeData->MANAGER_NAME))) . ' (' . trim($employeeData->MANAGER_EMPLOYEE_NUMBER) . ')'
        );
        /** Change status to "Pending Manager Approval" **/
        $requestReturnData = $Employee->submitApprovalResponse('P', $requestReturnData['request_id'], $request->getPost()->requestReason, json_encode($employeeData));

        if($requestReturnData['request_id']!=null) {
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
        
//        die( 'Time off request approved by ' . \Login\Helper\UserSession::getFullUserInfo() .
//             ' for ' . trim(ucwords(strtolower($employeeData['EMPLOYEE_NAME'])) . '(' . trim($employeeData['EMPLOYEE_NUMBER']) . ')') .
//             ( (!empty($request->getPost()->review_request_reason)) ? ' with the comment: ' . $request->getPost()->review_request_reason : '') );
        
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

//            die( 'Time off request approved by ' . \Login\Helper\UserSession::getFullUserInfo() .
//                ' for ' . trim(ucwords(strtolower($employeeData['EMPLOYEE_NAME'])) . ' (' . $employeeData['EMPLOYEE_NUMBER'] . ')') );
            
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
