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

     public function __construct(
         RequestServiceInterface $requestService,
         FormInterface $requestForm
     ) {
         $this->requestService = $requestService;
         $this->requestForm    = $requestForm;
     }

     public function createAction()
     {
         $data = $this->requestService->findTimeOffBalances('49499');

         echo '<pre>DATA TEST:';
         print_r($data);
         echo '</pre>';
         die("@@@@");

//        return new ViewModel(array(
//            'posts' => $this->requestService->findAllRequests()
//        ));
     }
 }
