<?php

error_reporting(E_ALL);

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

$currentSystem = $_SERVER['SERVER_NAME'];
$currentPath = getcwd();

if (trim($currentSystem) == 'swift') {
    switch ($currentPath) {
        case '/www/zendsvr6/htdocs/timeoff':
            define('ENVIRONMENT', 'production');
            break;
        default:
            define('ENVIRONMENT', 'development');
            break;
    }
} else {
    define('ENVIRONMENT', 'production');
}

if (ENVIRONMENT == 'development') {
     error_reporting(E_ALL);
     ini_set("display_errors", 1);
}

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (__FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}

// Setup autoloading
require 'init_autoloader.php';

//require zend_deployment_library_path('SwiftUtils') . '/Logging/Logger.php';

// USE:
// \SwiftIT\Logging\Logger::logError("NO MORE TACOS", "error", "/path/to/some/file/somewhere.log");

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
