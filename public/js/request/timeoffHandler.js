/**
 * Javascript timeoffHandler 'class'
 *
 */
var timeoffHandler = new function()
{
    var timeOffLoadCalendarUrl = 'http://swift:10080/sawik/timeoff/public/request/api',
    	employeePTORemaining = 0,
    	employeeFloatRemaining = 0,
    	employeeSickRemaining = 0,
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
        	$(".timeOffCategory").click(function() {
        		timeoffHandler.resetTimeoffCategory();
        		timeoffHandler.setTimeoffCategory($(this));
        	});
        	
        	$(document).on('hover', '.calendar-day', function() {
        		console.log("hovered");
        	});
        	
        	$(document).on('click', '.calendar-day', function() {
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
        });
    }

    this.resetTimeoffCategory = function() {
    	$(".timeOffCategory").removeClass("selected");
    	$(".timeOffCategoryLeft").html('&nbsp;<br />&nbsp;');
    }
    
    this.setTimeoffCategory = function(object) {
    	selectedTimeoffCategory = object.attr("data-category");
    	object.addClass("selected");
    	$("." + object.attr("data-category")).html('<span class="glyphicon glyphicon-ok" aria-hidden=true></span><br />&nbsp;');
    }
    
    this.loadCalendars = function() {
    	$.ajax({
          url: timeOffLoadCalendarUrl,
          type: 'POST',
          data: {
        	  color: 'blue'
          },
          dataType: 'json'
    	})
        .success( function(json) {
        	var calendarHtml = '';
        	$.each(json.calendars, function(index, thisCalendarHtml) {
        		$("#calendar" + index + "Html").html(
        			json.openHeader +
        		    thisCalendarHtml.header +
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
    
    this.setEmployeePTORemaining = function(ptoRemaining) {
    	employeePTORemaining = ptoRemaining;
    	timeoffHandler.printEmployeePTORemaining();
    }
    
    this.setEmployeeFloatRemaining = function(floatRemaining) {
    	employeeFloatRemaining = floatRemaining;
    	timeoffHandler.printEmployeeFloatRemaining();
    }
    
    this.setEmployeeSickRemaining = function(sickRemaining) {
    	employeeSickRemaining = sickRemaining;
    	timeoffHandler.printEmployeeSickRemaining();
    }
    
    this.printEmployeePTORemaining = function() {
    	$("#employeePTOHours").html(employeePTORemaining + " hr");
    }
    
    this.printEmployeeFloatRemaining = function() {
    	$("#employeeFloatHours").html(employeeFloatRemaining + " hr");
    }
    
    this.printEmployeeSickRemaining = function() {
    	$("#employeeSickHours").html(employeeSickRemaining + " hr");
    }    
    
    this.addTime = function(selectedTimeoffCategory, defaultHours) {
    	switch(selectedTimeoffCategory) {
	    	case 'timeOffPTO':
	    		employeePTORemaining -= defaultHours;
	    		employeePTORemaining = employeePTORemaining.toFixed(2);
	    		timeoffHandler.printEmployeePTORemaining();
	    		break;
	    		
	    	case 'timeOffFloat':
	    		employeeFloatRemaining -= defaultHours;
	    		employeeFloatRemaining = employeeFloatRemaining.toFixed(2);
	    		timeoffHandler.printEmployeeFloatRemaining();
	    		break;
	    		
	    	case 'timeOffSick':
	    		employeeSickRemaining -= defaultHours;
	    		employeeSickRemaining = employeeSickRemaining.toFixed(2);
	    		timeoffHandler.printEmployeeSickRemaining();
	    		break;
    	}
    }
    
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
};

// Initialize the class
timeoffHandler.initialize();