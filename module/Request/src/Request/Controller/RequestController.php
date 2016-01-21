<?php
namespace Request\Controller;

use Request\Service\RequestServiceInterface;
use Zend\Form\FormInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;

class RequestController extends AbstractActionController
{
    protected $requestService;

    protected $requestForm;

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
    
    public function __construct(RequestServiceInterface $requestService, FormInterface $requestForm)
    {
        $this->requestService = $requestService;
        $this->requestForm = $requestForm;

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
        
//         $session = new Container('User');
//         echo '<pre>';
//         print_r($_SESSION['User']);
//         echo '</pre>';
//         die('**');
        
//         if(!$this->isLoggedIn()) {
//             return $this->redirect()->toRoute('login');
//         }
    }
    
//     public function isLoggedIn()
//     {
//         return false;
//     }
    
//     public function loginAction()
//     {
//         return new ViewModel(array(
            
//         ));
//     }

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
//        $send = $this->testUpdateCal();
//        if($send) {
//            die("SENT");
//        } else {
//            die("NOT SENT");
//        }
        
        // One method to grab a service....
        // Not using this but leaving as a reference for maybe later.
        //         $service = $this->getServiceLocator()->get('Request\Service\RequestServiceInterface');
        //         var_dump($service->findTimeOffBalances($this->employeeId));
        //         exit();

//         \Request\Helper\Calendar::setCalendarHeadings(['S','M','T','W','T','F','S']);
//         \Request\Helper\Calendar::setBeginWeekOne('<tr class="calendar-row" style="height:40px;">');
//         \Request\Helper\Calendar::setBeginCalendarRow('<tr class="calendar-row" style="height:40px;">');
        
        return new ViewModel([
            'employeeData' => $this->requestService->findTimeOffBalancesByEmployee($this->employeeNumber),
//             'managerEmployees' => $this->requestService->findManagerEmployees($this->employeeNumber, 'sena'),
            'isManager' => \Login\Helper\UserSession::getUserSessionVariable('IS_MANAGER'),
            'flashMessages' => ['success' => $this->flashMessenger()->getCurrentSuccessMessages(),
                                'warning' => $this->flashMessenger()->getCurrentWarningMessages(),
                                'error' => $this->flashMessenger()->getCurrentErrorMessages(),
                                'info' => $this->flashMessenger()->getCurrentInfoMessages()
                               ]
//             'approvedRequestData' => $this->requestService->findTimeOffApprovedRequestsByEmployee($this->employeeNumber, 'datesOnly'),
//             'calendar1Html' => \Request\Helper\Calendar::drawCalendar('12', '2015', []),
//             'calendar2Html' => \Request\Helper\Calendar::drawCalendar('1', '2016', []),
//             'calendar3Html' => \Request\Helper\Calendar::drawCalendar('2', '2016', [])
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
                    $requestData = $this->requestService->checkHoursRequestedPerCategory($request->getPost()->request_id);
                    $employeeData = $this->requestService->findTimeOffBalancesByEmployee($requestData['EMPLOYEE_NUMBER']);
                    $validationHelper = new \Request\Helper\ValidationHelper();
                    $payrollReviewRequired = $validationHelper->isPayrollReviewRequired($requestData, $employeeData);
                    
                    if($payrollReviewRequired) {
                        // Log:
                        // Payroll review required because of insufficient hours
                    }
                    /**
                     * CHECK: Does employee have enough hours available for the categories being requested?
                     * PTO
                     *    If (PRPMS.PRVAC - PRPMS.PRVAT) > PTO_REQUESTED, ok
                     * FLOAT
                     *    If (PRPMS.PRSHA - PRPMS.PRSHT) > FLOAT_REQUESTED, ok
                     * SICK
                     *    If (PRPMS.PRSDA - PRPMS.PRSDT) > SICK_REQUESTED, ok
                     * GF
                     *    If (PRPMS.PRAC5E - PRPMS.PRAC5T) > GF_REQUESTED, ok
                     * 
                     * // If does not pass, route to Payroll for approval.
                     * // Status in current system is "Pending Payroll"
                     * // If passes, mark as approved and send calendar invites to "For" and Manager
                     */
                    echo '<pre>requestData';
                    print_r($requestData);
                    echo '</pre>';
                    
                    echo '<pre>employeeData';
                    print_r($employeeData);
                    echo '</pre>';
                    die("^@^@^@^@^@^@");
                    
                    
                    $calendarInviteData = $this->requestService->findRequestCalendarInviteData($request->getPost()->request_id); 
//                    var_dump($calendarInviteData);
//                    echo '#####' . trim($calendarInviteData['request']['EMPLOYEE_NUMBER']);
//                    die("@");
//                    $descriptionString = '';
//                    foreach($calendarInviteData['datesRequested'] as $group => $data) {
//                        // date("Ymd", strtotime($event['start']))
//                        $descriptionString .= 
//                            ($data['start']!==$data['end']) ?    
//                            $data['hours'] . " " . $data['type'] . ' on ' . date("m/d/Y", strtotime($data['start'])) . '; ' :
//                            $data['hours'] . " " . $data['type'] . ' daily from ' . date("m/d/Y", strtotime($data['start'])) . ' - ' . date("m/d/Y", strtotime($data['end'])) . '; ';
//                    }
//                    $descriptionString = substr($descriptionString, 0, -2);
                    
//                    $for = trim($calendarInviteData['request']['COMMON_NAME']) . " " . trim($calendarInviteData['request']['LAST_NAME']);
//                    $forEmail = trim($calendarInviteData['request']['EMAIL_ADDRESS']);
//                    $manager = trim($calendarInviteData['request']['MANAGER_FIRST_NAME']) . " " . trim($calendarInviteData['request']['MANAGER_LAST_NAME']);
//                    $managerEmail = trim($calendarInviteData['request']['MANAGER_EMAIL_ADDRESS']);
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
                    
                    
                    die($request->getPost()->request_id);
//                    echo '<pre>employeeData';
//                    print_r($employeeData);
//                    echo '</pre>';
                    
                    echo '<pre>For';
                    print_r($requestObject['for']);
                    echo '</pre>';
                    
                    echo '<pre>datesRequested';
                    print_r($requestObject['datesRequested']);
                    echo '</pre>';
                    
                    
                    
                    die("#$#$#$");
//                    
//                    Created by Pablo Garcia/Phoenix/Swift on 11/17/2015 05:37:32 PM
//                    Sent for manager approval to ->John Berg/Phoenix/Swift
//                    $comment = 'Created by ' . \Login\Helper\UserSession::getUserSessionVariable('FIRST_NAME') . ' '  . \Login\Helper\UserSession::getUserSessionVariable('LAST_NAME');
//                    $comment = 'Created by ' . \Login\Helper\UserSession::getUserSessionVariable('FIRST_NAME') . ' '  . \Login\Helper\UserSession::getUserSessionVariable('LAST_NAME');
//                    $this->requestService->logEntry($requestReturnData['request_id'], $requesterEmployeeNumber, $comment);
                    
//                    $employeeData = $this->requestService->findTimeOffBalancesByEmployee($employeeNumber);
                    
                    echo '<pre>';
                    print_r($employeeData);
                    echo '</pre>';
                    die("#$#$#$");
                    
                    $comment2 = 'Sent for manager approval to ' . trim(ucwords(strtolower($employeeData['MANAGER_FIRST_NAME']))) . " " . trim(ucwords(strtolower($employeeData['MANAGER_LAST_NAME'])));
                    $this->requestService->logEntry($request->getPost()->request_id, $requesterEmployeeNumber, $comment2);
                    
                    echo $comment . '<br />';
                    echo $comment2 . '<br />';
                    die("@@");
//                    $session->offsetSet('EMPLOYEE_NUMBER', trim($result[0]->EMPLOYEE_NUMBER));
//                    $session->offsetSet('EMAIL_ADDRESS', strtolower(trim($result[0]->EMAIL_ADDRESS)));
//                    $session->offsetSet('COMMON_NAME', ucwords(strtolower(trim($result[0]->COMMON_NAME))));
//                    $session->offsetSet('FIRST_NAME', ucwords(strtolower(trim($result[0]->FIRST_NAME))));
//                    $session->offsetSet('LAST_NAME', ucwords(strtolower(trim($result[0]->LAST_NAME))));
//                    $session->offsetSet('USERNAME', strtolower(trim($result[0]->USERNAME)));
//                    $session->offsetSet('POSITION_TITLE', trim($result[0]->POSITION_TITLE));
//
//                    $session->offsetSet('MANAGER_EMPLOYEE_NUMBER', trim($result[0]->MANAGER_EMPLOYEE_NUMBER));
//                    $session->offsetSet('MANAGER_FIRST_NAME', ucwords(strtolower(trim($result[0]->MANAGER_FIRST_NAME))));
//                    $session->offsetSet('MANAGER_LAST_NAME', ucwords(strtolower(trim($result[0]->MANAGER_LAST_NAME))));
//                    $session->offsetSet('MANAGER_EMAIL_ADDRESS', strtolower(trim($result[0]->MANAGER_EMAIL_ADDRESS)));
                    
                    echo '<pre>';
                    print_r($request->getPost());
                    echo '</pre>';
                    
                    die("@@@");

//                    $outlookHelper = new \Request\Helper\OutlookHelper();
//                    $isSent = $outlookHelper->addToCalendar($requestObject);
                    $isSent = true;
                    //$comment = 'Time off request approved by ' . trim($calendarInviteData['for'][''])
                    
                    $this->requestService->logEntry($request->getPost()->request_id, trim($calendarInviteData['for']['MANAGER_EMPLOYEE_NUMBER']), 'Time off request approved by ' . trim($calendarInviteData['for']['MANAGER_EMPLOYEE_NUMBER']) . ' for ' . trim($calendarInviteData['for']['EMPLOYEE_NUMBER']));
                    
                    $requestReturnData = $this->requestService->submitApprovalResponse('A', $request->getPost()->request_id, $request->getPost()->review_request_reason);
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
                    $managerEmployees = $this->requestService->findManagerEmployees($this->employeeNumber, $request->getPost()->search, $request->getPost()->directReportFilter);
                    foreach($managerEmployees as $id => $data) {
//                         $nameFormatted =
//                                trim($data->EMPLOYEE_NAME) . " " . 
//                                " (" . trim($data->EMPLOYEE_NUMBER) . ")\r\n" .
//                                $data->POSITION_TITLE;
                        
                        $return[] = [ 
                                      'id' => $data->EMPLOYEE_NUMBER,
                                      'text' => $data->EMPLOYEE_NAME . ' (' . $data->EMPLOYEE_NUMBER . ') - ' . $data->POSITION_TITLE
                                    ];
                        
//                         $nameFormatted =
//                                trim($data->EMPLOYEE_NAME) . " " . 
//                                " (" . trim($data->EMPLOYEE_NUMBER) . ")\r\n" .
//                                $data->POSITION_TITLE;
                        
//                         $return[] = [ 'id' => trim($data->EMPLOYEE_NUMBER),
//                                  'text' => $nameFormatted
//                                ];
                    }
                    $result = new JsonModel($return);
                    break;
                    
                case 'submitTimeoffRequest':
//                    echo '<pre>';
//                    print_r($request->getPost());
//                    echo '</pre>';
//                    die("@@");
                    
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
                    
                    $requestReturnData = $this->requestService->submitRequestForApproval($employeeNumber, $requestData, $request->getPost()->requestReason, $requesterEmployeeNumber);
                    
                    $comment = 'Created by ' . \Login\Helper\UserSession::getUserSessionVariable('FIRST_NAME') . ' '  . \Login\Helper\UserSession::getUserSessionVariable('LAST_NAME');
                    $this->requestService->logEntry($requestReturnData['request_id'], $requesterEmployeeNumber, $comment);
                    
                    $employeeData = $this->requestService->findTimeOffBalancesByEmployee($employeeNumber);
                    $comment2 = 'Sent for manager approval to ' . trim(ucwords(strtolower($employeeData['MANAGER_FIRST_NAME']))) . " " . trim(ucwords(strtolower($employeeData['MANAGER_LAST_NAME'])));
                    $this->requestService->logEntry($requestReturnData['request_id'], $requesterEmployeeNumber, $comment2);
                    
                    //$employeeNumber
//                    $comment2 = 'Sent for manager approval to ' . trim(ucwords(strtolower($calendarInviteData['for']['MANAGER_FIRST_NAME']))) . " " . trim(ucwords(strtolower($calendarInviteData['for']['MANAGER_LAST_NAME'])));
                    
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
                    $employeeNumber = (is_null($request->getPost()->employeeNumber) ? trim($this->employeeNumber) : trim($request->getPost()->employeeNumber));
                    
                    $time = strtotime($request->getPost()->startYear . "-" . $request->getPost()->startMonth . "-01");
                    $fastPrev = date("Y-m-d", strtotime("-6 month", $time));
                    $prev = date("Y-m-d", strtotime("-3 month", $time));
                    $current = date("Y-m-d", strtotime("+0 month", $time));
                    $one = date("Y-m-d", strtotime("+1 month", $time));
                    $two = date("Y-m-d", strtotime("+2 month", $time));
                    $three = date("Y-m-d", strtotime("+3 month", $time));
                    $six = date("Y-m-d", strtotime("+6 month", $time));
                    $sixMonthsBack = new \DateTime($fastPrev);
                    $threeMonthsBack = new \DateTime($prev);
                    $currentMonth = new \DateTime($current);
                    $oneMonthOut = new \DateTime($one);
                    $twoMonthsOut = new \DateTime($two);
                    $threeMonthsOut = new \DateTime($three);
                    $sixMonthsOut = new \DateTime($six);
                    
                    \Request\Helper\Calendar::setCalendarHeadings(['S','M','T','W','T','F','S']);
                    \Request\Helper\Calendar::setBeginWeekOne('<tr class="calendar-row" style="height:40px;">');
                    \Request\Helper\Calendar::setBeginCalendarRow('<tr class="calendar-row" style="height:40px;">');
                    \Request\Helper\Calendar::setInvalidRequestDates($this->invalidRequestDates);
                    
                    $employeeData = $this->requestService->findTimeOffBalancesByEmployee($employeeNumber);
                    $employeeData['IS_LOGGED_IN_USER_MANAGER'] = \Login\Helper\UserSession::getUserSessionVariable('IS_MANAGER');
                    $employeeData['IS_LOGGED_IN_USER_PAYROLL'] = \Login\Helper\UserSession::getUserSessionVariable('IS_PAYROLL');
                    $approvedRequestData = $this->requestService->findTimeOffRequestsByEmployeeAndStatus($employeeNumber, "A");
                    $pendingRequestData = $this->requestService->findTimeOffRequestsByEmployeeAndStatus($employeeNumber, "P");
                    
                    $approvedRequestJson = [];
                    $pendingRequestJson = [];
                    
                    foreach($approvedRequestData as $key => $approvedRequest) {
                        if($approvedRequest['REQUEST_TYPE']==='Unexcused') {
                            $approvedRequest['REQUEST_TYPE'] = 'UnexcusedAbsence';
                        }
                        if($approvedRequest['REQUEST_TYPE']==='Time Off Without Pay') {
                            $approvedRequest['REQUEST_TYPE'] = 'ApprovedNoPay';
                        }
                        
                        $approvedRequestJson[] = [
                            'REQUEST_DATE' => date("m/d/Y", strtotime($approvedRequest['REQUEST_DATE'])),
                            'REQUESTED_HOURS' => $approvedRequest['REQUESTED_HOURS'],
                            'REQUEST_TYPE' => self::$categoryToClass[$approvedRequest['REQUEST_TYPE']]
                        ];
                    }
                    foreach($pendingRequestData as $key => $pendingRequest) {
                        if($pendingRequest['REQUEST_TYPE']==='Unexcused') {
                            $pendingRequest['REQUEST_TYPE'] = 'UnexcusedAbsence';
                        }
                        if($pendingRequest['REQUEST_TYPE']==='Time Off Without Pay') {
                            $pendingRequest['REQUEST_TYPE'] = 'ApprovedNoPay';
                        }
                        $pendingRequestJson[] = [
                            'REQUEST_DATE' => date("m/d/Y", strtotime($pendingRequest['REQUEST_DATE'])),
                            'REQUESTED_HOURS' => $pendingRequest['REQUESTED_HOURS'],
                            'REQUEST_TYPE' => self::$categoryToClass[$pendingRequest['REQUEST_TYPE']]
                        ];
                    }
                    
                    $result = new JsonModel([
                        'success' => true,
                        'calendars' => [
                            1 => [ 'header' => $currentMonth->format('M') . ' ' . $currentMonth->format('Y'),
                                'data' => \Request\Helper\Calendar::drawCalendar($request->getPost()->startMonth, $request->getPost()->startYear, [])
                            ],
                            2 => [ 'header' => $oneMonthOut->format('M') . ' ' . $oneMonthOut->format('Y'),
                                'data' => \Request\Helper\Calendar::drawCalendar($oneMonthOut->format('m'), $oneMonthOut->format('Y'), [])
                            ],
                            3 => [ 'header' => $twoMonthsOut->format('M') . ' ' . $twoMonthsOut->format('Y'),
                                'data' => \Request\Helper\Calendar::drawCalendar($twoMonthsOut->format('m'), $twoMonthsOut->format('Y'), [])
                            ]
                        ],
                        'fastRewindButton' => '<span title="Go back 6 months" class="glyphicon-class glyphicon glyphicon-fast-backward calendarNavigation" data-month="' . $sixMonthsBack->format('m') . '" data-year="' . $sixMonthsBack->format('Y') . '"> </span>',
                        'prevButton' => '&nbsp;&nbsp;&nbsp;&nbsp;<span title="Go back 3 months" class="glyphicon-class glyphicon glyphicon-step-backward calendarNavigation" data-month="' . $threeMonthsBack->format('m') . '" data-year="' . $threeMonthsBack->format('Y') . '"> </span>&nbsp;&nbsp;&nbsp;&nbsp;',
                        'nextButton' => '&nbsp;&nbsp;&nbsp;&nbsp;<span title="Go forward 3 months" class="glyphicon-class glyphicon glyphicon-step-forward calendarNavigation" data-month="' . $threeMonthsOut->format('m') . '" data-year="' . $threeMonthsOut->format('Y') . '"> </span>',
                        'fastForwardButton' => '&nbsp;&nbsp;&nbsp;&nbsp;<span title="Go forward 6 months" class="glyphicon-class glyphicon glyphicon-fast-forward calendarNavigation" data-month="' . $sixMonthsOut->format('m') . '" data-year="' . $sixMonthsOut->format('Y') . '"> </span>',
                        'employeeData' => $employeeData,
                        'employeeNumber' => $employeeNumber,
                        'approvedRequestData' => $approvedRequestData,
                        'approvedRequestJson' => $approvedRequestJson,
                        'pendingRequestData' => $pendingRequestData,
                        'pendingRequestJson' => $pendingRequestJson,
                        'openHeader' => '<strong>',
                        'closeHeader' => '</strong><br /><br />'
                    ]);
                    break;
            }
            
            return $result;
        }
    }

    public function viewEmployeeRequestsAction()
    {
        $isLoggedInUserManager = $this->requestService->isManager($this->employeeNumber);
        if($isLoggedInUserManager!=="Y") {
            $this->flashMessenger()->addWarningMessage('You are not authorized to view that page.');
            return $this->redirect()->toRoute('create');
        }
        return new ViewModel(array(
            'isLoggedInUserManager' => $isLoggedInUserManager,
            'managerReportsData' => $this->requestService->findQueuesByManager($this->employeeNumber),
            'flashMessages' => ['success' => $this->flashMessenger()->getCurrentSuccessMessages(),
                                'warning' => $this->flashMessenger()->getCurrentWarningMessages(),
                                'error' => $this->flashMessenger()->getCurrentErrorMessages(),
                                'info' => $this->flashMessenger()->getCurrentInfoMessages()
                               ]
        ));
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

}
