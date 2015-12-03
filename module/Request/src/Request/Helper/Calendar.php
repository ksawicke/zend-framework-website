<?php
namespace Request\Helper;

class Calendar
{

    /**
     * Draws a calendar.
     *
     * @param unknown $month            
     * @param unknown $year            
     * @return string
     * 
     * @author David Walsh
     * @see https://davidwalsh.name/php-calendar
     */
    public static function drawCalendar($month, $year, $calendarData)
    {
        /* draw table */
        $calendar = '<table cellpadding="0" cellspacing="0" class="calendar">';
        
        /* table headings */
        $headings = [
            'Sunday',
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday'
        ];
        $calendar .= '<tr class="calendar-row"><td class="calendar-day-head">' . implode('</td><td class="calendar-day-head">', $headings) . '</td></tr>';
        
        /* days and weeks vars now ... */
        $running_day = date('w', mktime(0, 0, 0, $month, 1, $year));
        $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
        $days_in_this_week = 1;
        $day_counter = 0;
        $dates_array = [];
        
        /* row for week one */
        $calendar .= '<tr class="calendar-row">';
        
        /* print "blank" days until the first of the current week */
        for ($x = 0; $x < $running_day; $x ++) {
            $calendar .= '<td class="calendar-day-np"> </td>';
            $days_in_this_week ++;
        }
        
        /* keep going with days.... */
        for ($list_day = 1; $list_day <= $days_in_month; $list_day ++) {
            $calendar .= '<td class="calendar-day">';
            /* add in the day number */
            $calendar .= '<div class="day-number">' . $list_day . '</div>';
            
            /**
             * Add data to a cell
             */
            foreach($calendarData as $key => $cal) {
                $date = \DateTime::createFromFormat("Y-m-d", $cal['REQUEST_DATE']);
                if($list_day==$date->format('j')) {
                    $calendar .= '<p>' . $cal['FIRST_NAME'] . ' ' . $cal['LAST_NAME'] . '<br />' . $cal['REQUESTED_HOURS'] . ' ' . $cal['REQUEST_TYPE'] . '</p>';
                }
            }
            
            $calendar .= '</td>';
            if ($running_day == 6) {
                $calendar .= '</tr>';
                if (($day_counter + 1) != $days_in_month) {
                    $calendar .= '<tr class="calendar-row">';
                }
                $running_day = - 1;
                $days_in_this_week = 0;
            }
            $days_in_this_week ++;
            $running_day ++;
            $day_counter ++;
        }
        
        /* finish the rest of the days in the week */
        if ($days_in_this_week < 8) {
            for ($x = 1; $x <= (8 - $days_in_this_week); $x ++) {
                $calendar .= '<td class="calendar-day-np"> </td>';
            }
        }
        
        /* final row */
        $calendar .= '</tr>';
        
        /* end the table */
        $calendar .= '</table>';
        
        /* all done, return result */
        return $calendar;
    }
}