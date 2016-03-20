<?php

/**
 * Handles sending emails.
 */

namespace Application\Factory;

use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mime\Part;
use Zend\Mime\Mime;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;

/**
 * Sends emails
 *
 * @author sawik
 */
class EmailFactory {

    public $applicationFromEmail;
    
    public $applicationFromName;
    
    public $applicationReplyToEmail;
    
    public $testSubjectPrefix;
    
    public $mailName;
    
    public $mailHost;
    
    public $mailPort;

    function __construct( $emailSubject = null, $emailBody = null, $toEmail = null, $ccEmail = null, $bccEmail = null ) {
//        parent::__construct();

        $this->applicationFromEmail = 'ASWIFT_SYSTEM@SWIFTTRANS.COM';
        $this->applicationFromName = 'Time Off Requests Administrator';
        $this->applicationReplyToEmail = 'DO_NOT_REPLY@SWIFT_TRANS.COM';
        $this->testSubjectPrefix = '[ DEVELOPMENT - Time Off Requests ] - ';
        
        $this->mailName = 'mailrelay';
        $this->mailHost = 'mailrelay.swifttrans.com';
        $this->mailPort = '25';
        
        $this->emailSubject = $emailSubject;
        $this->emailBody = $emailBody;
        $this->toEmail = $toEmail;
        $this->ccEmail = $ccEmail;
//        $this->bccEmail = $bcc;
    }

    /**
     * Send an email from the Time Off Request system.
     */
    public function send() {
        $text = new Part( $this->emailBody );
        $text->type = Mime::TYPE_TEXT;
        $mailBodyParts = new MimeMessage();
        $mailBodyParts->addPart( $text );

        $options = new SmtpOptions( array(
            "name" => $this->mailName,
            "host" => $this->mailHost,
            "port" => $this->mailPort
        ) );

        $buildEmail = false;

        $mail = new Message();
        $mail->setBody( $mailBodyParts );
        $mail->setFrom( $this->applicationFromEmail, $this->applicationFromName );
        $mail->addTo( $this->toEmail );
        if ( !empty( $this->ccEmail ) ) {
            $mail->addCc( $this->ccEmail );
        }
//        if ( !empty( $bcc ) ) {
//            $mail->addBcc( $bcc );
//        }
        $mail->setSubject( $this->emailSubject );

        $transport = new SmtpTransport();
        $transport->setOptions( $options );

        try {
            $transport->send( $mail );
            die("Mail sent successfully.");
        } catch ( Zend_Exception $ex ) {
            echo '<pre>';
            print_r( $ex );
            echo '</pre>';
            die("...");
            //error_log(__CLASS__ .'->'.__FUNCTION__.' ERROR: [LINE: ' . $ex->getLine() . '] ' . $ex->getMessage());
        }
        die("Why did we stop here?");
    }

}
