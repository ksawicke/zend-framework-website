<?php
namespace Request\Service;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;
use Request\Model\TimeOffRequestSettings;
use Request\Model\RequestEntry;
use Request\Model\TimeOffRequests;
use Request\Model\Employee;
use Request\Model\EmployeeSchedules;

/**
 * CalendarInviteService.php
 *
 * Request API
 *
 * Handles sending calendar invitations using VCAL format.
 *
 * PHP version 5
 *
 * @package    Request\Service\CalendarInviteService
 * @author     Kevin Sawicke <kevin_sawicke@swifttrans.com>
 * @copyright  2016 Swift Transportation
 * @version    GIT: $Id$ In development
 */
class CalendarInviteService extends AbstractActionController
{

    protected $serviceLocator;

    protected $timeOffRequestSettings;

    protected $overrideEmails;

    protected $emailOverrideList;

    protected $requestEntry;

    protected $timeOffRequests;

    protected $employee;

    protected $employeeSchedules;

    protected $requestId;

    protected $toEmail;

    protected $organizerName;

    protected $organizerEmail;

    protected $participantName1;

    protected $participantEmail1;

    protected $participantName2;

    protected $participantEmail2;

    protected $emailSubject;

    protected $location;

    protected $description;

    protected $startDate;

    protected $endDate;

    protected $startTime;

    protected $endTime;

    public function __construct( TimeOffRequestSettings $timeOffRequestSettings, RequestEntry $requestEntry,
        TimeOffRequests $timeOffRequests, Employee $employee, EmployeeSchedules $employeeSchedules,
        EmailService $emailService )
    {
        $this->timeOffRequestSettings = $timeOffRequestSettings;
        $this->requestEntry = $requestEntry;
        $this->timeOffRequests = $timeOffRequests;
        $this->employee = $employee;
        $this->employeeSchedules = $employeeSchedules;
        $this->emailService = $emailService;
        
        /** Check for Email Override settings. **/
        $this->checkEmailOverride();
    }

    /**
     * Sets the Request ID.
     *
     * @param string $requestId
     * @return \Request\Service\CalendarInviteService
     */
    public function setRequestId( $requestId )
    {
        $this->requestId = $requestId;
        return $this;
    }

    /**
     * Sets the subject line.
     *
     * @param string $emailSubject
     */
    public function setSubject( $emailSubject )
    {
        $this->emailSubject = ( ( ENVIRONMENT=='testing' || ENVIRONMENT=='development' ) ?
            '[ ' . strtoupper( ENVIRONMENT ) . ' - Time Off Requests ] - ' . $emailSubject : $emailSubject );
        return $this;
    }

    /**
     * Checks if we need to override emails.
     */
    private function checkEmailOverride()
    {
        $emailOverrideList = $this->timeOffRequestSettings->getEmailOverrideList();
        $this->overrideEmails = $this->timeOffRequestSettings->getOverrideEmailsSetting();
        $this->emailOverrideList = ( ( ENVIRONMENT=='testing' || ENVIRONMENT=='development' ) ?
            $emailOverrideList : '' );
    }

    /**
     * Formats a name as uppercase and trimmed.
     *
     * @param string $string
     */
    private function formatName( $string )
    {
        return strtoupper( trim( $string ) );
    }

    /**
     * Formats an email address.
     *
     * @param string $string
     */
    private function formatEmail( $string )
    {
        return strtolower( trim( $string ) );
    }

    /**
     * Formats the subject line.
     * 
     * @param string $name
     * @param string $style
     */
    private function formatSubject( $name )
    {
        return $this->formatName( $name ) . ' - APPROVED TIME OFF';
    }

    /**
     * Returns the description string for the calendar invite.
     *
     * @param string $data
     */
    private function formatDescriptionString( $data ) {
        $descriptionString = ( $data['start'] == $data['end'] ) ?
           'Time off on ' . date( "m/d/Y", strtotime( $data['start'] ) ) :
           'Time off from ' . date( "m/d/Y", strtotime( $data['start'] ) ) . ' - ' . date( "m/d/Y", strtotime( $data['end'] ) );
        return $descriptionString;
    }

    /**
     * Formats a unique ID for the calendar invite.
     *
     * @return string
     */
    private function formatUID() {
        return md5( uniqid( mt_rand(), true ) );
    }

    /**
     * Formats an individual Calendar Request Object element.
     * Note: We need to add 1 day to the range so it shows up correctly on the calendar.
     * 
     * @param string $name
     * @param string $emailAddress
     * @param string $timezone
     * @param string $dtStart
     * @param string $dtEnd
     */
    protected function formatCalendarRequestObjectElement( $name = '', $emailAddress = '', $dtStart = null, $dtEnd = null )
    {
        return [ 'attendeeName' => $name,
                 'attendeeEmail' => $emailAddress,
                 'UID' => $this->formatUID(),
                 'dtStamp' => date( 'Ymd' ),
                 'dtStart' => date( "Ymd", strtotime( $dtStart ) ),
                 'dtEnd' => date( "Ymd", strtotime( $dtEnd . ' +1 day' ) ), 
                 'summary' => 'TIME OFF REQUEST'
               ];
    }
    
    /**
     * Gets a calendar request object based on Request ID that we use to send the invite.
     *
     * @return array
     */
    public function getCalendarRequestObject()
    {
        $calendarInviteData = $this->timeOffRequests->findRequestCalendarInviteData( $this->requestId );
        $dateRequestBlocks = $this->requestEntry->getRequestObject( $this->requestId );
        $employeeData = $this->employee->findEmployeeTimeOffData( $dateRequestBlocks['for']['employee_number'], "Y", "EMPLOYER_NUMBER, EMPLOYEE_NUMBER, LEVEL_1, LEVEL_2, LEVEL_3, LEVEL_4, SALARY_TYPE" );
        $employeeProfile = $this->employeeSchedules->getEmployeeProfile( $dateRequestBlocks['for']['employee_number'] );
        $calendarRequestObject = [];
        
        // Format the request object to contain data for each email that needs to be sent.
        foreach ( $calendarInviteData['datesRequested'] as $key => $request ) {
            if( $this->overrideEmails==1 && !empty( $this->emailOverrideList ) ) {
                foreach( $this->emailOverrideList as $emailCounter => $emailAddress ) {
                    // Note: We're using the actual employee name, but the override email
                    $calendarRequestObject[] = $this->formatCalendarRequestObjectElement(
                        $this->formatName( $employeeData['EMPLOYEE_NAME'] ), $this->formatEmail( $emailAddress ),
                        $request['start'], $request['end'] );
                }
            }
            if( $this->overrideEmails==0 && $employeeProfile['SEND_CALENDAR_INVITATIONS_TO_EMPLOYEE']==1 ) {
                $calendarRequestObject[] = $this->formatCalendarRequestObjectElement(
                    $this->formatName( $employeeData['EMPLOYEE_NAME'] ), $this->formatEmail( $employeeData['EMAIL_ADDRESS'] ),
                    $request['start'], $request['end'] );
            }
        
            if( $this->overrideEmails==0 && $employeeProfile['SEND_CALENDAR_INVITATIONS_TO_MANAGER']==1 ) {
                $calendarRequestObject[] = $this->formatCalendarRequestObjectElement(
                    $this->formatName( $employeeData['MANAGER_NAME'] ), $this->formatEmail( $employeeData['MANAGER_EMAIL_ADDRESS'] ),
                    $request['start'], $request['end'] );
            }
        }
        
        return $calendarRequestObject;
    }

    /**
     * Generates the body of the email.
     * 
     * @param array $calendarRequestData
     * @return string
     * @see http://stackoverflow.com/questions/1716237/single-day-all-day-appointments-in-ics-files
     * @see http://jon.netdork.net/2008/10/11/wrapping-my-head-around-ical
     */
    protected function renderEmailBody( $calendarRequestData = [] )
    {
        $view = new PhpRenderer();
        $resolver = new TemplateMapResolver();
        $viewModel = new ViewModel();
        $viewLayout = new ViewModel();
        
        $resolver->setMap( [
            'layoutBeginVCalendar' => __DIR__ . '/../../../view/calendarInvite/beginVCalendar.phtml',
            'layoutEndVCalendar' => __DIR__ . '/../../../view/calendarInvite/endVCalendar.phtml',
            'layoutCalendarInvite' => __DIR__ . '/../../../view/calendarInvite/calendarInviteLayout.phtml',
        ] );
        $view->setResolver( $resolver );
                
        $viewModel->setTemplate( 'layoutBeginVCalendar' );
        $contentBeginVCalendar = $view->render( $viewModel );
        
        $vCalendarVariables = [ 'attendeeName' => $calendarRequestData['attendeeName'],
            'attendeeEmail' => $calendarRequestData['attendeeEmail'],
            'UID' => $this->formatUID(),
            'dtStamp' => $calendarRequestData['dtStamp'],
            'dtStart' => $calendarRequestData['dtStart'],
            'dtEnd' => $calendarRequestData['dtEnd'],
            'summary' => 'TIME OFF REQUEST'
        ];
        
        $viewModel->setTemplate( 'layoutEndVCalendar' )
            ->setVariables( $vCalendarVariables );
        $contentEndVCalendar = $view->render( $viewModel );
        
        $viewLayout->setTemplate( 'layoutCalendarInvite' )
            ->setVariables( [ 'content' => $contentBeginVCalendar . $contentEndVCalendar ] );
        
        return $view->render( $viewLayout );
    }

    /**
     * Sends the calendar invitations for a request as an appointment.
     * Does not block off as busy.
     */
    public function send()
    {
        $calendarRequestObject = $this->getCalendarRequestObject();
        
        $headers = [ 'MIME-version' => '1.0',
                     'Content-Type' => 'text/calendar; method=REQUEST; charset="iso-8859-1"',
                     'Content-Transfer-Encoding' => '7bit' ];

        foreach ( $calendarRequestObject as $calendarRequestCounter => $calendarRequestData ) {            
            $message = $this->renderEmailBody( $calendarRequestData );
            
            $this->emailService->setTo( $calendarRequestData['attendeeEmail'] )
                ->setFrom( 'Time Off Requests <timeoffrequests-donotreply@swifttrans.com>' )
                ->setSubject( $calendarRequestData['attendeeName'] . ' - APPROVED TIME OFF' )
                ->setBody( $message )
                ->setHeaders( $headers )
                ->send();
        }
    }

}