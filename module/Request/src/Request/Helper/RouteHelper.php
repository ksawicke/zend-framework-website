<?php

namespace Request\Helper;

class RouteHelper {
    
    public function addNewRoute($currentConfig, $routeOptions)
    {
        $name = (array_key_exists('name', $routeOptions) ? $routeOptions['name'] : $routeOptions['action']);
        $action = $routeOptions['action'];
        $type = (array_key_exists('type', $routeOptions) ? $routeOptions['type'] : 'Zend\Mvc\Router\Http\Literal');
        $currentConfig['router']['routes'][$name] = [
            'type' => $type, // Zend\Mvc\Router\Http\Literal or literal
            'options' => [
                'route' => $routeOptions['route'],
                'defaults' => [
                    'controller' => $routeOptions['controller'],
                    'action' => $routeOptions['action']
                ]
            ],
            'may_terminate' => true,
            'child_routes' => []
        ];
                
        return $currentConfig;
    }
    
}