<?php

namespace Simpler\Controller;

//use Zend\Form\FormInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
//use Simpler\Controller\BlahController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
//use Simpler\Factory\PostControllerFactory;

use Simpler\Model\PostModel;

class BlahController extends AbstractActionController
{
//     protected $postService;
//
//     protected $postForm;

    /**
     *
     * @var PostModel
     */
    protected $postModel = null;
//    protected $dbAdapter = null;

     // https://zf2-docs.readthedocs.org/en/latest/tutorials/tutorial.dbadapter.html
     // https://samsonasik.wordpress.com/2012/08/28/set-default-db-adapter-in-zend-framework-2/
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
//         $postModel = new \Simpler\Model\PostModel();

//         $hydrator = new ClassMethods(false);
//         var_dump($hydrator);
//         var_dump(['yeah'=>1]);
//         exit();
     }

    public function indexAction()
    {
        $data = $this->postModel->find(51);
//        die("E");

        var_dump($data);

        die("sdf");
//        $this->getServiceLocator()->get('\\Simpler\\Model\PostModel')->find(51);
//        echo '<pre>';
//        print_r($this);
//        echo '</pre>';
//        exit();
//        $model = new \Simpler\Model\PostModel();
////        die("D");
//        $test = $model->find(51);
//        var_dump($test);
//        $adapter = \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter();
//        var_dump($adapter);
//        die("X");
//
//        echo "STOP";
//        echo "x";
//
//        var_dump($test);exit();
//
//        $test = $this->getServiceLocator()->get('Simpler\Model\PostModel')->find(56);
//        var_dump($test);
    }

    public function setPostModel(PostModel $model)
    {
        $this->postModel = $model;
    }

//    public function setDbAdapter($dbAdapter)
//    {
////        var_dump($dbAdapter);exit();
//        $this->dbAdapter = $dbAdapter;
//    }
 }
