<?php

/**
 * Handles sending emails.
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
        $this->applicationLogFile = '/www/zendsvr6/htdocs/sawik/timeoff/timeoff_dev.log';
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