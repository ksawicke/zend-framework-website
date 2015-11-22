<?php

 namespace Blog\Controller;

 use Blog\Service\PostServiceInterface;
 use Zend\Form\FormInterface;
 use Zend\Mvc\Controller\AbstractActionController;
 use Zend\View\Model\ViewModel;

 class PostController extends AbstractActionController
 {
     protected $postService;

     protected $postForm;

     public function __construct(
         PostServiceInterface $postService,
         FormInterface $postForm
     ) {
         $this->postService = $postService;
         $this->postForm    = $postForm;
     }

     public function indexAction()
     {
        return new ViewModel(array(
            'posts' => $this->postService->findAllPosts()
        ));
     }

     public function viewAction()
     {
        $id = $this->params()->fromRoute('id');

        try {
            $post = $this->postService->findPost($id);
        } catch (\InvalidArgumentException $ex) {
            return $this->redirect()->toRoute('blog');
        }

        return new ViewModel(array(
            'post' => $post
        ));
     }

     public function addAction()
     {
         $request = $this->getRequest();

         if ($request->isPost()) {
             $this->postForm->setData($request->getPost());
             if ($this->postForm->isValid()) {
                 try {
                     $this->postService->savePost($this->postForm->getData());

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

     public function editAction()
     {
         $request = $this->getRequest();
         $post    = $this->postService->findPost($this->params('id'));

         $this->postForm->bind($post);

         if ($request->isPost()) {
             $this->postForm->setData($request->getPost());

             if ($this->postForm->isValid()) {
                 try {
                     $this->postService->savePost($post);

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

     public function deleteAction()
     {
         try {
             $post = $this->postService->findPost($this->params('id'));
         } catch (\InvalidArgumentException $e) {
             return $this->redirect()->toRoute('blog');
         }

         $request = $this->getRequest();

         if ($request->isPost()) {
             $del = $request->getPost('delete_confirmation', 'no');

             if ($del === 'yes') {
                 try {
                    $this->postService->deletePost($post);
                 } catch (Exception $e) {
                    die('Error: ' . $e->getMessage());
                 }
             }

             return $this->redirect()->toRoute('blog');
         }

         return new ViewModel(array(
             'post' => $post
         ));
     }

     public function testAction()
     {
         try {
             $test = $this->postService->findTestDataset();
         } catch (Exception $e) {
             var_dump($e);
             die();
         }

         echo "FOUND TEST DATASET:<br />";
         echo '<pre>';
         print_r($test);
         echo '</pre>';
         die(".............");
     }

     public function test2Action()
     {
         try {
             $test2 = $this->postService->findAllDocumentTypes(['MDV', 'SAR', 'SBC', 'DBP', 'STD', 'ELD']);
         } catch (Exception $e) {
             var_dump($e);
             die();
         }

         echo "FOUND TEST DATASET:<br />";
         echo '<pre>';
         print_r($test2);
         echo '</pre>';
         die(".............");
     }
 }
