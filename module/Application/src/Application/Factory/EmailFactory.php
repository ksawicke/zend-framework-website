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
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;
use Application\Factory\Logger;

/**
 * Sends emails
 *
 * @author sawik
 */
class EmailFactory {

    public $applicationFromEmail;
    
    public $applicationFromName;
    
    public $applicationReplyToEmail;
    
    public $applicationEmailTemplate;
    
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
        $this->applicationEmailTemplate = dirname( dirname( dirname( __DIR__ ) ) ) . '/view/email/timeoffRequestTemplate.phtml';
        
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
        $buildEmail = false;
        $options = new SmtpOptions( array(
            "name" => $this->mailName,
            "host" => $this->mailHost,
            "port" => $this->mailPort
        ) );
        
        $text = new Part( $this->appendBodyToApplicationEmailTemplate() );
        $text->type = Mime::TYPE_HTML;
        $mailBodyParts = new MimeMessage();
        $mailBodyParts->addPart( $text );
        
        try {
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
            $transport->send( $mail );
            return true;
        } catch ( \Exception $ex ) {
            $logger = new Logger();
            $logger->logEntry( __CLASS__ .'->'.__FUNCTION__.' ERROR: [LINE: ' . $ex->getLine() . '] ' . $ex->getMessage() );
        }
        
        return false;
    }
    
    /**
     * Uses the template for the site when sending an email.
     */
    protected function appendBodyToApplicationEmailTemplate()
    {
        return str_replace( "{emailBody}", $this->emailBody, file_get_contents( $this->applicationEmailTemplate ) );
    }

}
