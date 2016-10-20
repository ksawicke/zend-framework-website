<?php
namespace Request\Service;

use Zend\Mvc\Controller\AbstractActionController;
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

    public function __construct( TimeOffRequestSettings $timeOffRequestSettings, RequestEntry $requestEntry, TimeOffRequests $timeOffRequests, Employee $employee, EmployeeSchedules $employeeSchedules )
    {
        $this->timeOffRequestSettings = $timeOffRequestSettings;
        $this->requestEntry = $requestEntry;
        $this->timeOffRequests = $timeOffRequests;
        $this->employee = $employee;
        $this->employeeSchedules = $employeeSchedules;
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

    private function formatSubject( $requestObject, $style = "normal" )
    {
        return $this->formatName( $requestObject['employee']['name'], $style ) . ' - APPROVED TIME OFF';
    }

    /**
     * Returns the description string for the calendar invite.
     *
     * @param string $data
     */
    private function formatDescriptionString( $data ) {
        $descriptionString = ( $data['start'] === $data['end'] ) ?
           'Time off on ' . date( "m/d/Y", strtotime( $data['start'] ) ) :
           'Time off from ' . date( "m/d/Y", strtotime( $data['start'] ) ) . ' - ' . date( "m/d/Y", strtotime( $data['end'] ) );
        return $descriptionString;
    }

    /**
     * Returns the participants text for the calendar invite.
     *
     * @param array $requestObject
     */
    private function formatParticipantsText( $requestObject ) {
        $participantsText = '';
        $participants = [];
        if( $requestObject['sendInvitationsTo']['employee'] ) {
            $participants[] = [ 'name' => $requestObject['employee']['name'], 'email' => $requestObject['employee']['email'] ];
        }
        if( $requestObject['sendInvitationsTo']['manager'] ) {
            $participants[] = [ 'name' => $requestObject['manager']['name'], 'email' => $requestObject['manager']['name'] ];
        }

        foreach ( $participants as $pctr => $participant ) {
            $participantsText = "ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CN" .
                $participants[$pctr]['name'] . ";X-NUM-GUESTS=0:MAILTO:" . $participants[$pctr]['email'];
        }

        return $participantsText;
    }

    /**
     * Formats the beginning VCALENDAR portion of the calendar invite.
     *
     * @return string
     */
    private function formatBeginVCalendar() {
        return "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//SwiftTransportation//TimeoffRequests/NONSGML v1.0//EN
METHOD:REQUEST\r\n";
    }

    /**
     * Formats the ending VCALENDAR portion of the calendar invite.
     *
     * @return string
     */
    private function formatEndVCalendar() {
        return "END:VCALENDAR";
    }

    /**
     * Formats the VTIMEZONE portion of the calendar invite.
     *
     * @return string
     */
    private function formatVTimezone() {
        return "BEGIN:VTIMEZONE
TZID:America/Phoenix
X-LIC-LOCATION:America/Phoenix
BEGIN:DAYLIGHT
TZOFFSETFROM:-0700
TZOFFSETTO:-0700
TZNAME:PDT
DTSTART:19700308T020000
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:-0700
TZOFFSETTO:-0700
TZNAME:PST
DTSTART:19701101T020000
RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU
END:STANDARD
END:VTIMEZONE\r\n";
    }

    /**
     * Formats a unique ID for the calendar invite.
     *
     * @return string
     */
    private function formatUID() {
        return md5( uniqid( mt_rand(), true ) ) . "swifttrans.com";
    }

    private function formatVEvents( $request, $subject, $descriptionString, $fromName, $fromEmail, $participantsText ) {
        $dtStamp = date( 'Ymd' );
        $vEvents = '';
        $startDate = date( "Ymd", strtotime( $request['start'] ) );
        $endDate = date( "Ymd", strtotime( $request['end'] ) );
        if( ENVIRONMENT==='development' || ENVIRONMENT==='testing' ) {
            $subject = '[ ' . strtoupper( ENVIRONMENT ) . ' - Time Off Requests ] - ' . $subject;
        }

        $vEvents .= "BEGIN:VEVENT
UID:" . $this->formatUID() . "
DTSTART;TZID=America/Phoenix:" . $startDate . "T" . $this->startTime . "00
DTEND;TZID=America/Phoenix:" . $endDate . "T" . $this->endTime . "00
DTSTAMP:" . $dtStamp . "
SUMMARY:" . $subject . "
LOCATION:
DESCRIPTION:" . $descriptionString . "
STATUS:CONFIRMED
X-MICROSOFT-CDO-BUSYSTATUS:FREE
X-MICROSOFT-CDO-INSTTYPE:0
X-MICROSOFT-CDO-INTENDEDSTATUS:FREE
X-MICROSOFT-CDO-ALLDAYEVENT:TRUE
FBTYPE:FREE
ORGANIZER;CN=" . $fromName . ":mailto:" . $fromEmail . "\r\n" .
    $participantsText .
    "\r\nEND:VEVENT\r\n";

        return $vEvents;
    }

    /**
     * Gets a calendar request object based on Request ID that we use to send the invite.
     *
     * @return array
     */
    private function getCalendarRequestObject()
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

    public function send()
    {
        $calendarRequestObject = $this->getCalendarRequestObject();

//         echo '<pre>';
//         var_dump( $calendarRequestObject );
//         echo '<pre>';
//         die( "...." );

        foreach ( $calendarRequestObject['datesRequested'] as $key => $request ) {
            $descriptionString = $this->formatDescriptionString( $request );
            $participantsText = $this->formatParticipantsText( $calendarRequestObject );
            $headers = 'Content-Type:text/calendar; Content-Disposition: inline; charset=utf-8;\r\n';
            $headers .= "Content-Type: text/plain;charset=\"utf-8\"\r\n";
            $message = $this->formatBeginVCalendar() .
                $this->formatVTimezone() .
                $this->formatVEvents( $request, $this->formatSubject( $calendarRequestObject, "upper" ), $descriptionString,
                    $calendarRequestObject['from']['name'], $calendarRequestObject['from']['email'], $participantsText ) .
                    $this->formatEndVCalendar();
            $headers .= $message;

            echo '<pre>HEADERS:';
            var_dump( $headers );
            echo '</pre>';

            echo '<pre>MESSAGE:';
            var_dump( $message );
            echo '</pre>';

//             $mailsent = mail( $calendarRequestObject['to'], $calendarRequestObject['subject'], $message, $headers );
        }

        die( "Test complete." );

//         return ($mailsent) ? (true) : (false);
    }

}