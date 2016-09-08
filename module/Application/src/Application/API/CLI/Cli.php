<?php
namespace Application\API\CLI;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Console\Request as ConsoleRequest;
use Request\Model\RequestEntry;

class Cli extends AbstractActionController
{
    public $serviceLocator;

    public function setRequestsToCompletedAction()
    {
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $requestService = new RequestEntry();
        $requestService->setRequestsToCompleted();

        return 'Ok!';
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
}

