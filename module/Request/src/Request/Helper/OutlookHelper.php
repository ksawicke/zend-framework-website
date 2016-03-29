<?php

namespace Request\Helper;

class OutlookHelper {

    public $toEmail = '';
    public $organizerName = '';
    public $organizerEmail = '';
    public $participantName1 = '';
    public $participantEmail1 = '';
    public $participantName2 = '';
    public $participantEmail2 = '';
    public $subject = '';
    public $location = '';
    public $description = '';
    public $startDate = '';
    public $endDate = '';
    public $startTime = '';
    public $endTime = '';

    public function __construct() {
        $this->startTime = '0000';
        $this->endTime = '2359';
    }

    public function outputBeginVCalendar() {
        return "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//SwiftTransportation//TimeoffRequests/NONSGML v1.0//EN
METHOD:REQUEST\r\n";
    }

    public function outputEndVCalendar() {
        return "END:VCALENDAR";
    }

    public function outputVTimezone() {
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

    public function outputUID() {
        return md5( uniqid( mt_rand(), true ) ) . "swifttrans.com";
    }

    public function outputParticipantsText( $requestObject ) {
        $participantsText = '';
        foreach ( $requestObject['participants'] as $pctr => $participant ) {
            $participantsText = "ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CN" .
                    $requestObject['participants'][$pctr]['name'] .
                    ";X-NUM-GUESTS=0:MAILTO:" .
                    $requestObject['participants'][$pctr]['email'];
        }

        return $participantsText;
    }

    public function outputDescriptionString( $data ) {
        $descriptionString = '';
        $descriptionString .=
                ($data['start'] === $data['end']) ?
                $data['hours'] . " " . $data['type'] . ' on ' . date( "m/d/Y", strtotime( $data['start'] ) ) . '; ' :
                $data['hours'] . " " . $data['type'] . ' daily from ' . date( "m/d/Y", strtotime( $data['start'] ) ) . ' - ' . date( "m/d/Y", strtotime( $data['end'] ) ) . '; ';
        return substr( $descriptionString, 0, -2 );
    }

    public function outputVEvents( $request, $subject, $descriptionString, $organizerName, $organizerEmail, $participantsText ) {
        $dtStamp = date( 'Ymd' );
        $vEvents = '';
        $startDate = date( "Ymd", strtotime( $request['start'] ) );
        $endDate = date( "Ymd", strtotime( $request['end'] ) );
        if( ENVIRONMENT==='development' || ENVIRONMENT==='testing' ) {
            $subject = '[ ' . strtoupper( ENVIRONMENT ) . ' - Time Off Requests ] - ' . $subject;
        }

        $vEvents .= "BEGIN:VEVENT
UID:" . $this->outputUID() . "
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
ORGANIZER;CN=" . $organizerName . ":mailto:" . $organizerEmail . "\r\n" .
                $participantsText .
                "\r\nEND:VEVENT\r\n";

        return $vEvents;
    }

    protected function buildCalendarRequestObject( $calendarInviteData, $employeeData ) {
        return [
            'datesRequested' => $calendarInviteData['datesRequested'],
            'for' => $employeeData,
            'organizer' => [ 'name' => 'Time Off Requests', 'email' => 'timeoffrequests-donotreply@swifttrans.com' ],
            'to' => 'kevin_sawicke@swifttrans.com',
            'participants' => [ [ 'name' => 'Kevin Sawicke', 'email' => 'kevin_sawicke@swifttrans.com' ] ]
        ];
    }

    /**
     * Add appointment for day, all day event
     * @return type
     */
    public function addToCalendar( $calendarInviteData, $employeeData ) {
        $calendarRequestObject = $this->buildCalendarRequestObject( $calendarInviteData, $employeeData );
        $for = trim( $calendarRequestObject['for']['EMPLOYEE_NAME'] );
        $forEmail = trim( $calendarRequestObject['for']['EMAIL_ADDRESS'] );
        $manager = trim( $calendarRequestObject['for']['MANAGER_NAME'] );
        $managerEmail = trim( $calendarRequestObject['for']['MANAGER_EMAIL_ADDRESS'] );
        $subject = strtoupper( $for ) . ' - APPROVED TIME OFF';
        if( ENVIRONMENT==='development' || ENVIRONMENT==='testing' ) {
            $subject = '[ ' . strtoupper( ENVIRONMENT ) . ' - Time Off Requests ] - ' . $subject;
        }
        
        foreach ( $calendarRequestObject['datesRequested'] as $key => $request ) {
            $descriptionString = $this->outputDescriptionString( $request );
            $headers = 'Content-Type:text/calendar; Content-Disposition: inline; charset=utf-8;\r\n';
            $headers .= "Content-Type: text/plain;charset=\"utf-8\"\r\n"; #EDIT: TYPO
            $participantsText = $this->outputParticipantsText( $calendarRequestObject );

            $message = $this->outputBeginVCalendar() .
                    $this->outputVTimezone() .
                    $this->outputVEvents( $request, $subject, $descriptionString, $calendarRequestObject['organizer']['name'], $calendarRequestObject['organizer']['email'], $participantsText ) .
                    $this->outputEndVCalendar();

            $headers .= $message;
            
            $mailsent = mail( $calendarRequestObject['to'], $subject, $message, $headers );
        }

        return ($mailsent) ? (true) : (false);
    }

    /*     * **
     * GOOD...SAVING
     * 
     * $headers = 'Content-Type:text/calendar; Content-Disposition: inline; charset=utf-8;\r\n';
      $headers .= "Content-Type: text/plain;charset=\"utf-8\"\r\n"; #EDIT: TYPO

      $method = "REQUEST";

      $dateBlock = "DTSTART:" . $this->getDate() . "T" . $this->getStartTime() . "00Z\r\n
      DTEND:" . $this->getDate() . "T" . $this->getEndTime() . "00Z\r\n";
      $vevent = "BEGIN:VEVENT\r\n
      UID:" . md5(uniqid(mt_rand(), true)) . "swifttrans.com\r\n
      " . $dateBlock . "
      SUMMARY:" . $this->getSubject() . "\r\n
      ORGANIZER;CN=" . $this->getOrganizerName() . ":mailto:" . $this->getOrganizerEmail() . "\r\n
      LOCATION:" . $this->getLocation() . "\r\n
      DESCRIPTION:" . $this->getDescription() . "\r\n
      STATUS:CONFIRMED\r\n
      X-MICROSOFT-CDO-BUSYSTATUS:FREE\r\n
      X-MICROSOFT-CDO-INSTTYPE:0\r\n
      X-MICROSOFT-CDO-INTENDEDSTATUS:FREE\r\n
      X-MICROSOFT-CDO-ALLDAYEVENT:TRUE\r\n
      FBTYPE:FREE\r\n
      ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CN" . $this->getParticipantName1() . ";X-NUM-GUESTS=0:MAILTO:" . $this->getParticipantEmail1() . "\r\n
      ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CN" . $this->getParticipantName2() . ";X-NUM-GUESTS=0:MAILTO:" . $this->getParticipantEmail2() . "\r\n
      END:VEVENT\r\n";

      $message = "BEGIN:VCALENDAR\r\n
      VERSION:2.0\r\n
      PRODID:-//SwiftTransportation//TimeoffRequests/NONSGML v1.0//EN\r\n
      METHOD:" . $method . "\r\n
      " . $vevent . "
      END:VCALENDAR\r\n";
     */

    public function getToEmail() {
        return $this->toEmail;
    }

    public function setToEmail( $toEmail ) {
        $this->toEmail = $toEmail;
    }

    public function getOrganizerName() {
        return $this->organizerName;
    }

    public function setOrganizerName( $organizerName ) {
        $this->organizerName = $organizerName;
    }

    public function getOrganizerEmail() {
        return $this->organizerEmail;
    }

    public function setOrganizerEmail( $organizerEmail ) {
        $this->organizerEmail = $organizerEmail;
    }

    public function getParticipantName1() {
        return $this->participantName1;
    }

    public function setParticipantName1( $participantName1 ) {
        $this->participantName1 = $participantName1;
    }

    public function getParticipantEmail1() {
        return $this->participantEmail1;
    }

    public function setParticipantEmail1( $participantEmail1 ) {
        $this->participantEmail1 = $participantEmail1;
    }

    public function getParticipantName2() {
        return $this->participantName2;
    }

    public function setParticipantName2( $participantName2 ) {
        $this->participantName2 = $participantName2;
    }

    public function getParticipantEmail2() {
        return $this->participantEmail2;
    }

    public function setParticipantEmail2( $participantEmail2 ) {
        $this->participantEmail2 = $participantEmail2;
    }

    public function getSubject() {
        return $this->subject;
    }

    public function setSubject( $subject ) {
        $this->subject = $subject;
    }

    public function getLocation() {
        return $this->location;
    }

    public function setLocation( $location ) {
        $this->location = $location;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription( $description ) {
        $this->description = $description;
    }

    public function getStartDate() {
        return $this->startDate;
    }

    public function setStartDate( $startDate ) {
        $this->startDate = $startDate;
    }

    public function getEndDate() {
        return $this->endDate;
    }

    public function setEndDate( $endDate ) {
        $this->endDate = $endDate;
    }

    public function getStartTime() {
        return $this->startTime;
    }

    public function setStartTime( $startTime ) {
        $this->startTime = $startTime;
    }

    public function getEndTime() {
        return $this->endTime;
    }

    public function setEndTime( $endTime ) {
        $this->endTime = $endTime;
    }

}
