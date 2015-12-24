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
//        			 cache: true,
        			 allowClear: true
        		},
//        		escapeMarkup: function(markup) {
//        			return markup;
//        		},
        		minimumInputLength: 2,
//        		theme: "classic"
//        		templateResult: function(result) {
//    		        var markup = result.employeeName + '<br />';
//
//    		        return markup;
//    		    },
//        		templateSelection: function(repo) {
//        		      return repo.full_name || repo.text;
//        	    }
        	});

        	/**
        	 * When we change the for dropdown using select2,
        	 * set the employee number and name as a local variable
        	 * for form submission, and refresh the calendars.
        	 */
        	$requestForEventSelect.on("select2:select", function (e) {
        		var selectedEmployee = e.params.data;
        		console.log(selectedEmployee);
        		requestForEmployeeNumber = selectedEmployee.id;
            	requestForEmployeeName = selectedEmployee.text;
            	timeOffCreateRequestHandler.loadCalendars(requestForEmployeeNumber);
            	$('.requestIsForMe').show();
        	});
        	
//        	$('#demo5').typeahead({
//                ajax: {
//                    url: timeOffLoadCalendarUrl,
//                    method: 'post',
//                    preDispatch: function (query) {
//                        return {
//                            search: query,
//                            action: 'getEmployeeList'
//                        }
//                    },
//                    valueField: 'employeeNumber',
//                    displayField: 'employeeName',
//                    triggerLength: 1
//                },
//                onSelect: function(selection) {
//                	requestForEmployeeNumber = selection.value;
//                	requestForEmployeeName = selection.text;
//                	timeOffCreateRequestHandler.requestForAnotherComplete();
//                	timeOffCreateRequestHandler.loadCalendars(requestForEmployeeNumber);
//                	timeOffCreateRequestHandler.setAsRequestForAnother();
//                	console.log(selection);
//                	console.log("Request is for", requestForEmployeeNumber);
//                }
//            });
        	
//        	$(document).on('click', '.requestIsForMe', function() {
//        		timeOffCreateRequestHandler.setAsRequestForAnother();
//        	});
//        	
//        	$(document).on('click', '.requestIsForAnother', function() {
//        		timeOffCreateRequestHandler.clearRequestFor();
//        	});
        	
        	$(document).on('click', '.requestIsForMe', function() {
        		$('.requestIsForMe').hide();
        		//console.log('initial', loggedInUserData);
        		requestForEmployeeNumber = loggedInUserData.EMPLOYEE_NUMBER;
            	requestForEmployeeName = loggedInUserData.LAST_NAME+', '+loggedInUserData.FIRST_NAME;
        		$("#requestFor")
        			.empty()
        			.append('<option value="'+requestForEmployeeNumber+'">'+requestForEmployeeName+'</option>')
        			.val(requestForEmployeeNumber).trigger('change');
        		timeOffCreateRequestHandler.loadCalendars(requestForEmployeeNumber);
        		
        	});

//        	$(document).on('focusout', '#demo5', function () {
//        		if(requestForEmployeeName==="" &&
//        		   requestForEmployeeName===$("#demo5").val() &&
//        		   $("#demo5").val()===""
//        		  ) {
//        			timeOffCreateRequestHandler.setAsRequestForMe();
//        		}
//        	});
        	
//        	$(document).on('blur', '#demo5', function () {
////        		timeOffCreateRequestHandler.setAsRequestForMe();
//        	});
        	
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
        		if(selectedTimeoffCategory != null) {
        			timeOffCreateRequestHandler.removeDateFromRequest(dateSelected);
        			timeOffCreateRequestHandler.drawHoursRequested();
        		}
        	});
        	
        	timeOffCreateRequestHandler.loadCalendars();
        	timeOffCreateRequestHandler.checkLocalStorage();
//        	timeOffCreateRequestHandler.enableTypeAhead();
        	
        	$('.timeOffCalendarWrapper').hide();
        });
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
     * Resets the remaining sick time for selected employee.
     */
    this.resetTimeoffCategory = function(object) {
//    	console.log("OBJECT", object);
    	$('.btn-requestCategory').removeClass("categorySelected");
    	$('.btn-requestCategory').removeClass(selectedTimeoffCategory);
    	//categoryText
    	for(category in categoryText) {
    		$('.'+category+'CloseIcon').removeClass('categoryCloseIcon glyphicon glyphicon-remove-circle');
    		$('.buttonDisappear'+category.substr(7)).show();
    	}
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
    		$('.'+object.attr("data-category")+'CloseIcon').removeClass('categoryCloseIcon glyphicon glyphicon-remove-circle');
    		//$('#noCategorySelected').show();
    		timeOffCreateRequestHandler.setStep('1');
    		$('.timeOffCalendarWrapper').hide();
    	} else {
	    	selectedTimeoffCategory = object.attr("data-category");
//	    	console.log("SELECT " + selectedTimeoffCategory);
//	    	object.prev('div').addClass("categoryColorSelected");
	    	object.addClass("categorySelected");
	    	object.addClass(selectedTimeoffCategory);
//	    	$('.buttonDisappearPTO').hide();
	    	for(category in categoryText) {
	    		if(selectedTimeoffCategory!=category) {
//	    			console.log('.buttonDisappear'+category.substr(7));
	    			$('.buttonDisappear'+category.substr(7)).hide();
	    		}
	    	}
//	    	console.log(selectedTimeoffCategory);
//	    	$('#noCategorySelected').hide();
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
        	
        	console.log('employeeData', json.employeeData);
        	// json.employeeData.EMPLOYEE_NUMBER
        	// json.employeeData.FIRST_NAME
        	// json.employeeData.LAST_NAME
//        	console.log($.trim(json.employeeData.EMPLOYEE_NUMBER));
        	
        	requestForEmployeeNumber = $.trim(json.employeeData.EMPLOYEE_NUMBER);
        	requestForEmployeeName = timeOffCreateRequestHandler.capitalizeFirstLetter(json.employeeData.LAST_NAME) +
        		', ' + timeOffCreateRequestHandler.capitalizeFirstLetter(json.employeeData.FIRST_NAME);
        	
//        	console.log(requestForEmployeeNumber);
//        	console.log(requestForEmployeeName);
        	
//        	$("#requestFor").empty().append('<option value="' + requestForEmployeeNumber + '">' +
//        									requestForEmployeeName + '</option>').val('id').trigger('change');
        	
        	$("#requestFor")
        		.empty()
        		.append('<option value="'+requestForEmployeeNumber+'">'+requestForEmployeeName+'</option>')
        		.val(requestForEmployeeNumber).trigger('change');
        	
//        	$('#requestFor').append(
//        	   $('<option></option>').val('123456').html('abcdefg')
//        	);
        	
        	// <option value="2" selected="selected">duplicate</option>
        	
//        	$(".requestIsForMe").html(requestForEmployeeName +
//        	  ' <span class="categoryCloseIcon glyphicon glyphicon-remove-circle red"></span>');
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
              employeeNumber: requestForEmployeeNumber
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
//		selectedDatesNew.sort(function(a,b) {
//			var dateA = new Date(a.date).getTime();
//	        var dateB = new Date(b.date).getTime();
//	        return dateA > dateB ? 1 : -1; 
//		});
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
//    	console.log("You selected", item);
    	timeOffCreateRequestHandler.loadCalendars(item.value);
    	// item.value = employeeNumber
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
//    	$(':focus').blur();
//    	console.log("Did we exit the field??");
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
};

// Initialize the class
timeOffCreateRequestHandler.initialize();