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

use \Request\Model\RequestEntry;
use \Request\Model\Papaatmp;

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
        'denied' => 'Denied',
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
        
//        echo '<pre>';
//        print_r( $this->invalidRequestDates );
//        echo '</pre>';
//        exit();
    }
    
    public function testpapaaAction() {
        $request_id = '100918';
        
        $Employee = new Employee();
        $TimeOffRequests = new TimeOffRequests();
//        $TimeOffRequestLog = new TimeOffRequestLog();
        $RequestEntry = new RequestEntry();
        $requestData = $TimeOffRequests->findRequest( $request_id );
        $dateRequestBlocks = $RequestEntry->getRequestObject( $request_id );
        $employeeData = $Employee->findEmployeeTimeOffData( $dateRequestBlocks['for']['employee_number'], "Y", "EMPLOYER_NUMBER, EMPLOYEE_NUMBER, LEVEL_1, LEVEL_2, LEVEL_3, LEVEL_4, SALARY_TYPE" );

//        echo '<pre>';
//        var_dump( $employeeData );
//        echo '</pre>';
//        
//        echo '<pre>';
//        var_dump( $dateRequestBlocks );
//        echo '</pre>';
//        
//        die( $request_id );
        
        /** Write record(s) to HPAPAATMP or PAPAATMP **/
        $Papaa = new Papaatmp();
        $Papaa->prepareToWritePapaatmpRecords( $employeeData, $dateRequestBlocks, $request_id );
        die( "COMPLETE" );
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
        $isPayroll = $Employee->isPayroll( $this->employeeNumber );
        if($isPayroll!=="Y") {
            $this->flashMessenger()->addWarningMessage('You are not authorized to view that page.');
            return $this->redirect()->toRoute('create');
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
            'loggedInEmployeeNumber' => \Login\Helper\UserSession::getUserSessionVariable( 'EMPLOYEE_NUMBER' ),
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
    
    public function downloadReportManagerActionNeededAction()
    {
        $PayrollQueues = new \Request\Model\PayrollQueues();
        $queueData = $PayrollQueues->getManagerActionEmailQueue( [], [ 'WARN_TYPE' => 'OLD_REQUESTS' ]);
        $this->outputReportManagerActionNeeded( $queueData );
        exit;
    }
    
    private function outputReportManagerActionNeeded( $spreadsheetRows = [] )
    {
        /** Include PHPExcel */
        $path = CURRENT_PATH . '/module/Request/src/Request/Helper/PHPExcel/PHPExcel.php';        
        require_once( $path );
        $objPHPExcel = new \PHPExcel();
        
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
        $worksheet->getColumnDimension('A')->setWidth(26.00);
        $worksheet->getColumnDimension('B')->setWidth(26.00);
        $worksheet->getColumnDimension('C')->setWidth(26.00);
        $worksheet->getColumnDimension('D')->setWidth(16.00);
        $worksheet->getColumnDimension('E')->setWidth(16.00);
        $worksheet->getColumnDimension('F')->setWidth(16.00);
        
        foreach($spreadsheetRows as $key => $spreadsheetRow)
        {
            $minDateRequested = date( "m/d/Y", strtotime( $spreadsheetRow['MIN_DATE_REQUESTED'] ) );
            
            $worksheet->setCellValue('A'.($key+2), $spreadsheetRow['EMPLOYEE_DESCRIPTION_ALT']);
            $worksheet->setCellValue('B'.($key+2), $spreadsheetRow['APPROVER_QUEUE']);
            $worksheet->setCellValue('C'.($key+2), $spreadsheetRow['REQUEST_STATUS_DESCRIPTION']);
            $worksheet->setCellValue('D'.($key+2), $spreadsheetRow['REQUESTED_HOURS']);
            $worksheet->getStyle('D'.($key+2))->getNumberFormat()->setFormatCode(
                \PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00 );
            $worksheet->setCellValue('E'.($key+2), $spreadsheetRow['REQUEST_REASON']);
            $worksheet->setCellValue('F'.($key+2), $minDateRequested);
        }
        
        // Redirect output to a client's web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="ManagerActionNeeded_' . date('Ymd-his') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }
}
