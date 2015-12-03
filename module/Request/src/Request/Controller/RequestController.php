<?php
namespace Request\Controller;

use Request\Service\RequestServiceInterface;
use Zend\Form\FormInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class RequestController extends AbstractActionController
{

    protected $requestService;

    protected $requestForm;

    public $employeeNumber;

    public $managerNumber;

    public function __construct(RequestServiceInterface $requestService, FormInterface $requestForm)
    {
        $this->requestService = $requestService;
        $this->requestForm = $requestForm;

        $this->employeeNumber = '49499';
        $this->managerNumber = '229589';
    }

    public function createAction()
    {
        // One method to grab a service....
        // Not using this but leaving as a reference for maybe later.
        //         $service = $this->getServiceLocator()->get('Request\Service\RequestServiceInterface');
        //         var_dump($service->findTimeOffBalances($this->employeeId));
        //         exit();

        return new ViewModel(array(
            'employeeData' => $this->requestService->findTimeOffBalancesByEmployee($this->employeeNumber),
            'approvedRequestData' => $this->requestService->findTimeOffApprovedRequestsByEmployee($this->employeeNumber)
        ));
    }

    public function viewEmployeeRequestsAction()
    {
        return new ViewModel(array(
            'managerDirectReportsData' => $this->requestService->findTimeOffBalancesByManager($this->managerNumber)
        ));
    }
    
    public function viewMyTeamCalendarAction()
    {
        return new ViewModel(array(
            'calendarData' => $this->requestService->findTimeOffCalendarByManager($this->managerNumber, 'current')
        ));
    }
}
