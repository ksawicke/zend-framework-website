<?php

return array(
//     'db'              => array( /** Db Config */ ),
//     'service_manager' => array(
//         'factories' => array(
//             'Blog\Mapper\PostMapperInterface'   => 'Blog\Factory\ZendDbSqlMapperFactory',
//             'Blog\Service\PostServiceInterface' => 'Blog\Service\Factory\PostServiceFactory',
//             'Zend\Db\Adapter\Adapter'           => 'Zend\Db\Adapter\AdapterServiceFactory'
//         )
//     ),
    'controllers' => array(
        'invokables' => array(
            'Blog\Controller\List' => 'Blog\Controller\ListController'
        )
    ),
    'router' => array(
        // Open configuration for all possible routes
        'routes' => array(
            // Define a new route called "post"
            'post' => array(
                // Define the routes type to be "Zend\Mvc\Router\Http\Literal", which is basically just a string
                'type' => 'literal',
                // Configure the route itself
                'options' => array(
                    // Listen to "/blog" as uri
                    'route'    => '/blog',
                    // Define default controller and action to be called when this route is matched
                    'defaults' => array(
                        'controller' => 'Blog\Controller\List',
                        'action'     => 'index',
                    )
                )
            )
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
             __DIR__ . '/../view',
         ),
    )
 );
