<?php
namespace Request\Helper;

class Calendar
{
    public static $calendarHeader = '<table cellpadding="0" cellspacing="0" class="calendar">';
    
    public static $calendarHeadings = [
        'Sunday',
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday'
    ];
    
    public static $beginCalendarColumnHeaders = '<tr class="calendar-row calendar-header-adjust"><td class="calendar-day-head">';
    
    public static $insertAfterCalendarHeading = '</td><td class="calendar-day-head">';
    
    public static $endCalendarColumnHeaders = '</td></tr>';
    
    public static $beginCalendarRow = '<tr class="calendar-row">';
    
    public static $blankCalendarDay = '<td class="calendar-day-np"> </td>';
    
    public static $blankCalendarRow = '<tr class="calendar-row"><td class="calendar-day-np"> </td><td class="calendar-day-np"> </td><td class="calendar-day-np"> </td><td class="calendar-day-np"> </td><td class="calendar-day-np"> </td><td class="calendar-day-np"> </td><td class="calendar-day-np"> </td></tr>';
    
    public static $beginWeekOne = '<tr class="calendar-row">';
    
    public static $beginDayCell = '<td class="calendar-day" data-category="" data-date="&date&">';
    
    public static $beginDay = '<div class="day-number">';
    
    public static $endDay = '</div>';
    
    public static $beforeDayData = '<p>';
    
    public static $afterDayData = '</p>';
    
    public static $closeCell = '</td>';
    
    public static $closeRow = '</tr>';
    
    public static $closeCalendar = '</table>';
    
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
    public static function drawCalendar($month, $year, $calendarData)
    {
        /* draw table */
        $calendar = self::$calendarHeader;
        
        /* table headings */
        $calendar .= self::$beginCalendarColumnHeaders . implode(self::$insertAfterCalendarHeading, self::$calendarHeadings) . self::$endCalendarColumnHeaders;
        
        /* days and weeks vars now ... */
        $running_day = date('w', mktime(0, 0, 0, $month, 1, $year));
        $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
        $days_in_this_week = 1;
        $day_counter = 0;
        $row_counter = 0;
        $dates_array = [];
        
        /* row for week one */
        $calendar .= self::$beginWeekOne;
        $row_counter++;
        
        /* print "blank" days until the first of the current week */
        for ($x = 0; $x < $running_day; $x ++) {
            $calendar .= self::$blankCalendarDay;
            $days_in_this_week ++;
        }
        
        /* keep going with days.... */
        for ($list_day = 1; $list_day <= $days_in_month; $list_day ++) {
            // &date&
            $beginDayCell = str_replace("&date&", str_pad($month, 2, "0", STR_PAD_LEFT) . "/" . str_pad($list_day, 2, "0", STR_PAD_LEFT) . "/" . $year, self::$beginDayCell);
            $calendar .= $beginDayCell;
            /* add in the day number */
            $calendar .= self::$beginDay . $list_day . self::$endDay;
            
            /**
             * Add data to a cell
             */
            $calendar .= self::addDataToCalendarDay($list_day, $calendarData);
                        
            $calendar .= self::$closeCell;
            if ($running_day == 6) {
                $calendar .= self::$closeRow;
                if (($day_counter + 1) != $days_in_month) {
                    $calendar .= self::$beginCalendarRow;
                    $row_counter++;
                }
                $running_day = - 1;
                $days_in_this_week = 0;
            }
            $days_in_this_week ++;
            $running_day ++;
            $day_counter ++;
        }
        
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
        foreach($calendarData as $key => $cal) {
            $date = \DateTime::createFromFormat("Y-m-d", $cal['REQUEST_DATE']);
            if($list_day==$date->format('j')) {
                $data .= '' . $cal['FIRST_NAME'] . ' ' . $cal['LAST_NAME'] . '<br />' . $cal['REQUESTED_HOURS'] . ' ' . $cal['REQUEST_TYPE'] . '<br /><br />';
            }
        }
        $data .= self::$afterDayData;
        
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
     * @param unknown $calendarHeadings
     */
    public static function setCalendarHeadings($calendarHeadings)
    {
        self::$calendarHeadings = $calendarHeadings;
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
}