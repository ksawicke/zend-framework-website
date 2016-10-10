<?php

/**
 * Handles logging application errors.
 */

namespace Application\Factory;

/**
 * Log application errors
 *
 * @author sawik
 */
class Logger {

    public $applicationLogFile = '';

    public function __construct()
    {
        $applicationLogFile = '/www/zendsvr6/htdocs/timeoff/log/timeoff_log_' . date( 'Y-m-d') . '.log';
        if( !file_exists( $applicationLogFile ) ) {
            touch( $applicationLogFile );
        }
        $this->applicationLogFile = $applicationLogFile;
    }

    public function logEntry( $entryText = null )
    {
        error_log( '[' . $this->getTimestamp() . '] ' . $entryText . PHP_EOL, 3, $this->applicationLogFile );
    }

    public function getTimestamp()
    {
        return date( "d-M-Y h:i:s e" );
    }

}