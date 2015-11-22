<?php

return array(
//     'db'              => array( /** Db Config */ ),
     'service_manager' => array(
//         'invokables' => array(
//            'Blog\Service\PostServiceInterface' => 'Blog\Service\PostService'
//         ),
         'factories' => array(
             'Blog\Mapper\PostMapperInterface'   => 'Blog\Factory\ZendDbSqlMapperFactory',
             'Blog\Service\PostServiceInterface' => 'Blog\Factory\PostServiceFactory',
             'Zend\Db\Adapter\Adapter'           => 'Zend\Db\Adapter\AdapterServiceFactory'
         )
     ),
    'controllers' => array(
        'factories' => array(
            'Blog\Controller\Post' => 'Blog\Factory\PostControllerFactory'
//            'Blog\Controller\List' => 'Blog\Factory\ListControllerFactory',
//            'Blog\Controller\Write'  => 'Blog\Factory\WriteControllerFactory',
//            'Blog\Controller\Delete' => 'Blog\Factory\DeleteControllerFactory'
        )
    ),
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Blog\Controller\Post',
                        'action'     => 'index',
                    ),
                ),
            ),
            'blog' => array(
                'type' => 'literal',
                 'options' => array(
                     'route'    => '/blog',
                     'defaults' => array(
                         'controller' => 'Blog\Controller\Post',
                         'action'     => 'index',
                     ),
                 ),
                 'may_terminate' => true,
                 'child_routes'  => array(
                     'view' => array(
                         'type' => 'segment',
                         'options' => array(
                             'route'    => '/view/:id',
                             'defaults' => array(
                                 'controller' => 'Blog\Controller\Post',
                                 'action' => 'view'
                             ),
                             'constraints' => array(
                                 'id' => '[1-9]\d*'
                             )
                         )
                     ),
                     'edit' => array(
                         'type' => 'segment',
                         'options' => array(
                             'route'    => '/edit/:id',
                             'defaults' => array(
                                 'controller' => 'Blog\Controller\Post',
                                 'action'     => 'edit'
                             ),
                             'constraints' => array(
                                 'id' => '\d+'
                             )
                         )
                     ),
                     'add' => array(
                         'type' => 'segment',
                         'options' => array(
                             'route'    => '/add',
                             'defaults' => array(
                                 'controller' => 'Blog\Controller\Post',
                                 'action'     => 'add'
                             )
                         )
                     ),
                     'delete' => array(
                         'type' => 'segment',
                         'options' => array(
                             'route'    => '/delete/:id',
                             'defaults' => array(
                                 'controller' => 'Blog\Controller\Post',
                                 'action'     => 'delete'
                             ),
                             'constraints' => array(
                                 'id' => '\d+'
                             )
                         )
                     ),
                     'test' => array(
                         'type' => 'segment',
                         'options' => array(
                             'route'    => '/test',
                             'defaults' => array(
                                 'controller' => 'Blog\Controller\Post',
                                 'action'     => 'test'
                             )
                         )
                     ),
                     'test2' => array(
                         'type' => 'segment',
                         'options' => array(
                             'route'    => '/test2',
                             'defaults' => array(
                                 'controller' => 'Blog\Controller\Post',
                                 'action'     => 'test2'
                             )
                         )
                     ),
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
