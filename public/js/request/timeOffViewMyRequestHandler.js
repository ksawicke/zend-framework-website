/**
 * Javascript timeOffViewRequestHandler 'class'
 *
 */
var timeOffViewRequestHandler = new function ()
{
    var timeOffLoadCalendarUrl = 'http://swift:10080/sawik/timeoff/public/request/api',
        timeOffSubmitTimeOffRequestUrl = 'http://swift:10080/sawik/timeoff/public/request/api',
        timeOffSubmitTimeOffSuccessUrl = 'http://swift:10080/sawik/timeoff/public/request/submitted-for-approval',
        employeePTOAvailable = 0,
        employeeFloatAvailable = 0,
        employeeSickAvailable = 0,
        employeeUnexcusedAbsenceAvailable = 0,
        employeeBereavementAvailable = 0,
        employeeCivicDutyAvailable = 0,
        employeeGrandfatheredAvailable = 0,
        employeeApprovedNoPayAvailable = 0,
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
        showCurrentRequestsOnOrAfter = '',
        showCurrentRequestsBefore = '',
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
                var selectedEmployee = e.params.data;
                //            console.log("SELECTED EMPLOYEE", selectedEmployee);
                requestForEmployeeNumber = selectedEmployee.id;
                requestForEmployeeName = selectedEmployee.text;
//                console.log("WWWWWW 1199");
                timeOffViewRequestHandler.loadCalendars(requestForEmployeeNumber);
                //            console.log('983');
                $('.requestIsForMe').show();
            })
                    .on("select2:open", function (e) {
                        //console.log("SELECT2 OPENED");
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
                        //console.log("SELECT2 CLOSED");
                    });

            /**
             * Handle clicking previous or next buttons on calendars
             */
            $(document).on('click', '.calendarNavigation', function () {
                timeOffViewRequestHandler.loadNewCalendars($(this).attr("data-month"), $(this).attr("data-year"));
            });
            
            $(document).on('click', '.toggleLegend', function() {
                timeOffViewRequestHandler.toggleLegend();
                console.log("TOGGLE");
            });

            /**
             * Handle clicking category
             */
//            $(".selectTimeOffCategory").click(function () {
//                timeOffViewRequestHandler.selectCategory($(this));
//            });

            /**
             * Handle clicking a calendar date
             */
//            $(document).on('click', '.calendar-day', function () {
//                var obj6 = {category: selectedTimeoffCategory, date: $(this).data('date')};
//                if (selectedTimeoffCategory !== null && "undefined" !== typeof obj6.date) {
//                    timeOffViewRequestHandler.updateRequestDates(obj6, $(this));
//                }
//
//                console.log("selectedDatesNew", selectedDatesNew);
//            });

            /**
             * Handle removing a date from request
             */
//            $(document).on('click', '.remove-date-requested', function () {
//
//                var obj6 = {category: $(this).attr('data-category'), date: $(this).data('date')};
//                if (selectedTimeoffCategory !== null && "undefined" !== typeof obj6.date) {
//                    timeOffViewRequestHandler.updateRequestDates(obj6, $(this));
//                }
//
//                console.log("selectedDatesNew", selectedDatesNew);
//            });

            /**
             * Handle user changing the hours for a date manually
             */
//            $(document).on('change', '.selectedDateHours', function () {
//                var key = $(this).attr("data-key");
//                var value = $(this).val();
//                selectedDateHours[key] = value;
//            });

            /**
             * Submit time off request
             */
//            $(document).on('click', '.submitTimeOffRequest', function () {
//                requestReason = $("#requestReason").val();
//                timeOffViewRequestHandler.submitTimeOffRequest();
//            });



            /**
             * Handle splitting a date into two categories
             */
//            $(document).on('click', '.split-date-requested', function () {
//                timeOffViewRequestHandler.splitDateRequested($(this));
//            });
//
//            $(document).on('click', '.changerequestForEmployeeNumber', function () {
////                    timeOffViewRequestHandler.loadCalendars($(this).attr("data-employee-number"));
////                    console.log('138');
//            });
//
//            $(document).on('change', '#directReportForm input', function () {
//                directReportFilter = $('input[name="directReportFilter"]:checked', '#directReportForm').val();
//            });

            timeOffViewRequestHandler.loadCalendars();
            timeOffViewRequestHandler.maskCalendars('show');
//            timeOffViewRequestHandler.drawHoursRequested();

            /**
             * Fade out flash messages automatically.
             */
            timeOffViewRequestHandler.fadeOutFlashMessage();

//        	timeOffViewRequestHandler.checkLocalStorage();

            console.log("TESTING PHP VARS PASSED IN", phpVars);
        });
    }

    this.isDateDisabled = function (object) {
        return (object.hasClass("calendar-day-disabled") ? true : false);
    }

    this.getCategoryText = function (category) {
        return categoryText[category];
    }

    this.checkLocalStorage = function () {
        if (typeof (Storage) !== "undefined") {
            // Code for localStorage/sessionStorage.
            console.log("local storage support enabled");
            var testObject = {'one': 1, 'two': 2, 'three': 3};

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
            timeOffViewRequestHandler.setStep('1');
//    		$('.timeOffCalendarWrapper').hide();
        } else {
            selectedTimeoffCategory = object.attr("data-category");
            object.addClass("categorySelected");
            object.addClass(selectedTimeoffCategory);

//	    	for(category in categoryText) {
//	    		if(selectedTimeoffCategory!=category) {
//	    			$('.buttonDisappear'+category.substr(7)).hide();
//	    		}
//	    	}

            if (selectedDatesNew.length > 0) {
                timeOffViewRequestHandler.setStep('3');
            } else {
                timeOffViewRequestHandler.setStep('2');
            }
            $('.' + selectedTimeoffCategory + 'CloseIcon').addClass('categoryCloseIcon glyphicon glyphicon-remove-circle');
//	    	$('.timeOffCalendarWrapper').show();
        }
//    	$('.timeOffCalendarWrapper').show();
    }

    /**
     * Loads calendars via ajax and displays them on the page.
     */
    this.loadCalendars = function (employeeNumber) {
        var month = (new Date()).getMonth() + 1;
        var year = (new Date()).getFullYear();

//        timeOffViewRequestHandler.clearSelectedDates();

        console.log("HIC");
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
        .success(function (json) {
//                    console.log("### 289");
            if (requestForEmployeeNumber === '') {
                loggedInUserData = json.employeeData;
            }

            requestForEmployeeNumber = json.employeeData.EMPLOYEE_NUMBER;
//        	console.log("requestForEmployeeNumber", requestForEmployeeNumber);
            var calendarHtml = '';
            $.each(json.calendarData.calendars, function (index, thisCalendarHtml) {
                $("#calendar" + index + "Html").html(
                    json.calendarData.openHeader +
                    ((index == 1) ? json.calendarData.navigation.fastRewindButton + ' ' + json.calendarData.navigation.prevButton : '') +
                    thisCalendarHtml.header + ((index == 3) ? json.calendarData.navigation.nextButton + ' ' + json.calendarData.navigation.fastForwardButton : '') +
                    json.calendarData.closeHeader +
                    thisCalendarHtml.data);
            });
                    
            // Take the JSON data and use it for highlighting dates
//                    selectedDatesApproved = json.requestData.json.approved;
//                    selectedDatesPendingApproval = json.requestData.json.pending;
//                    selectedDates = json.requestData.json.all;

            timeOffViewRequestHandler.setEmployeePTOAvailable(json.employeeData.PTO_AVAILABLE);
            timeOffViewRequestHandler.setEmployeePTOPending(json.employeeData.PTO_PENDING_TOTAL);

            timeOffViewRequestHandler.setEmployeeFloatAvailable(json.employeeData.FLOAT_AVAILABLE);
            timeOffViewRequestHandler.setEmployeeFloatPending(json.employeeData.FLOAT_PENDING_TOTAL);

            timeOffViewRequestHandler.setEmployeeSickAvailable(json.employeeData.SICK_AVAILABLE);
            timeOffViewRequestHandler.setEmployeeSickPending(json.employeeData.SICK_PENDING_TOTAL);

//        	timeOffViewRequestHandler.setEmployeeUnexcusedAbsenceAvailable(json.employeeData.UNEXCUSED_ABSENCE_AVAILABLE);
            timeOffViewRequestHandler.setEmployeeUnexcusedAbsencePending(json.employeeData.UNEXCUSED_PENDING_TOTAL);

//        	timeOffViewRequestHandler.setEmployeeBereavementAvailable(json.employeeData.BEREAVEMENT_AVAILABLE);
            timeOffViewRequestHandler.setEmployeeBereavementPending(json.employeeData.BEREAVEMENT_PENDING_TOTAL);

//        	timeOffViewRequestHandler.setEmployeeCivicDutyAvailable(json.employeeData.CIVIC_DUTY_AVAILABLE);
            timeOffViewRequestHandler.setEmployeeCivicDutyPending(json.employeeData.CIVIC_DUTY_PENDING_TOTAL);

            timeOffViewRequestHandler.setEmployeeGrandfatheredAvailable(json.employeeData.GF_AVAILABLE);
            timeOffViewRequestHandler.setEmployeeGrandfatheredPending(json.employeeData.GF_PENDING_TOTAL);

//        	timeOffViewRequestHandler.setEmployeeApprovedNoPayAvailable(json.employeeData.APPROVED_NO_PAY_AVAILABLE);
            timeOffViewRequestHandler.setEmployeeApprovedNoPayPending(json.employeeData.UNPAID_PENDING_TOTAL);

            timeOffViewRequestHandler.setSelectedDates(json.requestData.json.approved, json.requestData.json.pending);
            timeOffViewRequestHandler.highlightDates();

            // $(this).hasClass('disableTimeOffCategorySelection')
            if (json.employeeData.GF_AVAILABLE > 0) {
                $('.categoryPTO').addClass('disableTimeOffCategorySelection');
            }

            requestForEmployeeNumber = json.employeeData.EMPLOYEE_NUMBER;
            requestForEmployeeName = json.employeeData.EMPLOYEE_NAME +
                ' (' + json.employeeData.EMPLOYEE_NUMBER + ') - ' + json.employeeData.POSITION_TITLE;
//                            timeOffViewRequestHandler.capitalizeFirstLetter(json.employeeData.LAST_NAME) + ", " +
//                            timeOffViewRequestHandler.capitalizeFirstLetter(json.employeeData.COMMON_NAME) +
//                            ' (' + requestForEmployeeNumber + ') - ' + json.employeeData.POSITION_TITLE;

            console.log('json.employeeData', json.employeeData);
//            console.log('requestForEmployeeNumber', requestForEmployeeNumber);
//            console.log('requestForEmployeeName', requestForEmployeeName);

//                    $("#requestFor")
//                        .empty()
//                        .append('<option value="' + requestForEmployeeNumber + '">' + requestForEmployeeName + '</option>')
//                        .val(requestForEmployeeNumber).trigger('change');

//                    timeOffViewRequestHandler.checkAllowRequestOnBehalfOf();

            showCurrentRequestsOnOrAfter = json.calendarData.showCurrentRequestsOnOrAfter;
            showCurrentRequestsBefore = json.calendarData.showCurrentRequestsBefore;
            
            // YO DUDE
            selectedDates = json.requestData.json.all; //json.requestData.json.all;
            timeOffViewRequestHandler.drawHoursRequested();

            console.log(json.calendarData.showCurrentRequestsOnOrAfter + ' :: ' + json.calendarData.showCurrentRequestsBefore);

            console.log("json", json);

            return;
        })
        .error(function () {
            console.log('There was some error.');
            return;
        });
    }

    this.setStep = function (step) {
        $(".step1").removeClass("active");
        $(".step2").removeClass("active");
        $(".step3").removeClass("active");
        $(".step" + step).addClass("active");
    }

    this.submitTimeOffRequest = function () {
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
//                    timeOffViewRequestHandler.clearSelectedDates();
                    console.log("@@@@ 410");
                    var calendarHtml = '';
                    $.each(json.calendarData.calendars, function (index, thisCalendarHtml) {
                        $("#calendar" + index + "Html").html(
                            json.calendarData.openHeader +
                            ((index == 1) ? json.calendarData.navigation.fastRewindButton + '&nbsp;&nbsp;&nbsp;' + json.calendarData.navigation.prevButton : '') +
                            thisCalendarHtml.header + ((index == 3) ? json.calendarData.navigation.nextButton + '&nbsp;&nbsp;&nbsp;' + json.calendarData.navigation.fastForwardButton : '') +
                            json.calendarData.closeHeader +
                            thisCalendarHtml.data);
                    });

                    timeOffViewRequestHandler.setSelectedDates(json.requestData.json.approved, json.requestData.json.pending);
                    timeOffViewRequestHandler.highlightDates();
                    
                    //selectedDates = json.requestData.all;
                    showCurrentRequestsOnOrBefore = json.calendarData.showCurrentRequestsOnOrAfter;
                    showCurrentRequestsBefore = json.calendarData.showCurrentRequestsBefore;
                    
                    selectedDates = json.requestData.json.all; //json.requestData.json.all;
                    timeOffViewRequestHandler.drawHoursRequested();
                    return;
                })
                .error(function () {
                    console.log('There was some error.');
                    return;
                });
    }

    /**
     * Prints the Available PTO time for selected employee.
     */
    this.setEmployeePTOAvailable = function (ptoAvailable) {
        employeePTOAvailable = ptoAvailable;
        timeOffViewRequestHandler.printEmployeePTOAvailable();
    }

    this.setEmployeePTOPending = function (ptoPending) {
        employeePTOPending = ptoPending;
        timeOffViewRequestHandler.printEmployeePTOPending();
    }

    /**
     * Sets the Available Float time for selected employee.
     */
    this.setEmployeeFloatAvailable = function (floatAvailable) {
        employeeFloatAvailable = floatAvailable;
        timeOffViewRequestHandler.printEmployeeFloatAvailable();
    }

    this.setEmployeeFloatPending = function (floatPending) {
        employeeFloatPending = floatPending;
        timeOffViewRequestHandler.printEmployeeFloatPending();
    }

    /**
     * Sets the Available sick time for selected employee.
     */
    this.setEmployeeSickAvailable = function (sickAvailable) {
        employeeSickAvailable = sickAvailable;
        timeOffViewRequestHandler.printEmployeeSickAvailable();
    }

    this.setEmployeeSickPending = function (sickPending) {
        var employeeSickPending = sickPending;
        timeOffViewRequestHandler.printEmployeeSickPending();
    }

    this.setEmployeeGrandfatheredAvailable = function (grandfatheredAvailable) {
        employeeGrandfatheredAvailable = grandfatheredAvailable;
        timeOffViewRequestHandler.printEmployeeGrandfatheredAvailable();
    }

    this.setEmployeeGrandfatheredPending = function (grandfatheredPending) {
        var employeeGrandfatheredPending = grandfatheredPending;
        timeOffViewRequestHandler.printEmployeeGrandfatheredPending();
    }

//    this.setEmployeeUnexcusedAbsenceAvailable = function(unexcusedAbsenceAvailable) {
//    	var employeeUnexcusedAbsenceAvailable = unexcusedAbsenceAvailable;
//    	timeOffViewRequestHandler.printEmployeeUnexcusedAbsenceAvailable();
//    }

    this.setEmployeeUnexcusedAbsencePending = function (unexcusedAbsencePending) {
        var employeeUnexcusedAbsencePending = unexcusedAbsencePending;
        timeOffViewRequestHandler.printEmployeeUnexcusedAbsencePending();
    }

//    this.setEmployeeBereavementAvailable = function(bereavementAvailable) {
//    	var employeeBereavementAvailable = bereavementAvailable;
//    	timeOffViewRequestHandler.printEmployeeBereavementAvailable();
//    }

    this.setEmployeeBereavementPending = function (bereavementPending) {
        var employeeBereavementPending = bereavementPending;
        timeOffViewRequestHandler.printEmployeeBereavementPending();
    }

//    this.setEmployeeCivicDutyAvailable = function(civicDutyAvailable) {
//    	var employeeCivicDutyAvailable = civicDutyAvailable;
//    	timeOffViewRequestHandler.printEmployeeCivicDutyAvailable();
//    }

    this.setEmployeeCivicDutyPending = function (civicDutyPending) {
        employeeCivicDutyPending = civicDutyPending;
        timeOffViewRequestHandler.printEmployeeCivicDutyPending();
    }

//    this.setEmployeeApprovedNoPayAvailable = function(approvedNoPayAvailable) {
//    	employeeApprovedNoPayAvailable = approvedNoPayAvailable;
//    	timeOffViewRequestHandler.printEmployeeApprovedNoPayAvailable();
//    }

    this.setEmployeeApprovedNoPayPending = function (approvedNoPayPending) {
        employeeApprovedNoPayPending = approvedNoPayPending;
        timeOffViewRequestHandler.printEmployeeApprovedNoPayPending();
    }

    /**
     * Prints the Available PTO time for selected employee.
     */
    this.printEmployeePTOAvailable = function () {
//    	console.log("WHOOOOOOOOOOOOA", employeePTOAvailable);
        $("#employeePTOAvailableHours").html(timeOffViewRequestHandler.setTwoDecimalPlaces(employeePTOAvailable) + " hours");
        if (timeOffViewRequestHandler.setTwoDecimalPlaces(employeePTOAvailable) <= 0) {
            $('.buttonDisappearPTO').addClass('hidden');
        } else {
            $('.buttonDisappearPTO').removeClass('hidden');
        }
    }

    this.printEmployeePTOPending = function () {
        $("#employeePTOPendingHours").html(timeOffViewRequestHandler.setTwoDecimalPlaces(employeePTOPending) + " hours");
    }

    /**
     * Prints the Available Float time for selected employee.
     */
    this.printEmployeeFloatAvailable = function () {
        $("#employeeFloatAvailableHours").html(timeOffViewRequestHandler.setTwoDecimalPlaces(employeeFloatAvailable) + " hours");
        if (timeOffViewRequestHandler.setTwoDecimalPlaces(employeeFloatAvailable) <= 0) {
            $('.buttonDisappearFloat').addClass('hidden');
        } else {
            $('.buttonDisappearFloat').removeClass('hidden');
        }
    }

    this.printEmployeeFloatPending = function () {
        $("#employeeFloatPendingHours").html(timeOffViewRequestHandler.setTwoDecimalPlaces(employeeFloatPending) + " hours");
    }

    /**
     * Prints the Available Sick time for selected employee.
     */
    this.printEmployeeSickAvailable = function () {
        $("#employeeSickAvailableHours").html(timeOffViewRequestHandler.setTwoDecimalPlaces(employeeSickAvailable) + " hours");
        if (timeOffViewRequestHandler.setTwoDecimalPlaces(employeeSickAvailable) <= 0) {
            $('.buttonDisappearSick').addClass('hidden');
        } else {
            $('.buttonDisappearSick').removeClass('hidden');
        }
    }

    this.printEmployeeSickPending = function () {
        $("#employeeSickPendingHours").html(timeOffViewRequestHandler.setTwoDecimalPlaces(employeeSickPending) + " hours");
    }

    this.printEmployeeGrandfatheredAvailable = function () {
        $("#employeeGrandfatheredAvailableHours").html(timeOffViewRequestHandler.setTwoDecimalPlaces(employeeGrandfatheredAvailable) + " hours");
        if (timeOffViewRequestHandler.setTwoDecimalPlaces(employeeGrandfatheredAvailable) <= 0) {
            $('.buttonDisappearGrandfathered').addClass('hidden');
        }
        console.log("employeeGrandfatheredAvailable", employeeGrandfatheredAvailable);
    }

    this.printEmployeeGrandfatheredPending = function () {
        $("#employeeGrandfatheredPendingHours").html(timeOffViewRequestHandler.setTwoDecimalPlaces(employeeGrandfatheredPending) + " hours");
    }

//    this.printEmployeeUnexcusedAbsenceAvailable = function() {
//    	$("#employeeUnexcusedAbsenceAvailableHours").html(timeOffViewRequestHandler.setTwoDecimalPlaces(employeeUnexcusedAbsenceAvailable) + " hours");
//    	if(employeeUnexcusedAbsenceAvailable<=0) {
//    		$('.buttonDisappearUnexcusedAbsence').addClass('hidden');
//    	}
//    }

    this.printEmployeeUnexcusedAbsencePending = function () {
        $("#employeeUnexcusedAbsencePendingHours").html(timeOffViewRequestHandler.setTwoDecimalPlaces(employeeUnexcusedAbsencePending) + " hours");
    }

//    this.printEmployeeBereavementAvailable = function() {
//    	$("#employeeBereavementAvailableHours").html(timeOffViewRequestHandler.setTwoDecimalPlaces(employeeBereavementAvailable) + " hours");
//    	if(employeeBereavementAvailable<=0) {
//    		$('.buttonDisappearBereavementAbsence').addClass('hidden');
//    	}
//    }

    this.printEmployeeBereavementPending = function () {
        $("#employeeBereavementPendingHours").html(timeOffViewRequestHandler.setTwoDecimalPlaces(employeeBereavementPending) + " hours");
    }

//    this.printEmployeeCivicDutyAvailable = function() {
//    	$("#employeeCivicDutyAvailableHours").html(timeOffViewRequestHandler.setTwoDecimalPlaces(employeeCivicDutyAvailable) + " hours");
//    	if(employeeCivicDutyAvailable<=0) {
//    		$('.buttonDisappearCivicDutyAbsence').addClass('hidden');
//    	}
//    }

    this.printEmployeeCivicDutyPending = function () {
        $("#employeeCivicDutyPendingHours").html(timeOffViewRequestHandler.setTwoDecimalPlaces(employeeCivicDutyPending) + " hours");
    }

//    this.printEmployeeApprovedNoPayAvailable = function() {
//    	$("#employeeApprovedNoPayAvailableHours").html(timeOffViewRequestHandler.setTwoDecimalPlaces(employeeApprovedNoPayAvailable) + " hours");
//    	if(employeeApprovedNoPayAvailable<=0) {
//    		$('.buttonDisappearApprovedNoPayAbsence').addClass('hidden');
//    	}
//    }

    this.printEmployeeApprovedNoPayPending = function () {
        $("#employeeApprovedNoPayPendingHours").html(timeOffViewRequestHandler.setTwoDecimalPlaces(employeeApprovedNoPayPending) + " hours");
    }

    /**
     * Adds employee defaultHours from the current Category of time Available.
     */
    this.addTime = function (category, hours) {
        switch (category) {
            case 'timeOffPTO':
                employeePTOAvailable -= hours;
                timeOffViewRequestHandler.printEmployeePTOAvailable();
                break;

            case 'timeOffFloat':
                employeeFloatAvailable -= hours;
                timeOffViewRequestHandler.printEmployeeFloatAvailable();
                break;

            case 'timeOffSick':
                employeeSickAvailable -= hours;
                timeOffViewRequestHandler.printEmployeeSickAvailable();
                break;

//	    	case 'timeOffGrandfathered':
//	    		employeeGrandfatheredAvailable -= defaultHours;
//	    		timeOffViewRequestHandler.printEmployeeGrandfatheredAvailable();
//	    		break;
        }
    }

    /**
     * Subtracts employee defaultHours from the current Category of time Available.
     */
    this.subtractTime = function (category, hours) {
        switch (category) {
            case 'timeOffPTO':
                employeePTOAvailable += hours;
                timeOffViewRequestHandler.printEmployeePTOAvailable();
                break;

            case 'timeOffFloat':
                employeeFloatAvailable += hours;
                timeOffViewRequestHandler.printEmployeeFloatAvailable();
                break;

            case 'timeOffSick':
                employeeSickAvailable += hours;
                timeOffViewRequestHandler.printEmployeeSickAvailable();
                break;
        }
    }

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

//        console.log("highlight selectedDatesNew check", selectedDatesNew);
//        console.log("highlight selectedDatesPendingApproval check", selectedDatesPendingApproval);
//        console.log("highlight selectedDatesApproved check", selectedDatesApproved);

        $.each($(".calendar-day"), function (index, blah) {
            for (var i = 0; i < selectedDatesNew.length; i++) {
                if (selectedDates[i].date &&
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
//        timeOffViewRequestHandler.addTime(obj.category, obj.hours);
    }

    this.removeDateFromRequest = function (deleteIndex) {
//        console.log(selectedDate);
//        var category = selectedDatesNew[deleteIndex].category;
//        console.log("selectedDatesNew[deleteIndex]", selectedDatesNew[deleteIndex]);
//        console.log("category", category);
        selectedDatesNew.splice(deleteIndex, 1);
//        timeOffViewRequestHandler.subtractTime(selectedTimeoffCategory, defaultHours);
    }

    /**
     * Removes a date from the request.
     */
    this.toggleDateFromRequest = function (object) {
        var selectedDate = object.data('date');
        var isSelected = timeOffViewRequestHandler.isSelected(object);
        var isDateDisabled = timeOffViewRequestHandler.isDateDisabled(object);

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
            timeOffViewRequestHandler.addDateToRequest(obj);
        } else {
//                var index = isSelected.deleteIndex;
//                var split1Obj = selectedDatesNew[index];
//                var category = split1Obj.category;

            console.log("@ index: " + index);
            console.log("@ category: " + category);
            console.log("@ selectedTimeoffCategory: " + selectedTimeoffCategory);
//                if(category!==selectedTimeoffCategory) {
//                timeOffViewRequestHandler.removeDateFromRequest(index);
//                timeOffViewRequestHandler.subtractTime(category, defaultHours);

//                split1Obj.hours = defaultSplitHours;
//                
//                console.log("1", split1Obj);
//                
//                timeOffViewRequestHandler.addDateToRequest(split1Obj);
//                timeOffViewRequestHandler.addTime(split1Obj.category, split1Obj.hours);

//                var split2Obj = { date: selectedDate,
//                    hours: defaultSplitHours,
//                    category: selectedTimeoffCategory
//                };
//                timeOffViewRequestHandler.addDateToRequest(split2Obj);
//                
//                console.log("2", split2Obj);

//                timeOffViewRequestHandler.addTime(split2Obj.category, split2Obj.hours);
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
//                timeOffViewRequestHandler.removeDateFromRequest(index);
//                timeOffViewRequestHandler.addDateToRequest(obj);
//            }

//            $.each($('.calendar-day'), function(index, object) {
//                if(selectedDate==$(this).data("date")) {
//                    $(this).toggleClass(selectedTimeoffCategory + "Selected");
//                }
//            });
//
//            timeOffViewRequestHandler.sortDatesSelected();
//            timeOffViewRequestHandler.drawHoursRequested();
//        }
    }

    /**
     * Removes a date from the request.
     * @param {type} dateRequestObject
     * @returns {undefined}     */
    this.selectCalendarDay = function (dateRequestObject) {
        var selectedDate = timeOffViewRequestHandler.isSelected(dateRequestObject);
        var isDateDisabled = timeOffViewRequestHandler.isDateDisabled(dateRequestObject);
        if (selectedTimeoffCategory != null && isDateDisabled === false) {
            timeOffViewRequestHandler.toggleDateFromRequest(selectedDate);
        }
    }

    /**
     * Draws form fields we can submit for the user.
     */
    this.drawHoursRequested = function () {
        timeOffViewRequestHandler.sortDatesSelected();
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
        
//        console.log("DATES TEST", selectedDates);
//        console.log(showCurrentRequestsOnOrBefore + " :: " + showCurrentRequestsBefore);
        
        console.log("selectedDates", selectedDates);
        
        console.log("TEST LENGTH");
        console.log('length', selectedDates.length);
        if(selectedDates.length==0) {
            datesSelectedDetailsHtml += 'No dates requested during the calendar period shown.';
        }
        
        for (var key = 0; key < selectedDates.length; key++) {
            if(selectedDates[key].dateYmd >= showCurrentRequestsOnOrAfter && selectedDates[key].dateYmd <= showCurrentRequestsBefore) {
                datesSelectedDetailsHtml += selectedDates[key].date + '&nbsp;&nbsp;&nbsp;&nbsp;' +
                    '<input class="selectedDateHours" value="' + timeOffViewRequestHandler.setTwoDecimalPlaces(selectedDates[key].hours) + '" size="2" data-key="' + key + '" disabled="disabled">' +
                    '&nbsp;&nbsp;&nbsp;&nbsp;' +
                    '<span class="badge ' + selectedDates[key].category + '">' +
                    timeOffViewRequestHandler.getCategoryText(selectedDates[key].category) +
                    '</span>' +
                    '&nbsp;&nbsp;&nbsp;' +
                    '<span class="glyphicon ' + ((selectedDates[key].status=='A') ? 'glyphicon-ok green' : 'glyphicon-user red')  + '" ' +
                    'data-date="' + selectedDates[key].date + '" ' +
                    'data-category="' + selectedDates[key].category + '" ' +
                    'title="' + ((selectedDates[key].status=='A') ? 'Approved time off' : 'Pending approval') + '">' +
                    '</span>' +
                    '<br style="clear:both;" />';
            }

            switch (selectedDates[key].category) {
                case 'timeOffPTO':
                    totalPTORequested += parseInt(selectedDates[key].hours, 10);
                    break;

                case 'timeOffFloat':
                    totalFloatRequested += parseInt(selectedDates[key].hours, 10);
                    break;

                case 'timeOffSick':
                    totalSickRequested += parseInt(selectedDates[key].hours, 10);
                    break;

                case 'timeOffUnexcusedAbsence':
                    totalUnexcusedAbsenceRequested += parseInt(selectedDates[key].hours, 10);
                    break;

                case 'timeOffBereavement':
                    totalBereavementRequested += parseInt(selectedDates[key].hours, 10);
                    break;

                case 'timeOffCivicDuty':
                    totalCivicDutyRequested += parseInt(selectedDates[key].hours, 10);
                    break;

                case 'timeOffGrandfathered':
                    totalGrandfatheredRequested += parseInt(selectedDates[key].hours, 10);
                    break;

                case 'timeOffApprovedNoPay':
                    totalApprovedNoPayRequested += parseInt(selectedDates[key].hours, 10);
                    break;
            }
        }

//        datesSelectedDetailsHtml +=
//                '<br style="clear:both;"/>' +
//                '<strong>Reason for request:</strong>' +
//                '<br style="clear:both;"/>' +
//                '<br style="clear:both;"/>' +
//                '<textarea cols="40" rows="4" id="requestReason"></textarea><br /><br />' +
//                '<button type="button" class="btn btn-form-primary btn-lg submitTimeOffRequest">Submit My Request</button>' +
//                '<br style="clear:both;" /><br style="clear:both;" />@@@@@@@@@@@@@';

        $("#datesSelectedDetails2").html(datesSelectedDetailsHtml);

        timeOffViewRequestHandler.printEmployeePTOAvailable();
    }

    /**
     * Sorts dates in the selected array.
     */
    this.sortDatesSelected = function () {
//        selectedDates.sort(function (a, b) {
//            var dateA = new Date(a.date).getTime();
//            var dateB = new Date(b.date).getTime();
//            return dateA > dateB ? 1 : -1;
//        });
        console.log("SORT selectedDates", selectedDates);
    }

    this.selectResult = function (item) {
//    	timeOffViewRequestHandler.loadCalendars(item.value);
    }

    this.setAsRequestForAnother = function () {
        $('.requestIsForMe').hide();
        $('.requestIsForAnother').show();
        $('.requestIsForAnother').focus();
    }

    this.setAsRequestForMe = function () {
        $('.requestIsForMe').show();
        $('.requestIsForAnother').hide();
        timeOffViewRequestHandler.clearRequestFor();
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
            timeOffViewRequestHandler.enableSelectRequestFor();
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
                url: timeOffLoadCalendarUrl,
                method: 'post',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term,
                        action: 'getEmployeeList',
                        directReportFilter: directReportFilter,
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
//            timeOffViewRequestHandler.loadCalendars(requestForEmployeeNumber);
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
            $('body').append('<link href="/sawik/timeoff/public/css/timeOffCalendarEnable.css" rel="stylesheet" id="enableTimeOffCalendar" />');
        } else if (action === 'hide') {
            $('#enableTimeOffCalendar').remove();
        }
    }

    this.fadeOutFlashMessage = function () {
        var sec = 10;
        var timer = setInterval(function () {
            $('#applicationFlashMessage span').text(sec--);
            if (sec == -1) {
                $('#applicationFlashMessage').fadeOut('fast');
                clearInterval(timer);
            }
        }, 1000);
    }

    this.selectCategory = function (categoryButton) {
        if (!categoryButton.hasClass('disableTimeOffCategorySelection')) {
            timeOffViewRequestHandler.resetTimeoffCategory(categoryButton);
            timeOffViewRequestHandler.setTimeoffCategory(categoryButton);
        }
        if (categoryButton.hasClass('disableTimeOffCategorySelection') && categoryButton.hasClass('categoryPTO')) {
            timeOffViewRequestHandler.alertUserToTakeGrandfatheredTime();
        }
        if (selectedTimeoffCategory === null) {
            timeOffViewRequestHandler.maskCalendars('hide');
        } else {
            timeOffViewRequestHandler.maskCalendars('show');
        }
    }

    /**
     * Clears out the selected dates and refreshes the form.
     * @returns {undefined}     */
    this.clearSelectedDates = function () {
        selectedDates = [];
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
            timeOffViewRequestHandler.addDataToRequest(calendarDateObject, object);
        }

        /**
         * Delete date from request.
         */
        if (copy == null && deleteKey !== null) {
            timeOffViewRequestHandler.deleteDataFromRequest(calendarDateObject, deleteKey, object);
        }

        /**
         * Split the data.
         */
        if (copy !== null && deleteKey !== null) {
            timeOffViewRequestHandler.splitDataFromRequest(calendarDateObject, deleteKey, copy, newOne);
        }

        timeOffViewRequestHandler.sortDatesSelected();
        timeOffViewRequestHandler.drawHoursRequested();
    }

    this.addDataToRequest = function (calendarDateObject, object) {
        object.hours = "8.00";

        timeOffViewRequestHandler.addDateToRequest(object);
        timeOffViewRequestHandler.addTime(object.category, object.hours);

        calendarDateObject.addClass(object.category + "Selected");
    }

    this.deleteDataFromRequest = function (calendarDateObject, deleteKey, object) {
        timeOffViewRequestHandler.subtractTime(selectedDatesNew[deleteKey].category, Number(selectedDatesNew[deleteKey].hours));
        timeOffViewRequestHandler.removeDateFromRequest(deleteKey);
        calendarDateObject.removeClass(object.category + "Selected");

        $.each($('.calendar-day'), function (index, obj) {
            if ($(this).data("date") === calendarDateObject.data("date")) {
                $(this).removeClass("timeOffPTOSelected");
            }
        });
    }

    this.splitDataFromRequest = function (calendarDateObject, deleteKey, copy, newOne) {
        timeOffViewRequestHandler.subtractTime(copy.category, Number(copy.hours));
        timeOffViewRequestHandler.removeDateFromRequest(deleteKey);

        calendarDateObject.removeClass(copy.category + "Selected");

        copy.hours = "4.00";
        newOne.hours = "4.00";

        timeOffViewRequestHandler.addDateToRequest(copy);
        timeOffViewRequestHandler.addTime(copy.category, Number(copy.hours));

        timeOffViewRequestHandler.addDateToRequest(newOne);
        timeOffViewRequestHandler.addTime(newOne.category, Number(newOne.hours));

        calendarDateObject.addClass(newOne.category + "Selected");
    }
    
    this.toggleLegend = function() {
        $("#calendarLegend").toggle();
    }
};

// Initialize the class
timeOffViewRequestHandler.initialize();