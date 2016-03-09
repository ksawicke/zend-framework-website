<?php

/**
 * CalendarApi.php
 *
 * Calendar API
 *
 * API Handler for queue data
 *
 * PHP version 5
 *
 * @package    Application\API\CalendarApi
 * @author     Kevin Sawicke <kevin_sawicke@swifttrans.com>
 * @copyright  2016 Swift Transportation
 * @version    GIT: $Id$ In development
 */

namespace Application\API;

use Zend\View\Model\JsonModel;
use Request\Model\Employee;

/**
 *
 * @author sawik
 *
 */
class CalendarApi extends ApiController {

    public $invalidRequestDates = [
        'before' => '',
        'after' => '',
        'individual' => [ ]
    ];

    public function __construct() {
        // Disable dates starting with one month ago and any date before.
        $this->invalidRequestDates['before'] = date( "m/d/Y", strtotime( "-1 month", strtotime( date( "m/d/Y" ) ) ) );

        // Disable dates starting with the following date.
        $this->invalidRequestDates['after'] = date( "m/d/Y", strtotime( "+1 year", strtotime( date( "m/d/Y" ) ) ) );

        // Disable any dates in this array
        $this->invalidRequestDates['individual'] = [
            '12/25/2015',
            '01/01/2016',
            '05/30/2016',
            '07/04/2016',
            '09/05/2016',
            '11/24/2016',
            '12/26/2016',
            '01/02/2017'
        ];
    }

    public function loadCalendarAction() {
        $request = $this->getRequest();
        $startDate = $request->getPost()->startYear . "-" . $request->getPost()->startMonth . "-01";
        $endDate = date( "Y-m-t", strtotime( $startDate ) );
        $employeeNumber = (is_null( $request->getPost()->employeeNumber ) ? trim( $this->employeeNumber ) : trim( $request->getPost()->employeeNumber ));

        \Request\Helper\Calendar::setCalendarHeadings( ['S', 'M', 'T', 'W', 'T', 'F', 'S' ] );
        \Request\Helper\Calendar::setBeginWeekOne( '<tr class="calendar-row" style="height:40px;">' );
        \Request\Helper\Calendar::setBeginCalendarRow( '<tr class="calendar-row" style="height:40px;">' );
        \Request\Helper\Calendar::setInvalidRequestDates( $this->invalidRequestDates );
        $calendarDates = \Request\Helper\Calendar::getDatesForThreeCalendars( $request->getPost()->startYear, $request->getPost()->startMonth );

        $Employee = new \Request\Model\Employee();
        $employeeData = $Employee->findTimeOffEmployeeData( $employeeNumber, "Y" );
        $requestData = $Employee->findTimeOffRequestData( $employeeNumber, $calendarDates );
        
        $dates = [];
        foreach( $calendarDates as $timeFrame => $timeObject ) {
            $dates[$timeFrame] = $timeObject->format( "Y-m-d" );
        }
        
        $calendarData = $Employee->findTimeOffCalendarByEmployeeNumber( $employeeNumber, $startDate, $dates['twoMonthsOut'] );
//                    echo '<pre>';
//                    print_r($calendarData);
//                    echo '</pre>';
//                    die("@@@");

        $result = new JsonModel( [
            'success' => true,
            'calendarData' => \Request\Helper\Calendar::getThreeCalendars( $request->getPost()->startYear, $request->getPost()->startMonth, $calendarData ),
            'employeeData' => $employeeData,
            'requestData' => $requestData,
            'test' => $calendarData,
            'loggedInUser' => ['isManager' => \Login\Helper\UserSession::getUserSessionVariable( 'IS_MANAGER' ),
                'isPayroll' => \Login\Helper\UserSession::getUserSessionVariable( 'IS_PAYROLL' )
            ]
                ] );

        return $result;
    }

}
