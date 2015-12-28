<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Login;

return [
    'controllers' => [
        'invokables' => [
            'Login\Controller\Login' => 'Login\Controller\LoginController'
        ],
    ],
    'router' => [
        'routes' => [
            'login' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/login/index',
                    'defaults' => [
                        'controller' => 'Login\Controller\Login',
                        'action' => 'index'
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
