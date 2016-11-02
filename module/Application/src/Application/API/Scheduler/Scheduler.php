<?php
namespace Application\API\Scheduler;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\JsonModel;
use Request\Service\TimeOffEmailReminderService;
use Application\API\ApiController;

class Scheduler extends ApiController
{

    protected $serviceLocator;

    public function sendThreeDayReminderEmailToSupervisorAction()
    {
        $timeOffEmailReminderService = $this->serviceLocator->get('TimeOffEmailReminderService');
        $timeOffEmailReminderServiceResult = $timeOffEmailReminderService->sendThreeDayReminderEmailToSupervisor();

        return new JsonModel($timeOffEmailReminderServiceResult);
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
}

