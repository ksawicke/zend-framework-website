<?php
namespace Application\API\Scheduler;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorInterface;
// use Zend\View\Model\JsonModel;
use Zend\Console\Request as ConsoleRequest;
use Request\Service\TimeOffEmailReminderService;

class Scheduler extends AbstractActionController
{

    protected $serviceLocator;

    public function sendThreeDayReminderEmailToSupervisorAction()
    {
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $timeOffEmailReminderService = $this->serviceLocator->get('TimeOffEmailReminderService');
        $timeOffEmailReminderServiceResult = $timeOffEmailReminderService->sendThreeDayReminderEmailToSupervisor();

//         return new JsonModel($timeOffEmailReminderServiceResult);
        return "Done!";
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
}

