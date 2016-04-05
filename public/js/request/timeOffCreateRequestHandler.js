/**
 * Javascript timeOffCreateRequestHandler 'class'
 *
 */
var timeOffCreateRequestHandler = new function() {
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
            'timeOffPTO' : 'PTO',
            'timeOffFloat' : 'Float',
            'timeOffSick' : 'Sick',
            'timeOffGrandfathered' : 'Grandfathered',
            'timeOffUnexcusedAbsence' : 'Unexcused',
            'timeOffBereavement' : 'Bereavement',
            'timeOffCivicDuty' : 'Civic Duty',
            'timeOffApprovedNoPay' : 'Time Off Without Pay'
        },
        directReportFilter = 'B';
        
    /**
    * Initializes binding
    */
   this.initialize = function() {
       $(document).ready(function() {
            var $requestForEventSelect = $("#requestFor");
            /**
             * When we change the for dropdown using select2,
             * set the employee number and name as a local variable
             * for form submission, and refresh the calendars.
             */
            $requestForEventSelect.on("select2:select", function(e) {
                timeOffCreateRequestHandler.resetCategorySelection();
                var selectedEmployee = e.params.data;
                requestForEmployeeNumber = selectedEmployee.id;
                requestForEmployeeName = selectedEmployee.text;
                timeOffCreateRequestHandler.loadCalendars(requestForEmployeeNumber);
                $('.requestIsForMe').show();
            }).on("select2:open", function(e) {
                /**
                 * SELECT2 is opened
                 */
                if (loggedInUserData.IS_LOGGED_IN_USER_PAYROLL === "N") {
                    $("span").remove(".select2CustomTag");
                    var $filter = '<form id="directReportForm" style="display:inline-block;padding 5px;">'
                        + '<input type="radio" name="directReportFilter" value="B"'
                        + ((directReportFilter === 'B') ? ' checked'
                            : '')
                        + '> Both&nbsp;&nbsp;&nbsp;'
                        + '<input type="radio" name="directReportFilter" value="D"'
                        + ((directReportFilter === 'D') ? ' checked'
                            : '')
                        + '> Direct Reports&nbsp;&nbsp;&nbsp;'
                        + '<input type="radio" name="directReportFilter" value="I"'
                        + ((directReportFilter === 'I') ? ' checked'
                            : '')
                        + '> Indirect Reports&nbsp;&nbsp;&nbsp;'
                        + '</form>';
                    $("<span class='select2CustomTag' style='padding-left:6px;'>"
                        + $filter
                        + "</span>")
                    .insertBefore('.select2-results');
                }
            }).on("select2:close", function(e) {
                /**
                 * SELECT2 is closed
                 */
            });
            
            timeOffCreateRequestHandler.handleCalendarNavigation();
            timeOffCreateRequestHandler.handleToggleLegend();
            timeOffCreateRequestHandler.handleClickCategory();
            timeOffCreateRequestHandler.handleClickCalendarDate();
            timeOffCreateRequestHandler.handleRemoveDateFromRequest();
            timeOffCreateRequestHandler.handleChangeHoursForDateManually();
            timeOffCreateRequestHandler.handleSubmitRequest();
            timeOffCreateRequestHandler.handleSplitDate();
            timeOffCreateRequestHandler.handleChangeRequestForEmployee();
            timeOffCreateRequestHandler.handleDirectReportToggle();
            timeOffCreateRequestHandler.loadCalendars();
        });
    }

    /**
     * Handles when approved user changes Direct Report filter
     */
    this.handleDirectReportToggle = function() {
        $(document).on('change', '#directReportForm input', function() {
            directReportFilter = $(
                    'input[name="directReportFilter"]:checked',
                    '#directReportForm').val();
        });
    }

    /**
     * Unused
     */
    this.handleChangeRequestForEmployee = function() {
        $(document).on('click', '.changerequestForEmployeeNumber', function() {
            // timeOffCreateRequestHandler.loadCalendars($(this).attr("data-employee-number"));
        });
    }

    /**
     * Handle splitting a date into two categories
     */
    this.handleSplitDate = function() {
        $(document).on('click', '.split-date-requested', function() {
            timeOffCreateRequestHandler.splitDateRequested($(this));
        });
    }

    /**
     * Submit time off request
     */
    this.handleSubmitRequest = function() {
        $(document).on('click', '.submitTimeOffRequest', function() {
            requestReason = $("#requestReason").val();
            timeOffCreateRequestHandler.submitTimeOffRequest();
        });
    }

    /**
     * Handle user changing the hours for a date manually
     */
    this.handleChangeHoursForDateManually = function() {
        $(document).on('change', '.selectedDateHours', function() {
            var key = $(this).attr("data-key");
            var value = $(this).val();
            selectedDateHours[key] = value;
        });
    }

    /**
     * Handle removing a date from request
     */
    this.handleRemoveDateFromRequest = function() {
        $(document).on('click', '.remove-date-requested', function() {
            var dateObject = {
                category : $(this).attr('data-category'),
                date : $(this).data('date')
            };
            console.log( "DELETE DATE OBJECT", dateObject );
            console.log( "CURRENT DATES SELECTED", selectedDatesNew );
            $.each( selectedDatesNew, function( index, selectedDateNewObject ) {
                if( selectedDateNewObject.date===dateObject.date &&
                    selectedDateNewObject.category===dateObject.category ) {
                    console.log( "DELETE THIS FROM selectedDatesNew", selectedDateNewObject );
                    timeOffCreateRequestHandler.deleteDataFromRequest( selectedDatesNew, index, dateObject );
                } else {
                    console.log( "LEAVE THIS IN selectedDatesNew", selectedDateNewObject );
//                    timeOffCreateRequestHandler.addDataToRequest( calendarDateObject, object );
                }
            });
//            if (selectedTimeoffCategory !== null && "undefined" !== typeof dateObject.date) {
//                timeOffCreateRequestHandler.updateRequestDates(dateObject, $(this));
//            }
        });
    }

    /**
     * Handle clicking a calendar date
     */
    this.handleClickCalendarDate = function() {
        $(document).on('click', '.calendar-day', function() {
            var selectedCalendarDateObject = $(this);
            if( selectedCalendarDateObject.hasClass("calendar-day-holiday") ) {
                $("#dialogConfirmSelectHoliday").dialog({
                    modal : true,
                    closeOnEscape: false,
                    buttons : {
                        Yes : function() {
                            $(this).dialog("close");
                            timeOffCreateRequestHandler.markDayAsRequestedOff( selectedTimeoffCategory, selectedCalendarDateObject );
                        },
                        No : function() {
                            $(this).dialog("close");
                        }
                    }
                });
            } else {
                timeOffCreateRequestHandler.markDayAsRequestedOff( selectedTimeoffCategory, selectedCalendarDateObject );
            }
        });
    }
    
    /**
     * Marks a day as requested off.
     * 
     * @param {type} selectedTimeoffCategory
     * @param {type} currentObject
     * @returns {undefined}
     */
    this.markDayAsRequestedOff = function( selectedTimeoffCategory, selectedCalendarDateObject ) {
        console.log( "selectedTimeoffCategory", selectedTimeoffCategory );
        console.log( "selectedCalendarDateObject", selectedCalendarDateObject );
        var dateObject = {
            category : selectedTimeoffCategory,
            date : selectedCalendarDateObject.data('date')
        };
        if (selectedTimeoffCategory !== null && "undefined" !== typeof dateObject.date) {
            timeOffCreateRequestHandler.updateRequestDates( dateObject, selectedCalendarDateObject );
        }
    }

    /**
     * Handle clicking category
     */
    this.handleClickCategory = function() {
        $(".selectTimeOffCategory").click(function() {
            timeOffCreateRequestHandler.selectCategory($(this));
        });
    }

    /**
     * Toggle the category color legend
     */
    this.handleToggleLegend = function() {
        $(document).on('click', '.toggleLegend', function() {
            timeOffCreateRequestHandler.toggleLegend();
        });
    }

    /**
     * Handle clicking previous or next buttons on calendars
     */
    this.handleCalendarNavigation = function() {
        $('body').on('click', '.calendarNavigation', function(e) {
            timeOffCreateRequestHandler.loadNewCalendars($(this).attr("data-month"), $(this).attr("data-year"));
        });
    }

    /**
     * Checks if date is disabled
     * 
     * @param {type} object
     * @returns {Boolean}
     */
    this.isDateDisabled = function(object) {
        return (object.hasClass("calendar-day-disabled") ? true : false);
    }

    /**
     * Gets the text description of the category passed in.
     * 
     * @param {type} category
     * @returns {timeOffCreateRequestHandler.timeOffCreateRequestHandler_L5.categoryText|categoryText}
     */
    this.getCategoryText = function(category) {
        return categoryText[category];
    }

    /**
     * Resets the Remaining sick time for selected employee.
     */
    this.resetTimeoffCategory = function(object) {
        $('.btn-requestCategory').removeClass("categorySelected");
        $('.btn-requestCategory').removeClass(selectedTimeoffCategory);
        for (category in categoryText) {
            $('.' + category + 'CloseIcon').removeClass('categoryCloseIcon glyphicon glyphicon-remove-circle');
            // timeOffPTO
            $('.buttonDisappear' + category.substr(7)).show();
        }
    }

    /**
     * Sets the currently selected time off category.
     */
    this.setTimeoffCategory = function(object) {
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
    this.loadCalendars = function(employeeNumber) {
        var month = (new Date()).getMonth() + 1;
        var year = (new Date()).getFullYear();
        timeOffCreateRequestHandler.clearSelectedDates();
        $.ajax({
            url : timeOffLoadCalendarUrl,
            type : 'POST',
            data: {
            //                action: 'loadCalendars',
                startMonth : month,
                startYear : year,
                employeeNumber : ((typeof employeeNumber === "string") ? employeeNumber : phpVars.employee_number)
            },
            dataType : 'json'
        })
        .success(function(json) {
            if (requestForEmployeeNumber === '') {
                loggedInUserData = json.employeeData;
                loggedInUserData.IS_LOGGED_IN_USER_MANAGER = json.loggedInUser.isManager;
                loggedInUserData.IS_LOGGED_IN_USER_PAYROLL = json.loggedInUser.isPayroll;
            }

            requestForEmployeeNumber = json.employeeData.EMPLOYEE_NUMBER;
            requestForEmployeeObject = json.employeeData;
            timeOffCreateRequestHandler.drawThreeCalendars(json.calendarData);
            timeOffCreateRequestHandler.setHours(json.employeeData);
            if (json.employeeData.GF_REMAINING > 0) {
                $('.categoryPTO').addClass('disableTimeOffCategorySelection');
            }

            requestForEmployeeNumber = $.trim(requestForEmployeeObject.EMPLOYEE_NUMBER);
            requestForEmployeeName = requestForEmployeeObject.EMPLOYEE_DESCRIPTION
                + ' - '
                + requestForEmployeeObject.POSITION_TITLE;
                $("#requestFor").empty().append(
                '<option value="'
                + requestForEmployeeNumber + '">'
                + requestForEmployeeName
                + '</option>').val(requestForEmployeeNumber).trigger('change');
                timeOffCreateRequestHandler.checkAllowRequestOnBehalfOf();
            return;
        }).error(function() {
            console.log('There was some error.');
            return;
        });
    }

    /**
     * Update buttons with hour data.
     */
    this.setHours = function(employeeData) {
        timeOffCreateRequestHandler.setEmployeeGrandfatheredRemaining(employeeData.GF_REMAINING);
        timeOffCreateRequestHandler.setEmployeeGrandfatheredPending(employeeData.GF_PENDING_TOTAL);
        timeOffCreateRequestHandler.setEmployeePTORemaining(employeeData.PTO_REMAINING);
        timeOffCreateRequestHandler.setEmployeePTOPending(employeeData.PTO_PENDING_TOTAL);
        timeOffCreateRequestHandler.setEmployeeFloatRemaining(employeeData.FLOAT_REMAINING);
        timeOffCreateRequestHandler.setEmployeeFloatPending(employeeData.FLOAT_PENDING_TOTAL);
        timeOffCreateRequestHandler.setEmployeeSickRemaining(employeeData.SICK_REMAINING);
        timeOffCreateRequestHandler.setEmployeeSickPending(employeeData.SICK_PENDING_TOTAL);
        timeOffCreateRequestHandler.setEmployeeUnexcusedAbsencePending(employeeData.UNEXCUSED_PENDING_TOTAL);
        timeOffCreateRequestHandler.setEmployeeBereavementPending(employeeData.BEREAVEMENT_PENDING_TOTAL);
        timeOffCreateRequestHandler.setEmployeeCivicDutyPending(employeeData.CIVIC_DUTY_PENDING_TOTAL);
        timeOffCreateRequestHandler.setEmployeeApprovedNoPayPending(employeeData.UNPAID_PENDING_TOTAL);
    }

    /**
     * Draws the three calendars loaded
     */
    this.drawThreeCalendars = function(calendarData) {
        /** Update navigation button data **/
        $("#calendarNavigationFastRewind").attr("data-month", calendarData.navigation.calendarNavigationFastRewind.month);
        $("#calendarNavigationFastRewind").attr("data-year", calendarData.navigation.calendarNavigationFastRewind.year);
        $("#calendarNavigationRewind").attr("data-month", calendarData.navigation.calendarNavigationRewind.month);
        $("#calendarNavigationRewind").attr("data-year", calendarData.navigation.calendarNavigationRewind.year);
        $("#calendarNavigationFastForward").attr("data-month", calendarData.navigation.calendarNavigationFastForward.month);
        $("#calendarNavigationFastForward").attr("data-year", calendarData.navigation.calendarNavigationFastForward.year);
        $("#calendarNavigationForward").attr("data-month", calendarData.navigation.calendarNavigationForward.month);
        $("#calendarNavigationForward").attr("data-year", calendarData.navigation.calendarNavigationForward.year);
        
        /** Draw calendar labels **/
        $("#calendar1Label").html(calendarData.headers[1]);
        $("#calendar2Label").html(calendarData.headers[2]);
        $("#calendar3Label").html(calendarData.headers[3]);
        
        /** Draw calendars **/
        $("#calendar1Body").html(calendarData.calendars[1]);
        $("#calendar2Body").html(calendarData.calendars[2]);
        $("#calendar3Body").html(calendarData.calendars[3]);
        
        timeOffCreateRequestHandler.highlightDates();
    }
    
    /**
     * Marks the appropriate step the user is on for this request.
     * 
     * @param {type} step
     * @returns {undefined}
     */
    this.setStep = function(step) {
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
    this.submitTimeOffRequest = function() {
        $.ajax({
            url : timeOffSubmitTimeOffRequestUrl,
            type : 'POST',
            data : {
                request : {
                    forEmployee : {
                        EMPLOYEE_NUMBER : requestForEmployeeNumber
                    },
                    byEmployee : loggedInUserData,
                    dates : selectedDatesNew,
                    reason : requestReason
                }
            },
            dataType : 'json'
        }).success(function(json) {
            if (json.success == true) {
                window.location.href = timeOffSubmitTimeOffSuccessUrl;
            } else {
                alert(json.message);
            }
            return;
        }).error(function() {
            console.log('There was some error.');
            return;
        });
    };
    
    /**
     * Handles loading calendars after initial load
     */
    this.loadNewCalendars = function(startMonth, startYear) {
        $.ajax({
            url : timeOffLoadCalendarUrl,
            type : 'POST',
            data : {
                action : 'loadCalendar',
                startMonth : startMonth,
                startYear : startYear,
                employeeNumber : requestForEmployeeNumber
            },
            dataType : 'json'
        }).success(function(json) {
            timeOffCreateRequestHandler.drawThreeCalendars(json.calendarData);
            return;
        }).error(function() {
            console.log('There was some error.');
            return;
        });
    }

    /**
     * Prints the Remaining PTO time for selected employee.
     */
    this.setEmployeePTORemaining = function(ptoRemaining) {
        employeePTORemaining = ptoRemaining;
        timeOffCreateRequestHandler.printEmployeePTORemaining();
    }

    /**
     * Prints the Pending PTO time for selected employee.
     */
    this.setEmployeePTOPending = function(ptoPending) {
        employeePTOPending = ptoPending;
        timeOffCreateRequestHandler.printEmployeePTOPending();
    }

    /**
     * Sets the Remaining Float time for selected employee.
     */
    this.setEmployeeFloatRemaining = function(floatRemaining) {
        employeeFloatRemaining = floatRemaining;
        timeOffCreateRequestHandler.printEmployeeFloatRemaining();
    }

    /**
     * Sets the Pending Float time for selected employee.
     */
    this.setEmployeeFloatPending = function(floatPending) {
        employeeFloatPending = floatPending;
        timeOffCreateRequestHandler.printEmployeeFloatPending();
    }

    /**
     * Sets the Remaining Sick time for selected employee.
     */
    this.setEmployeeSickRemaining = function(sickRemaining) {
        employeeSickRemaining = sickRemaining;
        timeOffCreateRequestHandler.printEmployeeSickRemaining();
    }

    /**
     * Sets the Pending Sick time for selected employee.
     */
    this.setEmployeeSickPending = function(sickPending) {
        var employeeSickPending = sickPending;
        timeOffCreateRequestHandler.printEmployeeSickPending();
    }

    /**
     * Sets the Remaining Grandfathered time for selected employee.
     */
    this.setEmployeeGrandfatheredRemaining = function(grandfatheredRemaining) {
        employeeGrandfatheredRemaining = grandfatheredRemaining;
        timeOffCreateRequestHandler.printEmployeeGrandfatheredRemaining();
    }

    /**
     * Sets the Pending Grandfathered time for selected employee.
     */
    this.setEmployeeGrandfatheredPending = function(grandfatheredPending) {
        var employeeGrandfatheredPending = grandfatheredPending;
        timeOffCreateRequestHandler.printEmployeeGrandfatheredPending();
    }

    /**
     * Sets the Pending Unexcused Absence time for selected employee.
     */
    this.setEmployeeUnexcusedAbsencePending = function(unexcusedAbsencePending) {
        var employeeUnexcusedAbsencePending = unexcusedAbsencePending;
        timeOffCreateRequestHandler.printEmployeeUnexcusedAbsencePending();
    }

    /**
     * Sets the Pending Bereavement time for selected employee.
     */
    this.setEmployeeBereavementPending = function(bereavementPending) {
        var employeeBereavementPending = bereavementPending;
        timeOffCreateRequestHandler.printEmployeeBereavementPending();
    }

    /**
     * Sets the Pending Civic Duty time for selected employee.
     */
    this.setEmployeeCivicDutyPending = function(civicDutyPending) {
        employeeCivicDutyPending = civicDutyPending;
        timeOffCreateRequestHandler.printEmployeeCivicDutyPending();
    }

    /**
     * Sets the Pending Time Off Without Pay time for selected employee.
     */
    this.setEmployeeApprovedNoPayPending = function(approvedNoPayPending) {
        employeeApprovedNoPayPending = approvedNoPayPending;
        timeOffCreateRequestHandler.printEmployeeApprovedNoPayPending();
    }

    /**
     * Prints the Remaining PTO time for selected employee.
     */
    this.printEmployeePTORemaining = function() {
        $("#employeePTORemainingHours").html(
            timeOffCreateRequestHandler.setTwoDecimalPlaces(employeePTORemaining) + " hours");
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
    this.printEmployeePTOPending = function() {
        $("#employeePTOPendingHours").html(
            timeOffCreateRequestHandler.setTwoDecimalPlaces(employeePTOPending) + " hours");
    }

    /**
     * Prints the Remaining Float time for selected employee.
     */
    this.printEmployeeFloatRemaining = function() {
        $("#employeeFloatRemainingHours").html(
            timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeFloatRemaining) + " hours");
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
    this.printEmployeeFloatPending = function() {
        $("#employeeFloatPendingHours").html(
            timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeFloatPending) + " hours");
    }

    /**
     * Prints the Remaining Sick time for selected employee.
     */
    this.printEmployeeSickRemaining = function() {
        $("#employeeSickRemainingHours").html(
            timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeSickRemaining) + " hours");
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
    this.printEmployeeSickPending = function() {
        $("#employeeSickPendingHours").html(
            timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeSickPending) + " hours");
    }

    /**
     * Prints the Remaining Grandfathered time for selected employee.
     */
    this.printEmployeeGrandfatheredRemaining = function() {
        $("#employeeGrandfatheredRemainingHours").html(
            timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeGrandfatheredRemaining) + " hours");
        if (timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeGrandfatheredRemaining) <= 0) {
            $('.buttonDisappearGrandfathered').addClass('hidden');
        }
//            console.log("employeeGrandfatheredRemaining",
//            employeeGrandfatheredRemaining);
    }

    /**
     * Prints the Pending Grandfathered time for selected employee.
     */
    this.printEmployeeGrandfatheredPending = function() {
        $("#employeeGrandfatheredPendingHours").html(
            timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeGrandfatheredPending) + " hours");
    }

    /**
     * Prints the Pending Unexcused Absence time for selected employee.
     */
    this.printEmployeeUnexcusedAbsencePending = function() {
        $("#employeeUnexcusedAbsencePendingHours").html(
            timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeUnexcusedAbsencePending) + " hours");
    }

    /**
     * Prints the Pending Bereavement time for selected employee.
     */
    this.printEmployeeBereavementPending = function() {
        $("#employeeBereavementPendingHours").html(
            timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeBereavementPending) + " hours");
    }

    /**
     * Prints the Pending Civic Duty time for selected employee.
     */
    this.printEmployeeCivicDutyPending = function() {
        $("#employeeCivicDutyPendingHours").html(
            timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeCivicDutyPending) + " hours");
    }

    /**
     * Prints the Pending Time Off Without Pay time for selected employee.
     */
    this.printEmployeeApprovedNoPayPending = function() {
        $("#employeeApprovedNoPayPendingHours").html(
            timeOffCreateRequestHandler.setTwoDecimalPlaces(employeeApprovedNoPayPending) + " hours");
    }

    /**
     * Adds employee defaultHours from the current Category of time Remaining.
     */
    this.addTime = function(category, hours) {
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
    this.subtractTime = function(category, hours) {
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
    this.setSelectedDates = function(approvedRequests, pendingRequests) {
        selectedDatesApproved = [];
        selectedDatesPendingApproval = [];
        for (key in approvedRequests) {
            var obj = {
                date : approvedRequests[key].REQUEST_DATE,
                hours : approvedRequests[key].REQUESTED_HOURS,
                category : approvedRequests[key].REQUEST_TYPE
            };
            selectedDatesApproved.push(obj);
        }

        for (key in pendingRequests) {
            var obj = {
                date : pendingRequests[key].REQUEST_DATE,
                hours : pendingRequests[key].REQUESTED_HOURS,
                category : pendingRequests[key].REQUEST_TYPE
            };
            selectedDatesPendingApproval.push(obj);
        }
    }

    this.highlightDates = function() {
        $.each($(".calendar-day"), function(index, blah) {
            $(this).removeClass('timeOffPTOSelected');
            $(this).removeClass('timeOffFloatSelected');
            $(this).removeClass('timeOffSickSelected');
            $(this).removeClass('timeOffGrandfatheredSelected');
            $(this).removeClass('timeOffBereavementSelected');
            $(this).removeClass('timeOffApprovedNoPaySelected');
            $(this).removeClass('timeOffCivicDutySelected');
            $(this).removeClass('timeOffUnexcusedAbsenceSelected');
        });
        $.each($(".calendar-day"), function(index, blah) {
            if( $(this).attr("data-date") === moment().format('MM/DD/YYYY') ) {
                $(this).addClass("today");
            }
            for (var i = 0; i < selectedDatesNew.length; i++) {
                if (selectedDatesNew[i].date && selectedDatesNew[i].date === $(this).attr("data-date")) {
                    thisClass = selectedDatesNew[i].category + "Selected";
                    $(this).toggleClass(thisClass);
                    break;
                }
            }

            for (var i = 0; i < selectedDatesPendingApproval.length; i++) {
                if (selectedDatesPendingApproval[i].date && selectedDatesPendingApproval[i].date === $(this).attr("data-date")) {
                    thisClass = selectedDatesPendingApproval[i].category + " requestPending";
                    $(this).toggleClass(thisClass);
                    break;
                }
            }

            for (var i = 0; i < selectedDatesApproved.length; i++) {
                if (selectedDatesApproved[i].date && selectedDatesApproved[i].date === $(this).attr("data-date")) {
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
        console.log('object', object);
        console.log('boo ya check', j)
        var thisDate = object.data('date');
        var thisCategory = selectedTimeoffCategory;
        var thisHours = defaultHours;
        var obj = {
            date : thisDate,
            hours : '8.00',
            category : selectedTimeoffCategory
        };
        var isSelected = false;
        var deleteIndex = null;
        for (var i = 0; i < selectedDatesNew.length; i++) {
            if (selectedDatesNew[i].date === thisDate && selectedDatesNew[i].category != thisCategory) {
                isSelected = true;
                return {
                    isSelected : isSelected,
                    deleteIndex : i,
                    obj : obj
                };
            }
        }
        return {
            isSelected : isSelected,
            deleteIndex : i,
            obj : obj
        };
    }

    /**
     * Adds date to current request
     */
    this.addDateToRequest = function(obj) {
        selectedDatesNew.push(obj);
    }

    /**
     * Removes a date from current request
     */
    this.removeDateFromRequest = function(deleteIndex) {
        selectedDatesNew.splice(deleteIndex, 1);
    }

    /**
     * Removes a date from the request.
     */
    this.toggleDateFromRequest = function(object) {
        var selectedDate = object.data('date');
        var isSelected = timeOffCreateRequestHandler.isSelected(object);
        var isDateDisabled = timeOffCreateRequestHandler.isDateDisabled(object);
        if (isSelected.isSelected === false) {
            var obj = {
                date : selectedDate,
                hours : defaultHours,
                category : selectedTimeoffCategory
            };
            timeOffCreateRequestHandler.addDateToRequest(obj);
        } else {

        }
    }

    /**
     * Removes a date from the request.
     * 
     * @param {type} dateRequestObject
     * @returns {undefined}     */
    this.selectCalendarDay = function(dateRequestObject) {
        var selectedDate = timeOffCreateRequestHandler.isSelected(dateRequestObject);
        var isDateDisabled = timeOffCreateRequestHandler.isDateDisabled(dateRequestObject);
        if (selectedTimeoffCategory != null && isDateDisabled === false) {
            timeOffCreateRequestHandler.toggleDateFromRequest(selectedDate);
        }
    }

    /**
     * Draws form fields we can submit for the user.
     */
    this.drawHoursRequested = function() {
        var datesSelectedDetailsHtml = '<strong>Hours Requested:</strong>'
            + '<br style="clear:both;"/><br style="clear:both;"/>';
            totalPTORequested = 0;
            totalFloatRequested = 0;
            totalSickRequested = 0;
            totalUnexcusedAbsenceRequested = 0;
            totalBereavementRequested = 0;
            totalCivicDutyRequested = 0;
            totalGrandfatheredRequested = 0;
            totalApprovedNoPayRequested = 0;
        for (var key = 0; key < selectedDatesNew.length; key++) {
            datesSelectedDetailsHtml += selectedDatesNew[key].date
            + '&nbsp;&nbsp;&nbsp;&nbsp;'
            + '<input class="selectedDateHours" value="'
            + timeOffCreateRequestHandler
            .setTwoDecimalPlaces(selectedDatesNew[key].hours)
            + '" size="2" data-key="'
            + key
            + '" disabled="disabled">'
            + '&nbsp;&nbsp;&nbsp;&nbsp;'
            + '<span class="badge '
            + selectedDatesNew[key].category
            + '">'
            + timeOffCreateRequestHandler.getCategoryText(selectedDatesNew[key].category)
            + '</span>' + '&nbsp;&nbsp;&nbsp;' +
            '<span class="glyphicon glyphicon-remove-circle red remove-date-requested" '
            + 'data-date="' + selectedDatesNew[key].date + '" '
            + 'data-category="' + selectedDatesNew[key].category + '" '
            + 'title="Remove date from request">' + '</span>'
            + '<br style="clear:both;" />';

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
                    totalUnexcusedAbsenceRequested += parseInt( selectedDatesNew[key].hours, 10);
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
            timeOffCreateRequestHandler.unselectCategory();
        } else {
            $('#datesSelectedDetails').show();
            $("#requestSubmitDetails").show();
            timeOffCreateRequestHandler.setStep('3');
        }

        timeOffCreateRequestHandler.printEmployeePTORemaining();
    }

    /**
     * Sorts dates in the selected array.
     */
    this.sortDatesSelected = function() {
        selectedDatesNew.sort(function(a, b) {
            var dateA = new Date(a.date).getTime();
            var dateB = new Date(b.date).getTime();
            return dateA > dateB ? 1 : - 1;
        });
//        console.log(selectedDatesNew);
    }

    this.selectResult = function(item) {
        //    	timeOffCreateRequestHandler.loadCalendars(item.value);
    }

    /**
     * Changes to dropdown to select another employee
     */
    this.setAsRequestForAnother = function() {
        $('.requestIsForMe').hide();
        $('.requestIsForAnother').show();
        $('.requestIsForAnother').focus();
    }

    /**
     * Appends a red circle
     */
    this.requestForAnotherComplete = function() {
        $(".requestIsForAnother").append('<span class="categoryCloseIcon glyphicon glyphicon-remove-circle red"></span>');
    }

    /**
     * Capitalizes the First Letter
     */
    this.capitalizeFirstLetter = function(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    this.log = function(name, evt) {
        if (!evt) {
            var args = "{}";
        } else {
            var args = JSON.stringify(evt.params, function(key, value) {
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
        $e.animate({
            opacity : 1
        }, 10000, 'linear', function() {
            $e.animate({
                opacity : 0
            }, 2000, 'linear', function() {
                $e.remove();
            });
        });
    }

    this.checkAllowRequestOnBehalfOf = function() {
        if (loggedInUserData.IS_LOGGED_IN_USER_MANAGER === "Y" || loggedInUserData.IS_LOGGED_IN_USER_PAYROLL === "Y") {
            console.log('1132!!!');
        timeOffCreateRequestHandler.enableSelectRequestFor();
        $("#requestFor").prop('disabled', false);
        } else {
            $("#requestFor").prop('disabled', true);
            $(".categoryBereavement").hide();
            $(".categoryCivicDuty").hide();
            $(".categoryApprovedNoPay").hide();
        }
    }

    this.enableSelectRequestFor = function() {
        var $eventLog = $(".js-event-log");
        var $requestForEventSelect = $("#requestFor");
        $("#requestFor").select2({
            ajax : {
                url : timeOffEmployeeSearchUrl,
                method : 'post',
                dataType : 'json',
                delay : 250,
                data : function(params) {
                    return {
                    search : params.term,
                            directReportFilter : directReportFilter,
                            employeeNumber : phpVars.employee_number,
                            page : params.page
                    };
                },
                processResults : function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results : data,
                        pagination : {
                        more : (params.page * 30) < data.total_count
                        }
                    };
                },
            },
            allowClear : true,
            minimumInputLength : 2,
        });
    }

    /**
     * Mask the request calendar so user can not pick dates or scroll
     * to a different month.
     */
    this.maskCalendars = function(action) {
        if (!action || action === 'show') {
        $('body').append(
            '<link href="'
            + phpVars.basePath
            + '/css/timeOffCalendarEnable.css" rel="stylesheet" id="enableTimeOffCalendar" />');
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
        for (categoryClass in categoryText) {
            $(".selectTimeOffCategory").removeClass(categoryClass);
        }
        timeOffCreateRequestHandler.maskCalendars('hide');
    }

    /**
     * Select category
     * 
     * @param {type} categoryButton
     * @returns {undefined}
     */
    this.selectCategory = function(categoryButton) {
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

    this.unselectCategory = function() {
        $(".selectTimeOffCategory")
            .removeClass("categorySelected")
            .removeClass("categoryPTO")
            .removeClass("timeOffPTO")
            .removeClass("categoryFloat")
            .removeClass("timeOffFloat")
            .removeClass("categorySick")
            .removeClass("timeOffSick")
            .removeClass("categoryGrandfathered")
            .removeClass("timeOffyGrandfathered")
            .removeClass("categoryBereavement")
            .removeClass("timeOffBereavement")
            .removeClass("categoryCivicDuty")
            .removeClass("timeOffCivicDuty")
            .removeClass("categoryUnexcusedAbsence")
            .removeClass("timeOffUnexcusedAbsence")
            .removeClass("categoryApprovedNoPay")
            .removeClass("timeOffApprovedNoPay");
        for (category in categoryText) {
//            console.log('.' + category + 'CloseIcon');
            $('.' + category + 'CloseIcon').removeClass('categoryCloseIcon glyphicon glyphicon-remove-circle');
            $('.buttonDisappear' + category.substr(7)).show();
        }

        selectedTimeoffCategory = null;
        timeOffCreateRequestHandler.maskCalendars('hide');
        timeOffCreateRequestHandler.setStep('1');
    }

    /**
     * Splits a date
     */
    this.splitDateRequested = function(dateRequestObject) {
        var selectedDate = timeOffCreateRequestHandler .isSelected(dateRequestObject);
        if (selectedTimeoffCategory != null) {
            /**
             * Let's find exact keys where the date is the date selected
             */
            allowSplitDate = timeOffCreateRequestHandler.allowSplitDate(selectedDate);
            //console.log("allowSplitDate", allowSplitDate);
            if (allowSplitDate.allowSplitDate === true) {
                var item = allowSplitDate.items[0];
                var index = item.index;
                /** Update to number of split hours **/
                selectedDatesNew[index].hours = 4;
                /** Add back the split hours to the selected category **/
                timeOffCreateRequestHandler.subtractTime(selectedDatesNew[index].category, 4);
                /**
                 * Add the date to the request object
                 */
                var obj = {
                    date : item.date,
                    hours : 4,
                    category : selectedTimeoffCategory
                };
                selectedDatesNew.push(obj);
                timeOffCreateRequestHandler.addTime(selectedTimeoffCategory, 4);
                timeOffCreateRequestHandler.drawHoursRequested();
                timeOffCreateRequestHandler.sortDatesSelected();
            }
        }
    }

    this.alertUserToTakeGrandfatheredTime = function() {
        $("#dialogGrandfatheredAlert").dialog({
            modal : true,
            buttons : {
                Ok : function() {
                    $(this).dialog("close");
                }
            }
        });
    }

    this.allowSplitDate = function(selectedDate) {
        var allowSplitDate = false;
        items = [];
        $.each(selectedDatesNew, function(index, object) {
            if (object.date === selectedDate.obj.date) {
                object.index = index;
                items.push(object);
            }
        });
        if ((items.length === 1 && selectedTimeoffCategory === "timeOffFloat")
            || (items.length === 0) || (items.length > 1)) {
            allowSplitDate = false;
        }
        if (items.length === 1 && selectedTimeoffCategory != "timeOffFloat") {
            allowSplitDate = true;
        }

        return {
            allowSplitDate : allowSplitDate,
            items : items
        };
    }

    /**
     * Clears out the selected dates and refreshes the form.
     * @returns {undefined}     */
    this.clearSelectedDates = function() {
        selectedDatesNew = [];
        timeOffCreateRequestHandler.drawHoursRequested();
    }

    this.updateRequestDates = function(object, calendarDateObject) {
        var found = false;
        var copy = null;
        var newOne = null;
        var deleteKey = null;
        $.each(selectedDatesNew, function(key, dateObject) {
        if (object.date == dateObject.date
                && object.category === dateObject.category) {
        found = true;
                deleteKey = key;
        }
        if (object.date == dateObject.date
                && object.category != dateObject.category
                && found === false) {
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
    this.addDataToRequest = function(calendarDateObject, object) {
        object = timeOffCreateRequestHandler.formatDayRequested(object);
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
    this.formatDayRequested = function(object) {
        object.dow = moment(object.date, "MM/DD/YYYY").format("ddd").toUpperCase();
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
    this.deleteDataFromRequest = function(calendarDateObject, deleteKey, object) {
        console.log( 'a', calendarDateObject );
        console.log( 'deleteKey', deleteKey );
        console.log( 'object', object );
        
        timeOffCreateRequestHandler.subtractTime( selectedDatesNew[deleteKey].category,
                                                  Number( selectedDatesNew[deleteKey].hours ) );
        timeOffCreateRequestHandler.removeDateFromRequest( deleteKey );
        timeOffCreateRequestHandler.highlightDates();
        timeOffCreateRequestHandler.drawHoursRequested();
        
//        timeOffCreateRequestHandler.subtractTime(selectedDatesNew[deleteKey].category, Number(selectedDatesNew[deleteKey].hours));
//        timeOffCreateRequestHandler.removeDateFromRequest(deleteKey);
////        calendarDateObject.removeClass(object.category + "Selected");
//        $.each($('.calendar-day'), function(index, obj) {
//            if ($(this).data("date") === calendarDateObject.data("date")) {
//                $(this).removeClass("timeOffPTOSelected");
//            }
//        });
    }

    this.splitDataFromRequest = function(calendarDateObject, deleteKey, copy, newOne) {
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
//Initialize the class
timeOffCreateRequestHandler.initialize();