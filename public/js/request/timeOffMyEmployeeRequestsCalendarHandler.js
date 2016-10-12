/**
 * Javascript timeOffMyEmployeeRequestsCalendarHandler 'class'
 *
 */
var timeOffMyEmployeeRequestsCalendarHandler = new function ()
{
	var timeOffLoadEmployeeRequestsCalendarUrl = phpVars.basePath + '/api/calendar/get/manager-employees',
	    month = (new Date()).getMonth() + 1,
        year = (new Date()).getFullYear(),
        managerReportsType = 'D',
        employee_number = phpVars.employee_number;
	
	/**
     * Initializes binding
     */
    this.initialize = function () {
        $(document).ready(function () {
        	timeOffMyEmployeeRequestsCalendarHandler.handleClickCalendarViewTab();
        	timeOffMyEmployeeRequestsCalendarHandler.handleCalendarNavigation();
        	timeOffMyEmployeeRequestsCalendarHandler.handleChangeCalendarViewReportsType();
        });
    }
    
    this.handleChangeCalendarViewReportsType = function () {
    	$('body').on('change', '#calendarViewManagerReportsType', function(e) {
    		managerReportsType = $(this).val();
    		timeOffMyEmployeeRequestsCalendarHandler.loadMyEmployeeRequestsCalendar();
    	});
    }
    
    this.handleClickCalendarViewTab = function () {
    	$('body').on('click', '#myEmployeeRequestsCalendarViewTab', function(e) {
    		timeOffMyEmployeeRequestsCalendarHandler.loadMyEmployeeRequestsCalendar();
        });
    }
    
    /**
     * Handle clicking previous or next buttons on calendars
     */
    this.handleCalendarNavigation = function() {
        $('body').on('click', '.calendarNavigation', function(e) {
        	month = $(this).attr("data-month");
        	year = $(this).attr("data-year");
        	timeOffMyEmployeeRequestsCalendarHandler.loadMyEmployeeRequestsCalendar();
        });
    }
    
    /**
     * Loads calendars via ajax and displays them on the page.
     */
    this.loadMyEmployeeRequestsCalendar = function() {
    	timeOffMyEmployeeRequestsCalendarHandler.toggleCalendarLoading();
        $.ajax({
            url : timeOffLoadEmployeeRequestsCalendarUrl,
            type : 'POST',
            data: {
                startMonth : month,
                startYear : year,
                employeeNumber : employee_number,
                managerReportsType: managerReportsType,
                calendarsToLoad: 1
            },
            dataType : 'json'
        })
        .success(function(json) {
        	timeOffMyEmployeeRequestsCalendarHandler.drawOneCalendar(json.calendarData);
        	timeOffMyEmployeeRequestsCalendarHandler.toggleCalendarLoading();
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
        $("#calendarManagerLabel").html(calendarData.headers[1]);

        /** Draw calendars **/
        $("#calendarManagerBody").html(calendarData.calendars[1]);
        
        timeOffMyEmployeeRequestsCalendarHandler.unhighlightDates(); // Don't want the manger's calendar to show anything highlighted on this view
    }
    
    this.unhighlightDates = function() {
    	$.each($(".calendar-day"), function(index, blah) {
            $(this).removeClass('timeOffPTOSelected');
            $(this).removeClass('timeOffFloatSelected');
            $(this).removeClass('timeOffSickSelected');
            $(this).removeClass('timeOffGrandfatheredSelected');
            $(this).removeClass('timeOffBereavementSelected');
            $(this).removeClass('timeOffApprovedNoPaySelected');
            $(this).removeClass('timeOffCivicDutySelected');
            $(this).removeClass('timeOffUnexcusedAbsenceSelected');
        });
    }
    
    this.toggleCalendarLoading = function() {
    	$("#calendarManagerHeader").toggle();
    	$("#calendarManagerBody").toggle();
    	$("#calendarLoadingImage").toggle();
    }
};

// Initialize the class
timeOffMyEmployeeRequestsCalendarHandler.initialize();