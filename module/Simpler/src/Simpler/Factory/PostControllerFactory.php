<?php

namespace Simpler\Factory;

use Simpler\Controller\SimplerController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SimplerControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
//        $realServiceLocator = $serviceLocator->getServiceLocator();
//        $postService        = $realServiceLocator->get('Blog\Service\PostServiceInterface');
//        $postInsertForm     = $realServiceLocator->get('FormElementManager')->get('Blog\Form\PostForm');
//
//        return new PostController($postService, $postInsertForm);
    }
}
