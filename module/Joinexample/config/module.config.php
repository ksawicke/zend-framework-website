<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Joinexample\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'lettergroup' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/lettergroup',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Joinexample\Controller',
                        'controller'    => 'LetterGroup',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '[/:action]',
                            'constraints' => array(
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                'controller'    => 'LetterGroup',
                                'action'        => 'index',
                            ),
                        ),
                    ),
                ),
            ),
            'lettermonitor' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/lettermonitor',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Joinexample\Controller',
                        'controller'    => 'LetterMonitor',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '[/:action][/page/:page]',
                            'constraints' => array(
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'page'       => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller'    => 'LetterMonitor',
                                'action'        => 'index',
                            ),
                        ),
                    ),
                ),
            ),
            'display-join' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/letter-detail',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Joinexample\Controller',
                        'controller' => 'LetterGroup',
                        'action'     => 'letter-detail',
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'invokables' => [
            'letter-group-search-form' => 'Joinexample\\Form\\LetterGroupSearchForm',
            'letter-monitor-search-form' => 'Joinexample\\Form\\LetterMonitorSearchForm',
        ],
        'factories' => [
            'letter-group-model' => 'Joinexample\\Factory\\LetterGroupModelFactory',
            'letter-monitor-model' => 'Joinexample\\Factory\\LetterMonitorModelFactory',
        ],
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'factories' => [
            'Joinexample\Controller\LetterGroup' => 'Joinexample\Factory\LetterGroupControllerFactory',
            'Joinexample\Controller\LetterMonitor' => 'Joinexample\Factory\LetterMonitorControllerFactory',
        ],
        'invokables' => array(
            'Joinexample\Controller\Index' => 'Joinexample\Controller\IndexController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
