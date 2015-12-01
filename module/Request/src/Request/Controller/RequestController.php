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

    public $employeeId;

    public $managerId;

    public function __construct(RequestServiceInterface $requestService, FormInterface $requestForm)
    {
        $this->requestService = $requestService;
        $this->requestForm = $requestForm;

        $this->employeeId = '348370';
        $this->managerId = '229589';
    }

    public function createAction()
    {
        // One method to grab a service....
        // Not using this but leaving as a reference for maybe later.
        //         $service = $this->getServiceLocator()->get('Request\Service\RequestServiceInterface');
        //         var_dump($service->findTimeOffBalances($this->employeeId));
        //         exit();

        return new ViewModel(array(
            'employeeData' => $this->requestService->findTimeOffBalancesByEmployee($this->employeeId),
            'approvedRequestData' => $this->requestService->findTimeOffApprovedRequestsByEmployee($this->employeeId)
        ));
    }

    public function viewEmployeeRequestsAction()
    {
        $managerDirectReportsData = $this->requestService->findTimeOffBalancesByManager($this->managerId);

        return new ViewModel(array(
            'managerDirectReportsData' => $managerDirectReportsData
        ));
    }
}
