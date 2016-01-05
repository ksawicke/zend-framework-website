/**
 * Javascript timeOffCreateRequestHandler 'class'
 *
 */
var timeOffCreateRequestHandler = new function()
{
    var timeOffLoadCalendarUrl = 'http://swift:10080/sawik/timeoff/public/request/api',
    	timeOffSubmitTimeOffRequestUrl = 'http://swift:10080/sawik/timeoff/public/request/api',
    	timeOffSubmitTimeOffSuccessUrl = 'http://swift:10080/sawik/timeoff/public/request/submitted-for-approval',
    	employeePTOAvailable = 0,
    	employeeFloatAvailable = 0,
    	employeeSickAvailable = 0,
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
    	loggedInUserData = [],
    	requestForEmployeeNumber = '',
    	requestForEmployeeName = '',
    	requestReason = '',
    	/** Dates selected for this request **/
    	
    	selectedDatesNew = [],
    	selectedDatesApproved = [],
    	selectedDatesPendingApproval = [],
    	
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
    	selectedDateHoursPendingApproval = [],
    	categoryText = {
    		'timeOffPTO': 'PTO',
    		'timeOffFloat': 'Float',
    		'timeOffSick': 'Sick',
    		'timeOffGrandfathered': 'Grandfathered',
    		'timeOffUnexcusedAbsence': 'Unexcused',
    		'timeOffBereavement': 'Bereavement',
    		'timeOffCivicDuty': 'Civic Duty',
    		'timeOffApprovedNoPay': 'Approved No Pay'
    	};

    /**
     * Initializes binding
     */
    this.initialize = function() {
        $(document).ready(function() {
        	//var data = [{ id: 0, text: 'enhancement' }, { id: 1, text: 'bug' }, { id: 2, text: 'duplicate' }, { id: 3, text: 'invalid' }, { id: 4, text: 'wontfix' }];
        	
        	$(document).on('click', '.requestIsForMe', function() {
        		$('.requestIsForMe').hide();
        		//console.log('initial', loggedInUserData);
        		requestForEmployeeNumber = loggedInUserData.EMPLOYEE_NUMBER;
            	requestForEmployeeName = loggedInUserData.COMMON_NAME + " " + loggedInUserData.LAST_NAME;
        		$("#requestFor")
        			.empty()
        			.append('<option value="'+requestForEmployeeNumber+'">'+requestForEmployeeName+'</option>')
        			.val(requestForEmployeeNumber).trigger('change');
        		timeOffCreateRequestHandler.loadCalendars(requestForEmployeeNumber);
        		
        	});
        	
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
        		if(!$(this).hasClass('disableTimeOffCategorySelection')) {
        			timeOffCreateRequestHandler.resetTimeoffCategory($(this));
            		timeOffCreateRequestHandler.setTimeoffCategory($(this));
        		}
        		if($(this).hasClass('disableTimeOffCategorySelection') && $(this).hasClass('categoryPTO')) {
        			$( "#dialogGrandfatheredAlert").dialog({
    			      modal: true,
    			      buttons: {
    			        Ok: function() {
    			          $( this ).dialog( "close" );
    			        }
    			      }
    			    });
        		}
        	});
        	
        	$(document).on('change', '.selectedDateHours', function() {
        		var key = $(this).attr("data-key");
        		var value = $(this).val();
        		selectedDateHours[key] = value;
        	});
        	
        	$(document).on('click', '.submitTimeOffRequest', function() {
        		requestReason = $("#requestReason").val();
        		timeOffCreateRequestHandler.submitTimeOffRequest();
        	});
        	
        	$(document).on('click', '.date-requested', function() {
        		var dateSelected = timeOffCreateRequestHandler.isSelected($(this));
        		if(selectedTimeoffCategory != null) {
        			timeOffCreateRequestHandler.removeDateFromRequest(dateSelected);
        			timeOffCreateRequestHandler.drawHoursRequested();
        		}
        	});
        	
        	$(document).on('click', '.changerequestForEmployeeNumber', function() {
        		timeOffCreateRequestHandler.loadCalendars($(this).attr("data-employee-number"));
        	});
        	
        	/**
        	 * Handle clicking a calendar date
        	 */
        	$(document).on('click', '.calendar-day', function() {
        		var dateSelected = timeOffCreateRequestHandler.isSelected($(this));
        		var isDateDisabled = timeOffCreateRequestHandler.isDateDisabled($(this));
        		if(selectedTimeoffCategory != null && isDateDisabled === false) {
        			timeOffCreateRequestHandler.removeDateFromRequest(dateSelected);
        			timeOffCreateRequestHandler.drawHoursRequested();
        		}
        	});
        	
        	timeOffCreateRequestHandler.loadCalendars();
//        	timeOffCreateRequestHandler.checkLocalStorage();
        	
        	$('.timeOffCalendarWrapper').hide();
        });
    }
    
    this.isDateDisabled = function(object) {
    	return ( object.hasClass("calendar-day-disabled") ? true : false );
    }
    
    this.getCategoryText = function(category) {
    	return categoryText[category];
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
     * Resets the Available sick time for selected employee.
     */
    this.resetTimeoffCategory = function(object) {
    	$('.btn-requestCategory').removeClass("categorySelected");
    	$('.btn-requestCategory').removeClass(selectedTimeoffCategory);

    	for(category in categoryText) {
    		$('.'+category+'CloseIcon').removeClass('categoryCloseIcon glyphicon glyphicon-remove-circle');
    		$('.buttonDisappear'+category.substr(7)).show();
    	}
    }
    
    /**
     * Sets the currently selected time off category.
     */
    this.setTimeoffCategory = function(object) {
    	if(selectedTimeoffCategory==object.attr("data-category")) {
    		object.removeClass(object.attr("data-category"));
    		selectedTimeoffCategory = null;
    		object.removeClass("categorySelected");
    		$('.'+object.attr("data-category")+'CloseIcon').removeClass('categoryCloseIcon glyphicon glyphicon-remove-circle');
    		timeOffCreateRequestHandler.setStep('1');
    		$('.timeOffCalendarWrapper').hide();
    	} else {
	    	selectedTimeoffCategory = object.attr("data-category");
	    	object.addClass("categorySelected");
	    	object.addClass(selectedTimeoffCategory);

	    	for(category in categoryText) {
	    		if(selectedTimeoffCategory!=category) {
	    			$('.buttonDisappear'+category.substr(7)).hide();
	    		}
	    	}
	    	
	    	if(selectedDatesNew.length>0) {
	    		timeOffCreateRequestHandler.setStep('3');
	    	} else {
	    		timeOffCreateRequestHandler.setStep('2');
	    	}
	    	$('.'+selectedTimeoffCategory+'CloseIcon').addClass('categoryCloseIcon glyphicon glyphicon-remove-circle');
	    	$('.timeOffCalendarWrapper').show();
    	}
    }
    
    /**
     * Loads calendars via ajax and displays them on the page.
     */
    this.loadCalendars = function(employeeNumber) {
    	var month = (new Date()).getMonth() + 1;
    	var year = (new Date()).getFullYear();
    	$.ajax({
          url: timeOffLoadCalendarUrl,
          type: 'POST',
          data: {
        	  action: 'loadCalendar',
        	  startMonth: month,
        	  startYear: year,
        	  employeeNumber: employeeNumber
          },
          dataType: 'json'
    	})
        .success( function(json) {
        	if(requestForEmployeeNumber==='') {
        		loggedInUserData = json.employeeData;
        	}
        	
        	requestForEmployeeNumber = employeeNumber;
//        	console.log("requestForEmployeeNumber", requestForEmployeeNumber);
        	var calendarHtml = '';
        	$.each(json.calendars, function(index, thisCalendarHtml) {
        		$("#calendar" + index + "Html").html(
        			json.openHeader +
        			
        			( (index==1) ? json.fastRewindButton + ' ' + json.prevButton : '' ) +
        			thisCalendarHtml.header + ( (index==3) ? json.nextButton + ' ' + json.fastForwardButton : '' ) +
        		    json.closeHeader +
        		    thisCalendarHtml.data);
        	});
        	
        	timeOffCreateRequestHandler.setEmployeePTOAvailable(json.employeeData.PTO_AVAILABLE);
        	timeOffCreateRequestHandler.setEmployeePTOPending(json.employeeData.PTO_PENDING_APPROVAL);
        	
        	timeOffCreateRequestHandler.setEmployeeFloatAvailable(json.employeeData.FLOAT_AVAILABLE);
        	timeOffCreateRequestHandler.setEmployeeFloatPending(json.employeeData.FLOAT_PENDING_APPROVAL);
        	
        	timeOffCreateRequestHandler.setEmployeeSickAvailable(json.employeeData.SICK_AVAILABLE);
        	timeOffCreateRequestHandler.setEmployeeSickPending(json.employeeData.SICK_PENDING_APPROVAL);
        	
        	timeOffCreateRequestHandler.setEmployeeUnexcusedAbsenceAvailable(json.employeeData.UNEXCUSED_ABSENCE_AVAILABLE);
        	timeOffCreateRequestHandler.setEmployeeUnexcusedAbsencePending(json.employeeData.UNEXCUSED_ABSENCE_PENDING_APPROVAL);
        	
        	timeOffCreateRequestHandler.setEmployeeBereavementAvailable(json.employeeData.BEREAVEMENT_AVAILABLE);
        	timeOffCreateRequestHandler.setEmployeeBereavementPending(json.employeeData.BEREAVEMENT_PENDING_APPROVAL);
        	
        	timeOffCreateRequestHandler.setEmployeeCivicDutyAvailable(json.employeeData.CIVIC_DUTY_AVAILABLE);
        	timeOffCreateRequestHandler.setEmployeeCivicDutyPending(json.employeeData.CIVIC_DUTY_PENDING_APPROVAL);
        	
        	timeOffCreateRequestHandler.setEmployeeGrandfatheredAvailable(json.employeeData.GRANDFATHERED_AVAILABLE);
        	timeOffCreateRequestHandler.setEmployeeGrandfatheredPending(json.employeeData.GRANDFATHERED_PENDING_APPROVAL);
        	
        	timeOffCreateRequestHandler.setEmployeeApprovedNoPayAvailable(json.employeeData.APPROVED_NO_PAY_AVAILABLE);
        	timeOffCreateRequestHandler.setEmployeeApprovedNoPayPending(json.employeeData.APPROVED_NO_PAY_PENDING_APPROVAL);
        	
    		timeOffCreateRequestHandler.setSelectedDates(json.approvedRequestJson, json.pendingRequestJson);
        	timeOffCreateRequestHandler.highlightDates();
        	
        	// $(this).hasClass('disableTimeOffCategorySelection')
        	if(json.employeeData.GRANDFATHERED_AVAILABLE > 0) {
        		$('.categoryPTO').addClass('disableTimeOffCategorySelection');
        	}
        	
        	requestForEmployeeNumber = $.trim(json.employeeData.EMPLOYEE_NUMBER);
        	requestForEmployeeName =
        		timeOffCreateRequestHandler.capitalizeFirstLetter(json.employeeData.LAST_NAME) + ", " +
        		timeOffCreateRequestHandler.capitalizeFirstLetter(json.employeeData.COMMON_NAME) +
        		' (' + requestForEmployeeNumber + ') - ' + json.employeeData.POSITION_TITLE;
        	
        	$("#requestFor")
        		.empty()
        		.append('<option value="'+requestForEmployeeNumber+'">'+requestForEmployeeName+'</option>')
        		.val(requestForEmployeeNumber).trigger('change');
        	
        	timeOffCreateRequestHandler.checkAllowRequestOnBehalfOf();
        	
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
              selectedDatesNew: selectedDatesNew,
              requestReason: requestReason,
              employeeNumber: requestForEmployeeNumber,
              loggedInUserData: loggedInUserData
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
        	  startYear: startYear,
        	  employeeNumber: requestForEmployeeNumber
          },
          dataType: 'json'
    	})
        .success( function(json) {
        	var calendarHtml = '';
        	$.each(json.calendars, function(index, thisCalendarHtml) {
        		$("#calendar" + index + "Html").html(
        			json.openHeader +
        			
        			( (index==1) ? json.fastRewindButton + '&nbsp;&nbsp;&nbsp;' + json.prevButton : '' ) +
        			thisCalendarHtml.header + ( (index==3) ? json.nextButton + '&nbsp;&nbsp;&nbsp;' + json.fastForwardButton : '' ) +
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
     * Prints the Available PTO time for selected employee.
     */
    this.setEmployeePTOAvailable = function(ptoAvailable) {
    	employeePTOAvailable = ptoAvailable;
    	timeOffCreateRequestHandler.printEmployeePTOAvailable();
    }
    
    this.setEmployeePTOPending = function(ptoPending) {
    	employeePTOPending = ptoPending;
    	timeOffCreateRequestHandler.printEmployeePTOPending();
    }
    
    /**
     * Sets the Available Float time for selected employee.
     */
    this.setEmployeeFloatAvailable = function(floatAvailable) {
    	employeeFloatAvailable = floatAvailable;
    	timeOffCreateRequestHandler.printEmployeeFloatAvailable();
    }
    
    this.setEmployeeFloatPending = function(floatPending) {
    	employeeFloatPending = floatPending;
    	timeOffCreateRequestHandler.printEmployeeFloatPending();
    }
    
    /**
     * Sets the Available sick time for selected employee.
     */
    this.setEmployeeSickAvailable = function(sickAvailable) {
    	employeeSickAvailable = sickAvailable;
    	timeOffCreateRequestHandler.printEmployeeSickAvailable();
    }
    
    this.setEmployeeSickPending = function(sickPending) {
    	employeeSickPending = sickPending;
    	timeOffCreateRequestHandler.printEmployeeSickPending();
    }
    
    this.setEmployeeGrandfatheredAvailable = function(grandfatheredAvailable) {
    	employeeGrandfatheredAvailable = grandfatheredAvailable;
    	timeOffCreateRequestHandler.printEmployeeGrandfatheredAvailable();
    }
    
    this.setEmployeeGrandfatheredPending = function(grandfatheredPending) {
    	employeeGrandfatheredPending = grandfatheredPending;
    	timeOffCreateRequestHandler.printEmployeeGrandfatheredPending();
    }
    
    this.setEmployeeUnexcusedAbsenceAvailable = function(unexcusedAbsenceAvailable) {
    	employeeUnexcusedAbsenceAvailable = unexcusedAbsenceAvailable;
    	timeOffCreateRequestHandler.printEmployeeUnexcusedAbsenceAvailable();
    }
    
    this.setEmployeeUnexcusedAbsencePending = function(unexcusedAbsencePending) {
    	employeeUnexcusedAbsencePending = unexcusedAbsencePending;
    	timeOffCreateRequestHandler.printEmployeeUnexcusedAbsencePending();
    }
    
    this.setEmployeeBereavementAvailable = function(bereavementAvailable) {
    	employeeBereavementAvailable = bereavementAvailable;
    	timeOffCreateRequestHandler.printEmployeeBereavementAvailable();
    }
    
    this.setEmployeeBereavementPending = function(bereavementPending) {
    	employeeBereavementPending = bereavementPending;
    	timeOffCreateRequestHandler.printEmployeeBereavementPending();
    }
    
    this.setEmployeeCivicDutyAvailable = function(civicDutyAvailable) {
    	employeeCivicDutyAvailable = civicDutyAvailable;
    	timeOffCreateRequestHandler.printEmployeeCivicDutyAvailable();
    }
    
    this.setEmployeeCivicDutyPending = function(civicDutyPending) {
    	employeeCivicDutyPending = civicDutyPending;
    	timeOffCreateRequestHandler.printEmployeeCivicDutyPending();
    }
    
    this.setEmployeeApprovedNoPayAvailable = function(approvedNoPayAvailable) {
    	employeeApprovedNoPayAvailable = approvedNoPayAvailable;
    	timeOffCreateRequestHandler.printEmployeeApprovedNoPayAvailable();
    }
    
    this.setEmployeeApprovedNoPayPending = function(approvedNoPayPending) {
    	employeeApprovedNoPayPending = approvedNoPayPending;
    	timeOffCreateRequestHandler.printEmployeeApprovedNoPayPending();
    }
    
    /**
     * Prints the Available PTO time for selected employee.
     */
    this.printEmployeePTOAvailable = function() {
    	$("#employeePTOAvailableHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeePTOAvailable) + " hr");
    	if(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeePTOAvailable)<=0) {
    		$('.buttonDisappearPTO').addClass('hidden');
    	} else {
    		$('.buttonDisappearPTO').removeClass('hidden');
    	}
    }
    
    this.printEmployeePTOPending = function() {
    	$("#employeePTOPendingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeePTOPending) + " hr");
    }
    
    /**
     * Prints the Available Float time for selected employee.
     */
    this.printEmployeeFloatAvailable = function() {
    	$("#employeeFloatAvailableHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeFloatAvailable) + " hr");
    	if(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeFloatAvailable)<=0) {
    		$('.buttonDisappearFloat').addClass('hidden');
    	} else {
    		$('.buttonDisappearFloat').removeClass('hidden');
    	}
    }
    
    this.printEmployeeFloatPending = function() {
    	$("#employeeFloatPendingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeFloatPending) + " hr");
    }
    
    /**
     * Prints the Available Sick time for selected employee.
     */
    this.printEmployeeSickAvailable = function() {
    	$("#employeeSickAvailableHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeSickAvailable) + " hr");
    	if(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeSickAvailable)<=0) {
    		$('.buttonDisappearSick').addClass('hidden');
    	} else {
    		$('.buttonDisappearSick').removeClass('hidden');
    	}
    }
    
    this.printEmployeeSickPending = function() {
    	$("#employeeSickPendingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeSickPending) + " hr");
    }
    
    this.printEmployeeGrandfatheredAvailable = function() {
    	$("#employeeGrandfatheredAvailableHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeGrandfatheredAvailable) + " hr");
    	if(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeGrandfatheredAvailable)<=0) {
    		$('.buttonDisappearGrandfathered').addClass('hidden');
    	}
    }
    
    this.printEmployeeGrandfatheredPending = function() {
    	$("#employeeGrandfatheredPendingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeGrandfatheredPending) + " hr");
    }
    
    this.printEmployeeUnexcusedAbsenceAvailable = function() {
    	$("#employeeUnexcusedAbsenceAvailableHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeUnexcusedAbsenceAvailable) + " hr");
    	if(employeeUnexcusedAbsenceAvailable<=0) {
    		$('.buttonDisappearUnexcusedAbsence').addClass('hidden');
    	}
    }
    
    this.printEmployeeUnexcusedAbsencePending = function() {
    	$("#employeeUnexcusedAbsencePendingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeUnexcusedAbsencePending) + " hr");
    }
    
    this.printEmployeeBereavementAvailable = function() {
    	$("#employeeBereavementAvailableHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeBereavementAvailable) + " hr");
    	if(employeeBereavementAvailable<=0) {
    		$('.buttonDisappearBereavementAbsence').addClass('hidden');
    	}
    }
    
    this.printEmployeeBereavementPending = function() {
    	$("#employeeBereavementPendingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeBereavementPending) + " hr");
    }
    
    this.printEmployeeCivicDutyAvailable = function() {
    	$("#employeeCivicDutyAvailableHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeCivicDutyAvailable) + " hr");
    	if(employeeCivicDutyAvailable<=0) {
    		$('.buttonDisappearCivicDutyAbsence').addClass('hidden');
    	}
    }
    
    this.printEmployeeCivicDutyPending = function() {
    	$("#employeeCivicDutyPendingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeCivicDutyPending) + " hr");
    }
    
    this.printEmployeeApprovedNoPayAvailable = function() {
    	$("#employeeApprovedNoPayAvailableHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeApprovedNoPayAvailable) + " hr");
    	if(employeeApprovedNoPayAvailable<=0) {
    		$('.buttonDisappearApprovedNoPayAbsence').addClass('hidden');
    	}
    }
    
    this.printEmployeeApprovedNoPayPending = function() {
    	$("#employeeApprovedNoPayPendingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeApprovedNoPayPending) + " hr");
    }
    
    /**
     * Adds employee defaultHours from the current Category of time Available.
     */
    this.addTime = function(selectedTimeoffCategory, defaultHours) {
    	switch(selectedTimeoffCategory) {
	    	case 'timeOffPTO':
	    		employeePTOAvailable -= defaultHours;
	    		timeOffCreateRequestHandler.printEmployeePTOAvailable();
	    		break;
	    		
	    	case 'timeOffFloat':
	    		employeeFloatAvailable -= defaultHours;
	    		timeOffCreateRequestHandler.printEmployeeFloatAvailable();
	    		break;
	    		
	    	case 'timeOffSick':
	    		employeeSickAvailable -= defaultHours;
	    		timeOffCreateRequestHandler.printEmployeeSickAvailable();
	    		break;
    	}
    }
    
    /**
     * Subtracts employee defaultHours from the current Category of time Available.
     */
    this.subtractTime = function(selectedTimeoffCategory, defaultHours) {
    	switch(selectedTimeoffCategory) {
	    	case 'timeOffPTO':
	    		employeePTOAvailable += defaultHours;
	    		timeOffCreateRequestHandler.printEmployeePTOAvailable();
	    		break;
	    		
	    	case 'timeOffFloat':
	    		employeeFloatAvailable += defaultHours;
	    		timeOffCreateRequestHandler.printEmployeeFloatAvailable();
	    		break;
	    		
	    	case 'timeOffSick':
	    		employeeSickAvailable += defaultHours;
	    		timeOffCreateRequestHandler.printEmployeeSickAvailable();
	    		break;
		}
    }
    
    this.setSelectedDates = function(approvedRequests, pendingRequests) {
    	selectedDatesApproved = [];
    	selectedDatesPendingApproval = [];
    	
    	for(key in approvedRequests) {
    		var obj = { date: approvedRequests[key].REQUEST_DATE,
    				    hours: approvedRequests[key].REQUESTED_HOURS,
    				    category: approvedRequests[key].REQUEST_TYPE
    				  };
    		selectedDatesApproved.push(obj);
    	}

    	for(key in pendingRequests) {
    		var obj = { date: pendingRequests[key].REQUEST_DATE,
				    	hours: pendingRequests[key].REQUESTED_HOURS,
				    	category: pendingRequests[key].REQUEST_TYPE
				  	  };
    		selectedDatesPendingApproval.push(obj);
    	}
    }
    
    this.highlightDates = function() {
    	$.each($(".calendar-day"), function(index, blah) {    		
    		for(var i = 0; i < selectedDatesNew.length; i++) {
				if(selectedDatesNew[i].date &&
				   selectedDatesNew[i].date===$(this).attr("data-date")) {
					thisClass = selectedDatesNew[i].category + "Selected";
	    			$(this).toggleClass(thisClass);
					break;
	    		}
	    	}
    		
    		for(var i = 0; i < selectedDatesPendingApproval.length; i++) {
				if(selectedDatesPendingApproval[i].date &&
				   selectedDatesPendingApproval[i].date===$(this).attr("data-date")) {
					thisClass = selectedDatesPendingApproval[i].category + " requestPending";
	    			$(this).toggleClass(thisClass);
					break;
	    		}
	    	}
    		
    		for(var i = 0; i < selectedDatesApproved.length; i++) {
				if(selectedDatesApproved[i].date &&
				   selectedDatesApproved[i].date===$(this).attr("data-date")) {
					thisClass = selectedDatesApproved[i].category + " requestApproved";
	    			$(this).toggleClass(thisClass);
					break;
	    		}
	    	}
    	});
    }
    
    /**
     * Rounds a number to two decimal places.
     */
    this.setTwoDecimalPlaces = function(num) {
        return parseFloat(Math.round(num * 100) / 100).toFixed(2);
    }
    
    /**
     * Determines if the date is selected and returns an object we can handle later.
     */
    this.isSelected = function(object) {
    	var thisDate = object.attr("data-date");
		var thisCategory = selectedTimeoffCategory;
		var thisHours = defaultHours;
		var obj = {date:object.attr("data-date"), hours:'8.00', category:selectedTimeoffCategory};
		var isSelected = false;
		var deleteIndex = '';
		
		for(var i = 0; i < selectedDatesNew.length; i++) {
			if(selectedDatesNew[i].date &&
			   selectedDatesNew[i].date===thisDate &&
			   selectedDatesNew[i].category &&
			   selectedDatesNew[i].category===thisCategory) {
				isSelected = true;
				deleteIndex = i;
				break;
    		}
    	}
		
		return {isSelected:isSelected, deleteIndex:i, obj:obj};
    }
    
    /**
     * Removes a date from the request.
     */
    this.removeDateFromRequest = function(dateSelected) {
    	if(dateSelected.isSelected===false) {
			selectedDatesNew.push(dateSelected.obj);
			timeOffCreateRequestHandler.addTime(selectedTimeoffCategory, defaultHours);
		}
		else {
			selectedDatesNew.splice(dateSelected.deleteIndex, 1);
			timeOffCreateRequestHandler.subtractTime(selectedTimeoffCategory, defaultHours);
		}

    	$.each($('.calendar-day'), function(index, object) {
    		if(dateSelected.obj.date==$(this).data("date")) {
    			$(this).toggleClass(selectedTimeoffCategory + "Selected");
    		}
    	});
		
    	timeOffCreateRequestHandler.sortDatesSelected();
    }
    
    /**
     * Draws form fields we can submit for the user.
     */
    this.drawHoursRequested = function() {
    	var datesSelectedDetailsHtml = '<strong>Hours Requested:</strong>' +
		'<br style="clear:both;"/><br style="clear:both;"/>';
		
		totalPTORequested = 0;
		totalFloatRequested = 0;
		totalSickRequested = 0;
		totalUnexcusedAbsenceRequested = 0;
    	totalBereavementRequested = 0;
    	totalCivicDutyRequested = 0;
    	totalGrandfatheredRequested = 0;
    	totalApprovedNoPayRequested = 0;
    	
		for(var key = 0; key < selectedDatesNew.length; key++) {
			datesSelectedDetailsHtml += selectedDatesNew[key].date + '&nbsp;&nbsp;&nbsp;&nbsp;<input class="selectedDateHours" value="8.00" size="2" data-key="' + key + '" disabled="disabled">&nbsp;&nbsp;&nbsp;&nbsp;<span class="badge ' + selectedDatesNew[key].category + '">' + timeOffCreateRequestHandler.getCategoryText(selectedDatesNew[key].category) + '</span>&nbsp;&nbsp;&nbsp;<span class="glyphicon glyphicon-remove-circle red date-requested" data-date="' + selectedDatesNew[key].date + '"></span><br style="clear:both;" />';
			
			switch(selectedDatesNew[key].category) {
				case 'timeOffPTO':
					totalPTORequested += parseInt(selectedDatesNew[key].hours, 10);
					break;
					
				case 'timeOffFloat':
					totalFloatRequested += parseInt(selectedDatesNew[key].hours, 10);
					break;
					
				case 'timeOffSick':
					totalSickRequested += parseInt(selectedDatesNew[key].hours, 10);
					break;
					
				case 'timeOffUnexcusedAbsence':
					totalUnexcusedAbsenceRequested += parseInt(selectedDatesNew[key].hours, 10);
					break;
					
				case 'timeOffBereavement':
					totalBereavementRequested += parseInt(selectedDatesNew[key].hours, 10);
					break;
					
				case 'timeOffCivicDuty':
					totalCivicDutyRequested += parseInt(selectedDatesNew[key].hours, 10);
					break;
					
				case 'timeOffGrandfathered':
					totalGrandfatheredRequested += parseInt(selectedDatesNew[key].hours, 10);
					break;
					
				case 'timeOffApprovedNoPay':
					totalApprovedNoPayRequested += parseInt(selectedDatesNew[key].hours, 10);
					break;
			}
		}
		
		datesSelectedDetailsHtml +=
			'<br style="clear:both;"/>' +
    		'<strong>Reason for request:</strong>' +
			'<br style="clear:both;"/>' +
    		'<br style="clear:both;"/>' +
    		'<textarea cols="40" rows="4" id="requestReason"></textarea><br /><br />' +
			'<button type="button" class="btn btn-form-primary btn-lg submitTimeOffRequest">Submit My Request</button>' +
			'<br style="clear:both;" /><br style="clear:both;" />';
		
		$("#datesSelectedDetails").html(datesSelectedDetailsHtml);
		
		if(selectedDatesNew.length===0) {
			$('#datesSelectedDetails').hide();
			timeOffCreateRequestHandler.setStep('2');
		} else {
			$('#datesSelectedDetails').show();
			timeOffCreateRequestHandler.setStep('3');
		}
    }
    
    /**
     * Sorts dates in the selected array.
     */
    this.sortDatesSelected = function() {
    	selectedDatesNew.sort(function(a,b) {
			var dateA = new Date(a.date).getTime();
	        var dateB = new Date(b.date).getTime();
	        return dateA > dateB ? 1 : -1; 
		});
		console.log(selectedDatesNew);
    }
    
    this.selectResult = function(item) {
    	timeOffCreateRequestHandler.loadCalendars(item.value);
    }
    
    this.setAsRequestForAnother = function() {
    	$('.requestIsForMe').hide();
		$('.requestIsForAnother').show();
		$('.requestIsForAnother').focus();
    }
    
    this.setAsRequestForMe = function() {
    	$('.requestIsForMe').show();
		$('.requestIsForAnother').hide();
		timeOffCreateRequestHandler.clearRequestFor();
    }
    
    this.clearRequestFor = function() {
    	$('#demo5').val('');
    }
    
    this.requestForAnotherComplete = function() {
    	$(".requestIsForAnother").append(' <span class="categoryCloseIcon glyphicon glyphicon-remove-circle red"></span>');
    }
    
    this.capitalizeFirstLetter = function(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
    
    this.log = function(name, evt) {
    	if(!evt) {
    		var args = "{}";
    	} else {
    		var args = JSON.stringify(evt.params, function(key, value) {
    			if(value && value.nodeName) {
    				return "[DOM node]";
    			}
    			if(value instanceof $.Event) {
    				return "[$.Event]";
    			}
    			return value;
    		});
    	}
    	var $e = $("<li>" + name + " -> " + args + "</li>");
    	$eventLog.append($e);
    	$e.animate({ opacity: 1 }, 10000, 'linear', function() {
    		$e.animate({ opacity: 0 }, 2000, 'linear', function() {
    			$e.remove();
    		});
    	});
    }
    
    this.checkAllowRequestOnBehalfOf = function() {
    	if(loggedInUserData.IS_LOGGED_IN_USER_MANAGER==="Y") {
    		timeOffCreateRequestHandler.enableSelectRequestFor();
    		$("#requestFor").prop('disabled', false);
    	} else {
    		$("#requestFor").prop('disabled', true);
    		$(".categoryBereavement").hide();
    		$(".categoryCivicDuty").hide();
    		$(".categoryApprovedNoPay").hide();
    		$(".categoryUnexcusedAbsence").hide();
    	}
    }
    
    this.enableSelectRequestFor = function() {
    	var $eventLog = $(".js-event-log");
    	var $requestForEventSelect = $("#requestFor");
    	$("#requestFor").select2({
    		//data: data
    		ajax: {
    			 url: timeOffLoadCalendarUrl,
    			 method: 'post',
    			 dataType: 'json',
    			 delay: 250,
    			 data: function(params) {
    				 return {
    					 search: params.term,
    					 action: 'getEmployeeList',
    					 page: params.page
    				 };
    			 },
    			 processResults: function(data, params) {
    				 params.page = params.page || 1;
    				 
    				 return {
    					 results: data,
    					 pagination: {
    						 more: (params.page * 30) < data.total_count
    					 }
    				 };
    			 },
//    			 cache: true,
    			 allowClear: true
    		},
//    		escapeMarkup: function(markup) {
//    			return markup;
//    		},
    		minimumInputLength: 2,
//    		theme: "classic"
//    		templateResult: function(result) {
//		        var markup = result.employeeName + '<br />';
//
//		        return markup;
//		    },
//    		templateSelection: function(repo) {
//    		      return repo.full_name || repo.text;
//    	    }
    	});

    	/**
    	 * When we change the for dropdown using select2,
    	 * set the employee number and name as a local variable
    	 * for form submission, and refresh the calendars.
    	 */
    	$requestForEventSelect.on("select2:select", function (e) {
    		var selectedEmployee = e.params.data;
    		console.log("SELECTED EMPLOYEE", selectedEmployee);
    		requestForEmployeeNumber = selectedEmployee.id;
        	requestForEmployeeName = selectedEmployee.text;
        	timeOffCreateRequestHandler.loadCalendars(requestForEmployeeNumber);
        	$('.requestIsForMe').show();
    	});
    }
};

// Initialize the class
timeOffCreateRequestHandler.initialize();