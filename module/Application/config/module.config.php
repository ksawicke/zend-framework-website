<?php
/**
 * Zend Framework (http://framework.zend.com/]
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c] 2005-2015 Zend Technologies USA Inc. (http://www.zend.com]
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

return [
    'router' => [
        'routes' => [
            
            'loadCalendar' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/calendar/get',
                    'defaults' => [
                        'controller' => 'Application\API\CalendarApi',
                        'action' => 'loadCalendar'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],
            
            'submitTimeoffRequest' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/request',
                    'defaults' => [
                        'controller' => 'Application\API\RequestApi',
                        'action' => 'submitTimeoffRequest'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],
            
            'submitApprovalResponse' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/request/approve',
                    'defaults' => [
                        'controller' => 'Application\API\RequestApi',
                        'action' => 'submitApprovalResponse'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],
            
            'submitDenyResponse' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/request/deny',
                    'defaults' => [
                        'controller' => 'Application\API\RequestApi',
                        'action' => 'submitDenyResponse'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],
            
            'getSearchResults' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/search/[:search-type]',
                    'defaults' => [
                        'controller' => 'Application\API\SearchApi',
                        'action' => 'getSearchResults'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],
            
            'getManagerQueue' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/queue/manager/[:manager-queue]',
                    'defaults' => [
                        'controller' => 'Application\API\QueueApi',
                        'action' => 'getManagerQueue'
                    ],
                ],
            ],
            'getPayrollQueue' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/queue/payroll/[:payroll-queue]',
                    'defaults' => [
                        'controller' => 'Application\API\QueueApi',
                        'action' => 'getPayrollQueue'
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
            'Application\API\QueueApi' => API\QueueApi::class,
            'Application\API\SearchApi' => API\SearchApi::class,
            'Application\API\CalendarApi' => API\CalendarApi::class,
            'Application\API\RequestApi' => API\RequestApi::class
        ]
    ],
    'service_manager' => [
        'abstract_factories' => [
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ],
        'factories' => [
            'translator' => 'Zend\Mvc\Service\TranslatorServiceFactory',
        ],
        'invokables' => [
            'login-form' => 'Application\\Form\\Login',
            'request-time-form' => 'Application\\Form\\RequestTime',
            'approve-time-form' => 'Application\\Form\\ApproveTime'
        ]
    ],
    'translator' => [
        'locale' => 'en_US',
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],
//    'controllers' => [
//        'invokables' => [
//            'Application\Controller\Index' => Controller\IndexController::class
//        ],
//        'factories' => [
//            'Application\Controller\Login' => 'Application\\Factory\\LoginControllerFactory',
//        ]
//    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            // Main application template:
            'layout/layout'           => __DIR__ . '/../view/layout/swiftit_bootstrap.phtml',
//            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            // Home page template:
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    // Placeholder for console routes
    'console' => [
        'router' => [
            'routes' => [
            ],
        ],
    ],
];
