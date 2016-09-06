<?php
namespace Request\Service;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorInterface;
use Request\Model\TimeOffRequestSettings;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part;
use Zend\Mime\Mime;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mail\Transport\Smtp as SmtpTransport;

class EmailService extends AbstractActionController
{

    protected $serviceLocator;

    protected $timeOffRequestSettings;

    protected $overrideEmails;

    protected $emailOverrideList;

    protected $emailTo;

    protected $emailCC;

    protected $emailBCC;

    protected $emailFrom;

    protected $emailSubject;

    protected $emailBody;

    public function __construct(TimeOffRequestSettings $timeOffRequestSettings)
    {
        $this->timeOffRequestSettings = $timeOffRequestSettings;
    }

    public function setFrom( $emailFrom )
    {
        $this->emailFrom = $emailFrom;
        return $this;
    }

    public function setTo( $emailTo )
    {
        $this->checkEmailOverride();

        $this->emailTo = $emailTo;

        if( $this->overrideEmails == 1 && !empty( $this->emailOverrideList ) ) {
            $this->emailTo = $this->emailOverrideList;
            $this->emailCC = '';
        }

        return $this;
    }

    public function setCC( $emailCC )
    {
        $this->emailCC = $emailCC;
        return $this;
    }

    public function setBCC( $emailBCC )
    {
        $this->emailBCC = $emailBCC;
        return $this;
    }

    public function setSubject( $emailSubject )
    {
        $this->emailSubject = $emailSubject;
        return $this;
    }

    public function setBody( $emailBody )
    {
        $this->emailBody = $emailBody;
        return $this;
    }

//     public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
//     {
//         $this->serviceLocator = $serviceLocator;
//     }

    private function checkEmailOverride()
    {
        $emailOverrideList = $this->timeOffRequestSettings->getEmailOverrideList();
        $this->overrideEmails = $this->timeOffRequestSettings->getOverrideEmailsSetting();
        $this->emailOverrideList = ( ( ENVIRONMENT=='testing' || ENVIRONMENT=='development' ) ?
            $emailOverrideList : '' );

    }

    public function send()
    {

        $text = new Part($this->emailBody);
        $text->type = Mime::TYPE_HTML;
        $text->charset = "UTF-8";

        $mailBodyParts = new MimeMessage();
        $mailBodyParts->addPart($text);

        $options = new SmtpOptions(array(
            "name" => 'mailrelay',
            "host" => 'mailrelay.swifttrans.com',
            "port" => '25'
        ));

        $mail = new Message();
        $mail->setBody($mailBodyParts);
        $mail->setSubject( $this->emailSubject );
        $mail->setFrom( $this->emailFrom);
        $mail->addTo($this->emailTo);

        $transport = new SmtpTransport();
        $transport->setOptions($options);

        $transport->send($mail);

    }
}

