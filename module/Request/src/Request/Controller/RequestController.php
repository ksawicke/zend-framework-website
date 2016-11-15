<?php
namespace Request\Controller;

use Request\Service\RequestServiceInterface;
//use Zend\Form\FormInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;
use \Request\Model\Employee;
use \Request\Model\TimeOffRequests;
use \Request\Helper\ValidationHelper;
use \Login\Helper\UserSession;
use \Request\Model\RequestEntry;
use \Request\Model\Papaatmp;
use \Request\Helper\Calendar;
use PHPExcel;
use PHPExcel_Style_NumberFormat;
use PHPExcel_Style_Color;
use PHPExcel_IOFactory;

// use Request\Helper\PHPExcel\PHPExcel;
// use PHPExcel_Style_NumberFormat;
// use PHPExcel_IOFactory;

class RequestController extends AbstractActionController
{
   protected $requestService;
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
        'my-employee-requests' => 'My Employee Requests',
        'pending-manager-approval' => 'Pending Manager Approval Queue'
    ];

    public $payrollViewName = [
        'update-checks' => 'Update Checks Queue',
        'pending-payroll-approval' => 'Pending Payroll Approval Queue',
        'completed-pafs' => 'Completed PAFs Queue',
        'pending-as400-upload' => 'Pending AS400 Upload Queue',
        'denied' => 'Denied Queue',
        'by-status' => 'By Status',
        'manager-action' => 'Manager Action Needed'
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
        $this->employeeNumber = $_SESSION['Timeoff_'.ENVIRONMENT]['EMPLOYEE_NUMBER'];
        $this->managerEmployeeNumber = $_SESSION['Timeoff_'.ENVIRONMENT]['MANAGER_EMPLOYEE_NUMBER'];

        // Disable dates starting with the following date.
        $this->invalidRequestDates['before'] = $this->getEarliestRequestDate();

        // Disable dates starting with the following date.
        $this->invalidRequestDates['after'] = date("m/d/Y", strtotime("+1 year", strtotime(date("m/d/Y"))));

        // Disable any dates in this array
        $this->invalidRequestDates['individual'] = $this->getCompanyHolidays();

//        echo '<pre>';
//        print_r( $this->invalidRequestDates );
//        echo '</pre>';
//        exit();
    }

    /**
     * Gets a list of company holidays.
     *
     * @return date
     */
    public function getCompanyHolidays()
    {
        $TimeOffRequestSettings = new \Request\Model\TimeOffRequestSettings();
        $companyHolidays = $TimeOffRequestSettings->getCompanyHolidays();

        return $companyHolidays;
    }

    /**
     * Allow Payroll to put in a request up to 6 months ago from today's date.
     * All other roles can go back 1 month.
     *
     * @return date
     */
    public function getEarliestRequestDate()
    {
        $Employee = new \Request\Model\Employee();
        $isPayroll = $Employee->isPayroll( $this->employeeNumber );

        return ( $isPayroll=="Y" ? date("m/d/Y", strtotime("-6 months", strtotime(date("m/d/Y"))))
                                              : date("m/d/Y", strtotime("-1 month", strtotime(date("m/d/Y")))) );

    }

    /**
     * Allows an employee to edit their profile.
     *
     * @return ViewModel
     */
    public function editEmployeeProfileAction()
    {
        $Employee = new \Request\Model\Employee();

        return new ViewModel([
            'employeeData' => $Employee->findEmployeeTimeOffData($this->employeeNumber, "Y"),
            'employeeNumber' => $this->employeeNumber,
            'isManager' => \Login\Helper\UserSession::getUserSessionVariable('IS_MANAGER'),
            'flashMessages' => ['success' => $this->flashMessenger()->getCurrentSuccessMessages(),
                                'warning' => $this->flashMessenger()->getCurrentWarningMessages(),
                                'error' => $this->flashMessenger()->getCurrentErrorMessages(),
                                'info' => $this->flashMessenger()->getCurrentInfoMessages()
                               ]
        ]);
    }

    /**
     * Allows an employee to edit their profile.
     *
     * @return ViewModel
     */
    public function managePayrollAssistantsAction()
    {
        $Employee = new \Request\Model\Employee();

        return new ViewModel([
            'employeeData' => $Employee->findEmployeeTimeOffData($this->employeeNumber, "Y"),
            'isPayrollAdmin' => \Login\Helper\UserSession::getUserSessionVariable('IS_PAYROLL_ADMIN'),
            'flashMessages' => ['success' => $this->flashMessenger()->getCurrentSuccessMessages(),
                                'warning' => $this->flashMessenger()->getCurrentWarningMessages(),
                                'error' => $this->flashMessenger()->getCurrentErrorMessages(),
                                'info' => $this->flashMessenger()->getCurrentInfoMessages()
                               ]
        ]);
    }

    /**
     * Allows Payroll Admins to manage other Payroll Admins.
     *
     * @return ViewModel
     */
    public function managePayrollAdminsAction()
    {
        $Employee = new \Request\Model\Employee();

        return new ViewModel([
            'employeeData' => $Employee->findEmployeeTimeOffData($this->employeeNumber, "Y"),
            'isPayrollAdmin' => \Login\Helper\UserSession::getUserSessionVariable('IS_PAYROLL_ADMIN'),
            'flashMessages' => ['success' => $this->flashMessenger()->getCurrentSuccessMessages(),
                                'warning' => $this->flashMessenger()->getCurrentWarningMessages(),
                                'error' => $this->flashMessenger()->getCurrentErrorMessages(),
                                'info' => $this->flashMessenger()->getCurrentInfoMessages()
                               ]
        ]);
    }

    public function manageCompanyHolidaysAction()
    {
        $Employee = new \Request\Model\Employee();

        return new ViewModel([
            'isPayrollAdmin' => \Login\Helper\UserSession::getUserSessionVariable('IS_PAYROLL'),
            'flashMessages' => ['success' => $this->flashMessenger()->getCurrentSuccessMessages(),
                                'warning' => $this->flashMessenger()->getCurrentWarningMessages(),
                                'error' => $this->flashMessenger()->getCurrentErrorMessages(),
                                'info' => $this->flashMessenger()->getCurrentInfoMessages()
                               ]
        ]);
    }

    public function manageSupervisorProxiesAction()
    {
        $Employee = new \Request\Model\Employee();

        return new ViewModel([
            'isPayrollAdmin' => \Login\Helper\UserSession::getUserSessionVariable('IS_PAYROLL'),
            'flashMessages' => ['success' => $this->flashMessenger()->getCurrentSuccessMessages(),
                'warning' => $this->flashMessenger()->getCurrentWarningMessages(),
                'error' => $this->flashMessenger()->getCurrentErrorMessages(),
                'info' => $this->flashMessenger()->getCurrentInfoMessages()
            ]
        ]);
    }
    public function manageEmailOverridesAction()
    {
        $Employee = new \Request\Model\Employee();

        return new ViewModel([
            'isPayrollAdmin' => \Login\Helper\UserSession::getUserSessionVariable('IS_PAYROLL'),
            'flashMessages' => ['success' => $this->flashMessenger()->getCurrentSuccessMessages(),
                                'warning' => $this->flashMessenger()->getCurrentWarningMessages(),
                                'error' => $this->flashMessenger()->getCurrentErrorMessages(),
                                'info' => $this->flashMessenger()->getCurrentInfoMessages()
                               ]
        ]);
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
            'isSupervisor' => \Login\Helper\UserSession::getUserSessionVariable('IS_SUPERVISOR'),
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
        if( \Login\Helper\UserSession::getUserSessionVariable('IS_MANAGER')=="Y" ) {
            // route to Pending Manager Approval queue
            $this->redirect()->toRoute('viewManagerQueue', array(
                'controller' => 'request',
                'action' =>  'view-manager-queue',
                'manager-view' => 'pending-manager-approval'
            ));
        } else if( \Login\Helper\UserSession::getUserSessionVariable('IS_PAYROLL')=="Y" ) {
            // route to Pending Payroll Approval queue....
            $this->redirect()->toRoute('viewPayrollQueue', array(
                'controller' => 'request',
                'action' =>  'view-payroll-queue',
                'payroll-view' => 'pending-payroll-approval'
            ));
        } else {
            // route to view my requests....
            $this->redirect()->toRoute('viewMyRequests', array(
                'controller' => 'request',
                'action' =>  'view-my-requests'
            ));
        }
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
        if( !in_array( $managerView, ['pending-manager-approval','my-employee-requests'] ) ) {
            $this->flashMessenger()->addWarningMessage('Not a valid queue.');
            return $this->redirect()->toRoute('home');
            exit;
        }
        $Employee = new \Request\Model\Employee();
        $isLoggedInUserManager = $Employee->isManager($this->employeeNumber);
        $isLoggedInUserSupervisor = $Employee->isSupervisor($this->employeeNumber);
        $isPayroll = $Employee->isPayroll($this->employeeNumber);
        $isProxyForManager = $Employee->isProxyForManager($this->employeeNumber);
        $isProxyFor = $Employee->findProxiesByEmployeeNumber( $this->employeeNumber);
        if($isLoggedInUserManager!="Y" && $isLoggedInUserSupervisor!="Y" && $isPayroll!="Y" && $isProxyForManager!="Y") {
            $this->flashMessenger()->addWarningMessage('You are not authorized to view that page.');
            return $this->redirect()->toRoute('home');
        }

        $startDate = date( "Y-m-01" );
        $oneMonthOut = date( "Y-m-d", strtotime( "+1 month", strtotime( $startDate ) ) );
        $endDate = date( "Y-m-d", strtotime( "-1 day", strtotime( $oneMonthOut ) ) );

        $startMonth = date( "m" );
        $startYear = date( "Y" );

        $calendarDateTextData = $Employee->findTimeOffCalendarByManager( '002', $this->employeeNumber, 'D', $startDate, $endDate );
        \Request\Helper\Calendar::setCalendarDateTextToAppend( $calendarDateTextData );

        $this->layout()->setVariable( 'managerView', $managerView );

        $view = new ViewModel([
            'isLoggedInUserManager' => $isLoggedInUserManager,
            'isLoggedInUserSupervisor' => $isLoggedInUserSupervisor,
            'isProxyForManager' => $isProxyForManager,
            'isProxyFor' => $isProxyFor,
            'managerView' => $managerView,
            'managerViewName' => $this->getManagerViewName( $managerView ),
            'employeeNumber' => $this->employeeNumber,
            'flashMessages' => $this->getFlashMessages(),
            'calendarData' => $calendarDateTextData,
            'calendarHtml' => \Request\Helper\Calendar::drawCalendar( $startMonth, $startYear, [] )
        ]);
        $view->setTemplate( 'request/manager-queues/' . $managerView . '.phtml' );
        return $view;
    }

    /**
     * Returns a manager view/queue name.
     *
     * @param string $key
     */
    protected function getManagerViewName( $key = null ) {
        $managerViewName = 'View Requests';
        if( array_key_exists( $key, $this->managerViewName ) ) {
            $managerViewName =  $this->managerViewName[$key];
        }
        return $managerViewName;
    }

    /**
     * Returns a payroll view/queue name.
     *
     * @param string $key
     */
    protected function getPayrollViewName( $key = null ) {
        $payrollViewName = 'View Requests';
        if( array_key_exists( $key, $this->payrollViewName ) ) {
            $payrollViewName =  $this->payrollViewName[$key];
        }
        return $payrollViewName;
    }

    protected function handleNonPayrollRedirect()
    {
        $request = $this->getRequest();
        $request->getHeader('referer');
        $referredPage = $request->getQuery('q');

        die( $referredPage );
        if (trim($referredPage) != '') {
            die( getcwd() . $referredPage );
        }
        exit();
        if( !is_null( $referredPage ) ) {
            die( "redirect to " . $referredPage );
        } else {
            $this->flashMessenger()->addWarningMessage('You are not authorized to view that page.');
            return $this->redirect()->toRoute('home');
            exit();
        }
    }

    /**
     * Allow payroll to view requests in the Pending Checks status.
     *
     * @return ViewModel
     */
    public function viewPayrollQueueAction()
    {
        $payrollView = $this->params()->fromRoute('payroll-view');
        if( !in_array( $payrollView, ['by-status','completed-pafs','denied','manager-action','pending-as400-upload','pending-payroll-approval','update-checks'] ) ) {
            $this->flashMessenger()->addWarningMessage('Not a valid queue.');
            return $this->redirect()->toRoute('home');
        }
        $Employee = new \Request\Model\Employee();
        $isPayroll = $Employee->isPayroll( $this->employeeNumber );
        $isPayrollAssistant = $Employee->isPayrollAssistant( $this->employeeNumber );

        if($isPayroll!=="Y" && $isPayrollAssistant !== 'Y') {
            $this->handleNonPayrollRedirect();
//             $this->flashMessenger()->addWarningMessage('You are not authorized to view that page.');
//             return $this->redirect()->toRoute('home');
        }

        $this->layout()->setVariable( 'payrollView', $payrollView );

        $view = new ViewModel([
            'isPayroll' => $isPayroll,
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
        $redirect = \Login\Helper\UserSession::getUserSessionVariable('redirect');
        if ($redirect != false) {
            \Login\Helper\UserSession::setUserSessionVariable('redirect', false);
            return $this->redirect()->toUrl($redirect);
        }

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
        $request = $this->getRequest();

        $request->getHeader('referer');

        $referredPage = $request->getQuery('q');

        $fromQueue = $this->params()->fromRoute('fromQueue');

        $requestId = $this->params()->fromRoute('request_id');
        $Employee = new Employee();
        $TimeOffRequests = new TimeOffRequests();
        $ValidationHelper = new ValidationHelper();
        $employeeNumberAssociatedWithRequestData = $TimeOffRequests->findEmployeeNumberAssociatedWithRequest( $requestId, UserSession::getUserSessionVariable( 'IS_PAYROLL' ) );
        $employeeNumberAssociatedWithRequest = trim( $employeeNumberAssociatedWithRequestData->EMPLOYEE_NUMBER );
        $Employee->ensureEmployeeScheduleIsDefined( $employeeNumberAssociatedWithRequest );
        $timeOffRequestData = $TimeOffRequests->findRequest( $requestId, UserSession::getUserSessionVariable( 'IS_PAYROLL' ) );

        $isPayroll = \Login\Helper\UserSession::getUserSessionVariable( 'IS_PAYROLL' );
        $viewQueueLink = '/request/view-payroll-queue/by-status';

        switch( $timeOffRequestData['REQUEST_STATUS_DESCRIPTION'] ) {
            case "Pending Manager Approval":
                $viewQueueLink = '/request/view-manager-queue/pending-manager-approval';
                break;
            case "Completed PAFs":
                $viewQueueLink = '/request/view-payroll-queue/completed-pafs';
                break;
            case "Denied":
                $viewQueueLink = '/request/view-payroll-queue/denied';
                break;
            case "Pending AS400 Upload":
                $viewQueueLink = '/request/view-payroll-queue/pending-as400-upload';
                break;
            case "Pending Payroll Approval":
                $viewQueueLink = '/request/view-payroll-queue/pending-payroll-approval';
                break;
            case "Update Checks":
                $viewQueueLink = '/request/view-payroll-queue/update-checks';
                break;
        }

        if ($fromQueue !== null) {
            $viewQueueLink = '/request/view-payroll-queue/by-status';
        }

        return new ViewModel( [
            'isPayroll' => $isPayroll,
            'loggedInEmployeeNumber' => \Login\Helper\UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ),
            'timeoffRequestData' => $timeOffRequestData,
            'totalHoursRequested' => $TimeOffRequests->countTimeoffRequested( $requestId ),
            'hoursRequestedHtml' => $TimeOffRequests->drawHoursRequested( $timeOffRequestData['ENTRIES'] ),
            'isPayrollReviewRequired' => $ValidationHelper->isPayrollReviewRequired( $timeOffRequestData['REQUEST_ID'], $timeOffRequestData['EMPLOYEE_NUMBER'] ),
            'viewQueueLink' => ( !empty( $referredPage ) ? $referredPage : $viewQueueLink ),
            'nonPayrollReadOnlyStatuses' => [ "Pending Payroll Approval", "Pending AS400 Upload", "Completed PAFs", "Denied", "Update Checks" ]
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

    public function downloadMyEmployeeRequestsAction()
    {
        $data = [ 'employeeNumber' => \Login\Helper\UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ) ];
        $data['columns'][0]['search']['value'] = ( !empty( $this->getRequest()->getPost('reportFilter') ) ? $this->getRequest()->getPost('reportFilter') : 'D' );
        $data['columns'][2]['search']['value'] = ( !empty( $this->getRequest()->getPost('statusFilter') ) ? $this->getRequest()->getPost('statusFilter') : 'All' );
        $queue = $this->params()->fromRoute('queue');
        $ManagerQueues = new \Request\Model\ManagerQueues();
        $Employee = new Employee();
        $proxyForEntries = $Employee->findProxiesByEmployeeNumber( $data['employeeNumber'] );
        $proxyFor = [];
        foreach ( $proxyForEntries as $proxy) {
            $proxyFor[] = $proxy['EMPLOYEE_NUMBER'];
        }
//         $queueData = $ManagerQueues->getManagerEmployeeRequests( $data, $proxyFor,  [] );
        $queueData = $ManagerQueues->getProxyEmployeeRequests( $data, $proxyFor,  [] );

        $this->outputReportMyEmployeeRequests( $queueData );

        exit;
    }

    public function downloadReportManagerActionNeededAction()
    {
        $data = [ 'employeeNumber' => \Login\Helper\UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ) ];
        $data['columns'][2]['search']['value'] = ( !empty( $this->getRequest()->getPost('statusFilter') ) ? $this->getRequest()->getPost('statusFilter') : 'All' );
        $queue = $this->params()->fromRoute('queue');
        $PayrollQueues = new \Request\Model\PayrollQueues();
        $queueData = $PayrollQueues->getManagerActionEmailQueue( $data, [ 'WARN_TYPE' => 'OLD_REQUESTS' ]);

        $this->outputReportManagerActionNeeded( $queueData );

        exit;
    }

    public function downloadUpdateChecksAction()
    {
        $data = [ 'employeeNumber' => \Login\Helper\UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ) ];
        $data['columns'][2]['search']['value'] = ( !empty( $this->getRequest()->getPost('statusFilter') ) ? $this->getRequest()->getPost('statusFilter') : 'All' );
        $queue = $this->params()->fromRoute('queue');
        $PayrollQueues = new \Request\Model\PayrollQueues();
//         echo "<pre>";
        $queueData = $PayrollQueues->getUpdateChecksQueue( $data );
//         var_dump($queueData);
        $this->outputUpdatesCheckQueue( $queueData );

        exit;
    }

    private function outputReportMyEmployeeRequests( $spreadsheetRows = [] )
    {
        $objPHPExcel = new PHPExcel();
        $phpColor = new PHPExcel_Style_Color();
        $phpColor->setRGB('000000');

        // Initialize spreadsheet
        $objPHPExcel->setActiveSheetIndex(0);
        $worksheet = $objPHPExcel->getActiveSheet();
        $worksheet->setTitle('test worksheet');
        $worksheet->setCellValue('A1', 'Employee');
        $worksheet->setCellValue('B1', 'Approver Queue');
        $worksheet->setCellValue('C1', 'Request Status');
        $worksheet->setCellValue('D1', 'Hours Requested');
        $worksheet->setCellValue('E1', 'Request Reason');
        $worksheet->setCellValue('F1', 'First Day Requested');
        $worksheet->getStyle('A1')->getFont()->setBold(true);
        $worksheet->getStyle('B1')->getFont()->setBold(true);
        $worksheet->getStyle('C1')->getFont()->setBold(true);
        $worksheet->getStyle('D1')->getFont()->setBold(true);
        $worksheet->getStyle('E1')->getFont()->setBold(true);
        $worksheet->getStyle('F1')->getFont()->setBold(true);
        $worksheet->getColumnDimension('A')->setWidth(16.00);
        $worksheet->getColumnDimension('B')->setWidth(26.00);
        $worksheet->getColumnDimension('C')->setWidth(26.00);
        $worksheet->getColumnDimension('D')->setWidth(26.00);
        $worksheet->getColumnDimension('E')->setWidth(16.00);
        $worksheet->getColumnDimension('F')->setWidth(16.00);

        foreach($spreadsheetRows as $key => $spreadsheetRow)
        {
            $minDateRequested = date( "Y-m-d", strtotime( $spreadsheetRow['MIN_DATE_REQUESTED'] ) );
            $dateToCompare = date("Y-m-d", strtotime("-3 days", strtotime(date("m/d/Y"))));

            $worksheet->setCellValue('A'.($key+2), ( array_key_exists( 'EMPLOYEE_DESCRIPTION', $spreadsheetRow ) ? $spreadsheetRow['EMPLOYEE_DESCRIPTION'] : '' ) );
            $worksheet->setCellValue('B'.($key+2), $spreadsheetRow['APPROVER_QUEUE']);
            $worksheet->setCellValue('C'.($key+2), $spreadsheetRow['REQUEST_STATUS_DESCRIPTION']);
            $worksheet->setCellValue('D'.($key+2), $spreadsheetRow['REQUESTED_HOURS']);
            $worksheet->getStyle('D'.($key+2))->getNumberFormat()->setFormatCode(
                \PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00 );
            $worksheet->setCellValue('E'.($key+2), $spreadsheetRow['REQUEST_REASON']);
            if( $minDateRequested <= $dateToCompare && $spreadsheetRow['REQUEST_STATUS_DESCRIPTION'] == 'Pending Manager Approval') {
                $phpColor->setRGB('ff0000');
                $worksheet->getStyle('F'.($key+2))->getFont()->setColor( $phpColor );
                $worksheet->getStyle('F'.($key+2))->getFont()->setBold(true);
            }
            $worksheet->setCellValue('F'.($key+2), date( "m/d/Y", strtotime( $minDateRequested ) ) );
        }

        // Redirect output to a client's web browser (Excel2007)
//         header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//         header('Content-Disposition: attachment;filename="MyEmployeesRequests_' . date('Ymd-his') . '.xlsx"');
//         header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        ob_start();
        $objWriter->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();

//         $response = [ 'op' => 'ok', 'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,".base64_encode($xlsData) ];
        $response = [ 'op' => 'ok',
                      'fileContents' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,".base64_encode($xlsData),
                      'fileName' => 'MyEmployeesRequests_' . date('Ymd-his') . '.xlsx'
        ];

        die( json_encode( $response ) );
    }

    private function outputReportManagerActionNeeded( $spreadsheetRows = [] )
    {
        $objPHPExcel = new PHPExcel();
        $phpColor = new PHPExcel_Style_Color();
        $phpColor->setRGB('000000');

        // Initialize spreadsheet
        $objPHPExcel->setActiveSheetIndex(0);
        $worksheet = $objPHPExcel->getActiveSheet();
        $worksheet->setTitle('test worksheet');
        $worksheet->setCellValue('A1', 'Employee');
        $worksheet->setCellValue('B1', 'Approver Queue');
        $worksheet->setCellValue('C1', 'Request Status');
        $worksheet->setCellValue('D1', 'Hours Requested');
        $worksheet->setCellValue('E1', 'Request Reason');
        $worksheet->setCellValue('F1', 'First Day Requested');
        $worksheet->getStyle('A1')->getFont()->setBold(true);
        $worksheet->getStyle('B1')->getFont()->setBold(true);
        $worksheet->getStyle('C1')->getFont()->setBold(true);
        $worksheet->getStyle('D1')->getFont()->setBold(true);
        $worksheet->getStyle('E1')->getFont()->setBold(true);
        $worksheet->getStyle('F1')->getFont()->setBold(true);
        $worksheet->getColumnDimension('A')->setWidth(16.00);
        $worksheet->getColumnDimension('B')->setWidth(26.00);
        $worksheet->getColumnDimension('C')->setWidth(26.00);
        $worksheet->getColumnDimension('D')->setWidth(26.00);
        $worksheet->getColumnDimension('E')->setWidth(16.00);
        $worksheet->getColumnDimension('F')->setWidth(16.00);

        foreach($spreadsheetRows as $key => $spreadsheetRow)
        {
            $minDateRequested = date( "Y-m-d", strtotime( $spreadsheetRow['MIN_DATE_REQUESTED'] ) );
            $dateToCompare = date("Y-m-d", strtotime("-3 days", strtotime(date("m/d/Y"))));

            $worksheet->setCellValue('A'.($key+2), ( array_key_exists( 'EMPLOYEE_DESCRIPTION_ALT', $spreadsheetRow ) ? $spreadsheetRow['EMPLOYEE_DESCRIPTION_ALT'] : '' ) );
            $worksheet->setCellValue('B'.($key+2), $spreadsheetRow['APPROVER_QUEUE']);
            $worksheet->setCellValue('C'.($key+2), $spreadsheetRow['REQUEST_STATUS_DESCRIPTION']);
            $worksheet->setCellValue('D'.($key+2), $spreadsheetRow['REQUESTED_HOURS']);
            $worksheet->getStyle('D'.($key+2))->getNumberFormat()->setFormatCode(
                \PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00 );
            $worksheet->setCellValue('E'.($key+2), $spreadsheetRow['REQUEST_REASON']);
            if( $minDateRequested <= $dateToCompare ) {
                $phpColor->setRGB('ff0000');
                $worksheet->getStyle('F'.($key+2))->getFont()->setColor( $phpColor );
                $worksheet->getStyle('F'.($key+2))->getFont()->setBold(true);
            }
            $worksheet->setCellValue('F'.($key+2), date( "m/d/Y", strtotime( $minDateRequested ) ) );
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        ob_start();
        $objWriter->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();

        //         $response = [ 'op' => 'ok', 'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,".base64_encode($xlsData) ];
        $response = [ 'op' => 'ok',
            'fileContents' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,".base64_encode($xlsData),
            'fileName' => 'ManagerActionNeeded_' . date('Ymd-his') . '.xlsx'
        ];

        die( json_encode( $response ) );
    }

    private function outputUpdatesCheckQueue( $spreadsheetRows = [] )
    {
        /** Include PHPExcel */
//         $path = CURRENT_PATH . '/module/Request/src/Request/Helper/PHPExcel/PHPExcel.php';
//         require_once( $path );

        $objPHPExcel = new PHPExcel();
//         var_dump($spreadsheetRows); die();

        // Initialize spreadsheet
        $objPHPExcel->setActiveSheetIndex(0);
        $worksheet = $objPHPExcel->getActiveSheet();
        $worksheet->setTitle('test worksheet');
        $worksheet->setCellValue('A1', 'Cycle Code');
        $worksheet->setCellValue('B1', 'Employee');
        $worksheet->setCellValue('C1', 'Approver Queue');
        $worksheet->setCellValue('D1', 'Request Status');
        $worksheet->setCellValue('E1', 'Hours Requested');
        $worksheet->setCellValue('F1', 'Last Payroll Comment');
        $worksheet->setCellValue('G1', 'First Day Requested');
        $worksheet->getStyle('A1')->getFont()->setBold(true);
        $worksheet->getStyle('B1')->getFont()->setBold(true);
        $worksheet->getStyle('C1')->getFont()->setBold(true);
        $worksheet->getStyle('D1')->getFont()->setBold(true);
        $worksheet->getStyle('E1')->getFont()->setBold(true);
        $worksheet->getStyle('F1')->getFont()->setBold(true);
        $worksheet->getStyle('G1')->getFont()->setBold(true);
        $worksheet->getColumnDimension('A')->setWidth(16.00);
        $worksheet->getColumnDimension('B')->setWidth(26.00);
        $worksheet->getColumnDimension('C')->setWidth(26.00);
        $worksheet->getColumnDimension('D')->setWidth(26.00);
        $worksheet->getColumnDimension('E')->setWidth(16.00);
        $worksheet->getColumnDimension('F')->setWidth(16.00);
        $worksheet->getColumnDimension('G')->setWidth(16.00);

        foreach($spreadsheetRows as $key => $spreadsheetRow)
        {
            $minDateRequested = date( "m/d/Y", strtotime( $spreadsheetRow['MIN_DATE_REQUESTED'] ) );

            $worksheet->setCellValue('A'.($key+2), $spreadsheetRow['CYCLE_CODE']);
            $worksheet->setCellValue('B'.($key+2), $spreadsheetRow['EMPLOYEE_DESCRIPTION']);
            $worksheet->setCellValue('C'.($key+2), $spreadsheetRow['APPROVER_QUEUE']);
            $worksheet->setCellValue('D'.($key+2), $spreadsheetRow['REQUEST_STATUS_DESCRIPTION']);
            $worksheet->setCellValue('E'.($key+2), $spreadsheetRow['REQUESTED_HOURS']);
            $worksheet->getStyle('E'.($key+2))->getNumberFormat()->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00 );
            $worksheet->setCellValue('F'.($key+2), $spreadsheetRow['LAST_PAYROLL_COMMENT']);
            $worksheet->setCellValue('G'.($key+2), $minDateRequested);
        }

        // Redirect output to a client's web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="UpdatesCheckQueue_' . date('Ymd-his') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }

}
