<?php

namespace Post\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Post\Model\PostModel;

class PostController extends AbstractActionController
{
     /**
      *
      * @var PostModel
      */
     protected $postModel = null;

    public function indexAction()
    {
        $data = $this->postModel->find(51);

        var_dump($data);

        die("sdf");
    }

    public function setPostModel(PostModel $model)
    {
        $this->postModel = $model;
    }

 }
