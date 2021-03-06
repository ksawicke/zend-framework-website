<?php
namespace Request\Helper;

class Calendar
{
    public static $calendarHeader = '<table cellpadding="0" cellspacing="0" class="calendar">';

    public static $calendarColumns = [
        'Sunday',
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday'
    ];

    public static $invalidRequestDates = [
        'before' => '',
        'after' => '',
        'individual' => [
        ]
    ];

    public static $beginCalendarColumnHeaders = '<tr class="calendar-header-adjust"><td class="calendar-day-head">';

    public static $insertAfterCalendarHeading = '</td><td class="calendar-day-head">';

    public static $endCalendarColumnHeaders = '</td></tr>';

    public static $beginCalendarRow = '<tr class="calendar-row">';

    public static $blankCalendarDay = '<td class="calendar-day-np"> <p>&nbsp;</p> </td>';

    public static $blankCalendarRow = '<tr class="calendar-row"><td class="calendar-day-np"> </td><td class="calendar-day-np"> </td><td class="calendar-day-np"> </td><td class="calendar-day-np"> </td><td class="calendar-day-np"> </td><td class="calendar-day-np"> </td><td class="calendar-day-np"> </td></tr>';

    public static $beginWeekOne = '<tr class="calendar-row">';

    // TODO sawik 01/14/2016 - If request is moved to payroll, should we flag it
    // or disable clicking??
    public static $beginDayCell = '<td class="calendar-day &requestTypeClass&" data-category="" data-date="&date&">'; // &nbsp;&nbsp;<i class="fa fa-check-circle iconCalendarPayrollApproved"></i>

    public static $beginDayDisabledCell = '<td class="calendar-day calendar-day-disabled">';

    public static $beginHolidayCell = '<td class="calendar-day calendar-day-holiday">';

    public static $beginDay = '<div class="day-number">';

    public static $endDay = '</div>';

    public static $beforeDayData = '<p>';

    public static $afterDayData = '</p>';

    public static $closeCell = '</td>';

    public static $closeRow = '</tr>';

    public static $closeCalendar = '</table>';

    public static $openHeader = '<strong>';

    public static $closeHeader = '</strong><br /><br />';

    public static $preHighlightedDates = [];

    public static $calendarDateTextToAppend = [];

    public static function getOneCalendar($startYear = null, $startMonth = null, $calendarData = [], $requestId = null)
    {
        if( !empty( self::$preHighlightedDates ) ) {
            foreach( self::$preHighlightedDates as $key => $highlightMe ) {
                $requestDate = date( "Y-m-d", strtotime( $highlightMe['date'] ) );
                $calendarData[] = [
                    'REQUEST_DATE' => $requestDate,
                    'REQUESTED_HOURS' => $highlightMe['hours'],
                    'CALENDAR_DAY_CLASS' => $highlightMe['category'],
                    'REQUEST_STATUS' => 'P'
                ];
            }
        }

        usort( $calendarData, function( $item1, $item2 ) {
            if ($item1['REQUEST_DATE'] == $item2['REQUEST_DATE']) return 0;
            return $item1['REQUEST_DATE'] < $item2['REQUEST_DATE'] ? -1 : 1;
        });

        $dates = self::getDatesForOneCalendar($startYear, $startMonth);

        return ['calendars' => [ 1 => ['header' => $dates['currentMonth']->format('M') . ' ' . $dates['currentMonth']->format('Y'),
                                       'data' => self::drawCalendar($startMonth, $startYear, $calendarData, $requestId)]
                               ],
                'navigation' => self::getCalendarNavigationForOneCalendar($dates),
                'openHeader' => self::$openHeader,
                'closeHeader' => self::$closeHeader,
                'showCurrentRequestsOnOrAfter' => $dates['currentMonth']->format('Y-m-01'),
                'showCurrentRequestsBefore' => $dates['threeMonthsOut']->format('Y-m-01')
            ];
    }

    public static function getThreeCalendars($startYear = null, $startMonth = null, $calendarData = [])
    {
        $dates = self::getDatesForThreeCalendars($startYear, $startMonth);
        return ['calendars' => [ 1 => ['header' => $dates['currentMonth']->format('M') . ' ' . $dates['currentMonth']->format('Y'),
                                       'data' => self::drawCalendar($startMonth, $startYear, $calendarData)],
                                 2 => ['header' => $dates['oneMonthOut']->format('M') . ' ' . $dates['oneMonthOut']->format('Y'),
                                       'data' => self::drawCalendar($dates['oneMonthOut']->format('m'), $dates['oneMonthOut']->format('Y'), $calendarData)],
                                 3 => ['header' => $dates['twoMonthsOut']->format('M') . ' ' . $dates['twoMonthsOut']->format('Y'),
                                       'data' => self::drawCalendar($dates['twoMonthsOut']->format('m'), $dates['twoMonthsOut']->format('Y'), $calendarData)]
                               ],
                'navigation' => self::getCalendarNavigationForThreeCalendars($dates),
                'openHeader' => self::$openHeader,
                'closeHeader' => self::$closeHeader,
                'showCurrentRequestsOnOrAfter' => $dates['currentMonth']->format('Y-m-01'),
                'showCurrentRequestsBefore' => $dates['threeMonthsOut']->format('Y-m-01')
            ];
    }

    public static function getCalendarNavigationForOneCalendar($dates)
    {
        return ['fastRewindButton' => self::getprevButtonForThreeCalendars($dates),
                'prevButton' => self::getprevButtonForOneCalendar($dates),
                'nextButton' => self::getNextButtonForOneCalendar($dates),
                'fastForwardButton' => self::getNextButtonForThreeCalendars($dates)
               ];
    }

    public static function getCalendarNavigationForThreeCalendars($dates)
    {
        return ['fastRewindButton' => self::getfastRewindButtonForThreeCalendars($dates),
                'prevButton' => self::getprevButtonForThreeCalendars($dates),
                'nextButton' => self::getNextButtonForThreeCalendars($dates),
                'fastForwardButton' => self::getfastForwardButtonForThreeCalendars($dates)
               ];
    }

    /**
     * Gets fast rewind button for navigating calendars.
     *
     * @param array $dates
     * @return string
     */
    public static function getfastRewindButtonForThreeCalendars($dates)
    {
        return '<button type="button" class="fc-prev-button fc-button fc-state-default fc-corner-left"><span class="fc-icon fc-icon-left-double-arrow calendarNavigation" title="Go back 6 months" data-month="' . $dates['sixMonthsBack']->format('m') . '" data-year="' . $dates['sixMonthsBack']->format('Y') . '"></span></button>';
    }

    /**
     * Gets previous button for navigating calendars.
     *
     * @param array $dates
     * @return string
     */
    public static function getprevButtonForThreeCalendars($dates)
    {
        return '<button type="button" class="fc-prev-button fc-button fc-state-default fc-corner-left"><span class="fc-icon fc-icon-left-single-arrow calendarNavigation" title="Go back 3 months" data-month="' . $dates['threeMonthsBack']->format('m') . '" data-year="' . $dates['threeMonthsBack']->format('Y') . '"></span></button>&nbsp;&nbsp;&nbsp;&nbsp;';
    }

    /**
     * Gets previous button for navigating single calendar.
     *
     * @param array $dates
     * @return string
     */
    public static function getprevButtonForOneCalendar($dates)
    {
        return '<button type="button" class="fc-prev-button fc-button fc-state-default fc-corner-left"><span class="fc-icon fc-icon-left-single-arrow calendarNavigation" title="Go back 1 month" data-month="' . $dates['oneMonthBack']->format('m') . '" data-year="' . $dates['oneMonthBack']->format('Y') . '"></span></button>&nbsp;&nbsp;&nbsp;&nbsp;';
    }

    /**
     * Gets next button for navigating calendars.
     *
     * @param array $dates
     * @return string
     */
    public static function getNextButtonForThreeCalendars($dates)
    {
        return '&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" class="fc-prev-button fc-button fc-state-default fc-corner-left"><span class="fc-icon fc-icon-right-single-arrow calendarNavigation" title="Go forward 3 months" data-month="' . $dates['threeMonthsOut']->format('m') . '" data-year="' . $dates['threeMonthsOut']->format('Y') . '"></span></button>';
    }

    /**
     * Gets next button for single calendar.
     *
     * @param array $dates
     * @return string
     */
    public static function getNextButtonForOneCalendar($dates)
    {
        return '&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" class="fc-prev-button fc-button fc-state-default fc-corner-left"><span class="fc-icon fc-icon-right-single-arrow calendarNavigation" title="Go forward 1 month" data-month="' . $dates['oneMonthOut']->format('m') . '" data-year="' . $dates['oneMonthOut']->format('Y') . '"></span></button>';
    }

    /**
     * Gets fast forward button for navigating calendars.
     *
     * @param array $dates
     * @return string
     */
    public static function getfastForwardButtonForThreeCalendars($dates)
    {
        return '<button type="button" class="fc-prev-button fc-button fc-state-default fc-corner-left"><span class="fc-icon fc-icon-right-double-arrow calendarNavigation" title="Go forward 6 months" data-month="' . $dates['sixMonthsOut']->format('m') . '" data-year="' . $dates['sixMonthsOut']->format('Y') . '"></span></button>';
    }

    /**
     * Gets dates for one calendar.
     *
     * @param type $startYear
     * @param type $startMonth
     * @return \DateTime
     */
    public static function getDatesForOneCalendar($startYear = null, $startMonth = null)
    {
        if($startYear===null) {
            $startYear = date("Y");
        }
        if($startMonth===null) {
            $startMonth = date("m");
        }

        $time = strtotime($startYear . "-" . $startMonth . "-01");
        $return = ['threeMonthsBack' => new \DateTime(date("Y-m-d", strtotime("-3 month", $time))),
                   'oneMonthBack' => new \DateTime(date("Y-m-d", strtotime("-1 month", $time))),
                   'currentMonth' => new \DateTime(date("Y-m-d", strtotime("+0 month", $time))),
                   'oneMonthOut' => new \DateTime(date("Y-m-d", strtotime("+1 month", $time))),
                   'threeMonthsOut' => new \DateTime(date("Y-m-d", strtotime("+3 month", $time)))
                  ];

        return $return;
    }

    /**
     * Gets dates for three calendars.
     *
     * @param type $startYear
     * @param type $startMonth
     * @return \DateTime
     */
    public static function getDatesForThreeCalendars($startYear = null, $startMonth = null)
    {
        if($startYear===null) {
            $startYear = date("Y");
        }
        if($startMonth===null) {
            $startMonth = date("m");
        }

        $time = strtotime($startYear . "-" . $startMonth . "-01");
        $return = ['sixMonthsBack' => new \DateTime(date("Y-m-d", strtotime("-6 month", $time))),
                   'threeMonthsBack' => new \DateTime(date("Y-m-d", strtotime("-3 month", $time))),
                   'currentMonth' => new \DateTime(date("Y-m-d", strtotime("+0 month", $time))),
                   'oneMonthOut' => new \DateTime(date("Y-m-d", strtotime("+1 month", $time))),
                   'twoMonthsOut' => new \DateTime(date("Y-m-d", strtotime("+2 month", $time))),
                   'threeMonthsOut' => new \DateTime(date("Y-m-d", strtotime("+3 month", $time))),
                   'sixMonthsOut' => new \DateTime(date("Y-m-d", strtotime("+6 month", $time)))
                  ];

        return $return;
    }

    /**
     * Draws a calendar.
     *
     * @param unknown $month
     * @param unknown $year
     * @return string
     *
     * @author David Walsh
     * @see https://davidwlsh.name/php-calendar
     * @modified Kevin Sawicke
     */
    public static function drawCalendar($month, $year, $calendarData, $requestId = 0)
    {
//        echo '<pre>';
//        print_r($calendarData);
//        echo '</pre>';
//
//        die("|| STOP");

        /* draw table */
        $calendar = self::drawCalendarHeader();

        /* table headings */
        $calendar .= self::drawCalendarColumns();

        /* days and weeks vars now ... */
        $running_day = date('w', mktime(0, 0, 0, $month, 1, $year));
        $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
        $days_in_this_week = 1;
        $day_counter = 0;
        $row_counter = 0;
        $dates_array = [];

        /* row for week one */
        $calendar .= self::drawBeginningWeekOne();
        $row_counter++;

        /* print "blank" days until the first of the current week */
        $data = self::getBlankCalendarDaysBeforeFirstOfCurrentWeek($running_day, $days_in_this_week);
        $calendar .= $data['calendar'];
        $days_in_this_week = $data['days_in_this_week'];

        /* keep going with days.... */
        $data = self::drawCalendarDays($month, $year, $days_in_month, $running_day, $days_in_this_week, $day_counter, $row_counter, $calendarData, $requestId);
        $calendar .= $data['calendar'];
        $days_in_this_week = $data['days_in_this_week'];
        $running_day = $data['running_day'];
        $day_counter = $data['day_counter'];
        $row_counter = $data['row_counter'];

        /* finish the rest of the days in the week */
        $calendar .= self::finishDaysInWeek($days_in_this_week);

        /* final row */
        $calendar .= self::$closeRow;

        /* make sure we have a 6th row */
        if($row_counter == 5) {
            $calendar .= self::$beginCalendarRow;
            for($x = 1; $x <= 7; $x++) {
                $calendar .= self::$blankCalendarDay;
            }
            $calendar .= self::$closeRow;
        }

        /* end the table */
        $calendar .= self::$closeCalendar;

        /* all done, return result */
        return $calendar;
    }

    /**
     * Close out the week on calendar.
     *
     * @param unknown $days_in_this_week
     * @return string
     */
    public static function finishDaysInWeek($days_in_this_week)
    {
        $data = '';
        if ($days_in_this_week < 8) {
            for ($x = 1; $x <= (8 - $days_in_this_week); $x ++) {
                $data .= self::$blankCalendarDay;
            }
        }
        return $data;
    }

    /**
     * Add data to a calendar day on calendar.
     *
     * @param unknown $list_day
     */
    public static function addDataToCalendarDay($list_day, $calendarData)
    {
        $data = self::$beforeDayData;
        foreach( self::$calendarDateTextToAppend as $date => $text ) {
            $date = \DateTime::createFromFormat("Y-m-d", $date);
            if($list_day==$date->format('j')) {
                $data .= $text;
            }
        }
//         foreach($calendarData as $key => $cal) {
//             $date = \DateTime::createFromFormat("Y-m-d", $cal['REQUEST_DATE']);
//             if($list_day==$date->format('j')) {
// //                 $data .= '' . $cal['FIRST_NAME'] . ' ' . $cal['LAST_NAME'] . ' - ' . $cal['REQUESTED_HOURS'] . '<br />';
//             }
//         }
        $data .= self::$afterDayData;

        return $data;
    }

    /**
     * Draws the header row for the calendar.
     *
     * @return string|unknown
     */
    public static function drawCalendarHeader()
    {
        return self::$calendarHeader;
    }

    /**
     * Draws the columns for the calendar.
     */
    public static function drawCalendarColumns()
    {
        return self::$beginCalendarColumnHeaders . implode(self::$insertAfterCalendarHeading, self::$calendarColumns) . self::$endCalendarColumnHeaders;
    }

    /**
     * Draws the beginning of week one.
     */
    public static function drawBeginningWeekOne()
    {
        return self::$beginWeekOne;
    }

    //$invalidRequestDates

    public static function isDateValidToSelect($thisDay)
    {
        $return = true;
        if( ( !empty(self::$invalidRequestDates['before']) && strtotime($thisDay) < strtotime(self::$invalidRequestDates['before']) ) ||
            ( !empty(self::$invalidRequestDates['after']) && strtotime($thisDay) > strtotime(self::$invalidRequestDates['after']) )

          ) {
            $return = false;
        }

        return $return;
    }

    /**
     * Returns boolean if date is a company holiday.
     *
     * @param string $thisDay
     * @return boolean
     */
    public static function isDateHoliday($thisDay)
    {
        $return = false;
        if( in_array($thisDay, self::$invalidRequestDates['individual']) ) {
            $return = true;
        }

        return $return;
    }

    /**
     * Draws days on the calendar.
     *
     * @param integer $month
     * @param integer $year
     * @param integer $days_in_month
     * @param integer $running_day
     * @param integer $days_in_this_week
     * @param integer $day_counter
     * @param integer $row_counter
     * @param array $calendarData
     */
    public static function drawCalendarDays($month, $year, $days_in_month, $running_day, $days_in_this_week, $day_counter, $row_counter, $calendarData, $requestId = 0)
    {
        $selectedClass = ( ($requestId===0) ? "" : "Selected" );
        $pendingClass = ( ($requestId===0) ? " requestPending" : "" );
        $calendarClassesByDate = [];

        foreach( $calendarData as $ctr => $data ) {
            $calendarClassesByDate[$data['REQUEST_DATE']] = $data['CALENDAR_DAY_CLASS'] . $selectedClass . $pendingClass;
        }

//        echo '<pre>';
//        var_dump( $calendarClassesByDate );
//        echo '</pre>';
//        exit();

        $calendarTemp = '';
        for ($list_day = 1; $list_day <= $days_in_month; $list_day ++) {
            $thisDay = str_pad($month, 2, "0", STR_PAD_LEFT).'/'.str_pad($list_day, 2, "0", STR_PAD_LEFT).'/'.$year;
            $thisDayYmd = $year.'-'.str_pad($month, 2, "0", STR_PAD_LEFT).'-'.str_pad($list_day, 2, "0", STR_PAD_LEFT);

            if(self::isDateValidToSelect($thisDay)) {
                // Replace &requestTypeClass& with '' or CALENDAR_DAY_CLASS
                $requestClass = '';
                if( array_key_exists( $thisDayYmd, $calendarClassesByDate ) ) {
                    $requestClass = $calendarClassesByDate[$thisDayYmd];
                }
                if( in_array( $thisDay, self::$invalidRequestDates['individual'] ) ) {
                    $requestClass .= " calendar-day-holiday";
                }
                $beginDayCell = str_replace("&requestTypeClass&", $requestClass, self::$beginDayCell);
                $calendarTemp .= str_replace("&date&", str_pad($month, 2, "0", STR_PAD_LEFT) . "/" .
                               str_pad($list_day, 2, "0", STR_PAD_LEFT) . "/" . $year, $beginDayCell) . self::$beginDay . $list_day . self::$endDay .
                               self::addDataToCalendarDay($list_day, $calendarData);
            } else {
                $requestClass = '';
                if( array_key_exists( $thisDayYmd, $calendarClassesByDate ) ) {
                    $requestClass = $calendarClassesByDate[$thisDayYmd];
                }
                $beginDayCell = ( ($requestId!=0) ? str_replace("&requestTypeClass&", $requestClass, self::$beginDayCell) : self::$beginDayDisabledCell ); // self::$beginDayDisabledCell
                $calendarTemp .= str_replace("&date&", str_pad($month, 2, "0", STR_PAD_LEFT) . "/" .
                               str_pad($list_day, 2, "0", STR_PAD_LEFT) . "/" . $year, $beginDayCell) . self::$beginDay . $list_day . self::$endDay . self::addDataToCalendarDay($list_day, $calendarData);
            }

            $calendarTemp .= self::$closeCell;
            if ($running_day == 6) {
                $calendarTemp .= self::$closeRow;
                $row_counter++;
                if (($day_counter + 1) != $days_in_month) {
                    $calendarTemp .= self::$beginCalendarRow;
                }
                $running_day = - 1;
                $days_in_this_week = 0;
            }
            $days_in_this_week ++;
            $running_day ++;
            $day_counter ++;
        }

        $data = ['calendar' => $calendarTemp,
            'days_in_this_week' => $days_in_this_week,
            'running_day' => $running_day,
            'day_counter' => $day_counter,
            'row_counter' => $row_counter
        ];
        return $data;
    }

    /**
     * Gets the blank days before the first day of the week.
     *
     * @param unknown $running_day
     * @param unknown $days_in_this_week
     */
    public static function getBlankCalendarDaysBeforeFirstOfCurrentWeek($running_day, $days_in_this_week)
    {
        $data = ['calendar' => '', 'days_in_this_week' => $days_in_this_week];
        for ($x = 0; $x < $running_day; $x ++) {
            $data['calendar'] .= self::$blankCalendarDay;
            $data['days_in_this_week'] ++;
        }
        return $data;
    }

    /**
     *
     * @param unknown $calendarHeader
     */
    public static function setCalendarHeader($calendarHeader)
    {
        self::$calendarHeader = $calendarHeader;
    }

    /**
     *
     * @param unknown $calendarColumns
     */
    public static function setCalendarHeadings($calendarColumns)
    {
        self::$calendarColumns = $calendarColumns;
    }

    /**
     *
     * @param unknown $beginCalendarColumnHeaders
     */
    public static function setBeginCalendarColumnHeaders($beginCalendarColumnHeaders)
    {
        self::$beginCalendarColumnHeaders = $beginCalendarColumnHeaders;
    }

    /**
     *
     * @param unknown $insertAfterCalendarHeading
     */
    public static function setInsertAfterCalendarHeading($insertAfterCalendarHeading)
    {
        self::$insertAfterCalendarHeading = $insertAfterCalendarHeading;
    }

    /**
     *
     * @param unknown $endCalendarColumnHeaders
     */
    public static function setEndCalendarColumnHeaders($endCalendarColumnHeaders)
    {
        self::$endCalendarColumnHeaders = $endCalendarColumnHeaders;
    }

    /**
     *
     * @param unknown $beginCalendarRow
     */
    public static function setBeginCalendarRow($beginCalendarRow)
    {
        self::$beginCalendarRow = $beginCalendarRow;
    }

    /**
     *
     * @param unknown $blankCalendarDay
     */
    public static function setBlankCalendarDay($blankCalendarDay)
    {
        self::$blankCalendarDay = $blankCalendarDay;
    }

    /**
     *
     * @param unknown $beginWeekOne
     */
    public static function setBeginWeekOne($beginWeekOne)
    {
        self::$beginWeekOne = $beginWeekOne;
    }

    /**
     *
     * @param unknown $beginDayCell
     */
    public static function setBeginDayCell($beginDayCell)
    {
        self::$beginDayCell = $beginDayCell;
    }

    /**
     *
     * @param unknown $beginDay
     */
    public static function setBeginDay($beginDay)
    {
        self::$beginDay = $beginDay;
    }

    /**
     *
     * @param unknown $endDay
     */
    public static function setEndDay($endDay)
    {
        self::$endDay = $endDay;
    }

    /**
     *
     * @param unknown $beforeDayData
     */
    public static function setBeforeDayData($beforeDayData)
    {
        self::$beforeDayData = $beforeDayData;
    }

    /**
     *
     * @param unknown $afterDayData
     */
    public static function setAfterDayData($afterDayData)
    {
        self::$afterDayData = $afterDayData;
    }

    /**
     *
     * @param unknown $closeCell
     */
    public static function setCloseCell($closeCell)
    {
        self::$closeCell = $closeCell;
    }

    /**
     *
     * @param unknown $closeRow
     */
    public static function setCloseRow($closeRow)
    {
        self::$closeRow = $closeRow;
    }

    /**
     *
     * @param unknown $closeCalendar
     */
    public static function setCloseCalendar($closeCalendar)
    {
        self::$closeCalendar = $closeCalendar;
    }

    public static function setInvalidRequestDates($invalidRequestDates)
    {
        self::$invalidRequestDates = $invalidRequestDates;
//         var_dump($invalidRequestDates);
    }

    public static function setPreHighlightedDates($preHighlightedDates)
    {
        $return = [];
        foreach( $preHighlightedDates as $ctr => $highlightMe ) {
            if( !array_key_exists( 'entryId', $highlightMe ) ) {
                $return[] = $highlightMe;
            }
        }

        self::$preHighlightedDates = $return;
    }

    public static function setCalendarDateTextToAppend( $calendarDateTextData )
    {
        $calendarDateTextToAppend = [];
        foreach( $calendarDateTextData as $date => $data ) {
            foreach( $data as $i => $d ) {
                if( !array_key_exists( $date, $calendarDateTextToAppend ) ) {
                    $calendarDateTextToAppend[$date] = '';
                }
                $calendarDateTextToAppend[$date] .= $d['EMPLOYEE_NAME'] . " (" . $d['TOTAL_HOURS'] . ')<br />';
            }
        }
        self::$calendarDateTextToAppend = $calendarDateTextToAppend;
    }
}