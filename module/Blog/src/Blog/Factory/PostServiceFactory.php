<?php

namespace Blog\Factory; // Blog\Service\Factory?
                        // @see http://blog.alejandrocelaya.com/2014/10/09/advanced-usage-of-service-manager-in-zend-framework-2/

use Blog\Service\PostService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PostServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $dependencyService = $serviceLocator->get('Blog\Mapper\PostMapperInterface');

        return new PostService($dependencyService);
    }
}
