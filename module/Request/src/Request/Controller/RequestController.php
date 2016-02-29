<?php
namespace Request\Controller;

//use Request\Service\RequestServiceInterface;
//use Zend\Form\FormInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;

class RequestController extends AbstractActionController
{
//    protected $requestService;
//
//    protected $requestForm;

    protected $employeeNumber;

    protected $managerEmployeeNumber;
    
    public $invalidRequestDates = [
        'before' => '',
        'after' => '',
        'individual' => []
    ];
    
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
    
    protected static $categoryToClass = [
        'PTO' => 'timeOffPTO',
        'Float' => 'timeOffFloat',
        'Sick' => 'timeOffSick',
        'UnexcusedAbsence' => 'timeOffUnexcusedAbsence',
        'Bereavement' => 'timeOffBereavement',
        'CivicDuty' => 'timeOffCivicDuty',
        'Grandfathered' => 'timeOffGrandfathered',
        'ApprovedNoPay' => 'timeOffApprovedNoPay'
    ];
    
    protected static $codesToKronos = [
        'P' => 'PTO',
        'R' => 'GFVAC',
        'B' => 'BR',
        'K' => 'FHP',
        'S' => 'SK',
        'V' => 'VA'
    ];
    
    public function __construct() // RequestServiceInterface $requestService, FormInterface $requestForm
    {
//        $this->requestService = $requestService;
//        $this->requestForm = $requestForm;

//         echo '<pre>';
//         print_r($_SESSION['Timeoff_'.ENVIRONMENT]);
//         echo '</pre>';
//         die('**');

        $this->employeeNumber = $_SESSION['Timeoff_'.ENVIRONMENT]['EMPLOYEE_NUMBER'];
        $this->managerEmployeeNumber = $_SESSION['Timeoff_'.ENVIRONMENT]['MANAGER_EMPLOYEE_NUMBER'];
        
        // Disable dates starting with one month ago and any date before.
        $this->invalidRequestDates['before'] = date("m/d/Y", strtotime("-1 month", strtotime(date("m/d/Y"))));
        
        // Disable dates starting with the following date.
        $this->invalidRequestDates['after'] = date("m/d/Y", strtotime("+1 year", strtotime(date("m/d/Y"))));
        
        // Disable any dates in this array
        $this->invalidRequestDates['individual'] = [
            '12/25/2015',
            '01/01/2016',
            '05/30/2016',
            '07/04/2016',
            '09/05/2016',
            '11/24/2016',
            '12/26/2016',
            '01/02/2017'
        ];
    }

    public function outlookAction()
    {
        $employeeSchedules = new \Request\Model\EmployeeSchedules();
        $employeeSchedules->getAll(['test'=>'ww']);
        die('@@');
        
        $description = '[DEVELOPMENT] This is a reminder from the Time Off system that Kevin Sawicke is taking off the following time off: 01/25/2016 8.00 PTO; 01/26/2016 8.00 PTO; 01/27/2016 8.00 PTO; 01/28/2016 8.00 PTO; 01/29/2016 8.00 PTO';
        
        $requestObject = [
            'datesRequested' => [ ['start' => '20160125', 'end' => '20160129' ],
                                  ['start' => '20160201', 'end' => '20160205' ]
                                ],
            'subject' =>        '[DEVELOPMENT] KEVIN SAWICKE - APPROVED TIME OFF',
            'description' =>    '[DEVELOPMENT] This is a reminder from the Time Off system that Kevin Sawicke is taking off the following time off: 01/25/2016 8.00 PTO; 01/26/2016 8.00 PTO; 01/27/2016 8.00 PTO; 01/28/2016 8.00 PTO; 01/29/2016 8.00 PTO',
            'organizer' =>      [ 'name' => 'Kevin Sawicke', 'email' => 'kevin_sawicke@swifttrans.com' ],
            'to' =>             'kevin_sawicke@swifttrans.com',
            'participants' =>   [ ['name' => 'Kevin Sawicke', 'email' => 'kevin_sawicke@swifttrans.com' ],
                                  ['name' => 'Mary Jackson', 'email' => 'mary_jackson@swifttrans.com' ]
                                ]
        ];
        
        echo '<pre>requestObject';
        print_r($requestObject);
        echo '</pre>';
        die("@@");
        
        $outlookHelper = new \Request\Helper\OutlookHelper();
        $outlookHelper->setStartDate('20160125'); // date in yyyymmdd format
        $outlookHelper->setEndDate('20160129'); // date in yyyymmdd format
        $outlookHelper->setSubject('[DEVELOPMENT] KEVIN SAWICKE - APPROVED TIME OFF');
        $outlookHelper->setOrganizerName('Kevin Sawicke'); // Employee name
        $outlookHelper->setOrganizerEmail('kevin_sawicke@swifttrans.com'); // Employee email
        $outlookHelper->setToEmail('kevin_sawicke@swifttrans.com'); // Employee email
        $outlookHelper->setParticipantName1('Kevin Sawicke'); // Employee name
        $outlookHelper->setParticipantEmail1('kevin_sawicke@swifttrans.com'); // Employee email
        $outlookHelper->setParticipantName2('Mary Jackson'); // Manager name
        $outlookHelper->setParticipantEmail2('mary_jackson@swifttrans.com'); // Manager email
        $outlookHelper->setDescription($description); // date in mm/dd/yyyy format; 2nd line should be request i.e. 6.00 PTO + 2.00 Sick
        
        $isSent = $outlookHelper->addToCalendar();
        
        var_dump($isSent);
        
        
        
        $description2 = '[DEVELOPMENT] This is a reminder from the Time Off system that Kevin Sawicke is taking off the following time off: 02/01/2016 8.00 PTO; 02/02/2016 8.00 PTO; 02/03/2016 8.00 PTO; 02/04/2016 8.00 PTO; 02/05/2016 8.00 PTO';
        
        $outlookHelper->setStartDate('20160201'); // date in yyyymmdd format
        $outlookHelper->setEndDate('20160205'); // date in yyyymmdd format
        $outlookHelper->setSubject('[DEVELOPMENT] KEVIN SAWICKE - APPROVED TIME OFF');
        $outlookHelper->setOrganizerName('Kevin Sawicke'); // Employee name
        $outlookHelper->setOrganizerEmail('kevin_sawicke@swifttrans.com'); // Employee email
        $outlookHelper->setToEmail('kevin_sawicke@swifttrans.com'); // Employee email
        $outlookHelper->setParticipantName1('Kevin Sawicke'); // Employee name
        $outlookHelper->setParticipantEmail1('kevin_sawicke@swifttrans.com'); // Employee email
        $outlookHelper->setParticipantName2('Mary Jackson'); // Manager name
        $outlookHelper->setParticipantEmail2('mary_jackson@swifttrans.com'); // Manager email
        $outlookHelper->setDescription($description2); // date in mm/dd/yyyy format; 2nd line should be request i.e. 6.00 PTO + 2.00 Sick
        
        $isSent2 = $outlookHelper->addToCalendar();
        
        var_dump($isSent2);
        
        die("@@@");
    }
    
    public function createAction()
    {
        $Employee = new \Request\Model\Employee();
        
        return new ViewModel([
            'employeeData' => $Employee->findTimeOffEmployeeData($this->employeeNumber, "Y"),
//             'managerEmployees' => $this->requestService->findManagerEmployees($this->employeeNumber, 'sena'),
            'isManager' => \Login\Helper\UserSession::getUserSessionVariable('IS_MANAGER'),
            'flashMessages' => ['success' => $this->flashMessenger()->getCurrentSuccessMessages(),
                                'warning' => $this->flashMessenger()->getCurrentWarningMessages(),
                                'error' => $this->flashMessenger()->getCurrentErrorMessages(),
                                'info' => $this->flashMessenger()->getCurrentInfoMessages()
                               ]
        ]);
    }
    
    public function approvedRequestAction()
    {
        $this->flashMessenger()->addSuccessMessage('You approved the request succesfully.');
        return $this->redirect()->toRoute('viewEmployeeRequests');
    }
    
    public function deniedRequestAction()
    {
        $this->flashMessenger()->addSuccessMessage('You denied the request succesfully.');
        return $this->redirect()->toRoute('viewEmployeeRequests');
    }
    
    /**
     * Load three calendars starting with the month and year passed in via AJAX.
     * 
     * @return \Zend\View\Model\JsonModel
     */
    public function apiAction()
    {
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            switch($request->getPost()->action) {
                case 'submitApprovalResponse':
                    $Employee = new \Request\Model\Employee();
                    $requestData = $Employee->checkHoursRequestedPerCategory($request->getPost()->request_id);
                    $employeeData = $Employee->findTimeOffEmployeeData($requestData['EMPLOYEE_NUMBER']);
                    
                    var_dump($requestData);
                    var_dump($employeeData);
                    die("#");
                    
//                    var_dump($requestData);die("@@@");
                    
                    $validationHelper = new \Request\Helper\ValidationHelper();
                    $payrollReviewRequired = $validationHelper->isPayrollReviewRequired($requestData, $employeeData);
                    
                    if($payrollReviewRequired) {
                        $this->requestService->logEntry(
                            $request->getPost()->request_id,
                            \Login\Helper\UserSession::getUserSessionVariable('EMPLOYEE_NUMBER'),
                            'Time off request approved by ' . \Login\Helper\UserSession::getUserSessionVariable('FIRST_NAME') . ' '  . \Login\Helper\UserSession::getUserSessionVariable('LAST_NAME') .
                            ' for ' . trim(ucwords(strtolower($employeeData['COMMON_NAME']))) . " " . trim(ucwords(strtolower($employeeData['LAST_NAME']))));

                        $requestReturnData = $this->requestService->submitApprovalResponse('A', $request->getPost()->request_id, $request->getPost()->review_request_reason);
                        
                        $this->requestService->logEntry($request->getPost()->request_id, \Login\Helper\UserSession::getUserSessionVariable('EMPLOYEE_NUMBER'), 'Payroll review required because of insufficient hours');
                        $requestReturnData = $this->requestService->submitApprovalResponse('Y', $request->getPost()->request_id, $request->getPost()->review_request_reason);
                    } else {
                        $calendarInviteData = $this->requestService->findRequestCalendarInviteData($request->getPost()->request_id); 
                        $employeeData = $this->requestService->findTimeOffBalancesByEmployee(trim($calendarInviteData['for']['EMPLOYEE_NUMBER']));
                        $requestObject = [
                            'datesRequested' => $calendarInviteData['datesRequested'],
                            'for' =>            $employeeData,
    //                        'subject' =>        '[DEVELOPMENT] ' . strtoupper($for) . ' - APPROVED TIME OFF',
    //                        'description' =>    '[DEVELOPMENT] This is a reminder from the Time Off system that ' . ucwords(strtolower($for)) . ' is taking off the following time off: ' . $descriptionString,
                            'organizer' =>      [ 'name' => 'Time Off Requests', 'email' => 'timeoffrequests-donotreply@swifttrans.com' ], // 'name' => ucwords(strtolower($for)), 'email' => $forEmail
                            'to' =>             'kevin_sawicke@swifttrans.com', // ,'.$forEmail.",".$managerEmail
                            'participants' =>   [ [ 'name' => 'Kevin Sawicke', 'email' => 'kevin_sawicke@swifttrans.com' ] ]/*[ ['name' => ucwords(strtolower($for)), 'email' => $forEmail ],
                                                  ['name' => ucwords(strtolower($manager)), 'email' => $managerEmail ]
                                                ]*/
                        ];
                        
                        $outlookHelper = new \Request\Helper\OutlookHelper();
                        $isSent = $outlookHelper->addToCalendar($requestObject);

                        $this->requestService->logEntry(
                            $request->getPost()->request_id,
                            \Login\Helper\UserSession::getUserSessionVariable('EMPLOYEE_NUMBER'),
                            'Time off request approved by ' . \Login\Helper\UserSession::getUserSessionVariable('FIRST_NAME') . ' '  . \Login\Helper\UserSession::getUserSessionVariable('LAST_NAME') .
                            ' for ' . trim(ucwords(strtolower($employeeData['COMMON_NAME']))) . " " . trim(ucwords(strtolower($employeeData['LAST_NAME']))));

                        $requestReturnData = $this->requestService->submitApprovalResponse('A', $request->getPost()->request_id, $request->getPost()->review_request_reason);
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
                    break;
                    
                case 'submitDenyResponse':
                    $requestReturnData = $this->requestService->submitApprovalResponse('D', $request->getPost()->request_id, $request->getPost()->review_request_reason);
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
                    break;
                
                case 'getEmployeeList':
                    $return = [];
                    $Employee = new \Request\Model\Employee();
                    $managerEmployees = $Employee->findManagerEmployees($this->employeeNumber, $request->getPost()->search, $request->getPost()->directReportFilter);
                    foreach($managerEmployees as $id => $data) {
                        $return[] = [ 'id' => $data->EMPLOYEE_NUMBER,
                                      'text' => $data->EMPLOYEE_NAME . ' (' . $data->EMPLOYEE_NUMBER . ') - ' . $data->POSITION_TITLE
                                    ];
                    }
                    $result = new JsonModel($return);
                    break;
                    
                case 'submitTimeoffRequest':                    
                    $employeeNumber = (is_null($request->getPost()->employeeNumber) ? trim($this->employeeNumber) : trim($request->getPost()->employeeNumber));
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
                    break;
                    
                case 'loadTeamCalendar':
                    $startDate = $request->getPost()->startYear . "-" . $request->getPost()->startMonth . "-01";
                    $endDate = date("Y-m-t", strtotime($startDate));
                    
                    $time = strtotime($startDate);
                    $prev = date("Y-m-d", strtotime("-1 month", $time));
                    $current = date("Y-m-d", strtotime("+0 month", $time));
                    $one = date("Y-m-d", strtotime("+1 month", $time));
                    $oneMonthBack = new \DateTime($prev);
                    $currentMonth = new \DateTime($current);
                    $oneMonthOut = new \DateTime($one);
                    
                    $isLoggedInUserManager = $this->requestService->isManager($this->employeeNumber);
                    $employeeNumber = ( ($isLoggedInUserManager==="Y") ? $this->employeeNumber : $this->managerEmployeeNumber );
                    
                    $calendarData = $this->requestService->findTimeOffCalendarByManager($employeeNumber, $startDate, $endDate);
                    
                    $result = new JsonModel([
                        'success' => true,
                        'calendars' => [
                            1 => [ 'header' => '<span class="teamCalendarHeader">' . $currentMonth->format('M') . ' ' . $currentMonth->format('Y') . '</span>',
                                'data' => \Request\Helper\Calendar::drawCalendar($request->getPost()->startMonth, $request->getPost()->startYear, $calendarData)
                            ]
                        ],
                        'prevButton' => '<span class="glyphicon-class glyphicon glyphicon-chevron-left calendarNavigation" data-month="' . $oneMonthBack->format('m') . '" data-year="' . $oneMonthBack->format('Y') . '"> </span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                        'nextButton' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="glyphicon-class glyphicon glyphicon-chevron-right calendarNavigation" data-month="' . $oneMonthOut->format('m') . '" data-year="' . $oneMonthOut->format('Y') . '"> </span>',
                        'openHeader' => '<strong>',
                        'closeHeader' => '</strong><br /><br />'
                    ]);
                    break;

                case 'loadCalendar':
                    $startDate = $request->getPost()->startYear . "-" . $request->getPost()->startMonth . "-01";
                    $endDate = date("Y-m-t", strtotime($startDate));
                    $employeeNumber = (is_null($request->getPost()->employeeNumber) ? trim($this->employeeNumber) : trim($request->getPost()->employeeNumber));
                    \Request\Helper\Calendar::setCalendarHeadings(['S','M','T','W','T','F','S']);
                    \Request\Helper\Calendar::setBeginWeekOne('<tr class="calendar-row" style="height:40px;">');
                    \Request\Helper\Calendar::setBeginCalendarRow('<tr class="calendar-row" style="height:40px;">');
                    \Request\Helper\Calendar::setInvalidRequestDates($this->invalidRequestDates);
                    $calendarDates = \Request\Helper\Calendar::getDatesForThreeCalendars($request->getPost()->startYear, $request->getPost()->startMonth);

                    $Employee = new \Request\Model\Employee();
                    $employeeData = $Employee->findTimeOffEmployeeData($employeeNumber, "Y");
                    $requestData = $Employee->findTimeOffRequestData($employeeNumber, $calendarDates);
//                    $calendarData = $Employee->findTimeOffCalendarByEmployeeNumber($employeeNumber, $startDate, $endDate);
                    
//                    echo '<pre>';
//                    print_r($calendarData);
//                    echo '</pre>';
//                    die("@@@");
                    
                    $result = new JsonModel([
                        'success' => true,
                        'calendarData' => \Request\Helper\Calendar::getThreeCalendars($request->getPost()->startYear, $request->getPost()->startMonth),
                        'employeeData' => $employeeData,
                        'requestData' => $requestData,
//                        'test' => $calendarData,
                        'loggedInUser' => ['isManager' => \Login\Helper\UserSession::getUserSessionVariable('IS_MANAGER'),
                                           'isPayroll' => \Login\Helper\UserSession::getUserSessionVariable('IS_PAYROLL')
                                          ]
                    ]);
                    break;
            }
            
            return $result;
        }
    }

    public function viewEmployeeRequestsAction()
    {
        $Employee = new \Request\Model\Employee();
        $RequestEntry = new \Request\Model\RequestEntry();
        $Papaa = new \Request\Model\Papaa();
        
        $isLoggedInUserManager = $Employee->isManager($this->employeeNumber);
        if($isLoggedInUserManager!=="Y") {
            $this->flashMessenger()->addWarningMessage('You are not authorized to view that page.');
            return $this->redirect()->toRoute('create');
        }
        
        $dateRequestBlocks = $RequestEntry->getRequestBlocks( 10516 );
        $employeeData = $Employee->findTimeOffEmployeeData( "49499", "Y",
            "EMPLOYER_NUMBER, EMPLOYEE_NUMBER, LEVEL_1, LEVEL_2, LEVEL_3, LEVEL_4" );
        
//        echo '<pre>';
//            print_r( $employeeData );
//            echo '</pre>';
//            die("............");
        
        foreach( $dateRequestBlocks as $ctr => $dateCollection )
        {
            $Papaa->SaveDates( $employeeData, $dateCollection );
            echo '<pre>';
            print_r( $Papaa->collection );
            echo '</pre>';
        }
        
        die(".....");
        
        echo '<pre>';
        print_r( $dates );
        echo '</pre>';
        
        die(".....");
        
        $Papaa->SaveDates( ['1', '2', '3'] );
        echo '<pre>';
        print_r( $Papaa->collection );
        echo '</pre>';
        
        die(".....");
        
        
//        $Helper->papaa();
//        $Helper->getPapaaVars();
        
        die("<br /><br />.");
        
        return new ViewModel([
            'isLoggedInUserManager' => $isLoggedInUserManager,
            'managerReportsData' => $Employee->findQueuesByManager($this->employeeNumber),
            'flashMessages' => ['success' => $this->flashMessenger()->getCurrentSuccessMessages(),
                                'warning' => $this->flashMessenger()->getCurrentWarningMessages(),
                                'error' => $this->flashMessenger()->getCurrentErrorMessages(),
                                'info' => $this->flashMessenger()->getCurrentInfoMessages()
                               ]
        ]);
    }
    
    public function viewMyRequestsAction()
    {
        $startDate = date("Y") . "-" . date("m") . "-01";
        $endDate = date("Y-m-t", strtotime($startDate));
        $employeeNumber = trim($this->employeeNumber);
        \Request\Helper\Calendar::setCalendarHeadings(['S','M','T','W','T','F','S']);
        \Request\Helper\Calendar::setBeginWeekOne('<tr class="calendar-row" style="height:40px;">');
        \Request\Helper\Calendar::setBeginCalendarRow('<tr class="calendar-row" style="height:40px;">');
        \Request\Helper\Calendar::setInvalidRequestDates($this->invalidRequestDates);
        $calendarDates = \Request\Helper\Calendar::getDatesForThreeCalendars(date("Y"), date("m"));

        $Employee = new \Request\Model\Employee();
        $employeeData = $Employee->findTimeOffEmployeeData($employeeNumber, "Y");
        $requestData = $Employee->findTimeOffRequestData($employeeNumber, $calendarDates);
        
//        var_dump($this->layout()->employeeData);
        return new ViewModel([
            'employeeData' => $employeeData,
            'requestData' => $requestData,
            'flashMessages' => ['success' => $this->flashMessenger()->getCurrentSuccessMessages(),
                                'warning' => $this->flashMessenger()->getCurrentWarningMessages(),
                                'error' => $this->flashMessenger()->getCurrentErrorMessages(),
                                'info' => $this->flashMessenger()->getCurrentInfoMessages()
                               ]
        ]);
    }
    
    public function viewMyTeamCalendarAction()
    {
//         $calendarData = $this->requestService->findTimeOffCalendarByManager($this->employeeNumber, '2016-01-01', '2016-01-31');
         
        return new ViewModel(array(
//             'calendarData' => $calendarData,
//             'calendarHtml' => \Request\Helper\Calendar::drawCalendar('12', '2015', $calendarData)
        ));
    }
    
    public function submittedForApprovalAction()
    {
        $this->flashMessenger()->addSuccessMessage('Request has been submitted successfully.');
        return $this->redirect()->toRoute('create');
    }
    
    public function buildPapaaTestAction()
    {
        $requestId = $this->params()->fromRoute('request_id');
        
        echo '<pre>';
        print_r( $requestId );
        echo '</pre>';
        
        die("------------------");
    }
    
    public function reviewRequestAction()
    {
        $requestId = $this->params()->fromRoute('request_id');
        $pendingRequestData = $this->requestService->findTimeOffPendingRequestsByEmployee($this->employeeNumber, 'managerQueue', $requestId);
        
//         echo '<pre>';
//         print_r($pendingRequestData);
//         echo '</pre>';
//         die("@@@");
        
        $calendarFirstDate = \DateTime::createFromFormat('Y-m-d', trim($pendingRequestData[$requestId]['FIRST_DATE_REQUESTED']));
        $calendarLastDate = \DateTime::createFromFormat('Y-m-d', $pendingRequestData[$requestId]['LAST_DATE_REQUESTED']);
        
        $pendingRequestData[$requestId]['CALENDAR_FIRST_DATE'] = $calendarFirstDate->format('Y-m-01');
        $pendingRequestData[$requestId]['CALENDAR_LAST_DATE'] = $calendarLastDate->format('Y-m-t');
        
        $teamCalendarData = $this->requestService->findTimeOffCalendarByManager($this->employeeNumber, $pendingRequestData[$requestId]['CALENDAR_FIRST_DATE'], $pendingRequestData[$requestId]['CALENDAR_LAST_DATE']);
        
//         $teamCalendarByDate = [];
//         foreach($teamCalendarData as $key => $calendarData) {
//             $date = $calendarData['REQUEST_DATE'];
//             $teamCalendarByDate[$date][] = [
//                 'EMPLOYEE_NUMBER' => trim($calendarData['EMPLOYEE_NUMBER']),
//                 'NAME' => trim($calendarData['FIRST_NAME']) . ' ' . trim($calendarData['LAST_NAME']),
//                 'REQUEST_TYPE' => $calendarData['REQUEST_TYPE'],
//                 'REQUESTED_HOURS' => $calendarData['REQUESTED_HOURS']
//             ];
//         }
        
        $teamCalendarByDate = [];
        foreach($teamCalendarData as $key => $calendarData) {
            $date = \DateTime::createFromFormat('Y-m-d', $calendarData['REQUEST_DATE']);
            $date = $date->format('m/d/Y');
            $stuff = trim($calendarData['FIRST_NAME']) . ' ' . trim($calendarData['LAST_NAME']) . ' - ' .
                $calendarData['REQUESTED_HOURS'] . ' ' . $calendarData['REQUEST_TYPE'] . '<br />';
            if(array_key_exists($date, $teamCalendarByDate)) {
                $teamCalendarByDate[$date] .= $stuff;
            } else {
                $teamCalendarByDate[$date] = $stuff;
            }
        }
        
        $pendingRequestData[$requestId]['TEAM_CALENDAR'] = $teamCalendarByDate;
        
//         echo '<pre>TEAM CALENDAR';
//         print_r($teamCalendarByDate);
//         echo '</pre>';
//         die("@@@");
        
        return new ViewModel(array(
            'requestId' => $requestId,
            'pendingRequestData' => $pendingRequestData[$requestId]
        ));
    }
    
    protected function setEmployeeNumber($employeeNumber)
    {
        $this->employeeNumber = $employeeNumber;
    }
    
    // http://r2d2.cc/2014/01/27/create-outlook-meeting-request-php/
    protected function testUpdateCal() {
        $to = 'kevin_sawicke@swifttrans.com';
        $subject = "TEST Meeting";

        $organizer = 'Kevin Sawicke';
        $organizer_email = 'kevin_sawicke@swifttrans.com';

        $participant_name_1 = 'Kevin Sawicke';
        $participant_email_1 = 'kevin_sawicke@swifttrans.com';

        $participant_name_2 = 'Kevin Sawicke';
        $participant_email_2 = 'kevin_sawicke@swifttrans.com';

        $location = "Sample location here";
        $date = '20160114';
        $startTime = '0800';
        $endTime = '1300';
        $subject = 'Is this the subject';
        $desc = 'The purpose of the meeting is to discuss something.';

        $headers = 'Content-Type:text/calendar; Content-Disposition: inline; charset=utf-8;\r\n';
        $headers .= "Content-Type: text/plain;charset=\"utf-8\"\r\n"; #EDIT: TYPO

        $message = "BEGIN:VCALENDAR\r\n
    VERSION:2.0\r\n
    PRODID:-//Timeoff-mailer//timeoff/NONSGML v1.0//EN\r\n
    METHOD:REQUEST\r\n
    BEGIN:VEVENT\r\n
    UID:" . md5(uniqid(mt_rand(), true)) . "swifttrans.com\r\n
    DTSTAMP:" . gmdate('Ymd') . 'T' . gmdate('His') . "Z\r\n
    DTSTART:" . $date . "T" . $startTime . "00Z\r\n
    DTEND:" . $date . "T" . $endTime . "00Z\r\n
    SUMMARY:" . $subject . "\r\n
    ORGANIZER;CN=" . $organizer . ":mailto:" . $organizer_email . "\r\n
    LOCATION:" . $location . "\r\n
    DESCRIPTION:" . $desc . "\r\n
    ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=TRUE;CN" . $participant_name_1 . ";X-NUM-GUESTS=0:MAILTO:" . $participant_email_1 . "\r\n
    ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=TRUE;CN" . $participant_name_2 . ";X-NUM-GUESTS=0:MAILTO:" . $participant_email_2 . "\r\n
    END:VEVENT\r\n
    END:VCALENDAR\r\n";

        $headers .= $message;
        $mailsent = mail($to, $subject, $message, $headers);

        return ($mailsent) ? (true) : (false);

//        $from_name = "Kevin";        
//        $from_address = "kevin_sawicke@swifttrans.com";        
//        $to_name = "Kevin";        
//        $to_address = "kevin_sawicke@swifttrans.com";        
//        $startTime = "01/15/2016 08:00:00";        
//        $endTime = "01/15/2016 13:00:00";        
//        $subject = "My Test Subject";        
//        $description = "My Awesome Description";        
//        $location = "TESTING LOCATION";
//        $this->sendIcalEvent($from_name, $from_address, $to_name, $to_address, $startTime, $endTime, $subject, $description, $location);
    }

    protected function sendIcalEvent($from_name, $from_address, $to_name, $to_address, $startTime, $endTime, $subject, $description, $location) {
        $domain = 'swifttrans.com';

        //Create Email Headers
        $mime_boundary = "----Meeting Booking----" . MD5(TIME());

        $headers = "From: " . $from_name . " <" . $from_address . ">\n";
        $headers .= "Reply-To: " . $from_name . " <" . $from_address . ">\n";
        $headers .= "MIME-Version: 1.0\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\n";
        $headers .= "Content-class: urn:content-classes:calendarmessage\n";

        //Create Email Body (HTML)
        $message = "--$mime_boundary\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\n";
        $message .= "Content-Transfer-Encoding: 8bit\n\n";
        $message .= "<html>\n";
        $message .= "<body>\n";
        $message .= '<p>Dear ' . $to_name . ',</p>';
        $message .= '<p>' . $description . '</p>';
        $message .= "</body>\n";
        $message .= "</html>\n";
        $message .= "--$mime_boundary\r\n";

        $ical = 'BEGIN:VCALENDAR' . "\r\n" .
                'PRODID:-//Microsoft Corporation//Outlook 10.0 MIMEDIR//EN' . "\r\n" .
                'VERSION:2.0' . "\r\n" .
                'METHOD:REQUEST' . "\r\n" .
                'BEGIN:VTIMEZONE' . "\r\n" .
                'TZID:Eastern Time' . "\r\n" .
                'BEGIN:STANDARD' . "\r\n" .
                'DTSTART:20091101T020000' . "\r\n" .
                'RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=1SU;BYMONTH=11' . "\r\n" .
                'TZOFFSETFROM:-0400' . "\r\n" .
                'TZOFFSETTO:-0500' . "\r\n" .
                'TZNAME:EST' . "\r\n" .
                'END:STANDARD' . "\r\n" .
                'BEGIN:DAYLIGHT' . "\r\n" .
                'DTSTART:20090301T020000' . "\r\n" .
                'RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=2SU;BYMONTH=3' . "\r\n" .
                'TZOFFSETFROM:-0500' . "\r\n" .
                'TZOFFSETTO:-0400' . "\r\n" .
                'TZNAME:EDST' . "\r\n" .
                'END:DAYLIGHT' . "\r\n" .
                'END:VTIMEZONE' . "\r\n" .
                'BEGIN:VEVENT' . "\r\n" .
                'ORGANIZER;CN="' . $from_name . '":MAILTO:' . $from_address . "\r\n" .
                'ATTENDEE;CN="' . $to_name . '";ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:' . $to_address . "\r\n" .
                'LAST-MODIFIED:' . date("Ymd\TGis") . "\r\n" .
                'UID:' . date("Ymd\TGis", strtotime($startTime)) . rand() . "@" . $domain . "\r\n" .
                'DTSTAMP:' . date("Ymd\TGis") . "\r\n" .
                'DTSTART;TZID="Eastern Time":' . date("Ymd\THis", strtotime($startTime)) . "\r\n" .
                'DTEND;TZID="Eastern Time":' . date("Ymd\THis", strtotime($endTime)) . "\r\n" .
                'TRANSP:OPAQUE' . "\r\n" .
                'SEQUENCE:1' . "\r\n" .
                'SUMMARY:' . $subject . "\r\n" .
                'LOCATION:' . $location . "\r\n" .
                'CLASS:PUBLIC' . "\r\n" .
                'PRIORITY:5' . "\r\n" .
                'BEGIN:VALARM' . "\r\n" .
                'TRIGGER:-PT15M' . "\r\n" .
                'ACTION:DISPLAY' . "\r\n" .
                'DESCRIPTION:Reminder' . "\r\n" .
                'END:VALARM' . "\r\n" .
                'END:VEVENT' . "\r\n" .
                'END:VCALENDAR' . "\r\n";
        $message .= 'Content-Type: text/calendar;name="meeting.ics";method=REQUEST' . "\n";
        $message .= "Content-Transfer-Encoding: 8bit\n\n";
        $message .= $ical;

        $mailsent = mail($to_address, $subject, $message, $headers);

        return ($mailsent) ? (true) : (false);
    }
    
    public function testAction()
    {   
        $employeeNumber = $this->params()->fromRoute('employee_number');
        $employeeData = $this->requestService->findTimeOffEmployeeData($employeeNumber, "Y");
        
        echo '<pre>TEST 1';
        print_r($employeeData);
        echo '</pre>';
        
        $Employee = new \Request\Model\Employee();
        $employeeData2 = $Employee->findTimeOffEmployeeData($employeeNumber, "Y");
        
        echo '<pre>TEST 2';
        print_r($employeeData2);
        echo '</pre>';
        
        die('@@@');
    }

}
