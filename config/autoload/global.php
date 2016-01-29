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
    'development' => 'SAWIK HRTEST HRDBFA TRDBFA HRCUST IESR7FILE HR2000 TR2000 AM2000 KADIRI IESR7CSTM MLCTRL MLLIBR IESR7', // SAWIK HRTEST HRDBFA TRDBFA HRCUST IESR7FILE HR2000 TR2000 AM2000 KADIRI IESR7CSTM MLCTRL MLLIBR IESR7
    'production'  => 'HRDBFA TRDBFA HRCUST IESR7FILE HR2000 TR2000 AM2000 KADIRI IESR7CSTM MLCTRL MLLIBR IESR7'
];

return [
     'db' => [
        'driver' => 'IbmDb2',
        'database' => '*LOCAL',
        'username' => 'PHPUSER',
        'password' => 'SWIFT123',
        'driver_options' => [
            'i5_naming' => DB2_I5_NAMING_ON,
            'i5_libl' => $libraryLists[ENVIRONMENT]
        ],
        'platform_options' => ['quote_identifiers' => false]
     ],
     'service_manager' => [
         'factories' => [
//            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
            'Zend\Db\Adapter\Adapter' => function ($serviceManager) {
                $adapterFactory = new Zend\Db\Adapter\AdapterServiceFactory();
                $adapter = $adapterFactory->createService($serviceManager);
                // Set static Adapter, to be used in base DB Class
                \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter($adapter);

                return $adapter;
            }
         ]
     ]
 ];
