<?php
namespace Request\Helper;

class DrawHelper {
    
    public function drawHoursRequested( $timeoffRequestData )
    {
        $htmlData = [];
        foreach( $timeoffRequestData as $ctr => $data ) {
            $htmlData[] = '<span class="badge ' + selectedDatesNew[key].category + '">' +
                    timeOffCreateRequestHandler.getCategoryText(selectedDatesNew[key].category) +
                    '</span>';
        }
        
        /**
         * [0] => Array
        (
            [REQUEST_DATE] => 2016-04-04
            [REQUESTED_HOURS] => 8.00
            [REQUEST_CODE] => P
            [DESCRIPTION] => PTO
        )
         */
        
        return $htmlData;
//        datesSelectedDetailsHtml += selectedDatesNew[key].date + '&nbsp;&nbsp;&nbsp;&nbsp;' +
//                    '<input class="selectedDateHours" value="' + timeOffCreateRequestHandler.setTwoDecimalPlaces(selectedDatesNew[key].hours) + '" size="2" data-key="' + key + '" disabled="disabled">' +
//                    '&nbsp;&nbsp;&nbsp;&nbsp;' +
//                    '<span class="badge ' + selectedDatesNew[key].category + '">' +
//                    timeOffCreateRequestHandler.getCategoryText(selectedDatesNew[key].category) +
//                    '</span>' +
//                    '&nbsp;&nbsp;&nbsp;' +
//                    '<span class="glyphicon glyphicon-remove-circle red remove-date-requested" ' +
//                    'data-date="' + selectedDatesNew[key].date + '" ' +
//                    'data-category="' + selectedDatesNew[key].category + '" ' +
//                    'title="Remove date from request">' +
//                    '</span>' +
//                    '<br style="clear:both;" />';
    }
    
}