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
     'controller' => 'Request\Controller\Request',
     'action' => 'viewMyRequests'
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
$config = $route->addNewRoute(
    $config,
    ['type' => 'segment',
     'route' => '/request/test/[:employee_number]',
     'controller' => 'Request\Controller\Request',
     'action' => 'test'
    ]
);

return $config;
