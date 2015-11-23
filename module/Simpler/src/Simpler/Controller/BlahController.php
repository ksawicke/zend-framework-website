<?php

namespace Simpler\Controller;

//use Zend\Form\FormInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
//use Simpler\Controller\BlahController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
//use Simpler\Factory\PostControllerFactory;

class BlahController extends AbstractActionController
{
//     protected $postService;
//
//     protected $postForm;

     public function __construct(
     ) {
//         $this->postService = $postService;
//         $this->postForm    = $postForm;

//         $realServiceLocator = $serviceLocator->getServiceLocator();
//         $postService        = $realServiceLocator->get('Blog\Service\PostServiceInterface');
//         $postInsertForm     = $realServiceLocator->get('FormElementManager')->get('Blog\Form\PostForm');

         //$serviceLocator->get('Zend\Db\Adapter\Adapter'),
         //$hydrator

//         var_dump($postService);
//         var_dump($postInsertForm);

//         $serviceLocator = ServiceLocatorInterface();
//         die("OK");
         $postModel = new \Simpler\Model\PostModel();
//         $hydrator = new ClassMethods(false);

         var_dump(['yeah'=>1]);
         exit();
     }
 }
