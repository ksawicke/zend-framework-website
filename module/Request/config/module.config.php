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
            'create2' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/request/create',
                    'defaults' => [
                        'controller' => 'Request\Controller\Request',
                        'action' => 'create'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => []
            ],
            'success' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/request/submitted-for-approval',
                    'defaults' => [
                        'controller' => 'Request\Controller\Request',
                        'action' => 'submittedForApproval'
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
            ],
            'viewMyTeamCalendar' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/request/view-my-team-calendar',
                    'defaults' => [
                        'controller' => 'Request\Controller\Request',
                        'action' => 'viewMyTeamCalendar'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => []
            ],
            'api' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/request/api',
                    'defaults' => [
                        'controller' => 'Request\Controller\Request',
                        'action' => 'api'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => []
            ],
            'reviewRequest' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/request/review-request/[:request_id]',
                    'defaults' => [
                        'controller' => 'Request\Controller\Request',
                        'action' => 'reviewRequest'
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
