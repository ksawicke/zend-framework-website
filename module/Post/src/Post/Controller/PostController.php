<?php

namespace Post\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Post\Model\PostModel;
use Zend\View\Model\ViewModel;

class PostController extends AbstractActionController
{
    /**
     *
     * @var PostModel
     */
    protected $postModel = null;

    public function indexAction()
    {
        return new ViewModel(array(
            'posts' => $this->postModel->findAll()
        ));
    }

    public function viewAction()
    {
        $id = $this->params()->fromRoute('id');

        try {
            $post = $this->postModel->find(51);
//            echo '<pre>';
//            print_r($post);
//            echo '</pre>';
//            exit();
        } catch (\InvalidArgumentException $ex) {
            return $this->redirect()->toRoute('post');
        }

        return new ViewModel(array(
            'post' => $post
        ));
    }

    public function editAction()
     {
         $request = $this->getRequest();
         $post    = $this->postModel->findPost($this->params('id'));

         $this->postForm->bind($post);

         if ($request->isPost()) {
             $this->postForm->setData($request->getPost());

             if ($this->postForm->isValid()) {
                 try {
                     $this->postModel->savePost($post);

                     return $this->redirect()->toRoute('blog');
                 } catch (\Exception $e) {
                     die($e->getMessage());
                     // Some DB Error happened, log it and let the user know
                 }
             }
         }

         return new ViewModel(array(
             'form' => $this->postForm
         ));
     }

    public function setPostModel(PostModel $model)
    {
        $this->postModel = $model;
    }

 }
