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
    
    public function submitTimeoffRequestAction()
    {
        $request = $this->getRequest();
        $employeeNumber = $request->getPost()->employeeNumber;
        $requesterEmployeeNumber = trim($request->getPost()->loggedInUserData['EMPLOYEE_NUMBER']);

        $requestData = [];

        foreach($request->getPost()->selectedDatesNew as $key => $data) {
            $date = \DateTime::createFromFormat('m/d/Y', $request->getPost()->selectedDatesNew[$key]['date']);
            $requestData[] = [
                'date' => $date->format('Y-m-d'),
                'type' => self::$typesToCodes[$request->getPost()->selectedDatesNew[$key]['category']],
                'hours' => (int) $request->getPost()->selectedDatesNew[$key]['hours']
            ];
        }

        $Employee = new \Request\Model\Employee();
        $employeeSchedule = $Employee->findEmployeeSchedule( $employeeNumber );

        if( $employeeSchedule===false ) {
            $Employee->makeDefaultEmployeeSchedule( $employeeNumber );
            $employeeSchedule = $Employee->findEmployeeSchedule( $employeeNumber );
        }

        $employeeData = $Employee->findTimeOffEmployeeData($employeeNumber, "Y",
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

        $requestReturnData = $Employee->submitRequestForApproval($employeeNumber, $requestData, $request->getPost()->requestReason, $requesterEmployeeNumber, json_encode($employeeData));
        $requestId = $requestReturnData['request_id'];
        $comment = 'Created by ' . \Login\Helper\UserSession::getUserSessionVariable('FIRST_NAME') . ' '  . \Login\Helper\UserSession::getUserSessionVariable('LAST_NAME');
        $Employee->logEntry($requestId, $requesterEmployeeNumber, $comment);

        $Employee->logEntry(
            $requestId,
            $requesterEmployeeNumber,
            'Sent for manager approval to ' . trim(ucwords(strtolower($employeeData->MANAGER_NAME)))
        );
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
        
        if($payrollReviewRequired===true) {
            $TimeoffRequestLog->logEntry(
                $request->getPost()->request_id,
                \Login\Helper\UserSession::getUserSessionVariable('EMPLOYEE_NUMBER'),
                'Time off request approved by ' . \Login\Helper\UserSession::getUserSessionVariable('FIRST_NAME') . ' '  . \Login\Helper\UserSession::getUserSessionVariable('LAST_NAME') .
                ' for ' . trim(ucwords(strtolower($employeeData['EMPLOYEE_NAME']))));

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
                'Time off request approved by ' . \Login\Helper\UserSession::getUserSessionVariable('FIRST_NAME') . ' '  . \Login\Helper\UserSession::getUserSessionVariable('LAST_NAME') .
                ' for ' . trim(ucwords(strtolower($employeeData['EMPLOYEE_NAME']))));

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
