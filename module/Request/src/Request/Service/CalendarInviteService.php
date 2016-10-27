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
            $participantsText .= "ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CN" .
                $participants[$pctr]['name'] . ";X-NUM-GUESTS=0:MAILTO:" . $participants[$pctr]['email'] . "\r\n";
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
PRODID:-//SwiftTransportation//TimeOffRequests/NONSGML v1.0//EN
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
        $resolver->setMap( [
            'mailLayout' => __DIR__ . '/../../../view/email/TimeOffCalendarInviteLayout.phtml',
            'mailTemplate' => __DIR__ . '/../../../view/email/TimeOffCalendarInviteHeaderTemplate.phtml',
            'mailTemplateVEvents' => __DIR__ . '/../../../view/email/TimeOffCalendarInviteHeaderVEventsTemplate.phtml',
        ] );
        $view->setResolver( $resolver );

        $viewModel = new ViewModel();
        $viewModel->setTemplate( 'mailTemplateVEvents' )
            ->setVariables( [ 'formatUID' => '9e7e62a18de5b6e8c5dcb300376fe189',
                'timeZone' => 'America/Phoenix',
                'startDate' => '20161027',
                'startTime' => '0000',
                'endDate' => '20161028',
                'endTime' => '2359',
                'dtStamp' => '20161026',
                'subject' => '[ DEVELOPMENT - Time Off Requests ] - KEVIN SAWICKE - APPROVED TIME OFF',
                'descriptionString' => 'Time off from 10/27/2016 - 10/28/2016',
                'fromName' => 'Time Off Requests Administrator',
                'fromEmail' => 'ASWIFT_SYSTEM@SWIFTTRANS.COM',
                'participantsText' => 'ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CNKevin Sawicke;X-NUM-GUESTS=0:kevin_sawicke@swifttrans.com
ATTENDEE;'
            ] ); // ;CNMary Jackson;X-NUM-GUESTS=0:MAILTO:Mary Jackson
        $content = $view->render($viewModel);

        $viewModel = new ViewModel();
        $viewModel->setTemplate( 'mailTemplate' )
                  ->setVariables( [ 'content' => $content ] );
        $content = $view->render($viewModel);

        $viewLayout = new ViewModel();
        $viewLayout->setTemplate('mailLayout')
                   ->setVariables( [ 'content' => $content ] );

        return $view->render($viewLayout);
    }

    public function send()
    {
        $emailBody = $this->renderEmail();
        //$emailHeaders = 'Content-Type:text/calendar; Content-Disposition: inline; charset=utf-8;\r\nContent-Type: text/plain;charset=\"utf-8\"\r\n";' . $emailBody;
        
        /* prepare and send email */
        $this->emailService->setTo( 'kevin_sawicke@swifttrans.com' )
             ->setFrom( 'Time Off Requests Administrator <ASWIFT_SYSTEM@SWIFTTRANS.COM>' )
             ->setSubject( 'KEVIN SAWICKE - APPROVED TIME OFF' )
             ->setBody( $emailBody )
//              ->setHeaders( [ 'Content-Type' => 'text/calendar', 'Content-Type' => 'text/html; charset=UTF-8' ] )
//              ->sendAsCalendarInvite();
             ->send();
        
        echo '<pre>EMAIL BODY:<br />';
        var_dump( $emailBody );
        echo '</pre>';
        
//         echo '<pre>EMAIL HEADERS:<br />';
//         var_dump( $emailHeaders );
//         echo '</pre>';
        
        die( "<br /><br /><br />message sent.");

        /**
         * <pre>HEADERS:string(1422) "Content-Type:text/calendar; Content-Disposition: inline; charset=utf-8;\r\nContent-Type: text/plain;charset="utf-8"
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//SwiftTransportation//TimeOffRequests/NONSGML v1.0//EN
METHOD:REQUEST
BEGIN:VTIMEZONE
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
END:VTIMEZONE
BEGIN:VEVENT
UID:9e7e62a18de5b6e8c5dcb300376fe189swifttrans.com
DTSTART;TZID=America/Phoenix:20161017T00
DTEND;TZID=America/Phoenix:20161018T00
DTSTAMP:20161021
SUMMARY:[ DEVELOPMENT - Time Off Requests ] - STEVEN S MCKIM - APPROVED TIME OFF
LOCATION:
DESCRIPTION:Time off from 10/17/2016 - 10/18/2016
STATUS:CONFIRMED
X-MICROSOFT-CDO-BUSYSTATUS:FREE
X-MICROSOFT-CDO-INSTTYPE:0
X-MICROSOFT-CDO-INTENDEDSTATUS:FREE
X-MICROSOFT-CDO-ALLDAYEVENT:TRUE
FBTYPE:FREE
ORGANIZER;CN=Time Off Requests:mailto:timeoffrequests-donotreply@swifttrans.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CNSteven S Mckim;X-NUM-GUESTS=0:MAILTO:steve_mckim@swifttrans.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CNMary Jackson;X-NUM-GUESTS=0:MAILTO:Mary Jackson

END:VEVENT
END:VCALENDAR"
</pre><pre>MESSAGE:string(1305) "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//SwiftTransportation//TimeOffRequests/NONSGML v1.0//EN
METHOD:REQUEST
BEGIN:VTIMEZONE
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
END:VTIMEZONE
BEGIN:VEVENT
UID:9e7e62a18de5b6e8c5dcb300376fe189swifttrans.com
DTSTART;TZID=America/Phoenix:20161017T00
DTEND;TZID=America/Phoenix:20161018T00
DTSTAMP:20161021
SUMMARY:[ DEVELOPMENT - Time Off Requests ] - STEVEN S MCKIM - APPROVED TIME OFF
LOCATION:
DESCRIPTION:Time off from 10/17/2016 - 10/18/2016
STATUS:CONFIRMED
X-MICROSOFT-CDO-BUSYSTATUS:FREE
X-MICROSOFT-CDO-INSTTYPE:0
X-MICROSOFT-CDO-INTENDEDSTATUS:FREE
X-MICROSOFT-CDO-ALLDAYEVENT:TRUE
FBTYPE:FREE
ORGANIZER;CN=Time Off Requests:mailto:timeoffrequests-donotreply@swifttrans.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CNSteven S Mckim;X-NUM-GUESTS=0:MAILTO:steve_mckim@swifttrans.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CNMary Jackson;X-NUM-GUESTS=0:MAILTO:Mary Jackson

END:VEVENT
END:VCALENDAR"
</pre><pre>HEADERS:string(1407) "Content-Type:text/calendar; Content-Disposition: inline; charset=utf-8;\r\nContent-Type: text/plain;charset="utf-8"
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//SwiftTransportation//TimeOffRequests/NONSGML v1.0//EN
METHOD:REQUEST
BEGIN:VTIMEZONE
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
END:VTIMEZONE
BEGIN:VEVENT
UID:492e310ed52aa594b3e2143af11e3ba7swifttrans.com
DTSTART;TZID=America/Phoenix:20161104T00
DTEND;TZID=America/Phoenix:20161104T00
DTSTAMP:20161021
SUMMARY:[ DEVELOPMENT - Time Off Requests ] - STEVEN S MCKIM - APPROVED TIME OFF
LOCATION:
DESCRIPTION:Time off on 11/04/2016
STATUS:CONFIRMED
X-MICROSOFT-CDO-BUSYSTATUS:FREE
X-MICROSOFT-CDO-INSTTYPE:0
X-MICROSOFT-CDO-INTENDEDSTATUS:FREE
X-MICROSOFT-CDO-ALLDAYEVENT:TRUE
FBTYPE:FREE
ORGANIZER;CN=Time Off Requests:mailto:timeoffrequests-donotreply@swifttrans.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CNSteven S Mckim;X-NUM-GUESTS=0:MAILTO:steve_mckim@swifttrans.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CNMary Jackson;X-NUM-GUESTS=0:MAILTO:Mary Jackson

END:VEVENT
END:VCALENDAR"
</pre><pre>MESSAGE:string(1290) "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//SwiftTransportation//TimeOffRequests/NONSGML v1.0//EN
METHOD:REQUEST
BEGIN:VTIMEZONE
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
END:VTIMEZONE
BEGIN:VEVENT
UID:492e310ed52aa594b3e2143af11e3ba7swifttrans.com
DTSTART;TZID=America/Phoenix:20161104T00
DTEND;TZID=America/Phoenix:20161104T00
DTSTAMP:20161021
SUMMARY:[ DEVELOPMENT - Time Off Requests ] - STEVEN S MCKIM - APPROVED TIME OFF
LOCATION:
DESCRIPTION:Time off on 11/04/2016
STATUS:CONFIRMED
X-MICROSOFT-CDO-BUSYSTATUS:FREE
X-MICROSOFT-CDO-INSTTYPE:0
X-MICROSOFT-CDO-INTENDEDSTATUS:FREE
X-MICROSOFT-CDO-ALLDAYEVENT:TRUE
FBTYPE:FREE
ORGANIZER;CN=Time Off Requests:mailto:timeoffrequests-donotreply@swifttrans.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CNSteven S Mckim;X-NUM-GUESTS=0:MAILTO:steve_mckim@swifttrans.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CNMary Jackson;X-NUM-GUESTS=0:MAILTO:Mary Jackson

END:VEVENT
END:VCALENDAR"
</pre><pre>HEADERS:string(1407) "Content-Type:text/calendar; Content-Disposition: inline; charset=utf-8;\r\nContent-Type: text/plain;charset="utf-8"
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//SwiftTransportation//TimeOffRequests/NONSGML v1.0//EN
METHOD:REQUEST
BEGIN:VTIMEZONE
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
END:VTIMEZONE
BEGIN:VEVENT
UID:6a15036ae37b7a5896eb25dc3c17fedaswifttrans.com
DTSTART;TZID=America/Phoenix:20161121T00
DTEND;TZID=America/Phoenix:20161121T00
DTSTAMP:20161021
SUMMARY:[ DEVELOPMENT - Time Off Requests ] - STEVEN S MCKIM - APPROVED TIME OFF
LOCATION:
DESCRIPTION:Time off on 11/21/2016
STATUS:CONFIRMED
X-MICROSOFT-CDO-BUSYSTATUS:FREE
X-MICROSOFT-CDO-INSTTYPE:0
X-MICROSOFT-CDO-INTENDEDSTATUS:FREE
X-MICROSOFT-CDO-ALLDAYEVENT:TRUE
FBTYPE:FREE
ORGANIZER;CN=Time Off Requests:mailto:timeoffrequests-donotreply@swifttrans.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CNSteven S Mckim;X-NUM-GUESTS=0:MAILTO:steve_mckim@swifttrans.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CNMary Jackson;X-NUM-GUESTS=0:MAILTO:Mary Jackson

END:VEVENT
END:VCALENDAR"
</pre><pre>MESSAGE:string(1290) "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//SwiftTransportation//TimeOffRequests/NONSGML v1.0//EN
METHOD:REQUEST
BEGIN:VTIMEZONE
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
END:VTIMEZONE
BEGIN:VEVENT
UID:6a15036ae37b7a5896eb25dc3c17fedaswifttrans.com
DTSTART;TZID=America/Phoenix:20161121T00
DTEND;TZID=America/Phoenix:20161121T00
DTSTAMP:20161021
SUMMARY:[ DEVELOPMENT - Time Off Requests ] - STEVEN S MCKIM - APPROVED TIME OFF
LOCATION:
DESCRIPTION:Time off on 11/21/2016
STATUS:CONFIRMED
X-MICROSOFT-CDO-BUSYSTATUS:FREE
X-MICROSOFT-CDO-INSTTYPE:0
X-MICROSOFT-CDO-INTENDEDSTATUS:FREE
X-MICROSOFT-CDO-ALLDAYEVENT:TRUE
FBTYPE:FREE
ORGANIZER;CN=Time Off Requests:mailto:timeoffrequests-donotreply@swifttrans.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CNSteven S Mckim;X-NUM-GUESTS=0:MAILTO:steve_mckim@swifttrans.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CNMary Jackson;X-NUM-GUESTS=0:MAILTO:Mary Jackson

END:VEVENT
END:VCALENDAR"
</pre><pre>HEADERS:string(1422) "Content-Type:text/calendar; Content-Disposition: inline; charset=utf-8;\r\nContent-Type: text/plain;charset="utf-8"
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//SwiftTransportation//TimeOffRequests/NONSGML v1.0//EN
METHOD:REQUEST
BEGIN:VTIMEZONE
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
END:VTIMEZONE
BEGIN:VEVENT
UID:5c06abfa378bd693732acef930dee349swifttrans.com
DTSTART;TZID=America/Phoenix:20161227T00
DTEND;TZID=America/Phoenix:20161230T00
DTSTAMP:20161021
SUMMARY:[ DEVELOPMENT - Time Off Requests ] - STEVEN S MCKIM - APPROVED TIME OFF
LOCATION:
DESCRIPTION:Time off from 12/27/2016 - 12/30/2016
STATUS:CONFIRMED
X-MICROSOFT-CDO-BUSYSTATUS:FREE
X-MICROSOFT-CDO-INSTTYPE:0
X-MICROSOFT-CDO-INTENDEDSTATUS:FREE
X-MICROSOFT-CDO-ALLDAYEVENT:TRUE
FBTYPE:FREE
ORGANIZER;CN=Time Off Requests:mailto:timeoffrequests-donotreply@swifttrans.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CNSteven S Mckim;X-NUM-GUESTS=0:MAILTO:steve_mckim@swifttrans.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CNMary Jackson;X-NUM-GUESTS=0:MAILTO:Mary Jackson

END:VEVENT
END:VCALENDAR"
</pre><pre>MESSAGE:string(1305) "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//SwiftTransportation//TimeOffRequests/NONSGML v1.0//EN
METHOD:REQUEST
BEGIN:VTIMEZONE
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
END:VTIMEZONE
BEGIN:VEVENT
UID:5c06abfa378bd693732acef930dee349swifttrans.com
DTSTART;TZID=America/Phoenix:20161227T00
DTEND;TZID=America/Phoenix:20161230T00
DTSTAMP:20161021
SUMMARY:[ DEVELOPMENT - Time Off Requests ] - STEVEN S MCKIM - APPROVED TIME OFF
LOCATION:
DESCRIPTION:Time off from 12/27/2016 - 12/30/2016
STATUS:CONFIRMED
X-MICROSOFT-CDO-BUSYSTATUS:FREE
X-MICROSOFT-CDO-INSTTYPE:0
X-MICROSOFT-CDO-INTENDEDSTATUS:FREE
X-MICROSOFT-CDO-ALLDAYEVENT:TRUE
FBTYPE:FREE
ORGANIZER;CN=Time Off Requests:mailto:timeoffrequests-donotreply@swifttrans.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CNSteven S Mckim;X-NUM-GUESTS=0:MAILTO:steve_mckim@swifttrans.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CNMary Jackson;X-NUM-GUESTS=0:MAILTO:Mary Jackson

END:VEVENT
END:VCALENDAR"
</pre>Test complete.
         * @var unknown
         */

        $calendarRequestObject = $this->getCalendarRequestObject();

        echo '<pre>';
        var_dump( $calendarRequestObject );
        echo '</pre>';
//         die( "...." );

        foreach ( $calendarRequestObject['datesRequested'] as $key => $request ) {
            $descriptionString = $this->formatDescriptionString( $request );
            $participantsText = $this->formatParticipantsText( $calendarRequestObject );
            $emailSubject = $this->formatSubject( $calendarRequestObject['employee']['name'], "upper" );

            $headers = 'Content-Type:text/calendar; Content-Disposition: inline; charset=utf-8;\r\n';
            $headers .= "Content-Type: text/plain;charset=\"utf-8\"\r\n";
            $message = $this->formatBeginVCalendar() .
                $this->formatVTimezone() .
                $this->formatVEvents( $request, $this->formatSubject( $calendarRequestObject['employee']['name'], "upper" ), $descriptionString,
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