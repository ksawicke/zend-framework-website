/**
 * Javascript timeOffCreateRequestHandler 'class'
 *
 */
var timeOffCreateRequestHandler = new function ()
{
    var timeOffLoadCalendarUrl = phpVars.basePath + '/api/calendar/get', // http://swift:10080/sawik/timeoff/public
        timeOffSubmitTimeOffRequestUrl = phpVars.basePath + '/api/request',
        timeOffSubmitTimeOffSuccessUrl = phpVars.basePath + '/request/submitted-for-approval',
        timeOffEmployeeSearchUrl = phpVars.basePath + '/api/search/employees',
        employeePTORemaining = 0,
        employeeFloatRemaining = 0,
        employeeSickRemaining = 0,
        employeeUnexcusedAbsenceRemaining = 0,
        employeeBereavementRemaining = 0,
        employeeCivicDutyRemaining = 0,
        employeeGrandfatheredRemaining = 0,
        employeeApprovedNoPayRemaining = 0,
        employeePTOPending = 0,
        employeeFloatPending = 0,
        employeeSickPending = 0,
        employeeUnexcusedAbsencePending = 0,
        employeeBereavementPending = 0,
        employeeCivicDutyPending = 0,
        employeeGrandfatheredPending = 0,
        employeeApprovedNoPayPending = 0,
        totalPTORequested = 0,
        totalFloatRequested = 0,
        totalSickRequested = 0,
        totalUnexcusedAbsenceRequested = 0,
        totalBereavementRequested = 0,
        totalCivicDutyRequested = 0,
        totalGrandfatheredRequested = 0,
        totalApprovedNoPayRequested = 0,
        defaultHours = 8,
        defaultSplitHours = 4,
        selectedTimeoffCategory = null,
        loggedInUserData = [],
        requestForEmployeeNumber = '',
        requestForEmployeeName = '',
        requestForEmployeeObject = [],
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
        },
        directReportFilter = 'B';

    /**
     * Initializes binding
     */
    this.initialize = function () {
        $(document).ready(function () {
            var $requestForEventSelect = $("#requestFor");
            /**
             * When we change the for dropdown using select2,
             * set the employee number and name as a local variable
             * for form submission, and refresh the calendars.
             */
            $requestForEventSelect.on("select2:select", function (e) {
                timeOffCreateRequestHandler.resetCategorySelection();
                var selectedEmployee = e.params.data;
                requestForEmployeeNumber = selectedEmployee.id;
                requestForEmployeeName = selectedEmployee.text;
                timeOffCreateRequestHandler.loadCalendars(requestForEmployeeNumber);
                $('.requestIsForMe').show();
            })
            .on("select2:open", function (e) {
                /**
                 * SELECT2 is opened
                 */
                if (loggedInUserData.IS_LOGGED_IN_USER_PAYROLL === "N") {
                    $("span").remove(".select2CustomTag");
                    var $filter =
                            '<form id="directReportForm" style="display:inline-block;padding 5px;">' +
                            '<input type="radio" name="directReportFilter" value="B"' + ((directReportFilter==='B')?' checked':'') + '> Both&nbsp;&nbsp;&nbsp;' +
                            '<input type="radio" name="directReportFilter" value="D"' + ((directReportFilter === 'D') ? ' checked' : '') + '> Direct Reports&nbsp;&nbsp;&nbsp;' +
                            '<input type="radio" name="directReportFilter" value="I"' + ((directReportFilter === 'I') ? ' checked' : '') + '> Indirect Reports&nbsp;&nbsp;&nbsp;' +
                            '</form>';
                    $("<span class='select2CustomTag' style='padding-left:6px;'>" + $filter + "</span>").insertBefore('.select2-results');
                }
            })
            .on("select2:close", function (e) {
                /**
                 * SELECT2 is closed
                 */
            });

            // handle calendar navigation
            timeOffCreateRequestHandler.handleCalendarNavigation();
            
            $(document).on('click', '.toggleLegend', function() {
                timeOffCreateRequestHandler.toggleLegend();
            });

            /**
             * Handle clicking category
             */
            $(".selectTimeOffCategory").click(function () {
                timeOffCreateRequestHandler.selectCategory($(this));
            });

            /**
             * Handle clicking a calendar date
             */
            $(document).on('click', '.calendar-day', function () {
                var dateObject = { category: selectedTimeoffCategory, date: $(this).data('date') };
                if ( selectedTimeoffCategory !== null && "undefined" !== typeof dateObject.date ) {
                    timeOffCreateRequestHandler.updateRequestDates( dateObject, $(this));
                }
            });

            /**
             * Handle removing a date from request
             */
            $(document).on('click', '.remove-date-requested', function () {
                var dateObject = { category: $(this).attr('data-category'), date: $(this).data('date') };
                if ( selectedTimeoffCategory !== null && "undefined" !== typeof dateObject.date ) {
                    timeOffCreateRequestHandler.updateRequestDates( dateObject, $(this) );
                }
            });

            /**
             * Handle user changing the hours for a date manually
             */
            $(document).on('change', '.selectedDateHours', function () {
                var key = $(this).attr("data-key");
                var value = $(this).val();
                selectedDateHours[key] = value;
            });

            /**
             * Submit time off request
             */
            $(document).on('click', '.submitTimeOffRequest', function () {
                requestReason = $("#requestReason").val();
                timeOffCreateRequestHandler.submitTimeOffRequest();
            });
            
            /**
             * Handle splitting a date into two categories
             */
            $(document).on('click', '.split-date-requested', function () {
                timeOffCreateRequestHandler.splitDateRequested( $(this) );
            });

            $(document).on('click', '.changerequestForEmployeeNumber', function () {
//                    timeOffCreateRequestHandler.loadCalendars($(this).attr("data-employee-number"));
            });

            $(document).on('change', '#directReportForm input', function () {
                directReportFilter = $('input[name="directReportFilter"]:checked', '#directReportForm').val();
            });

            timeOffCreateRequestHandler.loadCalendars();
        });
    }

    this.handleCalendarNavigation = function() {
    	/**
         * Handle clicking previous or next buttons on calendars
         */
    	//.calendarNavigation
//        $('#timeOffCalendarWrapper').on('click', 'button', function (e) {
//        	console.log( e );
//        	$.each( e , function( key, value ) {
//                console.log( key + " :: " + value );
//                if( key==="target" ) {
//                	$.each( value , function( key2, value2 ) {
//                		if( key2==="attributes" ) {
//                        	$.each( value , function( key3, value3 ) {
//                        		console.log( key3[2] );
//                        		//console.log( "####  " + key3 + " :: " + value3 );
//                        	});
//                        }
//                	});
//                }
//            });
//        	var dataMonth = e.target.attributes[2].value;
//        	var dataYear = e.target.attributes[3].value;
//        	timeOffCreateRequestHandler.loadNewCalendars( dataMonth, dataYear );
//        });
//    	$('body').on('click', '#calendarNavigationFastRewind', function(e) {
//    		console.log( "MONTH TEST: " + $(this).attr('data-month') );
//    		console.log( "YEAR TEST: " + $(this).attr('data-year') );
//    	});
        $('body').on('click', '.calendarNavigation', function (e) {
//        	console.log( "calendar navigation clicked" );
//        	console.log( "this", $(this) );
//        	console.log( $(this).attr("data-month") );
//        	console.log( $(this).attr("data-year") );
            timeOffCreateRequestHandler.loadNewCalendars( $(this).attr("data-month"), $(this).attr("data-year") );
        });
    }
    
    /**
     * Checks if date is disabled
     * 
     * @param {type} object
     * @returns {Boolean}
     */
    this.isDateDisabled = function (object) {
        return (object.hasClass("calendar-day-disabled") ? true : false);
    }

    /**
     * Gets the text description of the category passed in.
     * 
     * @param {type} category
     * @returns {timeOffCreateRequestHandler.timeOffCreateRequestHandler_L5.categoryText|categoryText}
     */
    this.getCategoryText = function (category) {
        return categoryText[category];
    }

//    this.checkLocalStorage = function () {
//        if (typeof (Storage) !== "undefined") {
//            // Code for localStorage/sessionStorage.
//            console.log("local storage support enabled");
//            var testObject = {'one': 1, 'two': 2, 'three': 3};
//
//            // Put the object into storage
//            localStorage.setItem('testObject', JSON.stringify(testObject));
//
//            // Retrieve the object from storage
//            var retrievedObject = localStorage.getItem('testObject');
//
//            console.log('retrievedObject: ', JSON.parse(retrievedObject));
//        } else {
//            // Sorry! No Web Storage support..
//            console.log("NO local storage support enabled");
//        }
//    }

    /**
     * Resets the Remaining sick time for selected employee.
     */
    this.resetTimeoffCategory = function (object) {
        $('.btn-requestCategory').removeClass("categorySelected");
        $('.btn-requestCategory').removeClass(selectedTimeoffCategory);

        for (category in categoryText) {
            $('.' + category + 'CloseIcon').removeClass('categoryCloseIcon glyphicon glyphicon-remove-circle');
            $('.buttonDisappear' + category.substr(7)).show();
        }
    }

    /**
     * Sets the currently selected time off category.
     */
    this.setTimeoffCategory = function (object) {
        if (selectedTimeoffCategory == object.attr("data-category")) {
            object.removeClass(object.attr("data-category"));
            selectedTimeoffCategory = null;
            object.removeClass("categorySelected");
            $('.' + object.attr("data-category") + 'CloseIcon').removeClass('categoryCloseIcon glyphicon glyphicon-remove-circle');
            timeOffCreateRequestHandler.setStep('1');
        } else {
            selectedTimeoffCategory = object.attr("data-category");
            object.addClass("categorySelected");
            object.addClass(selectedTimeoffCategory);
            if (selectedDatesNew.length > 0) {
                timeOffCreateRequestHandler.setStep('3');
            } else {
                timeOffCreateRequestHandler.setStep('2');
            }
            $('.' + selectedTimeoffCategory + 'CloseIcon').addClass('categoryCloseIcon glyphicon glyphicon-remove-circle');
        }
    }

    /**
     * Loads calendars via ajax and displays them on the page.
     */
    this.loadCalendars = function (employeeNumber) {
        var month = (new Date()).getMonth() + 1;
        var year = (new Date()).getFullYear();

        timeOffCreateRequestHandler.clearSelectedDates();

        $.ajax({
            url: timeOffLoadCalendarUrl,
            type: 'POST',
            data: {
//                action: 'loadCalendars',
                startMonth: month,
                startYear: year,
                employeeNumber: ( (typeof employeeNumber==="string") ? employeeNumber : phpVars.employee_number )
            },
            dataType: 'json'
        })
        .success(function (json) {
            if (requestForEmployeeNumber === '') {
                loggedInUserData = json.employeeData;
                loggedInUserData.IS_LOGGED_IN_USER_MANAGER = json.loggedInUser.isManager;
                loggedInUserData.IS_LOGGED_IN_USER_PAYROLL = json.loggedInUser.isPayroll;
            }

            requestForEmployeeNumber = json.employeeData.EMPLOYEE_NUMBER;
            requestForEmployeeObject = json.employeeData;
            timeOffCreateRequestHandler.drawThreeCalendars( json.newCalendarData );
            timeOffCreateRequestHandler.setHours( json.employeeData );
            
            if (json.employeeData.GF_REMAINING > 0) {
                $('.categoryPTO').addClass('disableTimeOffCategorySelection');
            }

            requestForEmployeeNumber = $.trim(requestForEmployeeObject.EMPLOYEE_NUMBER);
            requestForEmployeeName = requestForEmployeeObject.EMPLOYEE_DESCRIPTION + ' - ' + requestForEmployeeObject.POSITION_TITLE;

            $("#requestFor")
                    .empty()
                    .append('<option value="' + requestForEmployeeNumber + '">' + requestForEmployeeName + '</option>')
                    .val(requestForEmployeeNumber).trigger('change');

            timeOffCreateRequestHandler.checkAllowRequestOnBehalfOf();
            return;
        })
        .error(function () {
            console.log('There was some error.');
            return;
        });
    }
    
    /**
     * Update buttons with hour data.
     */
    this.setHours = function( employeeData ) {
    	timeOffCreateRequestHandler.setEmployeePTORemaining(employeeData.PTO_REMAINING);
        timeOffCreateRequestHandler.setEmployeePTOPending(employeeData.PTO_PENDING_TOTAL);
        timeOffCreateRequestHandler.setEmployeeFloatRemaining(employeeData.FLOAT_REMAINING);
        timeOffCreateRequestHandler.setEmployeeFloatPending(employeeData.FLOAT_PENDING_TOTAL);
        timeOffCreateRequestHandler.setEmployeeSickRemaining(employeeData.SICK_REMAINING);
        timeOffCreateRequestHandler.setEmployeeSickPending(employeeData.SICK_PENDING_TOTAL);
        timeOffCreateRequestHandler.setEmployeeUnexcusedAbsencePending(employeeData.UNEXCUSED_PENDING_TOTAL);
        timeOffCreateRequestHandler.setEmployeeBereavementPending(employeeData.BEREAVEMENT_PENDING_TOTAL);
        timeOffCreateRequestHandler.setEmployeeCivicDutyPending(employeeData.CIVIC_DUTY_PENDING_TOTAL);
        timeOffCreateRequestHandler.setEmployeeGrandfatheredRemaining(employeeData.GF_REMAINING);
        timeOffCreateRequestHandler.setEmployeeGrandfatheredPending(employeeData.GF_PENDING_TOTAL);
        timeOffCreateRequestHandler.setEmployeeApprovedNoPayPending(employeeData.UNPAID_PENDING_TOTAL);
    }

    /**
     */
    this.drawThreeCalendars = function( calendarData ) {
    	/** Update navigation button data **/
    	$("#calendarNavigationFastRewind").attr( "data-month", calendarData.navigation.calendarNavigationFastRewind.month );
    	$("#calendarNavigationFastRewind").attr( "data-year", calendarData.navigation.calendarNavigationFastRewind.year );
    	$("#calendarNavigationRewind").attr( "data-month", calendarData.navigation.calendarNavigationRewind.month );
    	$("#calendarNavigationRewind").attr( "data-year", calendarData.navigation.calendarNavigationRewind.year );
    	$("#calendarNavigationFastForward").attr( "data-month", calendarData.navigation.calendarNavigationFastForward.month );
    	$("#calendarNavigationFastForward").attr( "data-year", calendarData.navigation.calendarNavigationFastForward.year );
    	$("#calendarNavigationForward").attr( "data-month", calendarData.navigation.calendarNavigationForward.month );
    	$("#calendarNavigationForward").attr( "data-year", calendarData.navigation.calendarNavigationForward.year );    	
    	
    	/** Draw calendar labels **/
    	$("#calendar1Label").html( calendarData.headers[1] );
    	$("#calendar2Label").html( calendarData.headers[2] );
    	$("#calendar3Label").html( calendarData.headers[3] );
    	
    	/** Draw calendars **/
    	$("#calendar1Body").html( calendarData.calendars[1] );
    	$("#calendar2Body").html( calendarData.calendars[2] );
    	$("#calendar3Body").html( calendarData.calendars[3] );
    	
    	timeOffCreateRequestHandler.highlightDates();
    }
    
    /**
     * Marks the appropriate step the user is on for this request.
     * 
     * @param {type} step
     * @returns {undefined}
     */
    this.setStep = function (step) {
        $(".step1").removeClass("active");
        $(".step2").removeClass("active");
        $(".step3").removeClass("active");
        $(".step" + step).addClass("active");
    }

    /**
     * Submit the user request for time off.
     * 
     * @returns {undefined}
     */
    this.submitTimeOffRequest = function () {
        $.ajax({
            url: timeOffSubmitTimeOffRequestUrl,
            type: 'POST',
            data: {
                request: { forEmployee: { EMPLOYEE_NUMBER: requestForEmployeeNumber },
                           byEmployee: loggedInUserData,
                           dates: selectedDatesNew,
                           reason: requestReason
                         }
            },
            dataType: 'json'
        })
        .success(function (json) {
            if (json.success == true) {
                window.location.href = timeOffSubmitTimeOffSuccessUrl;
            } else {
                alert(json.message);
            }
            return;
        })
        .error(function () {
            console.log('There was some error.');
            return;
        });
    };

    this.loadNewCalendars = function (startMonth, startYear) {
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
        .success(function (json) {
        	timeOffCreateRequestHandler.drawThreeCalendars( json.newCalendarData );
            return;
        })
        .error(function () {
            console.log('There was some error.');
            return;
        });
    }

    /**
     * Prints the Remaining PTO time for selected employee.
     */
    this.setEmployeePTORemaining = function (ptoRemaining) {
        employeePTORemaining = ptoRemaining;
        timeOffCreateRequestHandler.printEmployeePTORemaining();
    }

    /**
     * Prints the Pending PTO time for selected employee.
     */
    this.setEmployeePTOPending = function (ptoPending) {
        employeePTOPending = ptoPending;
        timeOffCreateRequestHandler.printEmployeePTOPending();
    }

    /**
     * Sets the Remaining Float time for selected employee.
     */
    this.setEmployeeFloatRemaining = function (floatRemaining) {
        employeeFloatRemaining = floatRemaining;
        timeOffCreateRequestHandler.printEmployeeFloatRemaining();
    }

    /**
     * Sets the Pending Float time for selected employee.
     */
    this.setEmployeeFloatPending = function (floatPending) {
        employeeFloatPending = floatPending;
        timeOffCreateRequestHandler.printEmployeeFloatPending();
    }

    /**
     * Sets the Remaining Sick time for selected employee.
     */
    this.setEmployeeSickRemaining = function (sickRemaining) {
        employeeSickRemaining = sickRemaining;
        timeOffCreateRequestHandler.printEmployeeSickRemaining();
    }

    /**
     * Sets the Pending Sick time for selected employee.
     */
    this.setEmployeeSickPending = function (sickPending) {
        var employeeSickPending = sickPending;
        timeOffCreateRequestHandler.printEmployeeSickPending();
    }

    /**
     * Sets the Remaining Grandfathered time for selected employee.
     */
    this.setEmployeeGrandfatheredRemaining = function (grandfatheredRemaining) {
        employeeGrandfatheredRemaining = grandfatheredRemaining;
        timeOffCreateRequestHandler.printEmployeeGrandfatheredRemaining();
    }

    /**
     * Sets the Pending Grandfathered time for selected employee.
     */
    this.setEmployeeGrandfatheredPending = function (grandfatheredPending) {
        var employeeGrandfatheredPending = grandfatheredPending;
        timeOffCreateRequestHandler.printEmployeeGrandfatheredPending();
    }

//    this.setEmployeeUnexcusedAbsenceRemaining = function(unexcusedAbsenceRemaining) {
//    	var employeeUnexcusedAbsenceRemaining = unexcusedAbsenceRemaining;
//    	timeOffCreateRequestHandler.printEmployeeUnexcusedAbsenceRemaining();
//    }

    /**
     * Sets the Pending Unexcused Absence time for selected employee.
     */
    this.setEmployeeUnexcusedAbsencePending = function (unexcusedAbsencePending) {
        var employeeUnexcusedAbsencePending = unexcusedAbsencePending;
        timeOffCreateRequestHandler.printEmployeeUnexcusedAbsencePending();
    }

//    this.setEmployeeBereavementRemaining = function(bereavementRemaining) {
//    	var employeeBereavementRemaining = bereavementRemaining;
//    	timeOffCreateRequestHandler.printEmployeeBereavementRemaining();
//    }

    /**
     * Sets the Pending Bereavement time for selected employee.
     */
    this.setEmployeeBereavementPending = function (bereavementPending) {
        var employeeBereavementPending = bereavementPending;
        timeOffCreateRequestHandler.printEmployeeBereavementPending();
    }

//    this.setEmployeeCivicDutyRemaining = function(civicDutyRemaining) {
//    	var employeeCivicDutyRemaining = civicDutyRemaining;
//    	timeOffCreateRequestHandler.printEmployeeCivicDutyRemaining();
//    }

    /**
     * Sets the Pending Civic Duty time for selected employee.
     */
    this.setEmployeeCivicDutyPending = function (civicDutyPending) {
        employeeCivicDutyPending = civicDutyPending;
        timeOffCreateRequestHandler.printEmployeeCivicDutyPending();
    }

//    this.setEmployeeApprovedNoPayRemaining = function(approvedNoPayRemaining) {
//    	employeeApprovedNoPayRemaining = approvedNoPayRemaining;
//    	timeOffCreateRequestHandler.printEmployeeApprovedNoPayRemaining();
//    }

    /**
     * Sets the Pending Approved No Pay time for selected employee.
     */
    this.setEmployeeApprovedNoPayPending = function (approvedNoPayPending) {
        employeeApprovedNoPayPending = approvedNoPayPending;
        timeOffCreateRequestHandler.printEmployeeApprovedNoPayPending();
    }

    /**
     * Prints the Remaining PTO time for selected employee.
     */
    this.printEmployeePTORemaining = function () {
        $("#employeePTORemainingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeePTORemaining) + " hours");
        if (timeOffCreateRequestHandler.setTwoDecimalPlaces(employeePTORemaining) <= 0) {
            $('.buttonDisappearPTO').addClass('hidden');
        } else {
            $('.buttonDisappearPTO').removeClass('hidden');
        }
        if (timeOffCreateRequestHandler.setTwoDecimalPlaces(employeePTORemaining) < 0) {
        	$('#warnPTO').show();
        }
    }

    /**
     * Prints the Pending PTO time for selected employee.
     */
    this.printEmployeePTOPending = function () {
        $("#employeePTOPendingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeePTOPending) + " hours");
    }

    /**
     * Prints the Remaining Float time for selected employee.
     */
    this.printEmployeeFloatRemaining = function () {
        $("#employeeFloatRemainingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeFloatRemaining) + " hours");
        if (timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeFloatRemaining) <= 0) {
            $('.buttonDisappearFloat').addClass('hidden');
        } else {
            $('.buttonDisappearFloat').removeClass('hidden');
        }
        if (timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeFloatRemaining) < 0) {
        	$('#warnFloat').show();
        }
    }

    /**
     * Prints the Pending Float time for selected employee.
     */
    this.printEmployeeFloatPending = function () {
        $("#employeeFloatPendingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeFloatPending) + " hours");
    }

    /**
     * Prints the Remaining Sick time for selected employee.
     */
    this.printEmployeeSickRemaining = function () {
        $("#employeeSickRemainingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeSickRemaining) + " hours");
        if (timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeSickRemaining) <= 0) {
            $('.buttonDisappearSick').addClass('hidden');
        } else {
            $('.buttonDisappearSick').removeClass('hidden');
        }
        if (timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeSickRemaining) < 0) {
        	$('#warnSick').show();
        }
    }

    /**
     * Prints the Pending Sick time for selected employee.
     */
    this.printEmployeeSickPending = function () {
        $("#employeeSickPendingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeSickPending) + " hours");
    }

    /**
     * Prints the Remaining Grandfathered time for selected employee.
     */
    this.printEmployeeGrandfatheredRemaining = function () {
        $("#employeeGrandfatheredRemainingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeGrandfatheredRemaining) + " hours");
        if (timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeGrandfatheredRemaining) <= 0) {
            $('.buttonDisappearGrandfathered').addClass('hidden');
        }
        console.log("employeeGrandfatheredRemaining", employeeGrandfatheredRemaining);
    }

    /**
     * Prints the Pending Grandfathered time for selected employee.
     */
    this.printEmployeeGrandfatheredPending = function () {
        $("#employeeGrandfatheredPendingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeGrandfatheredPending) + " hours");
    }

//    this.printEmployeeUnexcusedAbsenceRemaining = function() {
//    	$("#employeeUnexcusedAbsenceRemainingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeUnexcusedAbsenceRemaining) + " hours");
//    	if(employeeUnexcusedAbsenceRemaining<=0) {
//    		$('.buttonDisappearUnexcusedAbsence').addClass('hidden');
//    	}
//    }

    /**
     * Prints the Pending Unexcused Absence time for selected employee.
     */
    this.printEmployeeUnexcusedAbsencePending = function () {
        $("#employeeUnexcusedAbsencePendingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeUnexcusedAbsencePending) + " hours");
    }

//    this.printEmployeeBereavementRemaining = function() {
//    	$("#employeeBereavementRemainingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeBereavementRemaining) + " hours");
//    	if(employeeBereavementRemaining<=0) {
//    		$('.buttonDisappearBereavementAbsence').addClass('hidden');
//    	}
//    }

    /**
     * Prints the Pending Bereavement time for selected employee.
     */
    this.printEmployeeBereavementPending = function () {
        $("#employeeBereavementPendingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeBereavementPending) + " hours");
    }

//    this.printEmployeeCivicDutyRemaining = function() {
//    	$("#employeeCivicDutyRemainingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeCivicDutyRemaining) + " hours");
//    	if(employeeCivicDutyRemaining<=0) {
//    		$('.buttonDisappearCivicDutyAbsence').addClass('hidden');
//    	}
//    }

    /**
     * Prints the Pending Civic Duty time for selected employee.
     */
    this.printEmployeeCivicDutyPending = function () {
        $("#employeeCivicDutyPendingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeCivicDutyPending) + " hours");
    }

//    this.printEmployeeApprovedNoPayRemaining = function() {
//    	$("#employeeApprovedNoPayRemainingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeApprovedNoPayRemaining) + " hours");
//    	if(employeeApprovedNoPayRemaining<=0) {
//    		$('.buttonDisappearApprovedNoPayAbsence').addClass('hidden');
//    	}
//    }

    /**
     * Prints the Pending Approved No Pay time for selected employee.
     */
    this.printEmployeeApprovedNoPayPending = function () {
        $("#employeeApprovedNoPayPendingHours").html(timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeApprovedNoPayPending) + " hours");
    }

    /**
     * Adds employee defaultHours from the current Category of time Remaining.
     */
    this.addTime = function (category, hours) {
        switch (category) {
            case 'timeOffPTO':
                employeePTORemaining -= hours;
                timeOffCreateRequestHandler.printEmployeePTORemaining();
                break;

            case 'timeOffFloat':
                employeeFloatRemaining -= hours;
                timeOffCreateRequestHandler.printEmployeeFloatRemaining();
                break;

            case 'timeOffSick':
                employeeSickRemaining -= hours;
                timeOffCreateRequestHandler.printEmployeeSickRemaining();
                break;
        }
    }

    /**
     * Subtracts employee defaultHours from the current Category of time Remaining.
     */
    this.subtractTime = function (category, hours) {
        switch (category) {
            case 'timeOffPTO':
                employeePTORemaining += hours;
                timeOffCreateRequestHandler.printEmployeePTORemaining();
                break;

            case 'timeOffFloat':
                employeeFloatRemaining += hours;
                timeOffCreateRequestHandler.printEmployeeFloatRemaining();
                break;

            case 'timeOffSick':
                employeeSickRemaining += hours;
                timeOffCreateRequestHandler.printEmployeeSickRemaining();
                break;
        }
    }

    /**
     * 
     * @param {type} approvedRequests
     * @param {type} pendingRequests
     * @returns {undefined}
     */
    this.setSelectedDates = function (approvedRequests, pendingRequests) {
        selectedDatesApproved = [];
        selectedDatesPendingApproval = [];

        for (key in approvedRequests) {
            var obj = {date: approvedRequests[key].REQUEST_DATE,
                hours: approvedRequests[key].REQUESTED_HOURS,
                category: approvedRequests[key].REQUEST_TYPE
            };
            selectedDatesApproved.push(obj);
        }

        for (key in pendingRequests) {
            var obj = {date: pendingRequests[key].REQUEST_DATE,
                hours: pendingRequests[key].REQUESTED_HOURS,
                category: pendingRequests[key].REQUEST_TYPE
            };
            selectedDatesPendingApproval.push(obj);
        }
    }

    this.highlightDates = function () {
        $.each($(".calendar-day"), function (index, blah) {
            $(this).removeClass('timeOffPTOSelected');
            $(this).removeClass('timeOffFloatSelected');
            $(this).removeClass('timeOffSickSelected');
            $(this).removeClass('timeOffGrandfatheredSelected');
            $(this).removeClass('timeOffBereavementSelected');
            $(this).removeClass('timeOffApprovedNoPaySelected');
            $(this).removeClass('timeOffCivicDutySelected');
//            $(this).removeClass('');
        });

        $.each($(".calendar-day"), function (index, blah) {
            for (var i = 0; i < selectedDatesNew.length; i++) {
                if (selectedDatesNew[i].date &&
                        selectedDatesNew[i].date === $(this).attr("data-date")) {
                    thisClass = selectedDatesNew[i].category + "Selected";
                    $(this).toggleClass(thisClass);
                    break;
                }
            }

            for (var i = 0; i < selectedDatesPendingApproval.length; i++) {
                if (selectedDatesPendingApproval[i].date &&
                        selectedDatesPendingApproval[i].date === $(this).attr("data-date")) {
                    thisClass = selectedDatesPendingApproval[i].category + " requestPending";
                    $(this).toggleClass(thisClass);
                    break;
                }
            }

            for (var i = 0; i < selectedDatesApproved.length; i++) {
                if (selectedDatesApproved[i].date &&
                        selectedDatesApproved[i].date === $(this).attr("data-date")) {
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
    this.setTwoDecimalPlaces = function (num) {
        return parseFloat(Math.round(num * 100) / 100).toFixed(2);
    }

    /**
     * Determines if the date is selected and returns an object we can handle later.
     */
    this.isSelected = function (object) {
        console.log('object', object);
        console.log('boo ya check', j)
        var thisDate = object.data('date');
        var thisCategory = selectedTimeoffCategory;
        var thisHours = defaultHours;
        var obj = {date: thisDate, hours: '8.00', category: selectedTimeoffCategory};
        var isSelected = false;
        var deleteIndex = null;

        for (var i = 0; i < selectedDatesNew.length; i++) {
            if (selectedDatesNew[i].date === thisDate &&
                    selectedDatesNew[i].category != thisCategory) {
                console.log("STOP", i);
                isSelected = true;
                return {isSelected: isSelected, deleteIndex: i, obj: obj};
            }
        }
        return {isSelected: isSelected, deleteIndex: i, obj: obj};
//        console.log("Proposed object", obj);
//        console.log("Existing dates", selectedDatesNew);
//        
//        return { isSelected:null,
//                 obj:obj,
//                 selectedDatesNew:selectedDatesNew
//               };

//        var i = null;
//
////        console.log("thisDate", thisDate);
////        console.log("object", object);
//        console.log("obj", obj);
//
//        for(var i = 0; i < selectedDatesNew.length; i++) {
//            console.log("ZZ", selectedDatesNew[i]);
//            if(selectedDatesNew[i].date===thisDate) {
//                console.log(i + " :: dates match   " + selectedDatesNew[i].date + " | " + thisDate);
//            } else {
////                console.log(i + " :: dates don't match");
//            }
////            
//            if(selectedDatesNew[i].category===thisCategory) {
//                console.log(i + " :: categories match   " + selectedDatesNew[i].category + " | " + thisCategory);
//            } else {
//                console.log(i + " :: categories don't match" + selectedDatesNew[i].category + " | " + thisCategory);
//            }
////            
//            if(selectedDatesNew[i].date===thisDate &&
//               selectedDatesNew[i].category!=thisCategory) {
//                isSelected = true;
//                console.log("isSelected", isSelected);
//                console.log("i", i);
//                return {isSelected:isSelected, deleteIndex:i, obj:obj};
//            }
//               isSelected===false) {
////                console.log("FOUND " + i);
//                isSelected = true;
////                deleteIndex = i;
////                break;
//                return {isSelected:isSelected, deleteIndex:i, obj:obj};
//            }


//            if(selectedDatesNew[i].date &&
//               selectedDatesNew[i].date===thisDate &&
//               selectedDatesNew[i].category &&
//               selectedDatesNew[i].category===thisCategory) {
//                    console.log("FOUND " + i);
//                    isSelected = true;
//                    deleteIndex = i;
//                    break;
//            }
//        }

//        return {isSelected:isSelected, deleteIndex:i, obj:obj};
    }

    this.addDateToRequest = function (obj) {
//        console.log("selectedDate", selectedDate);
//        var obj = { date: selectedDate,
//                    hours: 4,
//                    category: selectedTimeoffCategory
//                  };
//        console.log("obj", obj);
        selectedDatesNew.push(obj);
//        timeOffCreateRequestHandler.addTime(obj.category, obj.hours);
    }

    this.removeDateFromRequest = function (deleteIndex) {
//        console.log(selectedDate);
//        var category = selectedDatesNew[deleteIndex].category;
//        console.log("selectedDatesNew[deleteIndex]", selectedDatesNew[deleteIndex]);
//        console.log("category", category);
        selectedDatesNew.splice(deleteIndex, 1);
//        timeOffCreateRequestHandler.subtractTime(selectedTimeoffCategory, defaultHours);
    }

    /**
     * Removes a date from the request.
     */
    this.toggleDateFromRequest = function (object) {
        var selectedDate = object.data('date');
        var isSelected = timeOffCreateRequestHandler.isSelected(object);
        var isDateDisabled = timeOffCreateRequestHandler.isDateDisabled(object);

//        console.log("selectedTimeoffCategory", selectedTimeoffCategory);
        console.log("isDateDisabled", isDateDisabled);
        console.log("isSelected", isSelected);
//        console.log("selectedDate", selectedDate);

//        if(selectedTimeoffCategory != null && isDateDisabled === false) {
//            console.log("isSelected", isSelected);
//            console.log("object", object);

        if (isSelected.isSelected === false) {
            var obj = {date: selectedDate,
                hours: defaultHours,
                category: selectedTimeoffCategory
            };
            timeOffCreateRequestHandler.addDateToRequest(obj);
        } else {
//                var index = isSelected.deleteIndex;
//                var split1Obj = selectedDatesNew[index];
//                var category = split1Obj.category;

            console.log("@ index: " + index);
            console.log("@ category: " + category);
            console.log("@ selectedTimeoffCategory: " + selectedTimeoffCategory);
//                if(category!==selectedTimeoffCategory) {
//                timeOffCreateRequestHandler.removeDateFromRequest(index);
//                timeOffCreateRequestHandler.subtractTime(category, defaultHours);

//                split1Obj.hours = defaultSplitHours;
//                
//                console.log("1", split1Obj);
//                
//                timeOffCreateRequestHandler.addDateToRequest(split1Obj);
//                timeOffCreateRequestHandler.addTime(split1Obj.category, split1Obj.hours);

//                var split2Obj = { date: selectedDate,
//                    hours: defaultSplitHours,
//                    category: selectedTimeoffCategory
//                };
//                timeOffCreateRequestHandler.addDateToRequest(split2Obj);
//                
//                console.log("2", split2Obj);

//                timeOffCreateRequestHandler.addTime(split2Obj.category, split2Obj.hours);
//                }
        }

//            console.log("selectedDatesNew", selectedDatesNew);
//                console.log("OBJJJJ", obj);
//            } else {
//                var index = isSelected.deleteIndex;
//                var obj = selectedDatesNew[index];
//                obj.hours = defaultSplitHours;
//                
//                console.log("REMOVE INDEX", index);

//                console.log("OBJZZZZZZZZZZZ", obj);
//                var obj = selectedDatesNew[index];
//                obj.hours = defaultSplitHours;

//                console.log('index', index);
//                console.log('obj', obj);
//                timeOffCreateRequestHandler.removeDateFromRequest(index);
//                timeOffCreateRequestHandler.addDateToRequest(obj);
//            }

//            $.each($('.calendar-day'), function(index, object) {
//                if(selectedDate==$(this).data("date")) {
//                    $(this).toggleClass(selectedTimeoffCategory + "Selected");
//                }
//            });
//
//            timeOffCreateRequestHandler.sortDatesSelected();
//            timeOffCreateRequestHandler.drawHoursRequested();
//        }
    }

    /**
     * Removes a date from the request.
     * 
     * @param {type} dateRequestObject
     * @returns {undefined}     */
    this.selectCalendarDay = function (dateRequestObject) {
        var selectedDate = timeOffCreateRequestHandler.isSelected(dateRequestObject);
        var isDateDisabled = timeOffCreateRequestHandler.isDateDisabled(dateRequestObject);
        if (selectedTimeoffCategory != null && isDateDisabled === false) {
            timeOffCreateRequestHandler.toggleDateFromRequest(selectedDate);
        }
    }

    /**
     * Draws form fields we can submit for the user.
     */
    this.drawHoursRequested = function () {
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

        for (var key = 0; key < selectedDatesNew.length; key++) {
            datesSelectedDetailsHtml += selectedDatesNew[key].date + '&nbsp;&nbsp;&nbsp;&nbsp;' +
                    '<input class="selectedDateHours" value="' + timeOffCreateRequestHandler.setTwoDecimalPlaces(selectedDatesNew[key].hours) + '" size="2" data-key="' + key + '" disabled="disabled">' +
                    '&nbsp;&nbsp;&nbsp;&nbsp;' +
                    '<span class="badge ' + selectedDatesNew[key].category + '">' +
                    timeOffCreateRequestHandler.getCategoryText(selectedDatesNew[key].category) +
                    '</span>' +
                    '&nbsp;&nbsp;&nbsp;' +
//                '<span class="glyphicon glyphicon-duplicate green split-date-requested" ' +
//                    'data-date="' + selectedDatesNew[key].date + '" ' +
//                    'data-category="' + selectedDatesNew[key].category + '" ' +
//                    'title="Split time with selected category">' +
//                '</span>' +
//                '&nbsp;&nbsp;&nbsp;' +
                    '<span class="glyphicon glyphicon-remove-circle red remove-date-requested" ' +
                    'data-date="' + selectedDatesNew[key].date + '" ' +
                    'data-category="' + selectedDatesNew[key].category + '" ' +
                    'title="Remove date from request">' +
                    '</span>' +
                    '<br style="clear:both;" />';

            // glyphicon glyphicon-duplicate

            switch (selectedDatesNew[key].category) {
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

        $("#datesSelectedDetails").html(datesSelectedDetailsHtml);

        if (selectedDatesNew.length === 0) {
            $('#datesSelectedDetails').hide();
            $("#requestSubmitDetails").hide();
            timeOffCreateRequestHandler.setStep('2');
        } else {
            $('#datesSelectedDetails').show();
            $("#requestSubmitDetails").show();
            timeOffCreateRequestHandler.setStep('3');
        }

//		if(employeePTORemaining > 0) {
//			
//		}
//		console.log("CURRENT SELECTED CATEGORY: " + selectedTimeoffCategory);
//		console.log("PTO AVAIL: " + employeePTORemaining);
//		console.log("FLOAT AVAIL: " + employeeFloatRemaining);
//		console.log("SICK AVAIL: " + employeeSickRemaining);
//		console.log("UNEXCUSED ABSENCE AVAIL: " + employeeUnexcusedAbsenceRemaining);
//		
//		console.log("BEREAVEMENT AVAIL: " + employeeBereavementRemaining);
//		console.log("CIVIC DUTY AVAIL: " + employeeCivicDutyRemaining);
//		console.log("GRANDFATHERED AVAIL: " + employeeGrandfatheredRemaining);
//		console.log("APPROVED NO PAY AVAIL: " + employeeApprovedNoPayRemaining);

        timeOffCreateRequestHandler.printEmployeePTORemaining();

//		if(selectedTimeoffCategory==="timeOffFloat" && employeeFloatRemaining===0) {
//			console.log("BUTTONS ARE MISSING. NOW WHAT?");
//		}
//		var type = selectedTimeoffCategory.substr(7);
//		var $$category = "timeOff" + type;
//		var $$available = "employee" + type + "Remaining";
//		if(selectedTimeoffCategory===$$category && $$available===0) {
//			console.log("BUTTONS ARE MISSING. NOW WHAT?");
//		}
//		if(selectedTimeoffCategory===$$category) {
//			console.log("YUP");
//		}
//		console.log("selectedTimeoffCategory " + selectedTimeoffCategory);
//		console.log("category " + $$category);
//		console.log("available " + $$available);
    }

    /**
     * Sorts dates in the selected array.
     */
    this.sortDatesSelected = function () {
        selectedDatesNew.sort(function (a, b) {
            var dateA = new Date(a.date).getTime();
            var dateB = new Date(b.date).getTime();
            return dateA > dateB ? 1 : -1;
        });
        console.log(selectedDatesNew);
    }

    this.selectResult = function (item) {
//    	timeOffCreateRequestHandler.loadCalendars(item.value);
    }

    this.setAsRequestForAnother = function () {
        $('.requestIsForMe').hide();
        $('.requestIsForAnother').show();
        $('.requestIsForAnother').focus();
    }

    this.setAsRequestForMe = function () {
        $('.requestIsForMe').show();
        $('.requestIsForAnother').hide();
        timeOffCreateRequestHandler.clearRequestFor();
    }

    this.clearRequestFor = function () {
        $('#demo5').val('');
    }

    this.requestForAnotherComplete = function () {
        $(".requestIsForAnother").append('<span class="categoryCloseIcon glyphicon glyphicon-remove-circle red"></span>');
    }

    this.capitalizeFirstLetter = function (string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    this.log = function (name, evt) {
        if (!evt) {
            var args = "{}";
        } else {
            var args = JSON.stringify(evt.params, function (key, value) {
                if (value && value.nodeName) {
                    return "[DOM node]";
                }
                if (value instanceof $.Event) {
                    return "[$.Event]";
                }
                return value;
            });
        }
        var $e = $("<li>" + name + " -> " + args + "</li>");
        $eventLog.append($e);
        $e.animate({opacity: 1}, 10000, 'linear', function () {
            $e.animate({opacity: 0}, 2000, 'linear', function () {
                $e.remove();
            });
        });
    }

    this.checkAllowRequestOnBehalfOf = function () {
        if (loggedInUserData.IS_LOGGED_IN_USER_MANAGER === "Y" || loggedInUserData.IS_LOGGED_IN_USER_PAYROLL === "Y") {
            console.log('1132!!!');
            timeOffCreateRequestHandler.enableSelectRequestFor();
            $("#requestFor").prop('disabled', false);
        } else {
            $("#requestFor").prop('disabled', true);
            $(".categoryBereavement").hide();
            $(".categoryCivicDuty").hide();
            $(".categoryApprovedNoPay").hide();
//            $(".categoryUnexcusedAbsence").hide();
        }
    }

    this.enableSelectRequestFor = function () {
        var $eventLog = $(".js-event-log");
        var $requestForEventSelect = $("#requestFor");
        $("#requestFor").select2({
            //data: data
            ajax: {
                url: timeOffEmployeeSearchUrl,
                method: 'post',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term,
                        directReportFilter: directReportFilter,
                        employeeNumber: phpVars.employee_number,
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    return {
                        results: data,
                        pagination: {
                            more: (params.page * 30) < data.total_count
                        }
                    };
                },
//    			 cache: true,
            },
            allowClear: true,
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
//        var $requestForEventSelect = $("#requestFor");
//    	/**
//    	 * When we change the for dropdown using select2,
//    	 * set the employee number and name as a local variable
//    	 * for form submission, and refresh the calendars.
//    	 */
//    	$requestForEventSelect.on("select2:select", function (e) {
//            var selectedEmployee = e.params.data;
////            console.log("SELECTED EMPLOYEE", selectedEmployee);
//            requestForEmployeeNumber = selectedEmployee.id;
//            requestForEmployeeName = selectedEmployee.text;
//            console.log("WWWWWW 1199");
//            timeOffCreateRequestHandler.loadCalendars(requestForEmployeeNumber);
////            console.log('983');
//            $('.requestIsForMe').show();
//    	})
//        .on("select2:open", function (e) {
//            //console.log("SELECT2 OPENED");
//            if(loggedInUserData.IS_LOGGED_IN_USER_PAYROLL==="N") {
//                $("span").remove(".select2CustomTag");
//                var $filter = 
//                    '<form id="directReportForm" style="display:inline-block;padding 5px;">' +
//                    //'<input type="radio" name="directReportFilter" value="B"' + ((directReportFilter==='B')?' checked':'') + '> Both&nbsp;&nbsp;&nbsp;' +
//                    '<input type="radio" name="directReportFilter" value="D"' + ((directReportFilter==='D')?' checked':'') + '> Direct Reports&nbsp;&nbsp;&nbsp;' +
//                    '<input type="radio" name="directReportFilter" value="I"' + ((directReportFilter==='I')?' checked':'') + '> Indirect Reports&nbsp;&nbsp;&nbsp;' +
//                    '</form>';
//                $("<span class='select2CustomTag' style='padding-left:6px;'>" + $filter + "</span>").insertBefore('.select2-results');
//            }
//    	})
//        .on("select2:close", function (e) {
//            //console.log("SELECT2 CLOSED");
//    	});

//        $requestForEventSelect
//            .on("change", function(e) {
//              // mostly used event, fired to the original element when the value changes
//              console.log("change val=" + e.val);
//            })
//            .on("select2-opening", function() {
//              console.log("opening");
//            })
//            .on("select2-open", function() {
//              // fired to the original element when the dropdown opens
//              console.log("open");
//            })
//            .on("select2-close", function() {
//              // fired to the original element when the dropdown closes
//              console.log("close");
//            })
//            .on("select2-highlight", function(e) {
//              console.log("highlighted val=" + e.val + " choice=" + e.choice.text);
//            })
//            .on("select2-selecting", function(e) {
//              console.log("selecting val=" + e.val + " choice=" + e.object.text);
//            })
//            .on("select2-removed", function(e) {
//              console.log("removed val=" + e.val + " choice=" + e.choice.text);
//            })
//            .on("select2-loaded", function(e) {
//              console.log("loaded (data property omitted for brevitiy)");
//            })
//            .on("select2-focus", function(e) {
//              console.log("focus");
//            });
    }

    /**
     * Mask the request calendar so user can not pick dates or scroll
     * to a different month.
     */
    this.maskCalendars = function (action) {
        if (!action || action === 'show') {
            $('body').append('<link href="' + phpVars.basePath + '/css/timeOffCalendarEnable.css" rel="stylesheet" id="enableTimeOffCalendar" />');
        } else if (action === 'hide') {
            $('#enableTimeOffCalendar').remove();
        }
    }
    
    /**
     * Reset category selection
     * 
     * @returns {undefined}
     */
    this.resetCategorySelection = function() {
        selectedTimeoffCategory = null;
        $(".selectTimeOffCategory").removeClass('categorySelected');
        for( categoryClass in categoryText ) {
            $(".selectTimeOffCategory").removeClass( categoryClass );
        }
        timeOffCreateRequestHandler.maskCalendars('hide');
    }

    /**
     * Select category
     * 
     * @param {type} categoryButton
     * @returns {undefined}
     */
    this.selectCategory = function (categoryButton) {
        if (!categoryButton.hasClass('disableTimeOffCategorySelection')) {
            timeOffCreateRequestHandler.resetTimeoffCategory(categoryButton);
            timeOffCreateRequestHandler.setTimeoffCategory(categoryButton);
        }
        if (categoryButton.hasClass('disableTimeOffCategorySelection') && categoryButton.hasClass('categoryPTO')) {
            timeOffCreateRequestHandler.alertUserToTakeGrandfatheredTime();
        }
        if (selectedTimeoffCategory === null) {
            timeOffCreateRequestHandler.maskCalendars('hide');
        } else {
            timeOffCreateRequestHandler.maskCalendars('show');
        }
    }

    this.splitDateRequested = function (dateRequestObject) {
        var selectedDate = timeOffCreateRequestHandler.isSelected(dateRequestObject);
        if (selectedTimeoffCategory != null) {
            //timeOffCreateRequestHandler.toggleDateFromRequest(selectedDate);
            //timeOffCreateRequestHandler.drawHoursRequested();

            /**
             * Let's find exact keys where the date is the date selected
             */
            allowSplitDate = timeOffCreateRequestHandler.allowSplitDate(selectedDate);
            console.log("allowSplitDate", allowSplitDate);
            if (allowSplitDate.allowSplitDate === true) {
                var item = allowSplitDate.items[0];
//				console.log("ITEM!!", item);
//				html = 'You want to split time off on ' + item.date + ' for ' +
//				item.hours + ' of ' + item.category + '. ' +
//				'Lets split it evenly with category ' + selectedTimeoffCategory;

//				console.log(html);
//				console.log(selectedDate.deleteIndex);
//				console.log(selectedDatesNew);
//				for(key in selectedDatesNew) {
//					console.log(selectedDatesNew[key]);
//				}
                //var index = selectedDate.deleteIndex - 1;
                var index = item.index;
//				console.log("BEFORE", selectedDatesNew[index]);

                /** Update to number of split hours **/
                selectedDatesNew[index].hours = 4;

                /** Add back the split hours to the selected category **/
                timeOffCreateRequestHandler.subtractTime(selectedDatesNew[index].category, 4);

//				console.log("AFTER", selectedDatesNew[index]);

                /**
                 * Add the date to the request object
                 */
                var obj = {date: item.date,
                    hours: 4,
                    category: selectedTimeoffCategory
                };
                selectedDatesNew.push(obj);
                timeOffCreateRequestHandler.addTime(selectedTimeoffCategory, 4);

                timeOffCreateRequestHandler.drawHoursRequested();

                //	    	$.each($('.calendar-day'), function(index, object) {
                //	    		if(selectedDate.obj.date==$(this).data("date")) {
                //	    			$(this).toggleClass(selectedTimeoffCategory + "Selected");
                //	    		}
                //	    	});

                timeOffCreateRequestHandler.sortDatesSelected();
            }

//			console.log("TESTING SPLIT", selectedTimeoffCategory);
//			for(key in selectedDate) {
//				console.log("selectedDate[" + key + "] :: " + selectedDate[key]);
//				if(key==='obj') {
//					for(key2 in selectedDate.obj) {
//						console.log("selectedDate.obj[" + key2 + "] :: " + selectedDate.obj[key2]);
//					}
//				}
//			}

//			var index = selectedDate.deleteIndex - 1;
//			html = 'You want to split time off on ' + selectedDate.obj.date + ' for ' +
//				selectedDate.obj.hours + ' of ' + selectedDatesNew[index].category + '. ' +
//				'Lets split it evenly with category ' + selectedTimeoffCategory;
//	
//			console.log(html);
//			console.log(selectedDate.deleteIndex);
//			console.log(selectedDatesNew);
//			for(key in selectedDatesNew) {
//				console.log(selectedDatesNew[key]);
//			}
//			var index = selectedDate.deleteIndex - 1;
//			console.log("BEFORE", selectedDatesNew[index]);
//			
//			/** Update to number of split hours **/
//			selectedDatesNew[index].hours = defaultSplitHours;
//			
//			/** Add back the split hours to the selected category **/
//			timeOffCreateRequestHandler.subtractTime(selectedDatesNew[index].category, defaultSplitHours);
//			
//			console.log("AFTER", selectedDatesNew[index]);
//			
//			/**
//			 * Add the date to the request object
//			 */
//			var obj = { date: selectedDate.obj.date,
//				    hours: defaultSplitHours,
//				    category: selectedTimeoffCategory
//				  };
//			selectedDatesNew.push(obj);
//			timeOffCreateRequestHandler.addTime(selectedTimeoffCategory, defaultSplitHours);
//			
//			timeOffCreateRequestHandler.drawHoursRequested();
//			
////	    	$.each($('.calendar-day'), function(index, object) {
////	    		if(selectedDate.obj.date==$(this).data("date")) {
////	    			$(this).toggleClass(selectedTimeoffCategory + "Selected");
////	    		}
////	    	});
//			
//	    	timeOffCreateRequestHandler.sortDatesSelected();
        }
    }

    this.alertUserToTakeGrandfatheredTime = function () {
        $("#dialogGrandfatheredAlert").dialog({
            modal: true,
            buttons: {
                Ok: function () {
                    $(this).dialog("close");
                }
            }
        });
    }

    this.allowSplitDate = function (selectedDate) {
        var allowSplitDate = false;
        items = [];
        $.each(selectedDatesNew, function (index, object) {
            if (object.date === selectedDate.obj.date) {
                object.index = index;
                items.push(object);
            }
        });
        if ((items.length === 1 && selectedTimeoffCategory === "timeOffFloat") ||
                (items.length === 0) ||
                (items.length > 1)
                ) {
            allowSplitDate = false;
        }
        if (items.length === 1 && selectedTimeoffCategory != "timeOffFloat") {
            allowSplitDate = true;
        }

        return {allowSplitDate: allowSplitDate, items: items};
    }

    /**
     * Clears out the selected dates and refreshes the form.
     * @returns {undefined}     */
    this.clearSelectedDates = function () {
        selectedDatesNew = [];
        timeOffCreateRequestHandler.drawHoursRequested();
    }

    this.updateRequestDates = function (object, calendarDateObject) {
        var found = false;
        var copy = null;
        var newOne = null;
        var deleteKey = null;

        $.each(selectedDatesNew, function (key, dateObject) {
            if (object.date == dateObject.date && object.category === dateObject.category) {
                found = true;
                deleteKey = key;
            }
            if (object.date == dateObject.date && object.category != dateObject.category && found === false) {
                found = true;
                copy = dateObject;
                newOne = object;
                deleteKey = key;
            }
        });

        /**
         * Add date to request.
         */
        if (copy === null && deleteKey === null) {
            timeOffCreateRequestHandler.addDataToRequest(calendarDateObject, object);
        }

        /**
         * Delete date from request.
         */
        if (copy == null && deleteKey !== null) {
            timeOffCreateRequestHandler.deleteDataFromRequest(calendarDateObject, deleteKey, object);
        }

        /**
         * Split the data.
         */
        if (copy !== null && deleteKey !== null) {
            timeOffCreateRequestHandler.splitDataFromRequest(calendarDateObject, deleteKey, copy, newOne);
        }

        timeOffCreateRequestHandler.sortDatesSelected();
        timeOffCreateRequestHandler.drawHoursRequested();
    }

    /**
     * Add date requested to the array
     *
     * @param {object} calendarDateObject
     * @param {object} object
     * @returns {none}     */
    this.addDataToRequest = function (calendarDateObject, object) {
        object = timeOffCreateRequestHandler.formatDayRequested( object );
        timeOffCreateRequestHandler.addDateToRequest(object);
        timeOffCreateRequestHandler.addTime(object.category, object.hours);
        timeOffCreateRequestHandler.highlightDates();
    }
    
    /**
     * Add the following to the day requested:
     * 1. Day of week (i.e. MON, TUE)
     * 2. Default hours for this employee's schedule.
     *
     * @param {string} object
     * @returns {object}     */
    this.formatDayRequested = function ( object ) {
        object.dow = moment( object.date, "MM/DD/YYYY" ).format( "ddd" ).toUpperCase();
        var scheduleDay = "SCHEDULE_" + object.dow;
        object.hours = requestForEmployeeObject[scheduleDay];
        
        return object;
    }

    /**
     * Deletes a day from the request.
     *
     * @param {type} calendarDateObject
     * @param {type} deleteKey
     * @param {type} object
     * @returns {undefined} */
    this.deleteDataFromRequest = function (calendarDateObject, deleteKey, object) {
        timeOffCreateRequestHandler.subtractTime(selectedDatesNew[deleteKey].category, Number(selectedDatesNew[deleteKey].hours));
        timeOffCreateRequestHandler.removeDateFromRequest(deleteKey);
        calendarDateObject.removeClass(object.category + "Selected");

        $.each($('.calendar-day'), function (index, obj) {
            if ($(this).data("date") === calendarDateObject.data("date")) {
                $(this).removeClass("timeOffPTOSelected");
            }
        });
    }

    this.splitDataFromRequest = function (calendarDateObject, deleteKey, copy, newOne) {
        timeOffCreateRequestHandler.subtractTime(copy.category, Number(copy.hours));
        timeOffCreateRequestHandler.removeDateFromRequest(deleteKey);

        calendarDateObject.removeClass(copy.category + "Selected");

        copy.hours = "4.00";
        newOne.hours = "4.00";

        timeOffCreateRequestHandler.addDateToRequest(copy);
        timeOffCreateRequestHandler.addTime(copy.category, Number(copy.hours));

        timeOffCreateRequestHandler.addDateToRequest(newOne);
        timeOffCreateRequestHandler.addTime(newOne.category, Number(newOne.hours));

        calendarDateObject.addClass(newOne.category + "Selected");
    }
    
    /**
     * Toggle the calendar legend showing the wonderful color system for categories. 
     *
     * @returns {undefined}     */
    this.toggleLegend = function() {
        $("#calendarLegend").toggle();
    }
};

// Initialize the class
timeOffCreateRequestHandler.initialize();