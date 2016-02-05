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
        'routes' => []
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
     'route' => '/request/view-my-requests',
     'controller' => 'RequestController',
     'action' => 'viewMyRequests'
    ]
);
$config = $route->addNewRoute(
    $config,
    ['name' => 'success',
     'route' => '/request/submitted-for-approval',
     'controller' => 'RequestController',
     'action' => 'submittedForApproval'
    ]
);
$config = $route->addNewRoute(
    $config,
    ['route' => '/request/view-employee-requests',
     'controller' => 'RequestController',
     'action' => 'viewEmployeeRequests'
    ]
);
$config = $route->addNewRoute(
    $config,
    ['route' => '/request/view-my-team-calendar',
     'controller' => 'RequestController',
     'action' => 'viewMyTeamCalendar'
    ]
);
$config = $route->addNewRoute(
    $config,
    ['name' => 'create',
     'route' => '/request/create',
     'controller' => 'RequestController',
     'action' => 'create'
    ]
);
$config = $route->addNewRoute(
    $config,
    ['name' => 'create2',
     'route' => '/request/create',
     'controller' => 'RequestController',
     'action' => 'create'
    ]
);
$config = $route->addNewRoute(
    $config,
    ['route' => '/request/api',
     'controller' => 'RequestController',
     'action' => 'api'
    ]
);
$config = $route->addNewRoute(
    $config,
    ['route' => '/request/view-my-requests',
     'controller' => 'RequestController',
     'action' => 'viewMyRequests'
    ]
);
$config = $route->addNewRoute(
    $config,
    ['type' => 'segment',
     'route' => '/request/review-request/[:request_id]',
     'controller' => 'RequestController',
     'action' => 'reviewRequest'
    ]
);
$config = $route->addNewRoute(
    $config,
    ['route' => '/request/approved-request',
     'controller' => 'RequestController',
     'action' => 'approvedRequest'
    ]
);
$config = $route->addNewRoute(
    $config,
    ['route' => '/request/denied-request',
     'controller' => 'RequestController',
     'action' => 'deniedRequest'
    ]
);
$config = $route->addNewRoute(
    $config,
    ['type' => 'segment',
     'route' => '/request/test/[:employee_number]',
     'controller' => 'RequestController',
     'action' => 'test'
    ]
);

return $config;
