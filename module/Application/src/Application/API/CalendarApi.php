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
        // Disable dates starting with the following date.
        $this->invalidRequestDates['before'] = $this->getEarliestRequestDate();

        // Disable dates starting with the following date.
        $this->invalidRequestDates['after'] = date( "Y-m-d", strtotime( "+1 year", strtotime( date( "m/d/Y" ) ) ) );

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
    
    /**
     * Allow Payroll to put in a request up to 6 months ago from today's date.
     * All other roles can go back 1 month.
     * 
     * @return date
     */
    public function getEarliestRequestDate()
    {
        $Employee = new \Request\Model\Employee();
        $isLoggedInUserPayroll = $Employee->isPayroll( $_SESSION['Timeoff_'.ENVIRONMENT]['EMPLOYEE_NUMBER'] );
        
        return ( $isLoggedInUserPayroll=="Y" ? date("m/d/Y", strtotime("-6 months", strtotime(date("m/d/Y"))))
                                              : date("m/d/Y", strtotime("-1 month", strtotime(date("m/d/Y")))) );
        
    }

    /**
     * Load three calendars and employee data.
     * 
     * @return JsonModel
     */
    public function loadCalendarAction() {
        $post = $this->getRequest()->getPost();
        $Employee = new \Request\Model\Employee();
        $Employee->ensureEmployeeScheduleIsDefined( $post->employeeNumber );
        $employeeData = $Employee->findEmployeeTimeOffData( $post->employeeNumber, "Y" );
        $startDate = $post->startYear . "-" . $post->startMonth . "-01";
        $endDate = date( "Y-m-t", strtotime( $startDate ) );
        $dates = [];
        $headers = [];
        $calendars = [];

        \Request\Helper\Calendar::setCalendarHeadings( ['S', 'M', 'T', 'W', 'T', 'F', 'S' ] );
        \Request\Helper\Calendar::setBeginWeekOne( '<tr class="calendar-row" style="height:40px;">' );
        \Request\Helper\Calendar::setBeginCalendarRow( '<tr class="calendar-row" style="height:40px;">' );
        \Request\Helper\Calendar::setInvalidRequestDates( $this->invalidRequestDates );
        $calendarDates = \Request\Helper\Calendar::getDatesForThreeCalendars( $post->startYear, $post->startMonth );
        $requestData = $Employee->findTimeOffRequestData( $post->employeeNumber, $calendarDates );
        
        foreach( $calendarDates as $timeFrame => $timeObject ) {
            $dates[$timeFrame] = $timeObject->format( "Y-m-d" );
        }
        
        $highlightDates = $Employee->findTimeOffCalendarByEmployeeNumber( $post->employeeNumber, $startDate, $dates['threeMonthsOut'] );
        $threeCalendars = \Request\Helper\Calendar::getThreeCalendars( $post->startYear, $post->startMonth, $highlightDates );
        
        foreach( $threeCalendars['calendars'] as $key => $calendar ) {
            $headers[$key] = $calendar['header'];
            $calendars[$key] = $calendar['data'];
        }
        
        foreach( $highlightDates as $key => $dateObject ) {
            // date( "m/d/Y", strtotime( $request['REQUEST_DATE'] ) )
            $highlightDates[$key]['REQUEST_DATE'] = date( "m/d/Y", strtotime( $dateObject['REQUEST_DATE'] ) );
        }
        
        $result = new JsonModel( [
            'success' => true,
            'employeeData' => $employeeData,
            'loggedInUser' => ['isManager' => \Login\Helper\UserSession::getUserSessionVariable( 'IS_MANAGER' ),
                'isPayroll' => \Login\Helper\UserSession::getUserSessionVariable( 'IS_PAYROLL' )
            ],
            'calendarData' => [
                'headers' => $headers,
                'calendars' => $calendars,
                'navigation' => [
                    'calendarNavigationFastRewind' => [ 'month' => $calendarDates['sixMonthsBack']->format( "m" ), 'year' => $calendarDates['sixMonthsBack']->format( "Y" ) ],
                    'calendarNavigationRewind' => [ 'month' => $calendarDates['threeMonthsBack']->format( "m" ), 'year' => $calendarDates['threeMonthsBack']->format( "Y" ) ],
                    'calendarNavigationForward' => [ 'month' => $calendarDates['threeMonthsOut']->format( "m" ), 'year' => $calendarDates['threeMonthsOut']->format( "Y" ) ],
                    'calendarNavigationFastForward' => [ 'month' => $calendarDates['sixMonthsOut']->format( "m" ), 'year' => $calendarDates['sixMonthsOut']->format( "Y" ) ],
                ],
                'highlightDates' => $highlightDates,
                'holidays' => $this->invalidRequestDates['individual']
            ]
        ] );

        return $result;
    }

}
