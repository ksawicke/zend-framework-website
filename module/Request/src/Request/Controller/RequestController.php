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

     public function __construct(
         RequestServiceInterface $requestService,
         FormInterface $requestForm
     ) {
         $this->requestService = $requestService;
         $this->requestForm    = $requestForm;

         $this->employeeId = '49499';
     }

     public function createAction()
     {
        return new ViewModel(array(
            'employeeData' => $this->requestService->findTimeOffBalances($this->employeeId)
        ));
     }
 }
