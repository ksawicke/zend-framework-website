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

$libraryLists = [
    'development' => 'HRTEST HRDBFA TRDBFA HRCUST IESR7FILE HR2000 TR2000 AM2000 KADIRI IESR7CSTM MLCTRL MLLIBR IESR7',
    'production'  => 'HRDBFA TRDBFA HRCUST IESR7FILE HR2000 TR2000 AM2000 KADIRI IESR7CSTM MLCTRL MLLIBR IESR7'
];

return array(
     'db' => array(
        'driver' => 'IbmDb2',
        'database' => '*LOCAL',
        'driver_options' => array(
            'i5_naming' => DB2_I5_NAMING_ON,
            'i5_libl' => $libraryLists['development']
        ),
        'platform_options' => array('quote_identifiers' => false)
     ),
     'service_manager' => array(
         'factories' => array(
             'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
         ),
     ),
 );
