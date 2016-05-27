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
    public $testingEmailAddressList = null;
    public $developmentEmailAddressList = null;
    
    public function __construct()
    {
        $this->testingEmailAddressList = [ 'kevin_sawicke@swifttrans.com',
                                           'sarah_koogle@swifttrans.com',
                                           'heather_baehr@swifttrans.com',
                                           'jessica_yanez@swifttrans.com',
                                           'nedra_munoz@swifttrans.com'
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
        } catch ( Exception $ex ) {
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
        $Employee = new Employee();
        $TimeOffRequests = new TimeOffRequests();
        $TimeOffRequestLog = new TimeOffRequestLog();
        
        /** Clean up / append data to the Request **/
        $post = $this->getRequest()->getPost();
        $post = $this->cleanUpRequestedDates( $post );
        $post = $this->addRequestForEmployeeData( $post );
        $post = $this->addRequestByEmployeeData( $post );
        
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
            'Created by ' . $post->request['byEmployee']['EMPLOYEE_DESCRIPTION_ALT'] );
        
        if( $isRequestToBeAutoApproved ) {
            $this->emailRequestToEmployee( $requestId, $post );
            $return = $this->submitManagerApprovedAction( [ 'request_id' => $requestId,
                'review_request_reason' => 'System auto-approved request because requester is in manager heirarchy of ' .
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
    protected function emailDeniedNoticeToEmployee( $post )
    {
        $renderer = $this->serviceLocator->get( 'Zend\View\Renderer\RendererInterface' );
        $reviewUrl = ( ( ENVIRONMENT==='development' || ENVIRONMENT==='testing' ) ? 'http://swift:10080' : 'http://aswift:10080' ) .
            $renderer->basePath( '/request/review-request/' . $post->request_id );
        $emailVariables = $this->getEmailRequestVariables( $post->request_id );
        $to = $post->request['forEmployee']['EMAIL_ADDRESS'];
        $cc = $post->request['forEmployee']['MANAGER_EMAIL_ADDRESS'];
        if( ENVIRONMENT==='development' ) {
            $to = $this->developmentEmailAddressList;
            $cc = '';
        }
        if( ENVIRONMENT==='testing' ) {
            $to = $this->testingEmailAddressList;
            $cc = '';
        }
        
        $Email = new EmailFactory(
            'Time off request for ' . $post->request['forEmployee']['EMPLOYEE_DESCRIPTION_ALT'] . ' was denied',
            'The request for ' .
                $post->request['forEmployee']['EMPLOYEE_DESCRIPTION_ALT'] . ' has been denied by Payroll' . 
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
        if( ENVIRONMENT==='development' ) {
            $to = $this->developmentEmailAddressList;
            $cc = '';
        }
        if( ENVIRONMENT==='testing' ) {
            $to = $this->testingEmailAddressList;
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
    
    public function checkForUpdatesMadeToForm( $post, $requestedDatesOld )
    {
        echo '<pre>Manager Approved this request...';
        var_dump( $post->selectedDatesNew );
        echo '</pre>';
        
        // If formDirty=="true" {
        //   1. Look at each day in request: $post->selectedDatesNew
        //   2. If contains 'entryId' && 'fieldDirty' == 'true' && 'delete' != 'true', update the entryId in table
        //   3. If contains 'entryId' && 'fieldDirty' == 'true' && 'delete' == 'true',
        //        copy entry in TIMEOFF_REQUEST_ENTRIES to TIMEOFF_REQUEST_ENTRIES_ARCHIVE,
        //        update entry in TIMEOFF_REQUEST_ENTRIES so IS_DELETED = 1
        //   4. If !contains 'entryId', add entry to TIMEOFF_REQUEST_ENTRIES
        //   5. Save json object of before / after
        //   6. Create a summary of changes to be used in email
        // }
        //
        
        echo '<pre>requestedDatesOld';
        var_dump( $requestedDatesOld );
        echo '</pre>';
        
        echo '<pre>post';
        var_dump( $post );
        echo '</pre>';
        
        echo "<br /><br /><br />";
        
        /**
         *
         * <pre>Manager Approved this request...
         * array(3) {
                [0]=>
                array(7) {
                  ["date"]=>
                  string(10) "01/03/2017"
                  ["hours"]=>
                  string(4) "8.00"
                  ["category"]=>
                  string(10) "timeOffPTO"
                  ["requestId"]=>
                  string(6) "100944"
                  ["entryId"]=>
                  string(4) "4505"
                  ["fieldDirty"]=>
                  string(4) "true"
                  ["delete"]=>
                  string(4) "true"
                }
                [1]=>
                array(4) {
                  ["category"]=>
                  string(23) "timeOffUnexcusedAbsence"
                  ["date"]=>
                  string(10) "01/04/2017"
                  ["dow"]=>
                  string(3) "WED"
                  ["hours"]=>
                  string(4) "8.00"
                }
                [2]=>
                array(4) {
                  ["category"]=>
                  string(18) "timeOffBereavement"
                  ["date"]=>
                  string(10) "01/05/2017"
                  ["dow"]=>
                  string(3) "THU"
                  ["hours"]=>
                  string(4) "8.00"
                }
              }
              </pre><pre>requestedDatesOldarray(1) {
                [0]=>
                array(5) {
                  ["REQUEST_DATE"]=>
                  string(10) "2017-01-03"
                  ["REQUEST_DAY_OF_WEEK"]=>
                  string(3) "TUE"
                  ["REQUESTED_HOURS"]=>
                  string(4) "8.00"
                  ["REQUEST_CODE"]=>
                  string(1) "P"
                  ["DESCRIPTION"]=>
                  string(3) "PTO"
                }
              }
              </pre><pre>postobject(Zend\Stdlib\Parameters)#122 (1) {
                ["storage":"ArrayObject":private]=>
                array(4) {
                  ["request_id"]=>
                  string(6) "100944"
                  ["review_request_reason"]=>
                  string(0) ""
                  ["formDirty"]=>
                  string(4) "true"
                  ["selectedDatesNew"]=>
                  array(3) {
                    [0]=>
                    array(7) {
                      ["date"]=>
                      string(10) "01/03/2017"
                      ["hours"]=>
                      string(4) "8.00"
                      ["category"]=>
                      string(10) "timeOffPTO"
                      ["requestId"]=>
                      string(6) "100944"
                      ["entryId"]=>
                      string(4) "4505"
                      ["fieldDirty"]=>
                      string(4) "true"
                      ["delete"]=>
                      string(4) "true"
                    }
                    [1]=>
                    array(4) {
                      ["category"]=>
                      string(23) "timeOffUnexcusedAbsence"
                      ["date"]=>
                      string(10) "01/04/2017"
                      ["dow"]=>
                      string(3) "WED"
                      ["hours"]=>
                      string(4) "8.00"
                    }
                    [2]=>
                    array(4) {
                      ["category"]=>
                      string(18) "timeOffBereavement"
                      ["date"]=>
                      string(10) "01/05/2017"
                      ["dow"]=>
                      string(3) "THU"
                      ["hours"]=>
                      string(4) "8.00"
                    }
                  }
                }
              }
         */
        
        /**
         * [0]=>
                array(7) {
                  ["date"]=>
                  string(10) "01/03/2017"
                  ["hours"]=>
                  string(4) "8.00"
                  ["category"]=>
                  string(10) "timeOffPTO"
                  ["requestId"]=>
                  string(6) "100944"
                  ["entryId"]=>
                  string(4) "4505"
                  ["fieldDirty"]=>
                  string(4) "true"
                  ["delete"]=>
                  string(4) "true"
                }
         * 
         * 
         * [0]=>
                array(5) {
                  ["REQUEST_DATE"]=>
                  string(10) "2017-01-03"
                  ["REQUEST_DAY_OF_WEEK"]=>
                  string(3) "TUE"
                  ["REQUESTED_HOURS"]=>
                  string(4) "8.00"
                  ["REQUEST_CODE"]=>
                  string(1) "P"
                  ["DESCRIPTION"]=>
                  string(3) "PTO"
                }
         */
        
        if( $post->formDirty=="true" ) {
            $TimeOffRequests = new TimeOffRequests();
            
//            echo '<pre>';
            foreach( $post->selectedDatesNew as $ctr => $request ) {
                if( array_key_exists( 'entryId', $request ) &&
                    $request['fieldDirty']=="true" &&
                    !array_key_exists( 'delete', $request )
                  ) {
                    // copy entry in TIMEOFF_REQUEST_ENTRIES to TIMEOFF_REQUEST_ENTRIES_ARCHIVE,
                    // Update the entryId in TIMEOFF_REQUEST_ENTRIES
//                    echo "Update the entryId in TIMEOFF_REQUEST_ENTRIES<br />";
                    
                    $data = [ 'ENTRY_ID' => $request['entryId'],
                              'REQUEST_ID' => $post->request_id,
                              'REQUEST_DATE' => $request['date'],
                              'REQUESTED_HOURS' => $request['hours'],
                              'REQUEST_CATEGORY' => $request['category'],
                              'REQUEST_DAY_OF_WEEK' => $request['dow']
                            ];
                    
                    $TimeOffRequests->copyRequestEntriesToArchive( $post->request_id );
                    $TimeOffRequests->updateRequestEntry( $data );
                }
                if( array_key_exists( 'entryId', $request ) &&
                    $request['fieldDirty']=="true" &&
                    array_key_exists( 'delete', $request )
                  ) {
                    // copy entry in TIMEOFF_REQUEST_ENTRIES to TIMEOFF_REQUEST_ENTRIES_ARCHIVE,
                    // update entry in TIMEOFF_REQUEST_ENTRIES so IS_DELETED = 1
//                    echo "copy entries in TIMEOFF_REQUEST_ENTRIES to TIMEOFF_REQUEST_ENTRIES_ARCHIVE,<br />";
//                    echo "update entry in TIMEOFF_REQUEST_ENTRIES so IS_DELETED = 1<br />";
                    
                    $TimeOffRequests->copyRequestEntriesToArchive( $post->request_id );
                    $TimeOffRequests->markRequestEntryAsDeleted( $request['entryId'] );
                }
                if( !array_key_exists( 'entryId', $request ) ) {
                    $data = [ 'REQUEST_ID' => $post->request_id,
                              'REQUEST_DATE' => $request['date'],
                              'REQUESTED_HOURS' => $request['hours'],
                              'REQUEST_CATEGORY' => $request['category'],
                              'REQUEST_DAY_OF_WEEK' => $request['dow']
                            ];
                    
                    $TimeOffRequests->addRequestEntry( $data );
                }
                
            }
            
            // Save json object of before / after
            // Create a summary of changes to be used in email
            
            $TimeOffRequests = new TimeOffRequests();
            $newRequest = $TimeOffRequests->findRequest( $post->request_id );
            // $newRequest['ENTRIES']
            // $requestedDatesOld
            
            $update_detail = [
                'old' => $requestedDatesOld,
                'new' => $newRequest['ENTRIES']
            ];
            
            $TimeOffRequests->addRequestUpdate( $post->loggedInUserEmployeeNumber, $post->request_id, $update_detail );
            
            echo '<pre>';
            echo json_encode( $update_detail );
            echo '</pre>';
            
//            echo "Save json object of before / after<br />";
//            echo "Create a summary of changes to be used in email<br />";
//            
//            echo '</pre>';
        }
        
        die( "*.*.*.*" );
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
        $employeeData = (array) $requestData['EMPLOYEE_DATA'];
        
        // Check if there were any updates to the form
        $updatesToFormMade = $this->checkForUpdatesMadeToForm( $post, $requestData['ENTRIES'] );
        
        die( "____++++____" );
        
//        echo '<pre>requestData';
//        var_dump( $requestData );
//        echo '</pre>';
        
        $dates = [];
        foreach( $requestData['ENTRIES'] as $ctr => $requestObject ) {
            $dates[]['date'] = $requestObject['REQUEST_DATE'];
        }
        
        $isFirstDateRequestedTooOld = $this->isFirstDateRequestedTooOld( $dates );
        
//        die($isFirstDateRequestedTooOld);
        
        $isPayrollReviewRequired = $validationHelper->isPayrollReviewRequired( $post->request_id, $requestData['EMPLOYEE_NUMBER'] ); // $validationHelper->isPayrollReviewRequired( $requestData, $employeeData );

        //  .....  && $isFirstDateRequestedTooOld === true
        
        if ( $isPayrollReviewRequired === true || $isFirstDateRequestedTooOld ) {
            $payrollReviewRequiredReason = '';
            if( $isPayrollReviewRequired ) {
                $payrollReviewRequiredReason = 'Payroll review required because of insufficient hours.';
            }
            if( $isFirstDateRequestedTooOld ) {
                $payrollReviewRequiredReason = 'Payroll review required because one or more days requested is at least 14 days old.';
            }
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
                $payrollReviewRequiredReason );
            
            /** Change status to Pending Payroll Approval */
            $requestReturnData = $TimeOffRequests->submitApprovalResponse(
                $TimeOffRequests->getRequestStatusCode( 'pendingPayrollApproval' ),
                $post->request_id,
                $post->review_request_reason );
        } else {
            /** Send calendar invites for this request **/
            $isSent = $this->sendCalendarInvitationsForRequest( $post );
            $RequestEntry = new RequestEntry();
            $dateRequestBlocks = $RequestEntry->getRequestObject( $post->request_id );
            
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
    
    /**
     * Sends Outlook calendar invitations for an approved request.
     * 
     * @param type $post
     * @return type
     */
    public function sendCalendarInvitationsForRequest( $post )
    {
        $OutlookHelper = new OutlookHelper();
        $RequestEntry = new RequestEntry();
        $TimeOffRequests = new TimeOffRequests();
        $Employee = new Employee();
        $calendarInviteData = $TimeOffRequests->findRequestCalendarInviteData( $post->request_id );
        $dateRequestBlocks = $RequestEntry->getRequestObject( $post->request_id );
        $employeeData = $Employee->findEmployeeTimeOffData( $dateRequestBlocks['for']['employee_number'], "Y", "EMPLOYER_NUMBER, EMPLOYEE_NUMBER, LEVEL_1, LEVEL_2, LEVEL_3, LEVEL_4, SALARY_TYPE" );
        
        return $OutlookHelper->addToCalendar( $calendarInviteData, $employeeData );
    }
 
    /**
     * Handles the manager denied process.
     * 
     * @return JsonModel
     */
    public function submitManagerDeniedAction()
    {
        $post = $this->getRequest()->getPost();
        
        echo '<pre>Manager Denied this request...';
        var_dump( $post );
        echo '</pre>';
        die();
        
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
            $isSent = $this->sendCalendarInvitationsForRequest( $post );
            
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
        /** Clean up / append data to the Request **/
        $post = $this->getRequest()->getPost();
        $post = $this->appendRequestData( $post );
        $post = $this->addRequestForEmployeeData( $post );
        $Employee = new Employee();
        $TimeOffRequests = new TimeOffRequests();
        $TimeOffRequestLog = new TimeOffRequestLog();
        $requestData = $TimeOffRequests->findRequest( $post->request_id );
        
        try {
            $this->emailDeniedNoticeToEmployee( $post );
            
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
        } catch ( Exception $ex ) {
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
}
