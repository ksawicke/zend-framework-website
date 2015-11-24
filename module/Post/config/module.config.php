<?php

// Please read:
// http://zend-framework-community.634137.n4.nabble.com/Fundamental-question-Why-isn-t-the-service-manager-super-global-td4659184.html
// &
// http://framework.zend.com/manual/current/en/tutorials/tutorial.dbadapter.html

return [
//     'db'              => [ /** Db Config */ ],
     'service_manager' => [
         'factories' => [
             'post-model' => 'Post\Factory\PostModelFactory',
             'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory'
//             'Zend\Db\Adapter\Adapter'           => 'Zend\Db\Adapter\AdapterServiceFactory'
//             'Simpler\Model\PostModel' => function($sm) {
//                $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
//                return new \Simpler\Model\PostModel($sm, $dbAdapter);
//            }
         ],
//         'abstract_factories' => [
//            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
//            'Zend\Log\LoggerAbstractServiceFactory',
//         ],
//         'aliases' => [
//            'translator' => 'MvcTranslator',
//         ]
    ],
//    'translator' => [
//        'locale' => 'en_US',
//        'translation_file_patterns' => [
//            [
//                'type'     => 'gettext',
//                'base_dir' => __DIR__ . '/../language',
//                'pattern'  => '%s.mo',
//            ],
//        ],
//    ],
    'controllers' => [
        'invokables' => [
//            'Simpler\Controller\Blah' => 'Simpler\Controller\BlahController',
//            'Zend\Db\Adapter\Adapter'           => 'Zend\Db\Adapter\AdapterServiceFactory'
        ],
        'factories' => [
            'Post\Controller\Post' => 'Post\Factory\PostControllerFactory'
        ]
    ],
    'router' => [
        'routes' => [
            'post' => [
                'type' => 'literal',
                 'options' => [
                     'route'    => '/post',
                     'defaults' => [
                         'controller' => 'Post\Controller\Post',
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
                                 'controller' => 'Post\Controller\Post',
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
                                 'controller' => 'Post\Controller\Post',
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
                                 'controller' => 'Post\Controller\Post',
                                 'action'     => 'add'
                             ]
                         ]
                     ],
                     'delete' => [
                         'type' => 'segment',
                         'options' => [
                             'route'    => '/delete/:id',
                             'defaults' => [
                                 'controller' => 'Post\Controller\Post',
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
                                 'controller' => 'Post\Controller\Post',
                                 'action'     => 'test'
                             ]
                         ]
                     ],
                     'test2' => [
                         'type' => 'segment',
                         'options' => [
                             'route'    => '/test2',
                             'defaults' => [
                                 'controller' => 'Post\Controller\Post',
                                 'action'     => 'test2'
                             ]
                         ]
                     ],
                     'test3' => [
                         'type' => 'segment',
                         'options' => [
                             'route'    => '/test3',
                             'defaults' => [
                                 'controller' => 'Post\Controller\Post',
                                 'action'     => 'test3'
                             ]
                         ]
                     ]
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
