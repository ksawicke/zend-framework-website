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
            $('#pendingApprovalQueue').DataTable({
                dom : 'fltirp',
                searching  : true,
                processing : true,
                serverSide : true,
                oLanguage: {
                    sProcessing: "<img src='/sawik/timeoff/public/img/loading/clock.gif'>"
                },
                columns : [
                             { "data": "EMPLOYEE_DESCRIPTION" },
                             { "data": "APPROVER_QUEUE" },
                             { "data": "REQUEST_STATUS_DESCRIPTION" },
                             { "data": "REQUESTED_HOURS" },
                             { "data": "REQUEST_REASON" },
                             { "data": "MIN_DATE_REQUESTED" },
                             { "data": "ACTIONS" }
                            ],
                order: [],
                columnDefs: [ { "orderable": false,
                                  "targets": [ 6 ] 
                                }
                              ],
                ajax : {
                    url : "/sawik/timeoff/public/api/queue/manager/p",
                    data : function( d ) {
                        return $.extend( {}, d, {
                            "employeeNumber": phpVars.employee_number
                        });
                    },
                    type : "POST",
                }
            })
//            .on("xhr.dt", function (e, settings, data, xhr) {
//                if (data.status == 'success') {
//                    $.each(data.data, function(index, record) {
//                        data.data[index]['shortName'] = '<input data-parsley-required class="tableedit form-control" disabled name="shortName_'+data.data[index]['id']+'" id="shortName_'+data.data[index]['id']+'" value="'+data.data[index]['shortName']+'">';
//                        data.data[index]['longName'] = '<input data-parsley-required class="tableedit form-control" disabled name="longName_'+data.data[index]['id']+'" id="longName_'+data.data[index]['id']+'" value="'+data.data[index]['longName']+'">';
//                        data.data[index]['address'] = '<input data-parsley-required class="tableedit form-control" disabled name="address_'+data.data[index]['id']+'" id="address_'+data.data[index]['id']+'" value="'+data.data[index]['address']+'">';
//                        data.data[index]['city'] = '<input data-parsley-required class="tableedit form-control" disabled name="city_'+data.data[index]['id']+'" id="city_'+data.data[index]['id']+'" value="'+data.data[index]['city']+'">';
//                        data.data[index]['state'] = '<div><select data-parsley-required data-parsley-errors-container="#editHotelStateError'+data.data[index]['id']+'" class="tableedit form-control select2" disabled id="state_'+data.data[index]['id']+'"><option value="'+data.data[index]['state']+'" selected="selected">'+data.data[index]['state']+' - '+data.data[index]['stateLong']+'</select></div><span id="editHotelStateError'+data.data[index]['id']+'"></span>';
//                        data.data[index]['zip'] = '<input data-parsley-required class="tableedit form-control" disabled name="zip_'+data.data[index]['id']+'" id="zip_'+data.data[index]['id']+'" value="'+data.data[index]['zip']+'">';
//                        data.data[index]['checkIn'] = '<input data-parsley-required class="tableedit form-control" disabled name="zip_'+data.data[index]['id']+'" id="checkIn_'+data.data[index]['id']+'" value="'+data.data[index]['checkIn']+'">';
//                        data.data[index]['checkOut'] = '<input data-parsley-required class="tableedit form-control" disabled name="zip_'+data.data[index]['id']+'" id="checkOut_'+data.data[index]['id']+'" value="'+data.data[index]['checkOut']+'">';
//                        data.data[index]['action'] = '<i class="fa fa-edit actionIcon" title="edit hotel" id="hotel_edit_'+data.data[index]['id']+'"></i><i class="hidden fa fa-save actionIcon" title="save changes" id="hotel_save_'+data.data[index]['id']+'"></i><i class="hidden fa fa-times actionIcon" title="cancel changes" id="hotel_cancel_'+data.data[index]['id']+'"></i><i class="fa fa-trash actionIcon" title="remove hotel" id="hotel_delete_'+data.data[index]['id']+'"></i>';
//                    });
//                }
//            })
            .on("error.dt", function (e, settings, techNote, message) {
                console.log("An error has been reported by DataTables: ", message);
            });
        	
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