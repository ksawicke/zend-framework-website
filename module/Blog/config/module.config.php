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
            'Blog\Controller\List' => 'Blog\Factory\ListControllerFactory',
            'Blog\Controller\Write'  => 'Blog\Factory\WriteControllerFactory',
            'Blog\Controller\Delete' => 'Blog\Factory\DeleteControllerFactory'
        )
    ),
    'router' => array(
        'routes' => array(
            'blog' => array(
//                'type' => 'segment',
//                'options' => array(
//                    // Listen to "/blog" as uri
//                    'route'    => '/blog[/:action][/:id]',
//                    'constraints' => array(
//                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
//                    ),
//                    // Define default controller and action to be called when this route is matched
//                    'defaults' => array(
//                        'controller' => 'Blog\Controller\List',
//                        'action'     => 'index',
//                    )
//                ),


//                'type' => 'literal',
//                 'options' => array(
//                     'route'    => '/blog',
//                     'defaults' => array(
//                         'controller' => 'Blog\Controller\List',
//                         'action'     => 'index',
//                     )
//                 ),
//                 'may_terminate' => true,
//                 'child_routes'  => array(
//                     'detail' => array(
//                         'type' => 'segment',
//                         'options' => array(
//                             'route'    => '/detail/:id',
//                             'defaults' => array(
//                                 'controller' => 'Blog\Controller\List',
//                                 'action' => 'detail'
//                             ),
//                             'constraints' => array(
//                                 'id' => '\d+'
//                             )
//                         )
//                     ),
//                 )

                'type' => 'literal',
                 'options' => array(
                     'route'    => '/blog',
                     'defaults' => array(
                         'controller' => 'Blog\Controller\List',
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
                                 'controller' => 'Blog\Controller\List',
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
                                 'controller' => 'Blog\Controller\Write',
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
                                 'controller' => 'Blog\Controller\Write',
                                 'action'     => 'add'
                             )
                         )
                     ),
                     'delete' => array(
                         'type' => 'segment',
                         'options' => array(
                             'route'    => '/delete/:id',
                             'defaults' => array(
                                 'controller' => 'Blog\Controller\Delete',
                                 'action'     => 'delete'
                             ),
                             'constraints' => array(
                                 'id' => '\d+'
                             )
                         )
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
