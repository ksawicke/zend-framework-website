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
    
    public $managerViewName = [
        'pending-manager-approval' => 'Pending Manager Approval'
    ];
    
    public $payrollViewName = [
        'update-checks' => 'Update Checks',
        'pending-payroll-approval' => 'Pending Payroll Approval',
        'completed-pafs' => 'Completed PAFs',
        'pending-as400-upload' => 'Pending AS400 Upload'
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

    /**
     * Allows an employee to submit a new time off request for themselves.
     * In addition, a manager may submit time off requests for their direct reports,
     * and Payroll may submit time off requests for anyone.
     * 
     * @return ViewModel
     */
    public function createAction()
    {
        $Employee = new \Request\Model\Employee();
        
        return new ViewModel([
            'employeeData' => $Employee->findTimeOffEmployeeData($this->employeeNumber, "Y"),
            'isManager' => \Login\Helper\UserSession::getUserSessionVariable('IS_MANAGER'),
            'flashMessages' => ['success' => $this->flashMessenger()->getCurrentSuccessMessages(),
                                'warning' => $this->flashMessenger()->getCurrentWarningMessages(),
                                'error' => $this->flashMessenger()->getCurrentErrorMessages(),
                                'info' => $this->flashMessenger()->getCurrentInfoMessages()
                               ]
        ]);
    }
    
    /**
     * Return a session message that the request was approved successfully.
     * 
     * @return string
     */
    public function approvedRequestAction()
    {
        $this->flashMessenger()->addSuccessMessage('You approved the request succesfully.');
        $this->redirect()->toRoute('viewManagerQueue', array(
            'controller' => 'request',
            'action' =>  'view-manager-queue',
            'manager-view' => 'pending-manager-approval'
        ));
    }
    
    /**
     * Return a session message that the request was denied successfully.
     * 
     * @return string
     */
    public function deniedRequestAction()
    {
        $this->flashMessenger()->addSuccessMessage('You denied the request succesfully.');
        $this->redirect()->toRoute('viewManagerQueue', array(
            'controller' => 'request',
            'action' =>  'view-manager-queue',
            'manager-view' => 'pending-manager-approval'
        ));
    }
    
    /**
     * Return a session message that the request was submitted successfully.
     * 
     * @return string
     */
    public function submittedForApprovalAction()
    {
        $this->flashMessenger()->addSuccessMessage('Request has been submitted successfully.');
        return $this->redirect()->toRoute('create');
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
                    $TimeoffRequests = new \Request\Model\TimeOffRequests();
                    $TimeoffRequestLog = new \Request\Model\TimeoffRequestLog();
                    $requestData = $Employee->checkHoursRequestedPerCategory($request->getPost()->request_id);
                    $employeeData = $Employee->findTimeOffEmployeeData($requestData['EMPLOYEE_NUMBER']);
                    
                    $validationHelper = new \Request\Helper\ValidationHelper();
                    $payrollReviewRequired = $validationHelper->isPayrollReviewRequired($requestData, $employeeData);
                    
                    if($payrollReviewRequired) {
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

    /**
     * Allow managers to view timeoff requests in status Pending Manager Approval.
     * 
     * @return ViewModel
     */
    public function viewManagerQueueAction()
    {
        $managerView = $this->params()->fromRoute('manager-view');
        $Employee = new \Request\Model\Employee();
        $isLoggedInUserManager = $Employee->isManager($this->employeeNumber);
        if($isLoggedInUserManager!=="Y") {
            $this->flashMessenger()->addWarningMessage('You are not authorized to view that page.');
            return $this->redirect()->toRoute('create');
        }

        $this->layout()->setVariable( 'managerView', $managerView );
        
        $view = new ViewModel([
            'isLoggedInUserManager' => $isLoggedInUserManager,
            'managerView' => $managerView,
            'managerViewName' => $this->managerViewName[$managerView],
            'employeeNumber' => $this->employeeNumber,
            'flashMessages' => $this->getFlashMessages()
        ]);
        $view->setTemplate( 'request/manager-queues/manager-queue.phtml' );
        return $view;
    }
    
    /**
     * Allow payroll to view requests in the Pending Checks status.
     * 
     * @return ViewModel
     */
    public function viewPayrollQueueAction()
    {
        $payrollView = $this->params()->fromRoute('payroll-view');
        $Employee = new \Request\Model\Employee();
        $isLoggedInUserPayroll = $Employee->isPayroll( $this->employeeNumber );
        if($isLoggedInUserPayroll!=="Y") {
            $this->flashMessenger()->addWarningMessage('You are not authorized to view that page.');
            return $this->redirect()->toRoute('create');
        }

        $this->layout()->setVariable( 'payrollView', $payrollView );
        
        $view = new ViewModel([
            'isLoggedInUserPayroll' => $isLoggedInUserPayroll,
            'payrollView' => $payrollView,
            'payrollViewName' => $this->payrollViewName[$payrollView],
            'employeeNumber' => $this->employeeNumber,
            'flashMessages' => $this->getFlashMessages()
        ]);
        $view->setTemplate( 'request/payroll-queues/payroll-queue.phtml' );
        return $view;
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
            'flashMessages' => $this->getFlashMessages()
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
    
    public function reviewRequestAction()
    {
        $requestId = $this->params()->fromRoute('request_id');
        $Employee = new \Request\Model\Employee();
        $TimeoffRequests = new \Request\Model\TimeoffRequests();
        $timeoffRequestData = $TimeoffRequests->findRequest( $requestId );
        $totalHoursRequested = $TimeoffRequests->countTimeoffRequested( $requestId );
        
//        echo '<pre>';
//        print_r( $timeoffRequestData['ENTRIES'] );
//        echo '</pre>';
//        exit();
        
        $hoursRequestedHtml = $TimeoffRequests->drawHoursRequested( $timeoffRequestData['ENTRIES'] );
        
        return new ViewModel(array(
            'timeoffRequestData' => $timeoffRequestData,
            'totalHoursRequested' => $totalHoursRequested,
            'hoursRequestedHtml' => $hoursRequestedHtml
        ));
    }
    
    protected function setEmployeeNumber($employeeNumber)
    {
        $this->employeeNumber = $employeeNumber;
    }
    
    /**
     * Returns any set flash messages
     * 
     * @return array
     */
    private function getFlashMessages()
    {
        return ['success' => $this->flashMessenger()->getCurrentSuccessMessages(),
                'warning' => $this->flashMessenger()->getCurrentWarningMessages(),
                'error' => $this->flashMessenger()->getCurrentErrorMessages(),
                'info' => $this->flashMessenger()->getCurrentInfoMessages()
               ];
    }

}
