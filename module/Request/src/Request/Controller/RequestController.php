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

    public $employeeNumber;

    public $managerNumber;
    
    protected static $typesToCodes = [
        'timeOffPTO' => 'P',
        'timeOffFloat' => 'K',
        'timeOffSick' => 'S'
    ];

    public function __construct(RequestServiceInterface $requestService, FormInterface $requestForm)
    {
        $this->requestService = $requestService;
        $this->requestForm = $requestForm;

        $this->employeeNumber = '49499';
        $this->managerNumber = '229589';
    }

    public function createAction()
    {
        // One method to grab a service....
        // Not using this but leaving as a reference for maybe later.
        //         $service = $this->getServiceLocator()->get('Request\Service\RequestServiceInterface');
        //         var_dump($service->findTimeOffBalances($this->employeeId));
        //         exit();

        \Request\Helper\Calendar::setCalendarHeadings(['S','M','T','W','T','F','S']);
        \Request\Helper\Calendar::setBeginWeekOne('<tr class="calendar-row" style="height:40px;">');
        \Request\Helper\Calendar::setBeginCalendarRow('<tr class="calendar-row" style="height:40px;">');
        
        return new ViewModel(array(
            'employeeData' => $this->requestService->findTimeOffBalancesByEmployee($this->employeeNumber),
            'approvedRequestData' => $this->requestService->findTimeOffApprovedRequestsByEmployee($this->employeeNumber),
            'calendar1Html' => \Request\Helper\Calendar::drawCalendar('12', '2015', []),
            'calendar2Html' => \Request\Helper\Calendar::drawCalendar('1', '2016', []),
            'calendar3Html' => \Request\Helper\Calendar::drawCalendar('2', '2016', [])
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
                case 'submitTimeoffRequest':
                    $requestData = [];
                    foreach($request->getPost()->selectedDates as $key => $date) {
                        $date = \DateTime::createFromFormat('m/d/Y', $date);
                        $requestData[] = [
                            'date' => $date->format('Y-m-d'),
                            'type' => self::$typesToCodes[$request->getPost()->selectedDateCategories[$key]],
                            'hours' => (int) $request->getPost()->selectedDateHours[$key]
                        ];
                    }
                    
                    $this->requestService->submitRequestForApproval($this->employeeNumber, $requestData);
                    
                    $result = new JsonModel([
                        'success' => true,
                        'selectedDates' => $request->getPost()->selectedDates,
                        'selectedDateCategories' => $request->getPost()->selectedDateCategories,
                        'selectedDateHours' => $request->getPost()->selectedDateHours,
                        'records' => $records
                    ]);
                    break;
                    
                case 'loadCalendar':
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
                    
                    $employeeData = $this->requestService->findTimeOffBalancesByEmployee($this->employeeNumber);
                    //$employeeData['FLOAT_REMAINING'] = "71.33";
                    
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
                        'approvedRequestData' => $this->requestService->findTimeOffApprovedRequestsByEmployee($this->employeeNumber),
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
            'managerDirectReportsData' => $this->requestService->findTimeOffBalancesByManager($this->managerNumber)
        ));
    }
    
    public function viewMyTeamCalendarAction()
    {
        $calendarData = $this->requestService->findTimeOffCalendarByManager($this->managerNumber, '2015-12-01', '2015-12-31');
        
        return new ViewModel(array(
            'calendarData' => $calendarData,
            'calendarHtml' => \Request\Helper\Calendar::drawCalendar('4', '2015', $calendarData)
        ));
    }
}
