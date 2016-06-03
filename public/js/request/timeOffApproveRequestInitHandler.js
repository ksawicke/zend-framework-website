/**
 * Javascript timeOffCreateRequestHandler 'class'
 *
 */
var timeOffCreateRequestInitHandler = new function() {
    /**
    * Initializes binding
    */
   this.initialize = function() {
       $(document).ready(function() {
           calendarsToLoad = 1;
//           var selectedDatesNew = [];
//           var obj = {
//                date : '04/04/2016',
//                hours : 8,
//                category : 'timeOffPTO'
//            };
//           selectedDatesNew.push(obj);
            
           timeOffCreateRequestHandler.loadCalendars( phpVars.employee_number, 1, phpVars.request_id );
           timeOffCreateRequestHandler.handleCalendarNavigation();
           doRealDelete = false;
       });
       
       /**
        * Handle clicking previous or next buttons on calendars
        */
//       this.handleCalendarNavigation = function() {
//           $('body').on('click', '.calendarNavigation', function(e) {
//               timeOffCreateRequestHandler.loadNewCalendars($(this).attr("data-month"), $(this).attr("data-year"), 1, phpVars.request_id);
//           });
//       }
   }
   
}

timeOffCreateRequestInitHandler.initialize();