<?php
namespace Application\API\Scheduler;

// use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\JsonModel;
// use Zend\Console\Request as ConsoleRequest;
use Request\Service\TimeOffEmailReminderService;
use Application\API\ApiController;
use Request\Factory\RequestServiceFactory;
use Request\Factory\RequestMapperFactory;
use Request\Mapper\RequestMapper;
use Request\Model\RequestEntry;

class Scheduler extends ApiController //AbstractActionController
{

    protected $serviceLocator;

    public function sendThreeDayReminderEmailToSupervisorAction()
    {
//         $request = $this->getRequest();

//         if (!$request instanceof ConsoleRequest) {
//             throw new \RuntimeException('You can only use this action from a console!');
//         }

        $timeOffEmailReminderService = $this->serviceLocator->get('TimeOffEmailReminderService');
        $timeOffEmailReminderServiceResult = $timeOffEmailReminderService->sendThreeDayReminderEmailToSupervisor();

        return new JsonModel($timeOffEmailReminderServiceResult);
//         return "Done!";
    }

    public function setRequestsToCompletedAction()
    {
        $requestService = new RequestEntry();
        $requestService->setRequestsToCompleted();

        return new JsonModel(['ok']);
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
}

