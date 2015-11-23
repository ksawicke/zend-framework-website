<?php

return [
//     'db'              => [ /** Db Config */ ],
     'service_manager' => [
         'factories' => [
             'Zend\Db\Adapter\Adapter'           => 'Zend\Db\Adapter\AdapterServiceFactory'
         ]
     ],
    'controllers' => [
        'invokables' => [
            'Simpler\Controller\Blah' => 'Simpler\Controller\BlahController'
        ],
        'factories' => [
        ]
    ],
    'router' => [
        'routes' => [
            'home' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => 'Simpler\Controller\Blah',
                        'action'     => 'index',
                    ],
                ],
            ],
            'simpler' => [
                'type' => 'literal',
                 'options' => [
                     'route'    => '/simpler',
                     'defaults' => [
                         'controller' => 'Simpler\Controller\Blah',
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
                                 'controller' => 'Simpler\Controller\Blah',
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
                                 'controller' => 'Simpler\Controller\Blah',
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
                                 'controller' => 'Simpler\Controller\Blah',
                                 'action'     => 'add'
                             ]
                         ]
                     ],
                     'delete' => [
                         'type' => 'segment',
                         'options' => [
                             'route'    => '/delete/:id',
                             'defaults' => [
                                 'controller' => 'Simpler\Controller\Blah',
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
                                 'controller' => 'Simpler\Controller\Blah',
                                 'action'     => 'test'
                             ]
                         ]
                     ],
                     'test2' => [
                         'type' => 'segment',
                         'options' => [
                             'route'    => '/test2',
                             'defaults' => [
                                 'controller' => 'Simpler\Controller\Blah',
                                 'action'     => 'test2'
                             ]
                         ]
                     ],
                     'test3' => [
                         'type' => 'segment',
                         'options' => [
                             'route'    => '/test3',
                             'defaults' => [
                                 'controller' => 'Simpler\Controller\Blah',
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
