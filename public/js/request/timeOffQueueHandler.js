/**
 * Javascript timeOffQueueHandler 'class'
 *
 */
var timeOffQueueHandler = new function()
{
    var timeOffLoadCalendarUrl = 'http://swift:10080/sawik/timeoff/public/request/api',
    	timeOffSubmitTimeOffRequestUrl = 'http://swift:10080/sawik/timeoff/public/request/api',
    	timeOffSubmitTimeOffSuccessUrl = 'http://swift:10080/sawik/timeoff/public/request/submitted-for-approval',
    	employeePTORemaining = 0,
    	employeeFloatRemaining = 0,
    	employeeSickRemaining = 0,
    	totalPTORequested = 0,
    	totalFloatRequested = 0,
    	totalSickRequested = 0,
    	defaultHours = 8,
    	selectedTimeoffCategory = null,
    	requestReason = '',
    	/** Dates selected for this request **/
    	selectedDates = [],
    	selectedDateCategories = [],
    	selectedDateHours = [],
    	/** Dates selected for approved requests **/
    	selectedDatesApproved = [],
    	selectedDateCategoriesApproved = [],
    	selectedDateHoursApproved = [],
    	/** Dates selected for pending approval requests **/
    	selectedDatesPendingApproval = [],
    	selectedDateCategoriesPendingApproval = [],
    	selectedDateHoursPendingApproval = [];

    /**
     * Initializes binding
     */
    this.initialize = function() {
        $(document).ready(function() {
        	$('#pendingApprovalQueue').DataTable();
        	
//        	timeOffQueueHandler.loadCalendars();
        });
    }
    
    /**
     * Loads calendars via ajax and displays them on the page.
     */
    this.loadCalendars = function() {
    	var month = (new Date()).getMonth() + 1;
    	var year = (new Date()).getFullYear();
    	$.ajax({
          url: timeOffLoadCalendarUrl,
          type: 'POST',
          data: {
        	  action: 'loadCalendar',
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
        			( (index==1) ? json.prevButton : '' ) + thisCalendarHtml.header + ( (index==3) ? json.nextButton : '' ) +
        		    json.closeHeader +
        		    thisCalendarHtml.data);
        	});
        	
        	timeOffQueueHandler.setEmployeePTORemaining(json.employeeData.PTO_AVAILABLE);
        	timeOffQueueHandler.setEmployeeFloatRemaining(json.employeeData.FLOAT_AVAILABLE);
        	timeOffQueueHandler.setEmployeeSickRemaining(json.employeeData.SICK_AVAILABLE);
        	timeOffQueueHandler.setSelectedDates(json.approvedRequestJson, json.pendingRequestJson);
        	timeOffQueueHandler.highlightDates();
            return;
        })
        .error( function() {
            console.log( 'There was some error.' );
            return;
        });
    }
};

// Initialize the class
timeOffQueueHandler.initialize();