<?php

namespace Request\Helper;

class RouteHelper {
    
    public function addNewRoute($currentConfig, $routeOptions)
    {
        $action = $routeOptions['action'];
        $currentConfig['router']['routes'][$action] = [
            'type' => 'literal',
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