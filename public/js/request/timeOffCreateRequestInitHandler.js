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
           timeOffCreateRequestHandler.loadCalendars(phpVars.employee_number, 3);
       });
   }
   
}

timeOffCreateRequestInitHandler.initialize();
