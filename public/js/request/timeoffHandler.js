/**
 * Javascript timeoffHandler 'class'
 *
 */
var timeoffHandler = new function()
{
    var timeOffLoadCalendarUrl = 'http://swift:10080/sawik/timeoff/public/request/api',
    	timeOffSubmitTimeOffRequestUrl = 'http://swift:10080/sawik/timeoff/public/request/api',
    	employeePTORemaining = 0,
    	employeeFloatRemaining = 0,
    	employeeSickRemaining = 0,
    	totalPTORequested = 0,
    	totalFloatRequested = 0,
    	totalSickRequested = 0,
    	defaultHours = 8,
    	selectedTimeoffCategory = null,
    	selectedDates = [],
    	selectedDateCategories = [],
    	selectedDateHours = [];

    /**
     * Initializes binding
     */
    this.initialize = function() {
        $(document).ready(function() {
        	/**
        	 * Handle clicking previous or next buttons on calendars
        	 */
        	$(document).on('click', '.calendarNavigation', function() {
        		timeoffHandler.loadNewCalendars($(this).attr("data-month"), $(this).attr("data-year"));
        	});
        	
        	/**
        	 * Handle clicking category
        	 */
        	$(".selectTimeOffCategory").click(function() {
        		timeoffHandler.resetTimeoffCategory();
        		timeoffHandler.setTimeoffCategory($(this));
        	});
        	
        	$(document).on('change', '.selectedDateHours', function() {
        		var key = $(this).attr("data-key");
        		var value = $(this).val();
        		console.log('selectedDateHours[' + key + ']: ' + selectedDateHours[key]);
        		console.log('value: ' + value);
        		selectedDateHours[key] = value;
        	});
        	
        	$(document).on('click', '.submitTimeOffRequest', function() {
        		timeoffHandler.submitTimeOffRequest();
        	});
        	
        	/**
        	 * Handle clicking a calendar date
        	 */
        	$(document).on('click', '.calendar-day', function() {
        		if(selectedTimeoffCategory != null) {
        			var index = selectedDates.indexOf($(this).attr("data-date"));
            		if (index != -1) {
            			selectedDates.splice(index, 1);
            			selectedDateCategories.splice(index, 1);
            			selectedDateHours.splice(index, 1);
            			$(this).toggleClass(selectedTimeoffCategory);
            			$(this).children("div").toggleClass(selectedTimeoffCategory);
            			
            			timeoffHandler.subtractTime(selectedTimeoffCategory, defaultHours);
            		} else {
            			selectedDates.push($(this).attr("data-date"));
            			selectedDateCategories.push(selectedTimeoffCategory);
            			selectedDateHours.push('8.00');
            			$(this).toggleClass(selectedTimeoffCategory);
            			$(this).children("div").toggleClass(selectedTimeoffCategory);
            			
            			timeoffHandler.addTime(selectedTimeoffCategory, defaultHours);
            		}
            		
            		datesSelectedHtml = '';
            		$.each(selectedDates, function(key, date) {
            			datesSelectedHtml += '<span class="glyphicon glyphicon-' + selectedDateCategories[key] + '"></span>&nbsp;&nbsp;&nbsp;&nbsp;' + date + '&nbsp;&nbsp;&nbsp;&nbsp;<input class="selectedDateHours" value="8.00" size="2" data-key="' + key + '" disabled="disabled"><br style="clear:both;" />';
            			// <span class="glyphicon glyphicon-timeOffPTO"></span>&nbsp;&nbsp;&nbsp;&nbsp;02/02/2016&nbsp;&nbsp;&nbsp;&nbsp;<input id="blah" value="8.00" size="2"><br style="clear:both;" />
            		});
            		if(selectedDates.length==0) {
            			datesSelectedHtml = '<i>No dates are currently selected.</i>';
            		}
            		
            		totalPTORequested = 0;
            		totalFloatRequested = 0;
            		totalSickRequested = 0;
            		
            		$.each(selectedDateCategories, function(key, value) {
            			switch(selectedDateCategories[key]) {
            				case 'timeOffPTO':
            					totalPTORequested += parseInt(selectedDateHours[key], 10);
            					break;
            					
            				case 'timeOffFloat':
            					totalFloatRequested += parseInt(selectedDateHours[key], 10);
            					break;
            					
            				case 'timeOffSick':
            					totalSickRequested += parseInt(selectedDateHours[key], 10);
            					break;
            			}
            		});
            		
            		datesSelectedHtml += '<br /><strong>Totals being requested:</strong><br />' +
            			'<span class="glyphicon glyphicon-timeOffPTO"></span> ' + totalPTORequested + '<br />' +
            			'<span class="glyphicon glyphicon-timeOffFloat"></span> ' + totalFloatRequested + '<br />' +
            			'<span class="glyphicon glyphicon-timeOffSick"></span> ' + totalSickRequested + '<br /><br />' +
            			'<textarea cols="40" rows="4" placeholder="Reason for request..."></textarea><br /><br />' +
            			'<button type="button" class="btn btn-form-primary btn-lg submitTimeOffRequest">Submit My Request</button>';
            		$("#datesSelected").html(datesSelectedHtml);
            		
            		console.log("selectedDates", selectedDates);
            		console.log("selectedDateCategories", selectedDateCategories);
            		console.log("selectedDateHours", selectedDateHours);
        		}
        	});
        	
//        	$(".calendar-day").hover(function() {
//        		if(selectedTimeoffCategory!==null) {
//        			$(this).toggleClass(selectedTimeoffCategory);
//        			$(this).children("div").toggleClass(selectedTimeoffCategory);
//        		}
//        	});
        	
        	/*******
        	$(".calendar-day").click(function() {
        		var index = selectedDates.indexOf($(this).attr("data-date"));
        		if (index != -1) {
        			selectedDates.splice(index, 1);
        			selectedDateCategories.splice(index, 1);
        			selectedDateHours.splice(index, 1);
        			$(this).toggleClass(selectedTimeoffCategory);
        			$(this).children("div").toggleClass(selectedTimeoffCategory);
        		} else {
        			selectedDates.push($(this).attr("data-date"));
        			selectedDateCategories.push(selectedTimeoffCategory);
        			selectedDateHours.push('8.00');
        			$(this).toggleClass(selectedTimeoffCategory);
        			$(this).children("div").toggleClass(selectedTimeoffCategory);
        		}
        		
        		datesSelectedHtml = '';
        		$.each(selectedDates, function(key, date) {
        			datesSelectedHtml += '<span class="glyphicon glyphicon-' + selectedDateCategories[key] + '"></span>&nbsp;&nbsp;&nbsp;&nbsp;' + date + '&nbsp;&nbsp;&nbsp;&nbsp;<input id="blah" value="8.00" size="2"><br style="clear:both;" />';
        			// <span class="glyphicon glyphicon-timeOffPTO"></span>&nbsp;&nbsp;&nbsp;&nbsp;02/02/2016&nbsp;&nbsp;&nbsp;&nbsp;<input id="blah" value="8.00" size="2"><br style="clear:both;" />
        		});
        		if(selectedDates.length==0) {
        			datesSelectedHtml = '<i>No dates are currently selected.</i>';
        		}
        		$("#datesSelected").html(datesSelectedHtml);
        		
//        		console.log(selectedDates);
//        		console.log(selectedDateCategories);
//        		console.log(selectedDateHours);
        	});
        	**/
        	
        	timeoffHandler.loadCalendars();
        	timeoffHandler.checkLocalStorage();
        });
    }

    this.checkLocalStorage = function() {
    	if(typeof(Storage) !== "undefined") {
    	    // Code for localStorage/sessionStorage.
    		console.log("local storage support enabled");
    		var testObject = { 'one': 1, 'two': 2, 'three': 3 };

    		// Put the object into storage
    		localStorage.setItem('testObject', JSON.stringify(testObject));

    		// Retrieve the object from storage
    		var retrievedObject = localStorage.getItem('testObject');

    		console.log('retrievedObject: ', JSON.parse(retrievedObject));
    	} else {
    	    // Sorry! No Web Storage support..
    		console.log("NO local storage support enabled");
    	}
    }
    
    /**
     * Resets the remaining sick time for selected employee.
     */
    this.resetTimeoffCategory = function() {
    	$(".selectTimeOffCategory").removeClass("selected");
    	$(".timeOffCategoryLeft").html('&nbsp;<br />&nbsp;');
    }
    
    /**
     * Sets the currently selected time off category.
     */
    this.setTimeoffCategory = function(object) {
//    	console.log(selectedTimeoffCategory + " : " + object.attr("data-category"));
    	if(selectedTimeoffCategory==object.attr("data-category")) {
    		selectedTimeoffCategory = null;
    	} else {
	    	selectedTimeoffCategory = object.attr("data-category");
	    	object.next('div').addClass("selected");
	    	object.addClass("selected");
//	    	$("." + object.attr("data-category")).html('<span class="glyphicon glyphicon-ok" aria-hidden=true></span><br />&nbsp;');
    	}
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
        	
        	timeoffHandler.setEmployeePTORemaining(json.employeeData.PTO_REMAINING);
        	timeoffHandler.setEmployeeFloatRemaining(json.employeeData.FLOAT_REMAINING);
        	timeoffHandler.setEmployeeSickRemaining(json.employeeData.SICK_REMAINING);
            return;
        })
        .error( function() {
            console.log( 'There was some error.' );
            return;
        });
    }
    
    this.submitTimeOffRequest = function() {
    	$.ajax({
            url: timeOffSubmitTimeOffRequestUrl,
            type: 'POST',
            data: {
              action: 'submitTimeoffRequest',
              selectedDates: selectedDates,
              selectedDateCategories: selectedDateCategories,
              selectedDateHours: selectedDateHours
            },
            dataType: 'json'
      	})
        .success( function(json) {
      		console.log( 'yo' );
            return;
        })
        .error( function() {
            console.log( 'There was some error.' );
            return;
        });
    };
    
    this.loadNewCalendars = function(startMonth, startYear) {
    	$.ajax({
          url: timeOffLoadCalendarUrl,
          type: 'POST',
          data: {
        	  action: 'loadCalendar',
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
        			( (index==1) ? json.prevButton : '' ) + thisCalendarHtml.header + ( (index==3) ? json.nextButton : '' ) +
        		    json.closeHeader +
        		    thisCalendarHtml.data);
        	});
        	
//        	timeoffHandler.setEmployeePTORemaining(json.employeeData.PTO_REMAINING);
//        	timeoffHandler.setEmployeeFloatRemaining(json.employeeData.FLOAT_REMAINING);
//        	timeoffHandler.setEmployeeSickRemaining(json.employeeData.SICK_REMAINING);
            return;
        })
        .error( function() {
            console.log( 'There was some error.' );
            return;
        });
    }
    
    /**
     * Prints the remaining PTO time for selected employee.
     */
    this.setEmployeePTORemaining = function(ptoRemaining) {
    	employeePTORemaining = ptoRemaining;
    	timeoffHandler.printEmployeePTORemaining();
    }
    
    /**
     * Sets the remaining Float time for selected employee.
     */
    this.setEmployeeFloatRemaining = function(floatRemaining) {
    	employeeFloatRemaining = floatRemaining;
    	timeoffHandler.printEmployeeFloatRemaining();
    }
    
    /**
     * Sets the remaining sick time for selected employee.
     */
    this.setEmployeeSickRemaining = function(sickRemaining) {
    	employeeSickRemaining = sickRemaining;
    	timeoffHandler.printEmployeeSickRemaining();
    }
    
    /**
     * Prints the remaining PTO time for selected employee.
     */
    this.printEmployeePTORemaining = function() {
    	$("#employeePTOHours").html(timeoffHandler.roundToTwo(employeePTORemaining) + " hr");
    }
    
    /**
     * Prints the remaining Float time for selected employee.
     */
    this.printEmployeeFloatRemaining = function() {
    	$("#employeeFloatHours").html(timeoffHandler.roundToTwo(employeeFloatRemaining) + " hr");
    }
    
    /**
     * Prints the remaining Sick time for selected employee.
     */
    this.printEmployeeSickRemaining = function() {
    	$("#employeeSickHours").html(timeoffHandler.roundToTwo(employeeSickRemaining) + " hr");
    }    
    
    /**
     * Adds employee defaultHours from the current Category of time remaining.
     */
    this.addTime = function(selectedTimeoffCategory, defaultHours) {
    	switch(selectedTimeoffCategory) {
	    	case 'timeOffPTO':
	    		employeePTORemaining -= defaultHours;
	    		timeoffHandler.printEmployeePTORemaining();
	    		break;
	    		
	    	case 'timeOffFloat':
	    		employeeFloatRemaining -= defaultHours;
	    		timeoffHandler.printEmployeeFloatRemaining();
	    		break;
	    		
	    	case 'timeOffSick':
	    		employeeSickRemaining -= defaultHours;
	    		timeoffHandler.printEmployeeSickRemaining();
	    		break;
    	}
    }
    
    /**
     * Subtracts employee defaultHours from the current Category of time remaining.
     */
    this.subtractTime = function(selectedTimeoffCategory, defaultHours) {
    	switch(selectedTimeoffCategory) {
	    	case 'timeOffPTO':
	    		employeePTORemaining += defaultHours;
	    		timeoffHandler.printEmployeePTORemaining();
	    		break;
	    		
	    	case 'timeOffFloat':
	    		employeeFloatRemaining += defaultHours;
	    		timeoffHandler.printEmployeeFloatRemaining();
	    		break;
	    		
	    	case 'timeOffSick':
	    		employeeSickRemaining += defaultHours;
	    		timeoffHandler.printEmployeeSickRemaining();
	    		break;
		}
    }
    
    /**
     * Rounds a number to two decimal places.
     */
    this.roundToTwo = function(num) {    
        return +(Math.round(num + "e+2")  + "e-2");
    }
};

// Initialize the class
timeoffHandler.initialize();