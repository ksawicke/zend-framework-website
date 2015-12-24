<?php
namespace Request\Controller;

use Request\Service\RequestServiceInterface;
use Zend\Form\FormInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class RequestController extends AbstractActionController
{

    protected $requestService;

    protected $requestForm;

    protected $employeeNumber;

    protected $managerNumber;
    
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

        $this->employeeNumber = '229589';
        $this->managerNumber = '49602';
    }

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
//                     $employeeData = $this->requestService->findManagerEmployees($this->employeeNumber);
//                     $result = new JsonModel([$employeeData]);
//                     echo '<pre>';
//                     print_r($employeeData);
//                     echo '</pre>';
//                     exit();
                    //$request->getPost()->search
                    
                    /**
                     * 366099  GUIDO FAECKE
                       49499  JAMES GASIOR
                       229589  MARY JACKSON
                       366124  NEDRA MUNOZ
                       229702  HEIDI CLARK
                       348370  DENNIS WEGLARZ
                       296261  RANDY SENA
                     **/
                    
                    $r = [];
                    $x = $this->requestService->findManagerEmployees($this->employeeNumber, $request->getPost()->search);
//                     echo '<pre>';
//                     print_r($x);
//                     echo '</pre>';
//                     die("@@@");
                    foreach($x as $id => $data) {
                        $r[] = [ 'id' => trim($data->EMPLOYEENUMBER),
                                 'text' => trim($data->EMPLOYEELASTNAME) . ", " .
                                           trim($data->EMPLOYEEFIRSTNAME)
                                 ];
                    }
                    $result = new JsonModel($r);
                    
//                     $result = new JsonModel([
//                         [ 'employeeNumber' => '366099', 'employeeName' => 'Faecke, Guido' ],
//                         [ 'employeeNumber' => '49499', 'employeeName' => 'Gasior, James' ],
//                         [ 'employeeNumber' => '366124', 'employeeName' => 'Munroz, Nedra' ],
//                         [ 'employeeNumber' => '229702', 'employeeName' => 'Clark, Heidi' ],
//                         [ 'employeeNumber' => '348370', 'employeeName' => 'Weglarz, Dennis' ],
//                         [ 'employeeNumber' => '296261', 'employeeName' => 'Sena, Randy' ]
//                     ]);
                    break;
                    
                case 'submitTimeoffRequest':
                    $employeeNumber = (is_null($request->getPost()->employeeNumber) ? trim($this->employeeNumber) : trim($request->getPost()->employeeNumber));
                    
                    $requestData = [];
                    
//                     echo '<pre>';
//                     print_r($request->getPost()->selectedDatesNew);
//                     echo '</pre>';
//                     exit();
                    
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
                    $calendarData = $this->requestService->findTimeOffCalendarByManager($this->managerNumber, $startDate, $endDate);
                    
                    $result = new JsonModel([
                        'success' => true,
                        'calendars' => [
                            1 => [ 'header' => '<span class="teamCalendarHeader">' . $currentMonth->format('M') . ' ' . $currentMonth->format('Y') . '</span>',
                                'data' => \Request\Helper\Calendar::drawCalendar($request->getPost()->startMonth, $request->getPost()->startYear, $calendarData)
                            ]
                        ],
                        'prevButton' => '<span class="glyphicon-class glyphicon glyphicon-chevron-left calendarNavigation" data-month="' . $oneMonthBack->format('m') . '" data-year="' . $oneMonthBack->format('Y') . '"> </span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                        'nextButton' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="glyphicon-class glyphicon glyphicon-chevron-right calendarNavigation" data-month="' . $oneMonthOut->format('m') . '" data-year="' . $oneMonthOut->format('Y') . '"> </span>',
                        //'employeeData' => $employeeData,
//                         'approvedRequestData' => $approvedRequestData,
//                         'approvedRequestJson' => $approvedRequestJson,
//                         'pendingRequestData' => $pendingRequestData,
//                         'pendingRequestJson' => $pendingRequestJson,
                        'openHeader' => '<strong>',
                        'closeHeader' => '</strong><br /><br />'
                    ]);
                    break;
                    
                case 'loadCalendar':
                    $employeeNumber = (is_null($request->getPost()->employeeNumber) ? trim($this->employeeNumber) : trim($request->getPost()->employeeNumber));
                    
                    //submitTimeoffRequest
                    $time = strtotime($request->getPost()->startYear . "-" . $request->getPost()->startMonth . "-01");
                    $prev = date("Y-m-d", strtotime("-3 month", $time));
                    $current = date("Y-m-d", strtotime("+0 month", $time));
                    $one = date("Y-m-d", strtotime("+1 month", $time));
                    $two = date("Y-m-d", strtotime("+2 month", $time));
                    $three = date("Y-m-d", strtotime("+3 month", $time));
                    $threeMonthsBack = new \DateTime($prev);
                    $currentMonth = new \DateTime($current);
                    $oneMonthOut = new \DateTime($one);
                    $twoMonthsOut = new \DateTime($two);
                    $threeMonthsOut = new \DateTime($three);
                    
                    \Request\Helper\Calendar::setCalendarHeadings(['S','M','T','W','T','F','S']);
                    \Request\Helper\Calendar::setBeginWeekOne('<tr class="calendar-row" style="height:40px;">');
                    \Request\Helper\Calendar::setBeginCalendarRow('<tr class="calendar-row" style="height:40px;">');
                    
                    $employeeData = $this->requestService->findTimeOffBalancesByEmployee($employeeNumber);
                    //$employeeData['FLOAT_REMAINING'] = "71.33";
                    $approvedRequestData = $this->requestService->findTimeOffRequestsByEmployeeAndStatus($employeeNumber, "A");
                    $pendingRequestData = $this->requestService->findTimeOffRequestsByEmployeeAndStatus($employeeNumber, "P");
//                     $approvedRequestData = $this->requestService->findTimeOffApprovedRequestsByEmployee($this->employeeNumber, 'datesOnly');
//                     $pendingRequestData = $this->requestService->findTimeOffPendingRequestsByEmployee($this->employeeNumber, 'datesOnly', null);
                    
                    $approvedRequestJson = [];
                    $pendingRequestJson = [];
                    
//                     echo '<pre>approvedRequestData';
//                     print_r($approvedRequestData);
//                     echo '</pre>';
                    
//                     echo '<pre>pendingRequestData';
//                     print_r($pendingRequestData);
//                     echo '</pre>';
                    
//                     exit();
                    
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
                        'prevButton' => '<span class="glyphicon-class glyphicon glyphicon-chevron-left calendarNavigation" data-month="' . $threeMonthsBack->format('m') . '" data-year="' . $threeMonthsBack->format('Y') . '"> </span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                        'nextButton' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="glyphicon-class glyphicon glyphicon-chevron-right calendarNavigation" data-month="' . $threeMonthsOut->format('m') . '" data-year="' . $threeMonthsOut->format('Y') . '"> </span>',
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
            'managerDirectReportsData' => $this->requestService->findQueuesByManager($this->managerNumber)
        ));
    }
    
    public function viewMyTeamCalendarAction()
    {
//         $calendarData = $this->requestService->findTimeOffCalendarByManager($this->managerNumber, '2015-12-01', '2015-12-31');
        
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
        $calendarFirstDate = \DateTime::createFromFormat('Y-m-d', trim($pendingRequestData[$requestId]['FIRST_DATE_REQUESTED']));
        $calendarLastDate = \DateTime::createFromFormat('Y-m-d', $pendingRequestData[$requestId]['LAST_DATE_REQUESTED']);
        
        $pendingRequestData[$requestId]['CALENDAR_FIRST_DATE'] = $calendarFirstDate->format('Y-m-01');
        $pendingRequestData[$requestId]['CALENDAR_LAST_DATE'] = $calendarLastDate->format('Y-m-t');
        
        $pendingRequestData[$requestId]['TEAM_CALENDAR'] = $this->requestService->findTimeOffCalendarByManager($this->managerNumber, $pendingRequestData[$requestId]['CALENDAR_FIRST_DATE'], $pendingRequestData[$requestId]['CALENDAR_LAST_DATE']);
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
