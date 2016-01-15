<?php

namespace Request\Helper;

class OutlookHelper {

    public static $organizerName = '';
    
    public static $organizerEmail = '';
    
    public static $participantName1 = '';
    
    public static $participantEmail1 = '';
    
    public static $participantName2 = '';
    
    public static $participantEmail2 = '';
    
    public static $subject = '';
    
    public static $location = '';
    
    public static $description = '';
    
    public static $date = '';
    
    public static $startTime = '';
    
    public static $endTime = '';
    
    public function __construct()
    {
        self::$startTime = '1500';
        self::$endTime = '1500';      
    }
    
    /**
     * Add appointment for day, all day event
     * @return type
     */
    public static function addToCalendar()
    {
        $to = 'kevin_sawicke@swifttrans.com';
        
        $organizerName = 'Kevin Sawicke';
        $organizerEmail = 'kevin_sawicke@swifttrans.com';

        $participantName1 = 'Kevin Sawicke';
        $participantEmail1 = 'kevin_sawicke@swifttrans.com';

        $participantName2 = 'Kevin Sawicke';
        $participantEmail2 = 'kevin_sawicke@swifttrans.com';

        $subject = 'TEST CALENDAR UPDATE FROM TIMEOFF APPLICATION';
        $location = 'addToCalendar function test';
        $description = 'The purpose of the meeting is to discuss something.';

        $date = '20160115';
        $startTime = '1500';
        $endTime = '1500';

        $headers = 'Content-Type:text/calendar; Content-Disposition: inline; charset=utf-8;\r\n';
        $headers .= "Content-Type: text/plain;charset=\"utf-8\"\r\n"; #EDIT: TYPO
        
        $method = "REQUEST";
        
        $dateBlock = "DTSTART:" . self::$date . "T" . self::$startTime . "00Z\r\n
            DTEND:" . self::$date . "T" . self::$endTime . "00Z\r\n";
        $vevent = "BEGIN:VEVENT\r\n
            UID:" . md5(uniqid(mt_rand(), true)) . "swifttrans.com\r\n
            " . $dateBlock . "
            SUMMARY:" . $subject . "\r\n
            ORGANIZER;CN=" . $organizerName . ":mailto:" . $organizerEmail . "\r\n
            LOCATION:" . $location . "\r\n
            DESCRIPTION:" . $desc . "\r\n
            STATUS:CONFIRMED\r\n
            X-MICROSOFT-CDO-BUSYSTATUS:FREE\r\n
            X-MICROSOFT-CDO-INSTTYPE:0\r\n
            X-MICROSOFT-CDO-INTENDEDSTATUS:FREE\r\n
            X-MICROSOFT-CDO-ALLDAYEVENT:TRUE\r\n
            FBTYPE:FREE\r\n
            ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CN" . $participantName1 . ";X-NUM-GUESTS=0:MAILTO:" . $participantEmail1 . "\r\n
            ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CN" . $participantName2 . ";X-NUM-GUESTS=0:MAILTO:" . $participantEmail2 . "\r\n
            END:VEVENT\r\n";
        
        $message = "BEGIN:VCALENDAR\r\n
            VERSION:2.0\r\n
            PRODID:-//SwiftTransportation//TimeoffRequests/NONSGML v1.0//EN\r\n
            METHOD:" . $method . "\r\n
            " . $vevent . "
            END:VCALENDAR\r\n";
        
        $headers .= $message;
        $mailsent = mail($to, $subject, $message, $headers);

        return ($mailsent) ? (true) : (false);
    }
    
    public static function getOrganizerName()
    {
        return self::$organizerName;
    }
    
    public static function setOrganizerName($organizerName)
    {
        self::$organizerName = $organizerName;
    }
    
    public static function getOrganizerEmail()
    {
        return self::$organizerEmail;
    }
    
    public static function setOrganizerEmail($organizerEmail)
    {
        self::$organizerEmail = $organizerEmail;
    }
    
    public static function getParticipantName1()
    {
        return self::$participantName1;
    }
    
    public static function setParticipantName1($participantName1)
    {
        self::$participantName1 = $participantName1;
    }
    
    public static function getParticipantEmail1()
    {
        return self::$participantEmail1;
    }
    
    public static function setParticipantEmail1($participantEmail1)
    {
        self::$participantEmail1 = $participantEmail1;
    }
    
    public static function getParticipantName2()
    {
        return self::$participantName2;
    }
    
    public static function setParticipantName2($participantName2)
    {
        self::$participantName2 = $participantName2;
    }
    
    public static function getParticipantEmail2()
    {
        return self::$participantEmail2;
    }
    
    public static function setParticipantEmail2($participantEmail2)
    {
        self::$participantEmail2 = $participantEmail2;
    }
    
    public static function getSubject()
    {
        return self::$subject;
    }
    
    public static function setSubject($subject)
    {
        self::$subject = $subject;
    }
    
    public static function getLocation()
    {
        return self::$location;
    }
    
    public static function setLocation($location)
    {
        self::$location = $location;
    }
    
    public static function getDescription()
    {
        return self::$description;
    }
    
    public static function setDescription($description)
    {
        self::$description = $description;
    }
    
    public static function getDate()
    {
        return self::$date;
    }
    
    public static function setDate($date)
    {
        self::$date = $date;
    }
    
    public static function getStartTime()
    {
        return self::$startTime;
    }
    
    public static function setStartTime($startTime)
    {
        self::$startTime = $startTime;
    }
    
    public static function getEndTime()
    {
        return self::$endTime;
    }
    
    public static function setEndTime($endTime)
    {
        self::$endTime = $endTime;
    }
    
    /**
     * Add appointment for day, all day event
     * @return type
     */
//    public static function addToCalendar()
//    {
//        $to = 'kevin_sawicke@swifttrans.com';
//        
//        $organizer = 'Kevin Sawicke';
//        $organizer_email = 'kevin_sawicke@swifttrans.com';
//
//        $participant_name_1 = 'Kevin Sawicke';
//        $participant_email_1 = 'kevin_sawicke@swifttrans.com';
//
//        $participant_name_2 = 'Kevin Sawicke';
//        $participant_email_2 = 'kevin_sawicke@swifttrans.com';
//
//        $subject = 'TEST CALENDAR UPDATE FROM TIMEOFF APPLICATION';
//        $location = "addToCalendar function test";
//        $desc = 'The purpose of the meeting is to discuss something.';
//
//        $date = '20160115';
//        $startTime = '0800';
//        $endTime = '1700';
//
//        $headers = 'Content-Type:text/calendar; Content-Disposition: inline; charset=utf-8;\r\n';
//        $headers .= "Content-Type: text/plain;charset=\"utf-8\"\r\n"; #EDIT: TYPO
//
////        $vevent = "BEGIN:VEVENT\r\n
////            DTSTART:20160115T050000Z\r\n
////            DTEND:20160115T050000Z\r\n
////            TRANSP:1\r\n
////            UID:" . md5(uniqid(mt_rand(), true)) . "swifttrans.com\r\n
////            DESCRIPTION;ENCODING=QUOTED-PRINTABLE:tes=0D=0A\r\n
////            SUMMARY;ENCODING=QUOTED-PRINTABLE:tes\r\n
////            ORGANIZER;CN=" . $organizer . ":mailto:" . $organizer_email . "\r\n
////            LOCATION:" . $location . "\r\n
////            DESCRIPTION:" . $desc . "\r\n
////            ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=FALSE;CN" . $participant_name_1 . ";X-NUM-GUESTS=0:MAILTO:" . $participant_email_1 . "\r\n
////            PRIORITY:3\r\n
////            END:VEVENT\r\n";
//        
////        DTSTART:" . $date . "T" . $startTime . "00Z\r\n
////            DTEND:" . $date . "T" . $endTime . "00Z\r\n
//        
//        // This works but does 5pm to 5pm - like appointment
//        // DTSTART;VALUE=DATE:20160115\r\n
//        //
//        // like meeting
//        // DTSTART:20160115T000000Z\r\n
//        // DTEND:20160115T000000Z\r\n
//        
//        $method = "REQUEST";
////        $method = "PUBLISH";
//          // 5pm - 5pm
////        $dateX = "DTSTART;VALUE=DATE:20160115\r\n"; // WORKS, 5pm-5pm
//        
//        
//        // 0700 works--12am
//        $dateX = "DTSTART:20160118T150000Z\r\n
//            DTEND:20160118T150000Z\r\n";
//        $vevent = "BEGIN:VEVENT\r\n
//            UID:" . md5(uniqid(mt_rand(), true)) . "swifttrans.com\r\n
//            " . $dateX . "
//            SUMMARY:" . $subject . "\r\n
//            ORGANIZER;CN=" . $organizer . ":mailto:" . $organizer_email . "\r\n
//            LOCATION:" . $location . "\r\n
//            DESCRIPTION:" . $desc . "\r\n
//            STATUS:CONFIRMED\r\n
//            X-MICROSOFT-CDO-BUSYSTATUS:FREE\r\n
//            X-MICROSOFT-CDO-INSTTYPE:0\r\n
//            X-MICROSOFT-CDO-INTENDEDSTATUS:FREE\r\n
//            X-MICROSOFT-CDO-ALLDAYEVENT:TRUE\r\n
//            FBTYPE:FREE\r\n
//            ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CN" . $participant_name_1 . ";X-NUM-GUESTS=0:MAILTO:" . $participant_email_1 . "\r\n
//            ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CN" . $participant_name_2 . ";X-NUM-GUESTS=0:MAILTO:" . $participant_email_2 . "\r\n
//            END:VEVENT\r\n";
//        
//        $message = "BEGIN:VCALENDAR\r\n
//            VERSION:2.0\r\n
//            PRODID:-//SwiftTransportation//TimeoffRequests/NONSGML v1.0//EN\r\n
//            METHOD:" . $method . "\r\n
//            " . $vevent . "
//            END:VCALENDAR\r\n";
//
//        echo '<pre>' . $message . '</pre><br /><br />';
//        
////        DTSTART:" . $date . "T" . $startTime . "00Z\r\n
////            DTEND:" . $date . "T" . $endTime . "00Z\r\n
//        
//        $headers .= $message;
//        $mailsent = mail($to, $subject, $message, $headers);
//
//        return ($mailsent) ? (true) : (false);
//    }

//    public static function sendCal() {
//        $dtstart = '20160114';
//        $dtend = '20160114';
//        
//        $date = '20160114';
//        $startTime = '0800';
//        $endTime = '1300';
//        
//        $loc = 'sendCal function test';
//        $summary = '!!!';
//        $from = 'kevin_sawicke@swifttrans.com';
//        $to = 'kevin_sawicke@swifttrans.com';
//        $subject = 'TEST CALENDAR UPDATE FROM TIMEOFF APPLICATION (2)';
//                
//        $vcal = "BEGIN:VCALENDAR\r\n";
//        $vcal .= "VERSION:2.0\r\n";
//        $vcal .= "PRODID:-//SwiftTransportation//TimeoffRequests//EN\r\n";
//        $vcal .= "METHOD:REQUEST\r\n";
//        $vcal .= "BEGIN:VEVENT\r\n";
//        $vcal .= "ATTENDEE;CN=\"Kevin Sawicke\";ROLE=REQ-PARTICIPANT;RSVP=FALSE:MAILTO:kevin_sawicke@swifttrans.com\r\n";
////        $vcal .= "ATTENDEE;CN=\"Attendee2Name\";ROLE=REQ-PARTICIPANT;RSVP=FALSE:MAILTO:kevin_sawicke@swifttrans.com\r\n";
//        $vcal .= "UID:" . date('Ymd') . 'T' . date('His') . "-" . rand() . "-domain.com\r\n";
//        $vcal .= "DTSTAMP:" . date('Ymd') . 'T' . date('His') . "\r\n";
//        $vcal .= "DTSTART:" . $date . "\r\n";
////        $vcal .= "DTSTART:" . $date . "T" . $startTime . "00Z\r\n";
////        $vcal .= "DTEND:" . $date . "T" . $endTime . "00Z\r\n";
//        if ($loc != "")
//            $vcal .= "LOCATION:$loc\r\n";
//        $vcal .= "SUMMARY:$summary\r\n";
//        $vcal .= "BEGIN:VALARM\r\n";
//        $vcal .= "TRIGGER:-PT15M\r\n";
//        $vcal .= "ACTION:DISPLAY\r\n";
//        $vcal .= "DESCRIPTION:Reminder\r\n";
//        $vcal .= "END:VALARM\r\n";
//        $vcal .= "END:VEVENT\r\n";
//        $vcal .= "END:VCALENDAR\r\n";
//
//        $headers = "From: $from\r\nReply-To: $from";
//        $headers .= "\r\nMIME-version: 1.0\r\nContent-Type: text/calendar; method=REQUEST; charset=\"iso-8859-1\"";
//        $headers .= "\r\nContent-Transfer-Encoding: 7bit\r\nX-Mailer: Microsoft Office Outlook 12.0";
//
//        $mailsent = mail($to, $subject, $vcal, $headers);
//        
//        return ($mailsent) ? (true) : (false);
//    }

    /**
     * Send a calendar invite to mutiple people.
     * 1. Meeting Request
     * @return type
     */
//    public static function addToCalendar()
//    {
//        $to = 'kevin_sawicke@swifttrans.com';
//        $subject = "TEST Meeting";
//
//        $organizer = 'Kevin Sawicke';
//        $organizer_email = 'kevin_sawicke@swifttrans.com';
//
//        $participant_name_1 = 'Kevin Sawicke';
//        $participant_email_1 = 'kevin_sawicke@swifttrans.com';
//
//        $participant_name_2 = 'Kevin Sawicke';
//        $participant_email_2 = 'kevin_sawicke@swifttrans.com';
//
//        $location = "Sample location here";
//        $date = '20160114';
//        $startTime = '0800';
//        $endTime = '1300';
//        $subject = 'Is this the subject';
//        $desc = 'The purpose of the meeting is to discuss something.';
//
//        $headers = 'Content-Type:text/calendar; Content-Disposition: inline; charset=utf-8;\r\n';
//        $headers .= "Content-Type: text/plain;charset=\"utf-8\"\r\n"; #EDIT: TYPO
//
//        $message = "BEGIN:VCALENDAR\r\n
//    VERSION:2.0\r\n
//    PRODID:-//Timeoff-mailer//timeoff/NONSGML v1.0//EN\r\n
//    METHOD:REQUEST\r\n
//    BEGIN:VEVENT\r\n
//    UID:" . md5(uniqid(mt_rand(), true)) . "swifttrans.com\r\n
//    DTSTAMP:" . gmdate('Ymd') . 'T' . gmdate('His') . "Z\r\n
//    DTSTART:" . $date . "T" . $startTime . "00Z\r\n
//    DTEND:" . $date . "T" . $endTime . "00Z\r\n
//    SUMMARY:" . $subject . "\r\n
//    ORGANIZER;CN=" . $organizer . ":mailto:" . $organizer_email . "\r\n
//    LOCATION:" . $location . "\r\n
//    DESCRIPTION:" . $desc . "\r\n
//    ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=TRUE;CN" . $participant_name_1 . ";X-NUM-GUESTS=0:MAILTO:" . $participant_email_1 . "\r\n
//    ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=TRUE;CN" . $participant_name_2 . ";X-NUM-GUESTS=0:MAILTO:" . $participant_email_2 . "\r\n
//    END:VEVENT\r\n
//    END:VCALENDAR\r\n";
//
//        $headers .= $message;
//        $mailsent = mail($to, $subject, $message, $headers);
//
//        return ($mailsent) ? (true) : (false);
//    }
}
