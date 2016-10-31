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
 * Handles sending calendar invitations using ICS format.
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

    public function __construct( TimeOffRequestSettings $timeOffRequestSettings, RequestEntry $requestEntry, TimeOffRequests $timeOffRequests, Employee $employee, EmployeeSchedules $employeeSchedules, EmailService $emailService )
    {
        $this->timeOffRequestSettings = $timeOffRequestSettings;
        $this->requestEntry = $requestEntry;
        $this->timeOffRequests = $timeOffRequests;
        $this->employee = $employee;
        $this->employeeSchedules = $employeeSchedules;
        $this->emailService = $emailService;
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
        $this->emailSubject = $emailSubject;
        if ( ENVIRONMENT=='testing' || ENVIRONMENT=='development' ) {
            $this->emailSubject = '[ ' . strtoupper( ENVIRONMENT ) . ' - Time Off Requests ] - ' . $this->emailSubject;
        }
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
    private function formatName( $string, $style = "normal" )
    {
        return ( $style=="normal" ? ucwords( strtolower( trim( $string ) ) ) : strtoupper( trim( $string ) ) );
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

    private function formatSubject( $name, $style = "normal" )
    {
        return $this->formatName( $name, $style ) . ' - APPROVED TIME OFF';
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

        return [ 'from' => [ 'name' => 'Time Off Requests', 'email' => 'timeoffrequests-donotreply@swifttrans.com' ],
                 'employee' => [ 'name' =>  $this->formatName( $employeeData['EMPLOYEE_NAME'] ), 'email' => $this->formatEmail( $employeeData['EMAIL_ADDRESS'] ) ],
                 'manager' => [ 'name' => $this->formatName( $employeeData['MANAGER_NAME'] ), 'email' => $this->formatEmail( $employeeData['MANAGER_EMAIL_ADDRESS'] ) ],
                 'sendInvitationsTo' => [ 'employee' => $employeeProfile['SEND_CALENDAR_INVITATIONS_TO_EMPLOYEE'], 'manager' => $employeeProfile['SEND_CALENDAR_INVITATIONS_TO_MANAGER'] ],
                 'datesRequested' => $calendarInviteData['datesRequested']
        ];
    }

    protected function renderEmail()
    {
        $view = new PhpRenderer();
        $resolver = new TemplateMapResolver();
        $viewModel = new ViewModel();
        $viewLayout = new ViewModel();
        
        $resolver->setMap( [
            'layoutBeginVCalendar' => __DIR__ . '/../../../view/calendarInvite/beginVCalendar.phtml',
            'layoutTimeZoneAmericaPhoenix' => __DIR__ . '/../../../view/calendarInvite/timeZoneAmericaPhoenix.phtml',
            'layoutEndVCalendar' => __DIR__ . '/../../../view/calendarInvite/endVCalendar.phtml',
            'layoutCalendarInvite' => __DIR__ . '/../../../view/calendarInvite/calendarInviteLayout.phtml',
        ] );
        $view->setResolver( $resolver );
                
        $viewModel->setTemplate( 'layoutBeginVCalendar' );
        $contentBeginVCalendar = $view->render( $viewModel );
        
        $viewModel->setTemplate( 'layoutTimeZoneAmericaPhoenix' );
        $contentTimeZone = $view->render( $viewModel );
        
        $viewModel->setTemplate( 'layoutEndVCalendar' )
            ->setVariables( [ 'attendeeName' => 'Kevin Sawicke',
                'attendeeEmail' => 'kevin_sawicke@swifttrans.com',
                'UID' => $this->formatUID(),
                'dtStamp' => '20161031',
                'timeZone' => 'America/Phoenix',
                'dtStart' => '20161114',
                'timeStart' => '000000',
                'dtEnd' => '20161116',
                'timeEnd' => '235959',
                'summary' => 'TIME OFF REQUEST'
            ] );
        $contentEndVCalendar = $view->render($viewModel);
        
        $viewLayout->setTemplate( 'layoutCalendarInvite' )
                   ->setVariables( [ 'content' => $contentBeginVCalendar . $contentTimeZone . $contentEndVCalendar
            ] );
        
        return $view->render( $viewLayout );
    }

    /**
     * Sends the calendar invitation as an appointment.
     * Does not block off as busy.
     */
    public function send()
    {
        $calendarRequestObject = $this->getCalendarRequestObject();
        
        echo '<pre>';
        var_dump( $calendarRequestObject );
        echo '</pre>';
        
        foreach ( $calendarRequestObject['datesRequested'] as $key => $request ) {
            
            echo '<pre>';
            var_dump( $request );
            echo '</pre>';
            
//             $message = $this->renderEmail();
//             $this->emailService->setTo( 'kevin_sawicke@swifttrans.com' )
//                 ->setFrom( $calendarRequestObject['from']['name'] . ' <' . $calendarRequestObject['from']['email'] . '>' )
//                 ->setSubject( $this->emailSubject )
//                 ->setBody( $message )
//                 ->setHeaders( [ 'MIME-version' => '1.0',
//                                 'Content-Type' => 'text/calendar; method=REQUEST; charset="iso-8859-1"',
//                                 'Content-Transfer-Encoding' => '7bit' ] )
//                 ->send();
        }
        
//         $message = $this->renderEmail();
//         $this->emailService->setTo( 'kevin_sawicke@swifttrans.com' )
//             ->setFrom( 'Time Off Requests Administrator <ASWIFT_SYSTEM@SWIFTTRANS.COM>' )
//             ->setSubject( 'APPOINTMENT TEST[10]' )
//             ->setBody( $message )
//             ->setHeaders( [ 'MIME-version' => '1.0',
//                             'Content-Type' => 'text/calendar; method=REQUEST; charset="iso-8859-1"',
//                             'Content-Transfer-Encoding' => '7bit' ] )
//             ->send();

        die();
    }

}