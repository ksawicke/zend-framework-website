<?php
/**
 * Zend Framework (http://framework.zend.com/]
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c] 2005-2015 Zend Technologies USA Inc. (http://www.zend.com]
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\Router\Http\Literal;

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

            'loadCalendarManagerEmployees' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/calendar/get/manager-employees',
                    'defaults' => [
                        'controller' => 'Application\API\CalendarApi',
                        'action' => 'loadCalendarManagerEmployees'
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

            'getEmployeeProfile' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/request/get-employee-profile',
                    'defaults' => [
                        'controller' => 'Application\API\RequestApi',
                        'action' => 'getEmployeeProfile'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'getEmailOverrideSettings' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/request/get-email-override-settings',
                    'defaults' => [
                        'controller' => 'Application\API\RequestApi',
                        'action' => 'getEmailOverrideSettings'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'editEmailOverrideSettings' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/request/edit-email-override-settings',
                    'defaults' => [
                        'controller' => 'Application\API\RequestApi',
                        'action' => 'editEmailOverrideSettings'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'toggleCalendarInvite' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/request/calendar-invite-toggle',
                    'defaults' => [
                        'controller' => 'Application\API\RequestApi',
                        'action' => 'toggleCalendarInvite'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'getCompanyHolidays' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/request/company-holidays',
                    'defaults' => [
                        'controller' => 'Application\API\RequestApi',
                        'action' => 'getCompanyHolidays'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'addCompanyHoliday' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/request/add-company-holiday',
                    'defaults' => [
                        'controller' => 'Application\API\RequestApi',
                        'action' => 'addCompanyHoliday'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'deleteCompanyHoliday' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/request/delete-company-holiday',
                    'defaults' => [
                        'controller' => 'Application\API\RequestApi',
                        'action' => 'deleteCompanyHoliday'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            /**
             * Handle Proxies - people authorized to submit
             * on behalf of someone else.
             */
            'get_supervisor_proxies_data_table' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/get_supervisor_proxies_data_table',
                    'defaults' => [
                        'controller' => 'Application\API\ProxyApi',
                        'action' => 'getSupervisorProxiesDataTable'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],
            'loadProxies' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/proxy/get',
                    'defaults' => [
                        'controller' => 'Application\API\ProxyApi',
                        'action' => 'loadProxies'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'deleteProxy' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/proxy/delete',
                    'defaults' => [
                        'controller' => 'Application\API\ProxyApi',
                        'action' => 'deleteProxy'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'toggleProxy' => [
            'type' => 'segment',
                'options' => [
                    'route' => '/api/proxy/toggle',
                    'defaults' => [
                        'controller' => 'Application\API\ProxyApi',
                        'action' => 'toggleProxy'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'submitProxyRequest' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/proxy',
                    'defaults' => [
                        'controller' => 'Application\API\ProxyApi',
                        'action' => 'submitProxyRequest'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            /**
             * Payroll Assistants
             */

            'loadPayrollAssistants' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/payroll-assistants/get',
                    'defaults' => [
                        'controller' => 'Application\API\PayrollAssistantApi',
                        'action' => 'loadPayrollAssistants'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'deletePayrollAssistant' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/payroll-assistant/delete',
                    'defaults' => [
                        'controller' => 'Application\API\PayrollAssistantApi',
                        'action' => 'deletePayrollAssistant'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'togglePayrollAssistant' => [
            'type' => 'segment',
                'options' => [
                    'route' => '/api/payroll-assistant/toggle',
                    'defaults' => [
                        'controller' => 'Application\API\PayrollAssistantApi',
                        'action' => 'togglePayrollAssistant'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'submitPayrollAssistantRequest' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/payroll-assistant',
                    'defaults' => [
                        'controller' => 'Application\API\PayrollAssistantApi',
                        'action' => 'submitPayrollAssistantRequest'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            /**
             * Payroll Admins
             */

            'loadPayrollAdmins' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/payroll-admins/get',
                    'defaults' => [
                        'controller' => 'Application\API\PayrollAdminApi',
                        'action' => 'loadPayrollAdmins'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'deletePayrollAdmin' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/payroll-admin/delete',
                    'defaults' => [
                        'controller' => 'Application\API\PayrollAdminApi',
                        'action' => 'deletePayrollAdmin'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'togglePayrollAdmin' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/payroll-admin/toggle',
                    'defaults' => [
                        'controller' => 'Application\API\PayrollAdminApi',
                        'action' => 'togglePayrollAdmin'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'submitPayrollAdminRequest' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/payroll-admin',
                    'defaults' => [
                        'controller' => 'Application\API\PayrollAdminApi',
                        'action' => 'submitPayrollAdminRequest'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            /**
             * Change Employee Schedule
             */

            'submitEmployeeScheduleRequest' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/api/employee-schedule',
                    'defaults' => [
                        'controller' => 'Application\API\RequestApi',
                        'action' => 'submitEmployeeScheduleRequest'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'submitManagerApproved' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/request/manager-approved',
                    'defaults' => [
                        'controller' => 'Application\API\RequestApi',
                        'action' => 'submitManagerApproved'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'submitManagerDenied' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/request/manager-denied',
                    'defaults' => [
                        'controller' => 'Application\API\RequestApi',
                        'action' => 'submitManagerDenied'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'submitPayrollApproved' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/request/payroll-approved',
                    'defaults' => [
                        'controller' => 'Application\API\RequestApi',
                        'action' => 'submitPayrollApproved'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'approveUpdateChecksRequest' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/request/approve-update-checks-request',
                    'defaults' => [
                        'controller' => 'Application\API\RequestApi',
                        'action' => 'approveUpdateChecksRequest'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'submitPayrollDenied' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/request/payroll-denied',
                    'defaults' => [
                        'controller' => 'Application\API\RequestApi',
                        'action' => 'submitPayrollDenied'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'submitPayrollUpload' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/request/payroll-upload',
                    'defaults' => [
                        'controller' => 'Application\API\RequestApi',
                        'action' => 'submitPayrollUpload'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            'submitPayrollUpdateChecks' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/request/payroll-update-checks',
                    'defaults' => [
                        'controller' => 'Application\API\RequestApi',
                        'action' => 'submitPayrollUpdateChecks'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            // payroll-modify-completed
            'submitPayrollModifyCompletedPAFs' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/request/payroll-modify-completed',
                    'defaults' => [
                        'controller' => 'Application\API\RequestApi',
                        'action' => 'submitPayrollModifyCompletedPAFs'
                    ]
                ],
                'may_terminate' => 1,
                'child_routes' => []
            ],

            // payroll-modify-comment
            'submitPayrollModifyComment' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/request/payroll-modify-comment',
                    'defaults' => [
                        'controller' => 'Application\API\RequestApi',
                        'action' => 'submitPayrollModifyComment'
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

            'getManagerActionEmailData' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/queue/manager/manager-action-email',
                    'defaults' => [
                        'controller' => 'Application\API\QueueApi',
                        'action' => 'getManagerActionEmailData'
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

            'sendThreeDayReminderEmailToSupervisor' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/api/scheduler/send_three_day_reminder_email_to_supervisor',
//                     'route'    => 'send_three_day_reminder_email_to_supervisor',
                    'defaults' => array(
                        'controller' => 'API\Scheduler\Controller',
                        'action'     => 'sendThreeDayReminderEmailToSupervisor',
                    ),
                ),
            ),

            'setPendingTransactionsToCompleted' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/api/scheduler/set_pending_transaction_to_completed',
//                     'route'    => 'set_pending_transaction_to_completed',
                    'defaults' => array(
                        'controller' => 'API\CLI\Controller',
                        'action'     => 'setRequestsToCompleted',
                    ),
                ),
            ),

            'updateUserSetting' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/api/update-user-setting',
//                     'route'    => 'set_pending_transaction_to_completed',
                    'defaults' => array(
                        'controller' => 'Application\API\UserSettingsApi',
                        'action'     => 'updateUserSettings',
                    ),
                ),
            ),

            'getUserSetting' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/api/get-user-settings',
//                     'route'    => 'set_pending_transaction_to_completed',
                    'defaults' => array(
                        'controller' => 'Application\API\UserSettingsApi',
                        'action'     => 'getUserSettings',
                    ),
                ),
            ),

        ],
    ],
    'controllers' => [
        'invokables' => [
            'Application\API\QueueApi' => API\QueueApi::class,
            'Application\API\SearchApi' => API\SearchApi::class,
            'Application\API\CalendarApi' => API\CalendarApi::class,
            'Application\API\RequestApi' => API\RequestApi::class,
            'Application\API\ProxyApi' => API\ProxyApi::class,
            'Application\API\PayrollAdminApi' => API\PayrollAdminApi::class,
            'Application\API\PayrollAssistantApi' => API\PayrollAssistantApi::class,
            'Application\API\UserSettingsApi' => API\UserSettingsApi::class
        ],
        'factories' => array(
            'API\Scheduler\Controller' => API\Scheduler\SchedulerFactory::class,
            'API\CLI\Controller' => API\CLI\CliFactory::class
        )
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
//                 'sendThreeDayReminderEmailToSupervisor' => array(
// //                     'type' => 'Zend\Mvc\Router\Http\Literal',
//                     'options' => array(
// //                         'route'    => '/api/scheduler/send_three_day_reminder_email_to_supervisor',
//                         'route'    => 'send_three_day_reminder_email_to_supervisor',
//                         'defaults' => array(
//                             'controller' => 'API\Scheduler\Controller',
//                             'action'     => 'sendThreeDayReminderEmailToSupervisor',
//                         ),
//                     ),
//                 ),
//                 'setPendingTransactionsToCompleted' => array(
// //                     'type' => 'Zend\Mvc\Router\Http\Literal',
//                     'options' => array(
// //                         'route'    => '/api/scheduler/set_pending_transaction_to_completed',
//                         'route'    => 'set_pending_transaction_to_completed',
//                         'defaults' => array(
//                             'controller' => 'API\CLI\Controller',
//                             'action'     => 'setRequestsToCompleted',
//                         ),
//                     ),
//                 ),
            ],
        ],
    ],
];
