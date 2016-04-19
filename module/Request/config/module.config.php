<?php

namespace Request;

$config = [
    'service_manager' => [
        'factories' => [
//            'Request\Mapper\RequestMapperInterface' => 'Request\Factory\RequestMapperFactory',
//            'Request\Service\RequestServiceInterface' => 'Request\Factory\RequestServiceFactory'
        ]
    ],
    'controllers' => [
//        'factories' => [
//            'RequestController' => 'Request\Factory\RequestControllerFactory'
//        ],
        'invokables' => [
            'RequestController' => Controller\RequestController::class
        ]
    ],
    'router' => [
        'routes' => [
//            'api' => [
//                'type' => 'Zend\Mvc\Router\Http\Literal',
//                'options' => [
//                    'route' => '/request/api',
//                    'defaults' => [
//                        'controller' => 'RequestController',
//                        'action' => 'api'
//                    ]
//                ],
//                'may_terminate' => 1,
//                'child_routes' => []
//            ],
            'approvedRequest' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/request/approved-request',
                    'defaults' => [
                        'controller' => 'RequestController',
                        'action' => 'approvedRequest'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],
            'create' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/request/create',
                    'defaults' => [
                        'controller' => 'RequestController',
                        'action' => 'create'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],
            'editEmployeeProfile' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/request/edit-employee-profile',
                    'defaults' => [
                        'controller' => 'RequestController',
                        'action' => 'editEmployeeProfile'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],
            'create2' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/request/create',
                    'defaults' => [
                        'controller' => 'RequestController',
                        'action' => 'create'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],
            'deniedRequest' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/request/denied-request',
                    'defaults' => [
                        'controller' => 'RequestController',
                        'action' => 'deniedRequest'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],
            'home' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/request/view-my-requests',
                    'defaults' => [
                        'controller' => 'RequestController',
                        'action' => 'viewMyRequests'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],
            'reviewRequest' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/request/review-request/[:request_id]',
                    'defaults' => [
                        'controller' => 'RequestController',
                        'action' => 'reviewRequest'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],
            'viewManagerQueue' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/request/view-manager-queue/[:manager-view]',
                    'defaults' => [
                        'controller' => 'RequestController',
                        'action' => 'viewManagerQueue'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],
            'viewPayrollQueue' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/request/view-payroll-queue/[:payroll-view]',
                    'defaults' => [
                        'controller' => 'RequestController',
                        'action' => 'viewPayrollQueue'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],
            'viewMyRequests' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/request/view-my-requests',
                    'defaults' => [
                        'controller' => 'RequestController',
                        'action' => 'viewMyRequests'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],
            'submittedForApproval' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/request/submitted-for-approval',
                    'defaults' => [
                        'controller' => 'RequestController',
                        'action' => 'submittedForApproval'
                    ]
                ],
                'may_terminate' => 1,
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

// Test add new routes here to make it easier
//$config['router']['routes'];
//$route = new \Request\Helper\RouteHelper();
//$config = $route->addNewRoute(
//    $config,
//    ['name' => 'home',
//     'route' => '/request/view-my-requests',
//     'controller' => 'RequestController',
//     'action' => 'viewMyRequests'
//    ]
//);
//$config = $route->addNewRoute(
//    $config,
//    ['name' => 'success',
//     'route' => '/request/submitted-for-approval',
//     'controller' => 'RequestController',
//     'action' => 'submittedForApproval'
//    ]
//);
//$config = $route->addNewRoute(
//    $config,
//    ['route' => '/request/view-employee-requests',
//     'controller' => 'RequestController',
//     'action' => 'viewEmployeeRequests'
//    ]
//);
//$config = $route->addNewRoute(
//    $config,
//    ['route' => '/request/view-my-team-calendar',
//     'controller' => 'RequestController',
//     'action' => 'viewMyTeamCalendar'
//    ]
//);
//$config = $route->addNewRoute(
//    $config,
//    ['name' => 'create',
//     'route' => '/request/create',
//     'controller' => 'RequestController',
//     'action' => 'create'
//    ]
//);
//$config = $route->addNewRoute(
//    $config,
//    ['name' => 'create2',
//     'route' => '/request/create',
//     'controller' => 'RequestController',
//     'action' => 'create'
//    ]
//);
//$config = $route->addNewRoute(
//    $config,
//    ['route' => '/request/api',
//     'controller' => 'RequestController',
//     'action' => 'api'
//    ]
//);
//$config = $route->addNewRoute(
//    $config,
//    ['route' => '/request/view-my-requests',
//     'controller' => 'RequestController',
//     'action' => 'viewMyRequests'
//    ]
//);
//$config = $route->addNewRoute(
//    $config,
//    ['type' => 'segment',
//     'route' => '/request/review-request/[:request_id]',
//     'controller' => 'RequestController',
//     'action' => 'reviewRequest'
//    ]
//);
//$config = $route->addNewRoute(
//    $config,
//    ['type' => 'segment',
//     'route' => '/request/build-papaa-test/[:request_id]',
//     'controller' => 'RequestController',
//     'action' => 'buildPapaaTest'
//    ]
//);
//$config = $route->addNewRoute(
//    $config,
//    ['route' => '/request/approved-request',
//     'controller' => 'RequestController',
//     'action' => 'approvedRequest'
//    ]
//);
//$config = $route->addNewRoute(
//    $config,
//    ['route' => '/request/denied-request',
//     'controller' => 'RequestController',
//     'action' => 'deniedRequest'
//    ]
//);
//$config = $route->addNewRoute(
//    $config,
//    ['type' => 'segment',
//     'route' => '/request/test/[:employee_number]',
//     'controller' => 'RequestController',
//     'action' => 'test'
//    ]
//);
//
//echo '<pre>';
//print_r( $config );
//echo '</pre>';
//die();

return $config;

/**
 * [router] => Array
        (
            [routes] => Array
                (
                    [viewEmployeeRequests] => Array
                        (
                            [type] => Zend\Mvc\Router\Http\Literal
                            [options] => Array
                                (
                                    [route] => /request/view-employee-requests
                                    [defaults] => Array
                                        (
                                            [controller] => RequestController
                                            [action] => viewEmployeeRequests
                                        )

                                )

                            [may_terminate] => 1
                            [child_routes] => Array
                                (
                                )

                        )

                    [home] => Array
                        (
                            [type] => Zend\Mvc\Router\Http\Literal
                            [options] => Array
                                (
                                    [route] => /request/view-my-requests
                                    [defaults] => Array
                                        (
                                            [controller] => RequestController
                                            [action] => viewMyRequests
                                        )

                                )

                            [may_terminate] => 1
                            [child_routes] => Array
                                (
                                )

                        )

                    [success] => Array
                        (
                            [type] => Zend\Mvc\Router\Http\Literal
                            [options] => Array
                                (
                                    [route] => /request/submitted-for-approval
                                    [defaults] => Array
                                        (
                                            [controller] => RequestController
                                            [action] => submittedForApproval
                                        )

                                )

                            [may_terminate] => 1
                            [child_routes] => Array
                                (
                                )

                        )

                    [viewMyTeamCalendar] => Array
                        (
                            [type] => Zend\Mvc\Router\Http\Literal
                            [options] => Array
                                (
                                    [route] => /request/view-my-team-calendar
                                    [defaults] => Array
                                        (
                                            [controller] => RequestController
                                            [action] => viewMyTeamCalendar
                                        )

                                )

                            [may_terminate] => 1
                            [child_routes] => Array
                                (
                                )

                        )

                    [create] => Array
                        (
                            [type] => Zend\Mvc\Router\Http\Literal
                            [options] => Array
                                (
                                    [route] => /request/create
                                    [defaults] => Array
                                        (
                                            [controller] => RequestController
                                            [action] => create
                                        )

                                )

                            [may_terminate] => 1
                            [child_routes] => Array
                                (
                                )

                        )

                    [create2] => Array
                        (
                            [type] => Zend\Mvc\Router\Http\Literal
                            [options] => Array
                                (
                                    [route] => /request/create
                                    [defaults] => Array
                                        (
                                            [controller] => RequestController
                                            [action] => create
                                        )

                                )

                            [may_terminate] => 1
                            [child_routes] => Array
                                (
                                )

                        )

                    [api] => Array
                        (
                            [type] => Zend\Mvc\Router\Http\Literal
                            [options] => Array
                                (
                                    [route] => /request/api
                                    [defaults] => Array
                                        (
                                            [controller] => RequestController
                                            [action] => api
                                        )

                                )

                            [may_terminate] => 1
                            [child_routes] => Array
                                (
                                )

                        )

                    [viewMyRequests] => Array
                        (
                            [type] => Zend\Mvc\Router\Http\Literal
                            [options] => Array
                                (
                                    [route] => /request/view-my-requests
                                    [defaults] => Array
                                        (
                                            [controller] => RequestController
                                            [action] => viewMyRequests
                                        )

                                )

                            [may_terminate] => 1
                            [child_routes] => Array
                                (
                                )

                        )

                    [reviewRequest] => Array
                        (
                            [type] => segment
                            [options] => Array
                                (
                                    [route] => /request/review-request/[:request_id]
                                    [defaults] => Array
                                        (
                                            [controller] => RequestController
                                            [action] => reviewRequest
                                        )

                                )

                            [may_terminate] => 1
                            [child_routes] => Array
                                (
                                )

                        )

                    [buildPapaaTest] => Array
                        (
                            [type] => segment
                            [options] => Array
                                (
                                    [route] => /request/build-papaa-test/[:request_id]
                                    [defaults] => Array
                                        (
                                            [controller] => RequestController
                                            [action] => buildPapaaTest
                                        )

                                )

                            [may_terminate] => 1
                            [child_routes] => Array
                                (
                                )

                        )

                    [approvedRequest] => Array
                        (
                            [type] => Zend\Mvc\Router\Http\Literal
                            [options] => Array
                                (
                                    [route] => /request/approved-request
                                    [defaults] => Array
                                        (
                                            [controller] => RequestController
                                            [action] => approvedRequest
                                        )

                                )

                            [may_terminate] => 1
                            [child_routes] => Array
                                (
                                )

                        )

                    [deniedRequest] => Array
                        (
                            [type] => Zend\Mvc\Router\Http\Literal
                            [options] => Array
                                (
                                    [route] => /request/denied-request
                                    [defaults] => Array
                                        (
                                            [controller] => RequestController
                                            [action] => deniedRequest
                                        )

                                )

                            [may_terminate] => 1
                            [child_routes] => Array
                                (
                                )

                        )

                    [test] => Array
                        (
                            [type] => segment
                            [options] => Array
                                (
                                    [route] => /request/test/[:employee_number]
                                    [defaults] => Array
                                        (
                                            [controller] => RequestController
                                            [action] => test
                                        )

                                )

                            [may_terminate] => 1
                            [child_routes] => Array
                                (
                                )

                        )

                )

        )
 */