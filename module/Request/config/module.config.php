<?php
return [
    'service_manager' => [
        'factories' => [
            'Request\Mapper\RequestMapperInterface' => 'Request\Factory\RequestMapperFactory',
            'Request\Service\RequestServiceInterface' => 'Request\Factory\RequestServiceFactory'
        ]
    ],
    'controllers' => [
        'factories' => [
            'Request\Controller\Request' => 'Request\Factory\RequestControllerFactory'
        ]
    ],
    'router' => [
        'routes' => [
            'home' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/',
                    'defaults' => [
                        'controller' => 'Request\Controller\Request',
                        'action' => 'create'
                    ]
                ]
            ],
            'create' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/request',
                    'defaults' => [
                        'controller' => 'Request\Controller\Request',
                        'action' => 'create'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => []
            ],
            'viewEmployeeRequests' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/request/view-employee-requests',
                    'defaults' => [
                        'controller' => 'Request\Controller\Request',
                        'action' => 'viewEmployeeRequests'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => []
            ]
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view'
        ]
    ]
];
