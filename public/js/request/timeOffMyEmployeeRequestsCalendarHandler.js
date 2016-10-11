/**
 * Javascript timeOfMyEmployeeRequestsCalendarHandler 'class'
 *
 */
var timeOfMyEmployeeRequestsCalendarHandler = new function ()
{
	var timeOffLoadEmployeeRequestsCalendarUrl = phpVars.basePath + '/api/calendar/get/manager-employees',
	    month = (new Date()).getMonth() + 1,
        year = (new Date()).getFullYear(),
        employee_number = phpVars.employee_number;
	
	/**
     * Initializes binding
     */
    this.initialize = function () {
        $(document).ready(function () {
        	timeOfMyEmployeeRequestsCalendarHandler.handleClickCalendarViewTab();
        	timeOfMyEmployeeRequestsCalendarHandler.handleCalendarNavigation();
        });
    }
    
    this.handleClickCalendarViewTab = function () {
    	$('body').on('click', '#myEmployeeRequestsCalendarViewTab', function(e) {
    		timeOfMyEmployeeRequestsCalendarHandler.loadMyEmployeeRequestsCalendar();
        });
    }
    
    /**
     * Loads calendars via ajax and displays them on the page.
     */
    this.loadMyEmployeeRequestsCalendar = function() {
        $.ajax({
            url : timeOffLoadEmployeeRequestsCalendarUrl,
            type : 'POST',
            data: {
                startMonth : month,
                startYear : year,
                employeeNumber : employee_number,
                managerReportsType: 'D',
                calendarsToLoad: 1
            },
            dataType : 'json'
        })
        .success(function(json) {
        	timeOfMyEmployeeRequestsCalendarHandler.drawOneCalendar(json.calendarData);
            return;
        }).error(function() {
            console.log('There was some error.');
            return;
        });
    }
    
    /**
     * Draws the one calendar loaded
     */
    this.drawOneCalendar = function(calendarData) {
        /** Update navigation button data **/
        $("#calendarNavigationFastRewind").attr("data-month", calendarData.navigation.calendarNavigationFastRewind.month);
        $("#calendarNavigationFastRewind").attr("data-year", calendarData.navigation.calendarNavigationFastRewind.year);
        $("#calendarNavigationRewind").attr("data-month", calendarData.navigation.calendarNavigationRewind.month);
        $("#calendarNavigationRewind").attr("data-year", calendarData.navigation.calendarNavigationRewind.year);
        $("#calendarNavigationFastForward").attr("data-month", calendarData.navigation.calendarNavigationFastForward.month);
        $("#calendarNavigationFastForward").attr("data-year", calendarData.navigation.calendarNavigationFastForward.year);
        $("#calendarNavigationForward").attr("data-month", calendarData.navigation.calendarNavigationForward.month);
        $("#calendarNavigationForward").attr("data-year", calendarData.navigation.calendarNavigationForward.year);

        /** Draw calendar labels **/
        $("#calendar1Label").html(calendarData.headers[1]);

        /** Draw calendars **/
        $("#calendar1Body").html(calendarData.calendars[1]);

//        timeOffCreateRequestHandler.highlightDates();
    }
    
    /**
     * Handle clicking previous or next buttons on calendars
     */
    this.handleCalendarNavigation = function() {
        $('body').on('click', '.calendarNavigation', function(e) {
        	console.log( $(this) );
//        	month = $(this).attr("data-month");
//        	year = $(this).attr("data-year");
//        	timeOfMyEmployeeRequestsCalendarHandler.loadMyEmployeeRequestsCalendar( );
        });
    }
};

// Initialize the class
timeOfMyEmployeeRequestsCalendarHandler.initialize();