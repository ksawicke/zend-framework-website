<?php

return [
//     'db'              => [ /** Db Config */ ],
     'service_manager' => [
//         'invokables' => [
//            'Blog\Service\PostServiceInterface' => 'Blog\Service\PostService'
//         ],
         'factories' => [
             'Blog\Mapper\PostMapperInterface'   => 'Blog\Factory\PostMapperFactory',
             'Blog\Service\PostServiceInterface' => 'Blog\Factory\PostServiceFactory',
             'Zend\Db\Adapter\Adapter'           => 'Zend\Db\Adapter\AdapterServiceFactory'
         ]
     ],
    'controllers' => [
        'factories' => [
            'Blog\Controller\Post' => 'Blog\Factory\PostControllerFactory'
//            'Blog\Controller\List' => 'Blog\Factory\ListControllerFactory',
//            'Blog\Controller\Write'  => 'Blog\Factory\WriteControllerFactory',
//            'Blog\Controller\Delete' => 'Blog\Factory\DeleteControllerFactory'
        ]
    ],
    'router' => [
        'routes' => [
            'home' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => 'Blog\Controller\Post',
                        'action'     => 'index',
                    ],
                ],
            ],
            'blog' => [
                'type' => 'literal',
                 'options' => [
                     'route'    => '/blog',
                     'defaults' => [
                         'controller' => 'Blog\Controller\Post',
                         'action'     => 'index',
                     ],
                 ],
                 'may_terminate' => true,
                 'child_routes'  => [
                     'view' => [
                         'type' => 'segment',
                         'options' => [
                             'route'    => '/view/:id',
                             'defaults' => [
                                 'controller' => 'Blog\Controller\Post',
                                 'action' => 'view'
                             ],
                             'constraints' => [
                                 'id' => '[1-9]\d*'
                             ]
                         ]
                     ],
                     'edit' => [
                         'type' => 'segment',
                         'options' => [
                             'route'    => '/edit/:id',
                             'defaults' => [
                                 'controller' => 'Blog\Controller\Post',
                                 'action'     => 'edit'
                             ],
                             'constraints' => [
                                 'id' => '\d+'
                             ]
                         ]
                     ],
                     'add' => [
                         'type' => 'segment',
                         'options' => [
                             'route'    => '/add',
                             'defaults' => [
                                 'controller' => 'Blog\Controller\Post',
                                 'action'     => 'add'
                             ]
                         ]
                     ],
                     'delete' => [
                         'type' => 'segment',
                         'options' => [
                             'route'    => '/delete/:id',
                             'defaults' => [
                                 'controller' => 'Blog\Controller\Post',
                                 'action'     => 'delete'
                             ],
                             'constraints' => [
                                 'id' => '\d+'
                             ]
                         ]
                     ],
                     'test' => [
                         'type' => 'segment',
                         'options' => [
                             'route'    => '/test',
                             'defaults' => [
                                 'controller' => 'Blog\Controller\Post',
                                 'action'     => 'test'
                             ]
                         ]
                     ],
                     'test2' => [
                         'type' => 'segment',
                         'options' => [
                             'route'    => '/test2',
                             'defaults' => [
                                 'controller' => 'Blog\Controller\Post',
                                 'action'     => 'test2'
                             ]
                         ]
                     ],
                     'test3' => [
                         'type' => 'segment',
                         'options' => [
                             'route'    => '/test3',
                             'defaults' => [
                                 'controller' => 'Blog\Controller\Post',
                                 'action'     => 'test3'
                             ]
                         ]
                     ],
                 ]

            ]
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
             __DIR__ . '/../view',
         ],
    ]
 ];
