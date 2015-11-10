<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
     'db' => array(
        'driver' => 'IbmDb2',
        'database' => '*LOCAL',
        'driver_options' => array(
            'i5_naming' => DB2_I5_NAMING_ON,
            'i5_libl' => 'SAWIK'
        ),
        'platform_options' => array('quote_identifiers' => false)
     ),
     'service_manager' => array(
         'factories' => array(
             'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
         ),
     ),
 );
