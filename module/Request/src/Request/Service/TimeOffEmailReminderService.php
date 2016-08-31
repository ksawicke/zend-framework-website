<?php
namespace Request\Service;

use Zend\Mvc\Controller\AbstractActionController;
use Request\Model\TimeOffRequests;
use Request\Model\TimeOffEmailReminder;

class TimeOffEmailReminderService extends AbstractActionController
{

    protected $serviceLocator;

    protected $timeOffRequests;

    protected $timeOffEmailReminder;

    protected $emailService;

    protected $emailReminderListl;

    /**
     *
     * @param TimeOffRequests $timeOffRequests
     * @param TimeOffEmailReminder $timeOffEmailReminder
     * @param EmailService $emailService
     */
    public function __construct(TimeOffRequests $timeOffRequests, TimeOffEmailReminder $timeOffEmailReminder, EmailService $emailService)
    {
        $this->timeOffRequests = $timeOffRequests;
        $this->timeOffEmailReminder = $timeOffEmailReminder;
        $this->emailService = $emailService;
    }

    public function sendThreeDayReminderEmailToSupervisor()
    {
        $timeOffRequestsResult = $this->timeOffRequests->getRequestsOverThreeDaysUnapproved();

        $insertResult = $this->timeOffEmailReminder->insertReminderRecords($timeOffRequestsResult);

        $timeOffEmailReminderResult = $this->timeOffEmailReminder->getAllUnsendRecordData();

        $this->prepareEmailArray($timeOffEmailReminderResult);

    }

    protected function prepareEmailArray($timeOffReminders)
    {
        if (!is_array($timeOffReminders)) {
            return false;
        }

        if (count($timeOffReminders) == 0) {
            return false;
        }

        $this->emailReminderListl = [];
echo "<pre>";
        foreach ($timeOffReminders as $timeOffReminder) {
            var_dump($timeOffReminder);
        }

    }

    protected function getEmailBodyTemplate()
    {
        //
    }

}

