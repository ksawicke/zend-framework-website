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

    public function createAction()
    {
        // One method to grab a service....
        // Not using this but leaving as a reference for maybe later.
        //         $service = $this->getServiceLocator()->get('Request\Service\RequestServiceInterface');
        //         var_dump($service->findTimeOffBalances($this->employeeId));
        //         exit();

//         \Request\Helper\Calendar::setCalendarHeadings(['S','M','T','W','T','F','S']);
//         \Request\Helper\Calendar::setBeginWeekOne('<tr class="calendar-row" style="height:40px;">');
//         \Request\Helper\Calendar::setBeginCalendarRow('<tr class="calendar-row" style="height:40px;">');
        
        return new ViewModel(array(
            'employeeData' => $this->requestService->findTimeOffBalancesByEmployee($this->employeeNumber),
            'managerEmployees' => $this->requestService->findManagerEmployees($this->employeeNumber, 'sena'),
            'isSupervisor' => $this->requestService->isManager($this->employeeNumber)
//             'approvedRequestData' => $this->requestService->findTimeOffApprovedRequestsByEmployee($this->employeeNumber, 'datesOnly'),
//             'calendar1Html' => \Request\Helper\Calendar::drawCalendar('12', '2015', []),
//             'calendar2Html' => \Request\Helper\Calendar::drawCalendar('1', '2016', []),
//             'calendar3Html' => \Request\Helper\Calendar::drawCalendar('2', '2016', [])
        ));
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
                case 'getEmployeeList':
                    $return = [];
                    $managerEmployees = $this->requestService->findManagerEmployees($this->employeeNumber, $request->getPost()->search);
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
                    $employeeNumber = (is_null($request->getPost()->employeeNumber) ? trim($this->employeeNumber) : trim($request->getPost()->employeeNumber));
                    
                    $requestData = [];
                    
                    foreach($request->getPost()->selectedDatesNew as $key => $data) {
                        $date = \DateTime::createFromFormat('m/d/Y', $request->getPost()->selectedDatesNew[$key]['date']);
                        $requestData[] = [
                            'date' => $date->format('Y-m-d'),
                            'type' => self::$typesToCodes[$request->getPost()->selectedDatesNew[$key]['category']],
                            'hours' => (int) $request->getPost()->selectedDatesNew[$key]['hours']
                        ];
                    }
                    
                    $requestReturnData = $this->requestService->submitRequestForApproval($employeeNumber, $requestData, $request->getPost()->requestReason);
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
                    $calendarData = $this->requestService->findTimeOffCalendarByManager($this->managerEmployeeNumber, $startDate, $endDate);
                    
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
                    $employeeData['IS_LOGGED_IN_USER_MANAGER'] = $this->requestService->isManager($this->employeeNumber);
                    $approvedRequestData = $this->requestService->findTimeOffRequestsByEmployeeAndStatus($employeeNumber, "A");
                    $pendingRequestData = $this->requestService->findTimeOffRequestsByEmployeeAndStatus($employeeNumber, "P");
                    
                    $approvedRequestJson = [];
                    $pendingRequestJson = [];
                    
                    foreach($approvedRequestData as $key => $approvedRequest) {
                        $approvedRequestJson[] = [
                            'REQUEST_DATE' => date("m/d/Y", strtotime($approvedRequest['REQUEST_DATE'])),
                            'REQUESTED_HOURS' => $approvedRequest['REQUESTED_HOURS'],
                            'REQUEST_TYPE' => self::$categoryToClass[$approvedRequest['REQUEST_TYPE']]
                        ];
                    }
                    foreach($pendingRequestData as $key => $pendingRequest) {
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
        return new ViewModel(array(
            'managerDirectReportsData' => $this->requestService->findQueuesByManager($this->managerEmployeeNumber)
        ));
    }
    
    public function viewMyTeamCalendarAction()
    {
//         $calendarData = $this->requestService->findTimeOffCalendarByManager($this->managerEmployeeNumber, '2015-12-01', '2015-12-31');
        
        return new ViewModel(array(
//             'calendarData' => $calendarData,
//             'calendarHtml' => \Request\Helper\Calendar::drawCalendar('12', '2015', $calendarData)
        ));
    }
    
    public function submittedForApprovalAction()
    {
        $this->flashMessenger()->addSuccessMessage('Saved!');
        
        return new ViewModel([
            'flashMessages' => ['success' => $this->flashMessenger()->getCurrentSuccessMessages(),
                                'warning' => $this->flashMessenger()->getCurrentWarningMessages(),
                                'error' => $this->flashMessenger()->getCurrentErrorMessages(),
                                'info' => $this->flashMessenger()->getCurrentInfoMessages()
                               ]
                             ]);
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
        
        $pendingRequestData[$requestId]['TEAM_CALENDAR'] = $this->requestService->findTimeOffCalendarByManager($this->managerEmployeeNumber, $pendingRequestData[$requestId]['CALENDAR_FIRST_DATE'], $pendingRequestData[$requestId]['CALENDAR_LAST_DATE']);
        return new ViewModel(array(
            'requestId' => $requestId,
            'pendingRequestData' => $pendingRequestData[$requestId]
        ));
    }
    
    protected function setEmployeeNumber($employeeNumber)
    {
        $this->employeeNumber = $employeeNumber;
    }
}
