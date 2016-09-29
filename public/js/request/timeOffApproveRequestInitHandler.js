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
           timeOffCreateRequestHandler.loadCalendars( phpVars.employee_number, 1, phpVars.request_id );
           timeOffCreateRequestHandler.handleCalendarNavigation();
           doRealDelete = false;
       });
   }
   
}

timeOffCreateRequestInitHandler.initialize();