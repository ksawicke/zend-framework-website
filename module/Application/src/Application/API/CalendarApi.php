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
        $this->invalidRequestDates['individual'] = $this->getCompanyHolidays();
    }

    /**
     * Gets a list of company holidays.
     *
     * @return date
     */
    public function getCompanyHolidays()
    {
        $TimeOffRequests = new \Request\Model\TimeOffRequests();
        $companyHolidays = $TimeOffRequests->getCompanyHolidays();

        return $companyHolidays;
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

    public function loadCalendarManagerEmployeesAction() {
        $post = $this->getRequest()->getPost();
        $Employee = new \Request\Model\Employee();
        $Employee->ensureEmployeeScheduleIsDefined( $post->employeeNumber );
        $employeeData = $Employee->findEmployeeTimeOffData( $post->employeeNumber, "Y" );
        $startYear = null;
        $startMonth = null;

        if( empty( $post->calendarsToLoad ) ) {
            $post->calendarsToLoad = 1;
        }

        $startYear = $post->startYear;
        $startMonth = $post->startMonth;

        $startDate = date( "Y-m-d", strtotime( $startYear . "-" . $startMonth . "-01" ) );
        $dates = [];
        $headers = [];
        $calendars = [];

        \Request\Helper\Calendar::setCalendarHeadings( ['S', 'M', 'T', 'W', 'T', 'F', 'S' ] );
        \Request\Helper\Calendar::setBeginWeekOne( '<tr class="calendar-row" style="height:40px;">' );
        \Request\Helper\Calendar::setBeginCalendarRow( '<tr class="calendar-row" style="height:40px;">' );
        \Request\Helper\Calendar::setInvalidRequestDates( $this->invalidRequestDates );

        $calendarDates = \Request\Helper\Calendar::getDatesForOneCalendar( $startYear, $startMonth );
        foreach( $calendarDates as $timeFrame => $timeObject ) {
            $dates[$timeFrame] = $timeObject->format( "Y-m-d" );
        }
        $endDate = date("m/d/Y", strtotime("-1 day", strtotime($dates['oneMonthOut'])));

        $Employee = new \Request\Model\Employee();

        $calendarDateTextData = $Employee->findTimeOffCalendarByManager( '002', $post->employeeNumber, $post->managerReportsType, $startDate, $endDate );
        \Request\Helper\Calendar::setCalendarDateTextToAppend( $calendarDateTextData );

        $calendar1Html = \Request\Helper\Calendar::drawCalendar( $startMonth, $startYear, [] );

        $calendarDates = \Request\Helper\Calendar::getDatesForOneCalendar( $startYear, $startMonth );

        foreach( $calendarDates as $timeFrame => $timeObject ) {
            $dates[$timeFrame] = $timeObject->format( "Y-m-d" );
        }

        $highlightDates = $Employee->findTimeOffCalendarByEmployeeNumber( $post->employeeNumber, $startDate,
            ( $post->calendarsToLoad==3 ? $dates['threeMonthsOut'] : $dates['oneMonthOut'] ), $post->requestId );

        $threeCalendars = \Request\Helper\Calendar::getOneCalendar( $startYear, $startMonth, $highlightDates, $post->requestId );
        $navigation = [ 'calendarNavigationFastRewind' => [ 'month' => $calendarDates['threeMonthsBack']->format( "m" ), 'year' => $calendarDates['threeMonthsBack']->format( "Y" ) ],
            'calendarNavigationRewind' => [ 'month' => $calendarDates['oneMonthBack']->format( "m" ), 'year' => $calendarDates['oneMonthBack']->format( "Y" ) ],
            'calendarNavigationForward' => [ 'month' => $calendarDates['oneMonthOut']->format( "m" ), 'year' => $calendarDates['oneMonthOut']->format( "Y" ) ],
            'calendarNavigationFastForward' => [ 'month' => $calendarDates['threeMonthsOut']->format( "m" ), 'year' => $calendarDates['threeMonthsOut']->format( "Y" ) ],
        ];

        foreach( $threeCalendars['calendars'] as $key => $calendar ) {
            $headers[$key] = $calendar['header'];
            $calendars[$key] = $calendar['data'];
        }

        foreach( $highlightDates as $key => $dateObject ) {
            $highlightDates[$key]['REQUEST_DATE'] = date( "m/d/Y", strtotime( $dateObject['REQUEST_DATE'] ) );
        }

        $endDate = ( $post->calendarsToLoad==3 ? $dates['threeMonthsOut'] : $dates['oneMonthOut'] );
        $result = new JsonModel( [
            'success' => true,
            'employeeData' => $employeeData,
            'loggedInUserData' => [ 'isManager' => \Login\Helper\UserSession::getUserSessionVariable( 'IS_MANAGER' ),
                'isSupervisor' => \Login\Helper\UserSession::getUserSessionVariable( 'IS_SUPERVISOR' ),
                'isPayroll' => \Login\Helper\UserSession::getUserSessionVariable( 'IS_PAYROLL' ),
                'isPayrollAdmin' => \Login\Helper\UserSession::getUserSessionVariable( 'IS_PAYROLL_ADMIN' ),
                'isPayrollAssistant' => \Login\Helper\UserSession::getUserSessionVariable( 'IS_PAYROLL_ASSISTANT' ),
                'isProxy' => \Login\Helper\UserSession::getUserSessionVariable( 'IS_PROXY' ),
                'isProxyForManager' => \Login\Helper\UserSession::getUserSessionVariable( 'IS_PROXY_FOR_MANAGER' )
            ],
            'proxyFor' => ( \Login\Helper\UserSession::getUserSessionVariable( 'IS_PROXY' )==="Y" ?
                $Employee->findProxiesByEmployeeNumber( $post->employeeNumber ) :
                []
                ),
            'calendarData' => [
                'headers' => $headers,
                'calendars' => $calendars,
                'calendar1Html' => $calendar1Html,
                'navigation' => $navigation,
                'highlightDates' => $highlightDates,
                'holidays' => $this->invalidRequestDates['individual'],
                'startDate' => date( "m/d/Y", strtotime( $startDate ) ),
                'endDate' => date( "m/d/Y", strtotime( $endDate ) )
            ]
        ] );

        if( \Login\Helper\UserSession::getUserSessionVariable( 'IS_PROXY' )==="Y" ) {

        }

        return $result;
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
        $startYear = null;
        $startMonth = null;

        if( empty( $post->calendarsToLoad ) ) {
            $post->calendarsToLoad = 3;
        }
        if( !empty( $post->appendDatesAsHighlighted ) ) {
            \Request\Helper\Calendar::setPreHighlightedDates($post->appendDatesAsHighlighted);
        }

        if( $post->calendarsToLoad==1 && $post->startYear==date("Y") && $post->startMonth==date("n") ) {
            if ((\Login\Helper\UserSession::getUserSessionVariable( 'IS_PAYROLL' ) == 'Y' ||
                 \Login\Helper\UserSession::getUserSessionVariable( 'IS_PAYROLL_ADMIN' ) == 'Y' ||
                 \Login\Helper\UserSession::getUserSessionVariable( 'IS_PAYROLL_ASSISTANT' ) == 'Y') &&
                 $post->initialCalendarLoad == false) {
                    $startYear = $post->startYear;
                    $startMonth = $post->startMonth;
            } else {
                $startDateData = $Employee->getStartDateDataFromRequest( $post->requestId );
                $startYear = $startDateData['START_YEAR'];
                $startMonth = $startDateData['START_MONTH'];
            }
        } else {
            $startYear = $post->startYear;
            $startMonth = $post->startMonth;
        }

        $startDate = date( "Y-m-d", strtotime( $startYear . "-" . $startMonth . "-01" ) );
        $dates = [];
        $headers = [];
        $calendars = [];

        \Request\Helper\Calendar::setCalendarHeadings( ['S', 'M', 'T', 'W', 'T', 'F', 'S' ] );
        \Request\Helper\Calendar::setBeginWeekOne( '<tr class="calendar-row" style="height:40px;">' );
        \Request\Helper\Calendar::setBeginCalendarRow( '<tr class="calendar-row" style="height:40px;">' );
        \Request\Helper\Calendar::setInvalidRequestDates( $this->invalidRequestDates );

        switch( $post->calendarsToLoad ) {
            case 3:
                $calendarDates = \Request\Helper\Calendar::getDatesForThreeCalendars( $startYear, $startMonth );
                break;

            case 1:
            default:
                $calendarDates = \Request\Helper\Calendar::getDatesForOneCalendar( $startYear, $startMonth );
                break;
        }

        foreach( $calendarDates as $timeFrame => $timeObject ) {
            $dates[$timeFrame] = $timeObject->format( "Y-m-d" );
        }

        $highlightDates = $Employee->findTimeOffCalendarByEmployeeNumber( $post->employeeNumber, $startDate,
            ( $post->calendarsToLoad==3 ? $dates['threeMonthsOut'] : $dates['oneMonthOut'] ), $post->requestId );

//        echo '<pre>';
//        var_dump( $post );
//        echo '</pre>';
//        die( ">>>>>" );


        if( $post->calendarsToLoad==1) {
            $threeCalendars = \Request\Helper\Calendar::getOneCalendar( $startYear, $startMonth, $highlightDates, $post->requestId );
            $navigation = [ 'calendarNavigationFastRewind' => [ 'month' => $calendarDates['threeMonthsBack']->format( "m" ), 'year' => $calendarDates['threeMonthsBack']->format( "Y" ) ],
                            'calendarNavigationRewind' => [ 'month' => $calendarDates['oneMonthBack']->format( "m" ), 'year' => $calendarDates['oneMonthBack']->format( "Y" ) ],
                            'calendarNavigationForward' => [ 'month' => $calendarDates['oneMonthOut']->format( "m" ), 'year' => $calendarDates['oneMonthOut']->format( "Y" ) ],
                            'calendarNavigationFastForward' => [ 'month' => $calendarDates['threeMonthsOut']->format( "m" ), 'year' => $calendarDates['threeMonthsOut']->format( "Y" ) ],
                          ];
        }
        if( $post->calendarsToLoad==3) {
            $threeCalendars = \Request\Helper\Calendar::getThreeCalendars( $startYear, $startMonth, $highlightDates );
            $navigation = [ 'calendarNavigationFastRewind' => [ 'month' => $calendarDates['sixMonthsBack']->format( "m" ), 'year' => $calendarDates['sixMonthsBack']->format( "Y" ) ],
                            'calendarNavigationRewind' => [ 'month' => $calendarDates['threeMonthsBack']->format( "m" ), 'year' => $calendarDates['threeMonthsBack']->format( "Y" ) ],
                            'calendarNavigationForward' => [ 'month' => $calendarDates['threeMonthsOut']->format( "m" ), 'year' => $calendarDates['threeMonthsOut']->format( "Y" ) ],
                            'calendarNavigationFastForward' => [ 'month' => $calendarDates['sixMonthsOut']->format( "m" ), 'year' => $calendarDates['sixMonthsOut']->format( "Y" ) ],
                          ];
        }

        foreach( $threeCalendars['calendars'] as $key => $calendar ) {
            $headers[$key] = $calendar['header'];
            $calendars[$key] = $calendar['data'];
        }

        foreach( $highlightDates as $key => $dateObject ) {
            $highlightDates[$key]['REQUEST_DATE'] = date( "m/d/Y", strtotime( $dateObject['REQUEST_DATE'] ) );
        }

        $endDate = ( $post->calendarsToLoad==3 ? $dates['threeMonthsOut'] : $dates['oneMonthOut'] );
        $result = new JsonModel( [
            'success' => true,
            'employeeData' => $employeeData,
            'loggedInUserData' => [ 'isManager' => \Login\Helper\UserSession::getUserSessionVariable( 'IS_MANAGER' ),
                                    'isSupervisor' => \Login\Helper\UserSession::getUserSessionVariable( 'IS_SUPERVISOR' ),
                                    'isPayroll' => \Login\Helper\UserSession::getUserSessionVariable( 'IS_PAYROLL' ),
                                    'isPayrollAdmin' => \Login\Helper\UserSession::getUserSessionVariable( 'IS_PAYROLL_ADMIN' ),
                                    'isPayrollAssistant' => \Login\Helper\UserSession::getUserSessionVariable( 'IS_PAYROLL_ASSISTANT' ),
                                    'isProxy' => \Login\Helper\UserSession::getUserSessionVariable( 'IS_PROXY' ),
                                    'isProxyForManager' => \Login\Helper\UserSession::getUserSessionVariable( 'IS_PROXY_FOR_MANAGER' )
            ],
            'proxyFor' => ( \Login\Helper\UserSession::getUserSessionVariable( 'IS_PROXY' )==="Y" ?
                            $Employee->findProxiesByEmployeeNumber( $post->employeeNumber ) :
                            []
                          ),
            'calendarData' => [
                'headers' => $headers,
                'calendars' => $calendars,
                'navigation' => $navigation,
                'highlightDates' => $highlightDates,
                'holidays' => $this->invalidRequestDates['individual'],
                'startDate' => date( "m/d/Y", strtotime( $startDate ) ),
                'endDate' => date( "m/d/Y", strtotime( $endDate ) )
            ]
        ] );

        if( \Login\Helper\UserSession::getUserSessionVariable( 'IS_PROXY' )==="Y" ) {

        }
//        if( $result['loggedInUserData']['isProxy']==='Y' ) {
//            $result['employeeData']->PROXY_FOR = [];
//        }

        return $result;
    }

}
