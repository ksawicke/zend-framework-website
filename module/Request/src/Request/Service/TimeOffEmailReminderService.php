<?php
namespace Request\Service;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorInterface;
use Request\Model\TimeOffRequests;
use Request\Model\TimeOffEmailReminder;

class TimeOffEmailReminderService extends AbstractActionController
{

    protected $serviceLocator;

    protected $emailReminderListl;

    public function sendThreeDayReminderEmailToSupervisor()
    {
        $timeOffRequests = new TimeOffRequests();
        $timeOffEmailReminder = $this->serviceLocator->get('TimeOffEmailReminder');

        $timeOffRequestsResult = $timeOffRequests->getRequestsOverThreeDaysUnapproved();

        $insertResult = $timeOffEmailReminder->insertReminderRecords($timeOffRequestsResult);

        $timeOffEmailReminderResult = $timeOffEmailReminder->getAllUnsendRecordData();

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

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
}

