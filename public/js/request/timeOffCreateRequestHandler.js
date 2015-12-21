/**
 * Javascript timeOffCreateRequestHandler 'class'
 *
 */
var timeOffCreateRequestHandler = new function()
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
    	totalUnexcusedAbsenceRequested = 0,
    	totalBereavementRequested = 0,
    	totalCivicDutyRequested = 0,
    	totalGrandfatheredRequested = 0,
    	totalApprovedNoPayRequested = 0,
    	defaultHours = 8,
    	selectedTimeoffCategory = null,
    	requestReason = '',
    	/** Dates selected for this request **/
    	selectedDatesNew = [],
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
        	/**
        	 * Handle clicking previous or next buttons on calendars
        	 */
        	$(document).on('click', '.calendarNavigation', function() {
        		timeOffCreateRequestHandler.loadNewCalendars($(this).attr("data-month"), $(this).attr("data-year"));
        	});
        	
        	/**
        	 * Handle clicking category
        	 */
        	$(".selectTimeOffCategory").click(function() {
        		timeOffCreateRequestHandler.resetTimeoffCategory($(this));
        		timeOffCreateRequestHandler.setTimeoffCategory($(this));
        	});
        	
        	$(document).on('change', '.selectedDateHours', function() {
        		var key = $(this).attr("data-key");
        		var value = $(this).val();
//        		console.log('selectedDateHours[' + key + ']: ' + selectedDateHours[key]);
//        		console.log('value: ' + value);
        		selectedDateHours[key] = value;
        	});
        	
        	$(document).on('click', '.submitTimeOffRequest', function() {
        		requestReason = $("#requestReason").val();
//        		console.log(requestReason);
        		timeOffCreateRequestHandler.submitTimeOffRequest();
        	});
        	
        	/**
        	 * Handle clicking a calendar date
        	 */
        	$(document).on('click', '.calendar-day', function() {
        		if(selectedTimeoffCategory != null) {
        			var thisDate = $(this).attr("data-date");
        			var thisCategory = selectedTimeoffCategory;
        			var thisHours = '8.00';
        			var obj = {date:$(this).attr("data-date"), hours:'8.00', category:selectedTimeoffCategory};
        			var isSelected = false;
        			var numba = '';
        			
        			for(var i = 0; i < selectedDatesNew.length; i++) {
        				if(selectedDatesNew[i].date &&
        				   selectedDatesNew[i].date===thisDate &&
        				   selectedDatesNew[i].category &&
        				   selectedDatesNew[i].category===thisCategory) {
        					isSelected = true;
        					numba = i;
        					break;
        	    		}
        	    	}
//        			console.log("selectedDatesNew", selectedDatesNew);
//        			console.log("isSelected", isSelected);
        			
        			if(isSelected===false) {
//        				console.log("ADD ME " + thisDate + " :: " + thisCategory);
        				selectedDatesNew.push(obj);
        			}
        			else {
//        				console.log("REMOVE ME " + thisDate + " :: " + thisCategory);
        				selectedDatesNew.splice(numba, 1);
        			}
        			
//        			for(var i = 0; i < selectedDatesNew.length; i++) {
//        	    		if(selectedDatesNew[i].date===thisDate && selectedDatesNew[i].category===thisCategory) {
//        	    			console.log("YES");
//        	    		} else {
//        	    			console.log("NO");
//        	    		}
//        	    	}
        			
//        			console.log(selectedDatesNew);
        			
//        			var isSelected = timeOffCreateRequestHandler.isSelected(thisDate, selectedTimeoffCategory);
//        			console.log("isSelected", isSelected);
//        			
//        			
//        			if(isSelected===false) {
//        				console.log("ADD ME " + thisDate + " :: " + thisCategory);
//        			}
//        			else {
//        				console.log("REMOVE ME " + thisDate + " :: " + thisCategory);
//        			}
//        			timeOffCreateRequestHandler.sortDatesSelected();
        			selectedDatesNew.sort(function(a,b) {
        				var dateA = new Date(a.date).getTime();
        		        var dateB = new Date(b.date).getTime();
        		        return dateA > dateB ? 1 : -1; 
        			});
        			console.log(selectedDatesNew);
        			
        			/************
//        			console.log("Q", selectedDatesNew);
        			var index = selectedDates.indexOf($(this).attr("data-date"));
            		if (index != -1) {
            			selectedDates.splice(index, 1);
            			selectedDateCategories.splice(index, 1);
            			selectedDateHours.splice(index, 1);
            			$(this).toggleClass(selectedTimeoffCategory + "Selected");
            			
            			timeOffCreateRequestHandler.subtractTime(selectedTimeoffCategory, defaultHours);
            		} else {
            			var obj = {date:$(this).attr("data-date"), hours:'8.00', category:selectedTimeoffCategory};
            			selectedDatesNew.push(obj);
            			selectedDatesNew.sort(function(a,b) {
            				var dateA = new Date(a.date).getTime();
            		        var dateB = new Date(b.date).getTime();
            		        return dateA > dateB ? 1 : -1; 
            			});
            			
            			console.log(selectedDatesNew);
            			
            			selectedDates.push($(this).attr("data-date"));
            			selectedDateCategories.push(selectedTimeoffCategory);
            			selectedDateHours.push('8.00');
            			$(this).toggleClass(selectedTimeoffCategory + "Selected");
            			
            			timeOffCreateRequestHandler.addTime(selectedTimeoffCategory, defaultHours);
            		}
            		*****************/
        			
            		datesSelectedDetailsHtml = '<br /><strong>Adjust Hours:</strong>' +
        			'<br style="clear:both;"/>';
            		$.each(selectedDates, function(key, date) {
            			datesSelectedDetailsHtml += '<span class="glyphicon glyphicon-' + selectedDateCategories[key] + '"></span>&nbsp;&nbsp;&nbsp;&nbsp;' + date + '&nbsp;&nbsp;&nbsp;&nbsp;<input class="selectedDateHours" value="8.00" size="2" data-key="' + key + '" disabled="disabled"><br style="clear:both;" />';
            		});
            		
            		totalPTORequested = 0;
            		totalFloatRequested = 0;
            		totalSickRequested = 0;
            		totalUnexcusedAbsenceRequested = 0;
                	totalBereavementRequested = 0;
                	totalCivicDutyRequested = 0;
                	totalGrandfatheredRequested = 0;
                	totalApprovedNoPayRequested = 0;
            		
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
            					
            				case 'timeOffUnexcusedAbsence':
            					totalUnexcusedAbsenceRequested += parseInt(selectedDateHours[key], 10);
            					break;
            					
            				case 'timeOffBereavement':
            					totalBereavementRequested += parseInt(selectedDateHours[key], 10);
            					break;
            					
            				case 'timeOffCivicDuty':
            					totalCivicDutyRequested += parseInt(selectedDateHours[key], 10);
            					break;
            					
            				case 'timeOffGrandfathered':
            					totalGrandfatheredRequested += parseInt(selectedDateHours[key], 10);
            					break;
            					
            				case 'timeOffApprovedNoPay':
            					totalApprovedNoPayRequested += parseInt(selectedDateHours[key], 10);
            					break;
            			}
            		});
            		
//            		datesSelectedDetailsHtml += '<br /><strong>Totals being requested:</strong><br />' +
//            			'<span class="glyphicon glyphicon-timeOffPTO"></span> ' + timeOffCreateRequestHandler.setTwoDecimalPlaces(totalPTORequested) + '<br />' +
//            			'<span class="glyphicon glyphicon-timeOffFloat"></span> ' + timeOffCreateRequestHandler.setTwoDecimalPlaces(totalFloatRequested) + '<br />' +
//            			'<span class="glyphicon glyphicon-timeOffSick"></span> ' + timeOffCreateRequestHandler.setTwoDecimalPlaces(totalSickRequested) + '<br />' +
//            			'<span class="glyphicon glyphicon-timeOffUnexcusedAbsence"></span> ' + timeOffCreateRequestHandler.setTwoDecimalPlaces(totalUnexcusedAbsenceRequested) + '<br />' +
//            			'<span class="glyphicon glyphicon-timeOffBereavement"></span> ' + timeOffCreateRequestHandler.setTwoDecimalPlaces(totalBereavementRequested) + '<br />' +
//            			'<span class="glyphicon glyphicon-timeOffCivicDuty"></span> ' + timeOffCreateRequestHandler.setTwoDecimalPlaces(totalCivicDutyRequested) + '<br />' +
//            			'<span class="glyphicon glyphicon-timeOffGrandfathered"></span> ' + timeOffCreateRequestHandler.setTwoDecimalPlaces(totalGrandfatheredRequested) + '<br />' +
//            			'<span class="glyphicon glyphicon-timeOffApprovedNoPay"></span> ' + timeOffCreateRequestHandler.setTwoDecimalPlaces(totalApprovedNoPayRequested) + '<br />' +
//            			'<textarea cols="40" rows="4" placeholder="Reason for request..." id="requestReason"></textarea><br /><br />' +
//            			'<button type="button" class="btn btn-form-primary btn-lg submitTimeOffRequest">Submit My Request</button>';
            		
            		datesSelectedDetailsHtml +=
            			'<br /><strong>Totals being requested:</strong>' +
            			'<br style="clear:both;"/>' +
	            		'<br style="clear:both;"/>' +
	            		'<span class="badge timeOffPTO">PTO: ' + timeOffCreateRequestHandler.setTwoDecimalPlaces(totalPTORequested) + '</span>&nbsp;&nbsp;' +
	            		'<span class="badge timeOffFloat">FLOAT: ' + timeOffCreateRequestHandler.setTwoDecimalPlaces(totalFloatRequested) + '</span>&nbsp;&nbsp;' +
	            		'<span class="badge timeOffSick">SICK: ' + timeOffCreateRequestHandler.setTwoDecimalPlaces(totalSickRequested) + '</span>&nbsp;&nbsp;' +
	            		'<span class="badge timeOffGrandfathered">GRANDFATHERED: ' + timeOffCreateRequestHandler.setTwoDecimalPlaces(totalGrandfatheredRequested) + '</span>&nbsp;&nbsp;' +
	            		'<span class="badge timeOffUnexcusedAbsence">UNEXCUSED ABSENCE: ' + timeOffCreateRequestHandler.setTwoDecimalPlaces(totalUnexcusedAbsenceRequested) + '</span>&nbsp;&nbsp;' +
	            		'<span class="badge timeOffBereavement">BEREAVEMENT: ' + timeOffCreateRequestHandler.setTwoDecimalPlaces(totalBereavementRequested) + '</span>&nbsp;&nbsp;' +
	            		'<span class="badge timeOffCivicDuty">CIVIC DUTY: ' + timeOffCreateRequestHandler.setTwoDecimalPlaces(totalCivicDutyRequested) + '</span>&nbsp;&nbsp;' +
	            		'<span class="badge timeOffApprovedNoPay">APPROVED NO PAY: ' + timeOffCreateRequestHandler.setTwoDecimalPlaces(totalApprovedNoPayRequested) + '</span>' +
	            		'<br style="clear:both;"/>' +
	            		'<br style="clear:both;"/>' +
	            		'<strong>Totals being requested:</strong>' +
            			'<br style="clear:both;"/>' +
	            		'<br style="clear:both;"/>' +
	            		'<textarea cols="40" rows="4" id="requestReason"></textarea><br /><br />' +
            			'<button type="button" class="btn btn-form-primary btn-lg submitTimeOffRequest">Submit My Request</button>' +
            			'<br style="clear:both;" /><br style="clear:both;" />';
            		
            		$("#datesSelectedDetails").html(datesSelectedDetailsHtml);
            		
            		if(selectedDates.length===0) {
            			$('#datesSelectedDetails').hide();
            			timeOffCreateRequestHandler.setStep('2');
//            			$('#noDatesSelectedWarning').show();
            		} else {
            			$('#datesSelectedDetails').show();
            			timeOffCreateRequestHandler.setStep('3');
//            			$('#noDatesSelectedWarning').hide();
            		}
        		}
        	});
        	
        	timeOffCreateRequestHandler.loadCalendars();
        	timeOffCreateRequestHandler.checkLocalStorage();
        	
        	$('.timeOffCalendarWrapper').hide();
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
    this.resetTimeoffCategory = function(object) {
//    	console.log("OBJECT", object);
    	$('.btn-requestCategory').removeClass("categorySelected");
    	$('.btn-requestCategory').removeClass(selectedTimeoffCategory);
//    	object.removeClass(object.attr("data-category"));
//    	object.removeClass("categorySelected");
//    	$(".selectTimeOffCategory").removeClass("categorySelected");
//    	$(".selectTimeOffCategory").removeClass("categorySelected").prev('div').removeClass("categoryColorSelected");
//    	$(".timeOffCategoryLeft").html('&nbsp;<br />&nbsp;');
    }
    
    /**
     * Sets the currently selected time off category.
     */
    this.setTimeoffCategory = function(object) {
    	if(selectedTimeoffCategory==object.attr("data-category")) {
    		object.removeClass(object.attr("data-category"));
    		selectedTimeoffCategory = null;
    		object.removeClass("categorySelected");
    		//$('#noCategorySelected').show();
    		timeOffCreateRequestHandler.setStep('1');
    		$('.timeOffCalendarWrapper').hide();
    	} else {
	    	selectedTimeoffCategory = object.attr("data-category");
//	    	console.log("SELECT " + selectedTimeoffCategory);
//	    	object.prev('div').addClass("categoryColorSelected");
	    	object.addClass("categorySelected");
	    	object.addClass(selectedTimeoffCategory);
	    	console.log(selectedTimeoffCategory);
//	    	$('#noCategorySelected').hide();
	    	timeOffCreateRequestHandler.setStep('2');
	    	$('.timeOffCalendarWrapper').show();
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
        	
        	timeOffCreateRequestHandler.setEmployeePTORemaining(json.employeeData.PTO_AVAILABLE);
        	timeOffCreateRequestHandler.setEmployeeFloatRemaining(json.employeeData.FLOAT_AVAILABLE);
        	timeOffCreateRequestHandler.setEmployeeSickRemaining(json.employeeData.SICK_AVAILABLE);
        	timeOffCreateRequestHandler.setEmployeeUnexcusedAbsenceRemaining(json.employeeData.UNEXCUSED_ABSENCE_AVAILABLE);
        	timeOffCreateRequestHandler.setEmployeeBereavementRemaining(json.employeeData.BEREAVEMENT_AVAILABLE);
        	timeOffCreateRequestHandler.setEmployeeCivicDutyRemaining(json.employeeData.CIVIC_DUTY_AVAILABLE);
        	timeOffCreateRequestHandler.setEmployeeGrandfatheredRemaining(json.employeeData.GRANDFATHERED_AVAILABLE);
        	timeOffCreateRequestHandler.setEmployeeApprovedNoPayRemaining(json.employeeData.APPROVED_NO_PAY_AVAILABLE);
//        	console.log('json.pendingRequestJson', json.pendingRequestJson);
        	timeOffCreateRequestHandler.setSelectedDates(json.approvedRequestJson, json.pendingRequestJson);
        	timeOffCreateRequestHandler.highlightDates();
            return;
        })
        .error( function() {
            console.log( 'There was some error.' );
            return;
        });
    }
    
    this.setStep = function(step) {
    	$(".step1").removeClass("active");
    	$(".step2").removeClass("active");
    	$(".step3").removeClass("active");
    	$(".step"+step).addClass("active");
    }
    
    this.submitTimeOffRequest = function() {
    	$.ajax({
            url: timeOffSubmitTimeOffRequestUrl,
            type: 'POST',
            data: {
              action: 'submitTimeoffRequest',
              selectedDates: selectedDates,
              selectedDateCategories: selectedDateCategories,
              selectedDateHours: selectedDateHours,
              requestReason: requestReason
            },
            dataType: 'json'
      	})
        .success( function(json) {
      		if(json.success==true) {
      			window.location.href = timeOffSubmitTimeOffSuccessUrl;
      		} else {
      			alert(json.message);
      		}
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
        	
        	timeOffCreateRequestHandler.setSelectedDates(json.approvedRequestJson, json.pendingRequestJson);
        	timeOffCreateRequestHandler.highlightDates();
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
    	timeOffCreateRequestHandler.printEmployeePTORemaining();
    }
    
    /**
     * Sets the remaining Float time for selected employee.
     */
    this.setEmployeeFloatRemaining = function(floatRemaining) {
    	employeeFloatRemaining = floatRemaining;
    	timeOffCreateRequestHandler.printEmployeeFloatRemaining();
    }
    
    /**
     * Sets the remaining sick time for selected employee.
     */
    this.setEmployeeSickRemaining = function(sickRemaining) {
    	employeeSickRemaining = sickRemaining;
    	timeOffCreateRequestHandler.printEmployeeSickRemaining();
    }
    
    this.setEmployeeGrandfatheredRemaining = function(grandfatheredRemaining) {
    	employeeGrandfatheredRemaining = grandfatheredRemaining;
    	timeOffCreateRequestHandler.printEmployeeGrandfatheredRemaining();
    }
    
    this.setEmployeeUnexcusedAbsenceRemaining = function(unexcusedAbsenceRemaining) {
    	employeeUnexcusedAbsenceRemaining = unexcusedAbsenceRemaining;
    	timeOffCreateRequestHandler.printEmployeeUnexcusedAbsenceRemaining();
    }
    
    this.setEmployeeBereavementRemaining = function(bereavementRemaining) {
    	employeeBereavementRemaining = bereavementRemaining;
    	timeOffCreateRequestHandler.printEmployeeBereavementRemaining();
    }
    
    this.setEmployeeCivicDutyRemaining = function(civicDutyRemaining) {
    	employeeCivicDutyRemaining = civicDutyRemaining;
    	timeOffCreateRequestHandler.printEmployeeCivicDutyRemaining();
    }
    
    this.setEmployeeApprovedNoPayRemaining = function(approvedNoPayRemaining) {
    	employeeApprovedNoPayRemaining = approvedNoPayRemaining;
    	timeOffCreateRequestHandler.printEmployeeApprovedNoPayRemaining();
    }
    
    /**
     * Prints the remaining PTO time for selected employee.
     */
    this.printEmployeePTORemaining = function() {
    	$("#employeePTOAvailableHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeePTORemaining) + " hr");
    	$("#employeePTOPendingHours").html("100.00 hr");
//    	$("#employeePTOAvailableHours").html(
//    		timeOffCreateRequestHandler.setTwoDecimalPlaces(employeePTORemaining) + " hr" +
//    		"<div class='pendingHours'>Includes<br />" +
//    		"120.00 hr pending approval</div>"
//    	);
    }
    
    /**
     * Prints the remaining Float time for selected employee.
     */
    this.printEmployeeFloatRemaining = function() {
    	$("#employeeFloatAvailableHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeFloatRemaining) + " hr");
    	$("#employeeFloatPendingHours").html("100.00 hr");
//    	$("#employeeFloatHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeFloatRemaining) + " hr");
    }
    
    /**
     * Prints the remaining Sick time for selected employee.
     */
    this.printEmployeeSickRemaining = function() {
    	$("#employeeSickAvailableHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeSickRemaining) + " hr");
    	$("#employeeSickPendingHours").html("100.00 hr");
//    	$("#employeeSickHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeSickRemaining) + " hr");
    }    
    
    this.printEmployeeGrandfatheredRemaining = function() {
    	$("#employeeGrandfatheredAvailableHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeGrandfatheredRemaining) + " hr");
    	$("#employeeGrandfatheredPendingHours").html("100.00 hr");
//    	$("#employeeGrandfatheredHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeGrandfatheredRemaining) + " hr");
    }
    
    this.printEmployeeUnexcusedAbsenceRemaining = function() {
    	$("#employeeUnexcusedAbsenceAvailableHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeUnexcusedAbsenceRemaining) + " hr");
    	$("#employeeUnexcusedAbsencePendingHours").html("100.00 hr");
//    	$("#employeeUnexcusedAbsenceHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeUnexcusedAbsenceRemaining) + " hr");
    }
    
    this.printEmployeeBereavementRemaining = function() {
    	$("#employeeBereavementAvailableHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeBereavementRemaining) + " hr");
    	$("#employeeBereavementPendingHours").html("100.00 hr");
//    	$("#employeeBereavementHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeBereavementRemaining) + " hr");
    }
    
    this.printEmployeeCivicDutyRemaining = function() {
    	$("#employeeCivicDutyAvailableHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeCivicDutyRemaining) + " hr");
    	$("#employeeCivicDutyPendingHours").html("100.00 hr");
//    	$("#employeeCivicDutyHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeCivicDutyRemaining) + " hr");
    }
    
    this.printEmployeeApprovedNoPayRemaining = function() {
    	$("#employeeApprovedNoPayAvailableHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeApprovedNoPayRemaining) + " hr");
    	$("#employeeApprovedNoPayPendingHours").html("100.00 hr");
//    	$("#employeeApprovedNoPayHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeApprovedNoPayRemaining) + " hr");
    }
    
    /**
     * Adds employee defaultHours from the current Category of time remaining.
     */
    this.addTime = function(selectedTimeoffCategory, defaultHours) {
    	switch(selectedTimeoffCategory) {
	    	case 'timeOffPTO':
	    		employeePTORemaining -= defaultHours;
	    		timeOffCreateRequestHandler.printEmployeePTORemaining();
	    		break;
	    		
	    	case 'timeOffFloat':
	    		employeeFloatRemaining -= defaultHours;
	    		timeOffCreateRequestHandler.printEmployeeFloatRemaining();
	    		break;
	    		
	    	case 'timeOffSick':
	    		employeeSickRemaining -= defaultHours;
	    		timeOffCreateRequestHandler.printEmployeeSickRemaining();
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
	    		timeOffCreateRequestHandler.printEmployeePTORemaining();
	    		break;
	    		
	    	case 'timeOffFloat':
	    		employeeFloatRemaining += defaultHours;
	    		timeOffCreateRequestHandler.printEmployeeFloatRemaining();
	    		break;
	    		
	    	case 'timeOffSick':
	    		employeeSickRemaining += defaultHours;
	    		timeOffCreateRequestHandler.printEmployeeSickRemaining();
	    		break;
		}
    }
    
    this.setSelectedDates = function(approvedRequests, pendingRequests) {
    	selectedDatesApproved = [];
    	selectedDateCategoriesApproved = [];
    	selectedDateHoursApproved = [];
    	selectedDatesPendingApproval = [];
    	selectedDateCategoriesPendingApproval = [];
    	selectedDateHoursPendingApproval = [];
    	for(key in approvedRequests) {
    		selectedDatesApproved.push(approvedRequests[key].REQUEST_DATE);
    		selectedDateCategoriesApproved.push(approvedRequests[key].REQUEST_TYPE);
    		selectedDateHoursApproved.push(approvedRequests[key].REQUESTED_HOURS);
    	}
//    	console.log("pendingRequests", pendingRequests);
    	for(key in pendingRequests) {
    		selectedDatesPendingApproval.push(pendingRequests[key].REQUEST_DATE);
    		selectedDateCategoriesPendingApproval.push(pendingRequests[key].REQUEST_TYPE);
    		selectedDateHoursPendingApproval.push(pendingRequests[key].REQUESTED_HOURS);
    	}
    }
    
    this.highlightDates = function() {
    	$.each($(".calendar-day"), function(index, blah) {
    		// Check: is $(this).attr("data-date") in array selectedDates ?
    		indexSelectedFound = selectedDates.lastIndexOf($(this).attr("data-date"));
    		indexApprovedFound = selectedDatesApproved.lastIndexOf($(this).attr("data-date"));
    		indexPendingApprovalFound = selectedDatesPendingApproval.lastIndexOf($(this).attr("data-date"));
    		
    		if(indexSelectedFound > -1) {
    			// Highlight the date.
    			thisClass = selectedDateCategories[indexSelectedFound] + "Selected";
    			$(this).toggleClass(thisClass);
//    			$(this).children("div").toggleClass(thisClass);
    		}
    		if(indexApprovedFound > -1) {
    			// Highlight the date.
    			thisClass = selectedDateCategoriesApproved[indexApprovedFound] + " requestApproved";
    			$(this).toggleClass(thisClass);
    			$(this).children("div").toggleClass(thisClass);
    		}
    		if(indexPendingApprovalFound > -1) {
    			// Highlight the date.
    			thisClass = selectedDateCategoriesPendingApproval[indexPendingApprovalFound] + " requestPending";
    			$(this).toggleClass(thisClass);
    			$(this).children("div").toggleClass(thisClass);
    		}
    	});
    }
    
    /**
     * Rounds a number to two decimal places.
     */
    this.setTwoDecimalPlaces = function(num) {
        return parseFloat(Math.round(num * 100) / 100).toFixed(2);
    }
    
    this.isSelected = function(date, category) {
    	for(var i = 0; i < selectedDatesNew.length; i++) {
    		if(selectedDatesNew[i].date===thisDate && selectedDatesNew[i].category===thisCategory) {
    			return true;
    		}
    	}
    	return false;
    }
    
    this.sortDatesSelected = function() {
    	selectedDatesNew.sort(function(a,b) {
			var dateA = new Date(a.date).getTime();
	        var dateB = new Date(b.date).getTime();
	        return dateA > dateB ? 1 : -1; 
		});
		console.log(selectedDatesNew);
    }
};

// Initialize the class
timeOffCreateRequestHandler.initialize();