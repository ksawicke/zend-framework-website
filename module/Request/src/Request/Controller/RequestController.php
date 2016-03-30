<?php
namespace Request\Controller;

//use Request\Service\RequestServiceInterface;
//use Zend\Form\FormInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;
use \Request\Model\Employee;
use \Request\Model\TimeOffRequests;
use \Request\Helper\ValidationHelper;

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
        'pending-as400-upload' => 'Pending AS400 Upload',
        'denied' => 'Denied'
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
            'employeeData' => $Employee->findEmployeeTimeOffData($this->employeeNumber, "Y"),
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
        $employeeData = $Employee->findEmployeeTimeOffData($employeeNumber, "Y");
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
        $Employee = new Employee();
        $TimeOffRequests = new TimeOffRequests();
        $ValidationHelper = new ValidationHelper();
        $timeOffRequestData = $TimeOffRequests->findRequest( $requestId );
        
        return new ViewModel( [
            'timeoffRequestData' => $timeOffRequestData,
            'totalHoursRequested' => $TimeOffRequests->countTimeoffRequested( $requestId ),
            'hoursRequestedHtml' => $TimeOffRequests->drawHoursRequested( $timeOffRequestData['ENTRIES'] ),
            'isPayrollReviewRequired' => $ValidationHelper->isPayrollReviewRequired( $timeOffRequestData['REQUEST_ID'], $timeOffRequestData['EMPLOYEE_NUMBER'] )
        ] );
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
