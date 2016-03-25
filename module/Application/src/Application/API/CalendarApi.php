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
        $this->invalidRequestDates['before'] = date( "Y-m-d", strtotime( "-1 month", strtotime( date( "m/d/Y" ) ) ) );

        // Disable dates starting with the following date.
        $this->invalidRequestDates['after'] = date( "Y-m-d", strtotime( "+1 year", strtotime( date( "m/d/Y" ) ) ) );

        // Disable any dates in this array
        $this->invalidRequestDates['individual'] = [
            '2015-12-25',
            '2016-01-01',
            '2016-05-30',
            '2016-07-04',
            '2016-09-05',
            '2016-11-24',
            '2016-12-26',
            '2017-01-02'
        ];
    }

    public function loadCalendarAction() {
        $post = $this->getRequest()->getPost();
//        $request = $this->getRequest();
        $startDate = $post->startYear . "-" . $post->startMonth . "-01";
        $endDate = date( "Y-m-t", strtotime( $startDate ) );
        $employeeNumber = $post->employeeNumber;

        \Request\Helper\Calendar::setCalendarHeadings( ['S', 'M', 'T', 'W', 'T', 'F', 'S' ] );
        \Request\Helper\Calendar::setBeginWeekOne( '<tr class="calendar-row" style="height:40px;">' );
        \Request\Helper\Calendar::setBeginCalendarRow( '<tr class="calendar-row" style="height:40px;">' );
        \Request\Helper\Calendar::setInvalidRequestDates( $this->invalidRequestDates );
        $calendarDates = \Request\Helper\Calendar::getDatesForThreeCalendars( $post->startYear, $post->startMonth );

        $Employee = new \Request\Model\Employee();
        $Employee->ensureEmployeeScheduleIsDefined( $employeeNumber );
        $employeeData = $Employee->findEmployeeTimeOffData( $employeeNumber, "Y" );
        
        $requestData = $Employee->findTimeOffRequestData( $employeeNumber, $calendarDates );
        
        $dates = [];
        foreach( $calendarDates as $timeFrame => $timeObject ) {
            $dates[$timeFrame] = $timeObject->format( "Y-m-d" );
        }
        
        $calendarData = $Employee->findTimeOffCalendarByEmployeeNumber( $employeeNumber, $startDate, $dates['threeMonthsOut'] );

        $threeCalendars = \Request\Helper\Calendar::getThreeCalendars( $post->startYear, $post->startMonth, $calendarData );
        
        $headers = [];
        $calendars = [];
        $navigation = [];
        
        foreach( $threeCalendars['calendars'] as $key => $calendar ) {
            $headers[$key] = $calendar['header'];
            $calendars[$key] = $calendar['data'];
        }
        $navigation = [
            'calendarNavigationFastRewind' => [ 'month' => $calendarDates['sixMonthsBack']->format( "m" ), 'year' => $calendarDates['sixMonthsBack']->format( "Y" ) ],
            'calendarNavigationRewind' => [ 'month' => $calendarDates['threeMonthsBack']->format( "m" ), 'year' => $calendarDates['threeMonthsBack']->format( "Y" ) ],
            'calendarNavigationForward' => [ 'month' => $calendarDates['threeMonthsOut']->format( "m" ), 'year' => $calendarDates['threeMonthsOut']->format( "Y" ) ],
            'calendarNavigationFastForward' => [ 'month' => $calendarDates['sixMonthsOut']->format( "m" ), 'year' => $calendarDates['sixMonthsOut']->format( "Y" ) ],
        ];
        
        $result = new JsonModel( [
            'success' => true,
            'calendarData' => \Request\Helper\Calendar::getThreeCalendars( $post->startYear, $post->startMonth, $calendarData ),
            'employeeData' => $employeeData,
            'loggedInUser' => ['isManager' => \Login\Helper\UserSession::getUserSessionVariable( 'IS_MANAGER' ),
                'isPayroll' => \Login\Helper\UserSession::getUserSessionVariable( 'IS_PAYROLL' )
            ],
            'newCalendarData' => [
                'headers' => $headers,
                'calendars' => $calendars,
                'navigation' => $navigation
            ]
        ] );

        return $result;
    }

}
