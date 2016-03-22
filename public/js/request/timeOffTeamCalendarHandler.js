/**
 * Javascript timeOffCreateRequestHandler 'class'
 *
 */
var timeOffTeamCalendarHandler = new function()
{
	var timeOffLoadCalendarUrl = phpVars.basePath + '/request/api';
	
	/**
     * Initializes binding
     */
    this.initialize = function() {
        $(document).ready(function() {
        	/**
        	 * Handle clicking previous or next buttons on calendars
        	 */
        	$(document).on('click', '.calendarNavigation', function() {
        		timeOffTeamCalendarHandler.loadNewCalendar($(this).attr("data-month"), $(this).attr("data-year"));
        	});
        	
        	timeOffTeamCalendarHandler.loadTeamCalendar();
        });
    }
    
    /**
     * Loads calendars via ajax and displays them on the page.
     */
    this.loadTeamCalendar = function() {
    	var month = (new Date()).getMonth() + 1;
    	var year = (new Date()).getFullYear();
    	$.ajax({
          url: timeOffLoadCalendarUrl,
          type: 'POST',
          data: {
        	  action: 'loadTeamCalendar',
        	  startMonth: month,
        	  startYear: year
          },
          dataType: 'json'
    	})
        .success( function(json) {
        	var calendarHtml = '';
        	$.each(json.calendars, function(index, thisCalendarHtml) {
        		$("#calendar" + index + "Html").html(
        			json.openHeader +
        			( (index==1) ? json.prevButton : '' ) + thisCalendarHtml.header + ( (index==1) ? json.nextButton : '' ) +
        		    json.closeHeader +
        		    thisCalendarHtml.data);
        	});
        	
//        	timeOffCreateRequestHandler.setEmployeePTORemaining(json.employeeData.PTO_AVAILABLE);
//        	timeOffCreateRequestHandler.setEmployeeFloatRemaining(json.employeeData.FLOAT_AVAILABLE);
//        	timeOffCreateRequestHandler.setEmployeeSickRemaining(json.employeeData.SICK_AVAILABLE);
//        	timeOffCreateRequestHandler.setSelectedDates(json.approvedRequestJson, json.pendingRequestJson);
//        	timeOffCreateRequestHandler.highlightDates();
            return;
        })
        .error( function() {
            console.log( 'There was some error.' );
            return;
        });
    }
    
    this.loadNewCalendar = function(startMonth, startYear) {
    	$.ajax({
          url: timeOffLoadCalendarUrl,
          type: 'POST',
          data: {
        	  action: 'loadTeamCalendar',
        	  startMonth: startMonth,
        	  startYear: startYear
          },
          dataType: 'json'
    	})
        .success( function(json) {
        	var calendarHtml = '';
        	$.each(json.calendars, function(index, thisCalendarHtml) {
        		$("#calendar" + index + "Html").html(
        			json.openHeader +
        			( (index==1) ? json.prevButton : '' ) + thisCalendarHtml.header + ( (index==1) ? json.nextButton : '' ) +
        		    json.closeHeader +
        		    thisCalendarHtml.data);
        	});
            return;
        })
        .error( function() {
            console.log( 'There was some error.' );
            return;
        });
    }
};

//Initialize the class
timeOffTeamCalendarHandler.initialize();