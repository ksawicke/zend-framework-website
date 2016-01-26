<?php
$config = [
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
//            'home' => [
//                'type' => 'Zend\Mvc\Router\Http\Literal',
//                'options' => [
//                    'route' => '/request/create',
//                    'defaults' => [
//                        'controller' => 'Request\Controller\Request',
//                        'action' => 'create'
//                    ]
//                ]
//            ],
//            'outlook' => [
//                'type' => 'literal',
//                'options' => [
//                    'route' => '/request/outlook',
//                    'defaults' => [
//                        'controller' => 'Request\Controller\Request',
//                        'action' => 'outlook'
//                    ]
//                ],
//                'may_terminate' => true,
//                'child_routes' => []
//            ],
//            'create' => [
//                'type' => 'literal',
//                'options' => [
//                    'route' => '/request/create',
//                    'defaults' => [
//                        'controller' => 'Request\Controller\Request',
//                        'action' => 'create'
//                    ]
//                ],
//                'may_terminate' => true,
//                'child_routes' => []
//            ],
//            'create2' => [
//                'type' => 'literal',
//                'options' => [
//                    'route' => '/request/create',
//                    'defaults' => [
//                        'controller' => 'Request\Controller\Request',
//                        'action' => 'create'
//                    ]
//                ],
//                'may_terminate' => true,
//                'child_routes' => []
//            ],
//            'success' => [
//                'type' => 'literal',
//                'options' => [
//                    'route' => '/request/submitted-for-approval',
//                    'defaults' => [
//                        'controller' => 'Request\Controller\Request',
//                        'action' => 'submittedForApproval'
//                    ]
//                ],
//                'may_terminate' => true,
//                'child_routes' => []
//            ],
//             'login' => [
//                 'type' => 'literal',
//                 'options' => [
//                     'route' => '/request/login',
//                     'defaults' => [
//                         'controller' => 'Request\Controller\Request',
//                         'action' => 'login'
//                     ]
//                 ],
//                 'may_terminate' => true,
//                 'child_routes' => []
//             ],
//            'viewEmployeeRequests' => [
//                'type' => 'literal',
//                'options' => [
//                    'route' => '/request/view-employee-requests',
//                    'defaults' => [
//                        'controller' => 'Request\Controller\Request',
//                        'action' => 'viewEmployeeRequests'
//                    ]
//                ],
//                'may_terminate' => true,
//                'child_routes' => []
//            ],
//            'viewMyTeamCalendar' => [
//                'type' => 'literal',
//                'options' => [
//                    'route' => '/request/view-my-team-calendar',
//                    'defaults' => [
//                        'controller' => 'Request\Controller\Request',
//                        'action' => 'viewMyTeamCalendar'
//                    ]
//                ],
//                'may_terminate' => true,
//                'child_routes' => []
//            ],
//            'api' => [
//                'type' => 'literal',
//                'options' => [
//                    'route' => '/request/api',
//                    'defaults' => [
//                        'controller' => 'Request\Controller\Request',
//                        'action' => 'api'
//                    ]
//                ],
//                'may_terminate' => true,
//                'child_routes' => []
//            ],
//            'reviewRequest' => [
//                'type' => 'segment',
//                'options' => [
//                    'route' => '/request/review-request/[:request_id]',
//                    'defaults' => [
//                        'controller' => 'Request\Controller\Request',
//                        'action' => 'reviewRequest'
//                    ]
//                ],
//                'may_terminate' => true,
//                'child_routes' => []
//            ],
//            'approvedRequest' => [
//                'type' => 'segment',
//                'options' => [
//                    'route' => '/request/approved-request',
//                    'defaults' => [
//                        'controller' => 'Request\Controller\Request',
//                        'action' => 'approvedRequest'
//                    ]
//                ],
//                'may_terminate' => true,
//                'child_routes' => []
//            ],
//            'deniedRequest' => [
//                'type' => 'segment',
//                'options' => [
//                    'route' => '/request/denied-request',
//                    'defaults' => [
//                        'controller' => 'Request\Controller\Request',
//                        'action' => 'deniedRequest'
//                    ]
//                ],
//                'may_terminate' => true,
//                'child_routes' => []
//            ]
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view'
        ]
    ]
];

// Test add new routes here to make it easier
//$config['router']['routes'];
$route = new \Request\Helper\RouteHelper();

$config = $route->addNewRoute(
    $config,
    ['name' => 'home',
     'route' => '/request/create',
     'controller' => 'Request\Controller\Request',
     'action' => 'create'
    ]
);
$config = $route->addNewRoute(
    $config,
    ['name' => 'success',
     'route' => '/request/submitted-for-approval',
     'controller' => 'Request\Controller\Request',
     'action' => 'submittedForApproval'
    ]
);
$config = $route->addNewRoute(
    $config,
    ['route' => '/request/view-employee-requests',
     'controller' => 'Request\Controller\Request',
     'action' => 'viewEmployeeRequests'
    ]
);
$config = $route->addNewRoute(
    $config,
    ['route' => '/request/view-my-team-calendar',
     'controller' => 'Request\Controller\Request',
     'action' => 'viewMyTeamCalendar'
    ]
);
$config = $route->addNewRoute(
    $config,
    ['name' => 'create',
     'route' => '/request/create',
     'controller' => 'Request\Controller\Request',
     'action' => 'create'
    ]
);
$config = $route->addNewRoute(
    $config,
    ['name' => 'create2',
     'route' => '/request/create',
     'controller' => 'Request\Controller\Request',
     'action' => 'create'
    ]
);
$config = $route->addNewRoute(
    $config,
    ['route' => '/request/api',
     'controller' => 'Request\Controller\Request',
     'action' => 'api'
    ]
);
$config = $route->addNewRoute(
    $config,
    ['route' => '/request/view-my-requests',
     'controller' => 'Request\Controller\Request',
     'action' => 'viewMyRequests'
    ]
);
$config = $route->addNewRoute(
    $config,
    ['type' => 'segment',
     'route' => '/request/review-request/[:request_id]',
     'controller' => 'Request\Controller\Request',
     'action' => 'reviewRequest'
    ]
);
$config = $route->addNewRoute(
    $config,
    ['route' => '/request/approved-request',
     'controller' => 'Request\Controller\Request',
     'action' => 'approvedRequest'
    ]
);
$config = $route->addNewRoute(
    $config,
    ['route' => '/request/denied-request',
     'controller' => 'Request\Controller\Request',
     'action' => 'deniedRequest'
    ]
);

return $config;
