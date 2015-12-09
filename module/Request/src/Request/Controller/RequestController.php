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
    
    public function apiAction()
    {
        \Request\Helper\Calendar::setCalendarHeadings(['S','M','T','W','T','F','S']);
        \Request\Helper\Calendar::setBeginWeekOne('<tr class="calendar-row" style="height:40px;">');
        \Request\Helper\Calendar::setBeginCalendarRow('<tr class="calendar-row" style="height:40px;">');
        
        $result = new JsonModel([
            'success' => true,
            'calendars' => [
                1 => [ 'header' => 'December 2015',
                       'data' => \Request\Helper\Calendar::drawCalendar('12', '2015', [])
                     ],
                2 => [ 'header' => 'January 2016',
                       'data' => \Request\Helper\Calendar::drawCalendar('1', '2016', [])
                     ],
                3 => [ 'header' => 'February 2016',
                       'data' => \Request\Helper\Calendar::drawCalendar('2', '2016', [])
                     ]
            ],
            'openHeader' => '<strong>',
            'closeHeader' => '</strong><br /><br />'
        ]);
        
        return $result;
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
            'calendarHtml' => \Request\Helper\Calendar::drawCalendar('12', '2015', $calendarData)
        ));
    }
}
