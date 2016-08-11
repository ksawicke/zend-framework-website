/**
 * Javascript timeOffCreateRequestHandler 'class'
 *
 */
var timeOffCreateRequestHandler = new function() {
    var timeOffLoadCalendarUrl = phpVars.basePath + '/api/calendar/get', // http://swift:10080/sawik/timeoff/public
        timeOffSubmitTimeOffRequestUrl = phpVars.basePath + '/api/request',
        timeOffSubmitTimeOffSuccessUrl = phpVars.basePath + '/request/submitted-for-approval',
        timeOffEmployeeSearchUrl = phpVars.basePath + '/api/search/employees',
        settingsDisableHoursInputFields = false,
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
        selectedTimeOffCategory = null,
        loggedInUserData = [],
        requestForEmployeeNumber = '',
        requestForEmployeeName = '',
        requestForEmployeeObject = [],
        requestReason = '',
        /** Dates selected for this request **/

        selectedDatesNewHoursByDate = [],
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
        directReportFilter = 'B',
        doRealDelete = true;
      
   /**
    * Handles what happens when you choose another employee in the For: field.
    * 
    * @param {type} event
    * @returns {undefined}
    */ 
   this.handleSelectRequestForChoice = function( event ) {
       var selectedEmployee = event.params.data,
           requestForEmployeeNumber = selectedEmployee.id,
           requestForEmployeeName = selectedEmployee.text;
       timeOffCreateRequestHandler.resetCategorySelection();
       timeOffCreateRequestHandler.loadCalendars(requestForEmployeeNumber, 3);
       $('.requestIsForMe').show();
   }     
        
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
                timeOffCreateRequestHandler.handleSelectRequestForChoice(e);
            }).on("select2:open", function(e) {
                /**
                 * SELECT2 is opened
                 */
                // loggedInUserData.IS_LOGGED_IN_USER_PROXY === "Y"
                if ( ( ( loggedInUserData.isManager == "Y" || loggedInUserData.isSupervisor == "Y" ) &&
                       loggedInUserData.isPayrollAdmin == "N" &&
                       loggedInUserData.isPayrollAssistant == "N" ) ||
                       loggedInUserData.isProxy == "Y"
                   ) {
                    /**
                     * Allow user to search their reports (for Managers/Supervisors) and/or
                     * employees for which they are a proxy.
                     */
                    $("span").remove(".select2CustomTag");
                    var $filter = '<form id="directReportForm" style="display:inline-block;padding 5px;">';
                }
                if( ( loggedInUserData.isManager == "Y" || loggedInUserData.isSupervisor == "Y" ) && loggedInUserData.isPayrollAdmin == "N" ) {
                    $filter += '<input type="radio" name="directReportFilter" value="B"'
                        + ((directReportFilter == 'B') ? ' checked'
                            : '')
                        + '> Both&nbsp;&nbsp;&nbsp;'
                        + '<input type="radio" name="directReportFilter" value="D"'
                        + ((directReportFilter == 'D') ? ' checked'
                            : '')
                        + '> Direct Reports&nbsp;&nbsp;&nbsp;'
                        + '<input type="radio" name="directReportFilter" value="I"'
                        + ((directReportFilter == 'I') ? ' checked'
                            : '')
                        + '> Indirect Reports&nbsp;&nbsp;&nbsp;';
                }
                if( loggedInUserData.isProxy == "Y" ) {
                    if( loggedInUserData.isManager == "N" &&
                        loggedInUserData.isSupervisor == "N" && 
                        loggedInUserData.isPayrollAdmin == "N" &&
                        loggedInUserData.isPayrollAssistant == "N" ) {
                        directReportFilter = 'P';
                    }
                    $filter += '<input type="radio" name="directReportFilter" value="P"'
                        + ((directReportFilter == 'P' ) ? ' checked'
                            : '')
                        + '> Employees For Whom I Am Authorized to Submit Requests';
                }
                if ( ( ( loggedInUserData.isManager == "Y" || loggedInUserData.isSupervisor ) &&
                       loggedInUserData.isPayrollAdmin == "N" &&
                       loggedInUserData.isPayrollAssistant == "N" ) ||
                       loggedInUserData.isProxy == "Y"
                   ) {
                        $filter += '</form>';
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
            //
            timeOffCreateRequestHandler.handleCalendarNavigation();
            timeOffCreateRequestHandler.handleToggleLegend();
            timeOffCreateRequestHandler.handleClickCategory();
            timeOffCreateRequestHandler.handleClickCalendarDate();
            timeOffCreateRequestHandler.handleRemoveDateFromRequest();
            timeOffCreateRequestHandler.handleChangeHoursForDateManually();
            timeOffCreateRequestHandler.verifyNewRequest();
            timeOffCreateRequestHandler.handleNewRequestFormIsUpdated();
            timeOffCreateRequestHandler.handleChangeRequestForEmployee();
            timeOffCreateRequestHandler.handleDirectReportToggle();
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
    
    this.checkAndSetFormWarnings = function() {
    	var hoursWarningBlock = ( requestForEmployeeObject.SALARY_TYPE=='S' ?
                '#warnSalaryTakingRequiredHoursPerDay' :
                '#warnHourlyTakingRequiredHoursPerDay' );

    	timeOffCreateRequestHandler.updateHours();
    	exceededHours = timeOffCreateRequestHandler.verifyExceededHours();
    	
    	if( exceededHours.Grandfathered ) {
    		$("#warnExceededGrandfatheredHours").show();
    	} else {
    		$("#warnExceededGrandfatheredHours").hide();
    	}
    	
    	if( exceededHours.Sick ) {
    		$("#warnExceededSickHours").show();
    	} else {
    		$("#warnExceededSickHours").hide();
    	}
    	
		if( timeOffCreateRequestHandler.verifyBereavementRequestLimitReached()==true ) {
			$( "#warnBereavementHoursPerRequest" ).show();
		} else {
			$( "#warnBereavementHoursPerRequest" ).hide();
		}
		if( timeOffCreateRequestHandler.verifySalaryTakingRequiredHoursPerDay()==false ) {
			$( hoursWarningBlock ).show();
		} else {
			$( hoursWarningBlock ).hide();
		}
		
//		if( validates==false || bereavementTotalForRequest > 24 ) {
//        	$('.submitTimeOffRequest').addClass('disabled');
//        } else {
//        	$('.submitTimeOffRequest').removeClass('disabled');
//        }
    }
    
    
    
    this.handleNewRequestFormIsUpdated = function() {
    	$('#newTimeOffRequestForm').on('change', function() {
    		timeOffCreateRequestHandler.checkAndSetFormWarnings();
    	});
    }

    /**
     * Submit time off request
     */
    this.verifyNewRequest = function() {
        $(document).on('click', '.submitTimeOffRequest', function() {
            if( timeOffCreateRequestHandler.verifyBereavementRequestLimitReached()===false &&
                timeOffCreateRequestHandler.verifySalaryTakingRequiredHoursPerDay()===true ) {
                requestReason = $("#requestReason").val();
                timeOffCreateRequestHandler.handlePleaseWaitStatus( $(this) );
                timeOffCreateRequestHandler.submitTimeOffRequest();
            }
        });
    }
    
    /**
     * Handles showing the user the API action is being processed.
     * 
     * @param {type} selectedButton
     * @returns {undefined}
     */
    this.handlePleaseWaitStatus = function( selectedButton ) {
        $( '.btn' ).addClass( 'disabled' ); // Disable all buttons from being selected first.
        //selectedButton.addClass( 'disabled' );
        selectedButton.blur(); // Click out of button.
        
        // Add a spinning icon and a couple of spaces before the button text.
        selectedButton.prepend( '<i class="glyphicon glyphicon-refresh gly-spin"></i>&nbsp;&nbsp;' );
    }
    
    this.getBereavementHoursRequested = function() {
    	var bereavementTotalForRequest = 0;
        $.each( selectedDatesNew, function( index, selectedDateNewObject ) {
            if( selectedDateNewObject.category=="timeOffBereavement" ) {
                bereavementTotalForRequest += +selectedDateNewObject.hours;
            }
        });
        return bereavementTotalForRequest;
    }
    
    this.verifyBereavementRequestLimitReached = function() {
        var validates = false;
        bereavementTotalForRequest = timeOffCreateRequestHandler.getBereavementHoursRequested();
        
        if( bereavementTotalForRequest >= 24 ) {
            validates = true;
            if( bereavementTotalForRequest > 24 ||
                timeOffCreateRequestHandler.verifySalaryTakingRequiredHoursPerDay()===false ) {
            	$('.submitTimeOffRequest').addClass('disabled');
            } else {
            	$('.submitTimeOffRequest').removeClass('disabled');
            }
        }
        
        return validates;
    }
    
    /**
     * Verify that no single day has less than 8 hours requested if the employee is Salary.
     * Also verify that the hours requested match the employee's schedule.
     */
    this.verifySalaryTakingRequiredHoursPerDay = function() {
        var validates = true,
            selectedDatesNewHoursByDate = [];
        
        $.each( selectedDatesNew, function( index, selectedDateNewObject ) {
            if( !selectedDatesNewHoursByDate.hasOwnProperty(selectedDateNewObject.date) ) {
                selectedDatesNewHoursByDate[selectedDateNewObject.date] = +selectedDateNewObject.hours;
            } else {
                selectedDatesNewHoursByDate[selectedDateNewObject.date] += +selectedDateNewObject.hours;
            }
        });
        $.each( selectedDatesNew, function( index, selectedDateNewObject ) {
        	var hoursOff = selectedDatesNewHoursByDate[selectedDateNewObject.date];
            if( requestForEmployeeObject.SALARY_TYPE=='S' && validates ) {
            	validates = ( hoursOff >= 8 && hoursOff <= 12 ? true : false );
            }
        });
                
        return validates;
    }
    
    /**
     * Verifies if user exceeded PTO time.
     */
    this.verifyExceededPTOHours = function() {
    	var validates = false;
    	if( parseFloat(totalPTORequested).toFixed(2) > parseFloat(requestForEmployeeObject.PTO_REMAINING).toFixed(2) ) {
    		validates = true;
    	}
    	return validates;
    }
    
    /**
     * Verifies if user exceeded Float time.
     */
    this.verifyExceededFloatHours = function() {
    	var validates = false;
    	if( parseFloat(totalFloatRequested).toFixed(2) > parseFloat(requestForEmployeeObject.FLOAT_REMAINING).toFixed(2) ) {
    		validates = true;
    	}
    	return validates;
    }
    
    /**
     * Verifies if user exceeded Sick time.
     */
    this.verifyExceededSickHours = function() {
    	var validates = false;
    	if( parseFloat(totalSickRequested).toFixed(2) > parseFloat(requestForEmployeeObject.SICK_REMAINING).toFixed(2) ) {
    		validates = true;
    	}
    	return validates;
    }
    
    /**
     * Verifies if user exceeded Grandfathered time.
     */
    this.verifyExceededGrandfatheredHours = function() {
    	var validates = false;
    	if( parseFloat(totalGrandfatheredRequested).toFixed(2) > parseFloat(requestForEmployeeObject.GF_REMAINING).toFixed(2) ) {
    		validates = true;
    	}
    	return validates;
    }
    
    /**
     * Updates the hour totals for request.
     */
    this.updateHours = function() {
    	var validates = false;
    	timeOffCreateRequestHandler.updateTotalsPerCategory();
    	var data = { GF_REMAINING: parseFloat(requestForEmployeeObject.GF_REMAINING - totalGrandfatheredRequested).toFixed(2),
    				 PTO_REMAINING: parseFloat(requestForEmployeeObject.PTO_REMAINING - totalPTORequested).toFixed(2),
    				 FLOAT_REMAINING: parseFloat(requestForEmployeeObject.FLOAT_REMAINING - totalFloatRequested).toFixed(2),
    			     SICK_REMAINING: parseFloat(requestForEmployeeObject.SICK_REMAINING - totalSickRequested).toFixed(2) };
    	timeOffCreateRequestHandler.updateButtonsWithEmployeeRemainingTime( data );
    }
    
    /**
     * Verifies if user exceeds hours in 4 categories for request.
     */
    this.verifyExceededHours = function() {
    	var validates = false;
    	validatesPTO = timeOffCreateRequestHandler.verifyExceededPTOHours();
    	validatesFloat = timeOffCreateRequestHandler.verifyExceededFloatHours();
    	validatesSick = timeOffCreateRequestHandler.verifyExceededSickHours();
    	validatesGrandfathered = timeOffCreateRequestHandler.verifyExceededGrandfatheredHours();

    	return { PTO: validatesPTO, Float: validatesFloat, Sick: validatesSick, Grandfathered: validatesGrandfathered };
    }

    /**
     * Handle user changing the hours for a date manually
     */
    this.handleChangeHoursForDateManually = function() {
        $(document).on('blur', '.selectedDateHours', function() { 
            var key = $(this).attr("data-key");
            var value = $(this).val();
            
            selectedDatesNew[key].hours = value;
            selectedDatesNew[key].fieldDirty = true;
            $("#formDirty").val('true');
            // Recalculate totals
            timeOffCreateRequestHandler.updateTotalsPerCategory(); // taco
//            totalPTORequested = 0;
//        	totalFloatRequested = 0;
//        	totalSickRequested = 0;
//        	totalUnexcusedAbsenceRequested = 0;
//        	totalBereavementRequested = 0;
//        	totalCivicDutyRequested = 0;
//        	totalGrandfatheredRequested = 0;
//        	totalApprovedNoPayRequested = 0;
            timeOffCreateRequestHandler.checkAndSetFormWarnings();
        });
    }

    /**
     * Handle removing a date from request
     */
    this.handleRemoveDateFromRequest = function() {
        $(document).on('click', '.remove-date-requested', function() {
            var deleteKey = $(this).attr('data-selecteddatesnew-key'),
                category = $(this).attr('data-categroy'),
                method = timeOffCreateRequestHandler.getMethodToModifyDates(),
                isSelected = timeOffCreateRequestHandler.isSelected( $(this) );
            timeOffCreateRequestHandler.deleteRequestedDateByIndex( deleteKey, category );
            timeOffCreateRequestHandler.adjustRemainingDate( method, isSelected );
            timeOffCreateRequestHandler.sortDatesSelected();
            timeOffCreateRequestHandler.drawHoursRequested();
            timeOffCreateRequestHandler.checkAndSetFormWarnings();
        });
    }
    
    this.getMethodToModifyDates = function() {
        var isHandledFromReviewRequestScreen = timeOffCreateRequestHandler.isHandledFromReviewRequestScreen();
        return ( isHandledFromReviewRequestScreen ? 'mark' : 'do' );
    }

    this.confirmIfUserWantsToRequestOffCompanyHoliday = function() {
        $("#dialogConfirmSelectHoliday").dialog({
            modal : true,
            closeOnEscape: false,
            buttons : {
                Yes : function() {
                    $(this).dialog("close");
                    timeOffCreateRequestHandler.markDayAsRequestedOff( selectedTimeOffCategory, selectedCalendarDateObject );
                },
                No : function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    
    /**
     * Handle clicking on a calendar date.
     * 
     * @returns {undefined}
     */
    this.handleClickCalendarDate = function() {
        $(document).on('click', '.calendar-day', function() {
            var selectedCalendarDateObject = $(this),
                isCompanyHoliday = timeOffCreateRequestHandler.isCompanyHoliday( $(this) ),
                method = timeOffCreateRequestHandler.getMethodToModifyDates(),
                selectedDate = selectedCalendarDateObject.data("date"),
                isSelected = timeOffCreateRequestHandler.isSelected( $(this) ),
                dateObject = isSelected.dateObject,
                isDateDisabled = timeOffCreateRequestHandler.isDateDisabled( $(this) ),
                foundIndex = timeOffCreateRequestHandler.datesAlreadyInRequestArray( dateObject );
            if( timeOffCommon.empty( selectedTimeOffCategory ) ) {
            	return;
            }
//            if( employeeGrandfatheredRemaining <= 0 ) {
//            	alert( "Grandfathered time has been used up." );
//            }
//            if( employeeSickRemaining <= 0 ) {
//            	alert( "Sick Time has been used up." );
//            }
            if( selectedTimeOffCategory=="timeOffGrandfathered" && employeeGrandfatheredRemaining <= 0 ) {
            	return;
            }
            if( selectedTimeOffCategory=="timeOffSick" && employeeSickRemaining <= 0 ) {
            	return;
            }
            if( isCompanyHoliday ) {
                timeOffCreateRequestHandler.confirmIfUserWantsToRequestOffCompanyHoliday();
            } else {
                if( foundIndex!==null && selectedDatesNew[foundIndex].category!=selectedTimeOffCategory ) {
                    timeOffCreateRequestHandler.splitRequestedDate( method, isSelected, foundIndex );
                } else if( isSelected.isSelected === true && typeof isSelected.isSelected==='boolean' ) {
                    timeOffCreateRequestHandler.removeRequestedDate( method, isSelected );
                    timeOffCreateRequestHandler.adjustRemainingDate( method, isSelected );
                    timeOffCreateRequestHandler.toggleDateCategorySelection( selectedDate );
                } else {
                    timeOffCreateRequestHandler.addRequestedDate( method, isSelected );
                    timeOffCreateRequestHandler.toggleDateCategorySelection( selectedDate );
                }
                if( $('#formDirty').val()=="false" ) {
                    $('#formDirty').val('true'); // This method allows us to see if form was edited.
                }
            }
            timeOffCreateRequestHandler.sortDatesSelected();
            timeOffCreateRequestHandler.drawHoursRequested();
            timeOffCreateRequestHandler.checkAndSetFormWarnings();
        });
    }
    
    this.isCompanyHoliday = function( selectedCalendarDateObject ) {
        return ( selectedCalendarDateObject.hasClass("calendar-day-holiday") ? true : false );
    }
    
    this.isHandledFromReviewRequestScreen = function() {
        return ( typeof timeOffApproveRequestHandler==="object" ? true : false );
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
            if( phpVars.request_id != 0 ) {
                timeOffCreateRequestHandler.loadNewCalendars($(this).attr("data-month"), $(this).attr("data-year"), 1, phpVars.request_id);
            } else {
                timeOffCreateRequestHandler.loadNewCalendars($(this).attr("data-month"), $(this).attr("data-year"), 3);
            }
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
        $('.btn-requestCategory').removeClass(selectedTimeOffCategory);
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
        if (selectedTimeOffCategory == object.attr("data-category")) {
            object.removeClass(object.attr("data-category"));
            selectedTimeOffCategory = null;
            object.removeClass("categorySelected");
            $('.' + object.attr("data-category") + 'CloseIcon').removeClass('categoryCloseIcon glyphicon glyphicon-remove-circle');
            timeOffCreateRequestHandler.setStep('1');
        } else {
            selectedTimeOffCategory = object.attr("data-category");
            object.addClass("categorySelected");
            object.addClass(selectedTimeOffCategory);
            if (selectedDatesNew.length > 0) {
                timeOffCreateRequestHandler.setStep('3');
            } else {
                timeOffCreateRequestHandler.setStep('2');
            }
            $('.' + selectedTimeOffCategory + 'CloseIcon').addClass('categoryCloseIcon glyphicon glyphicon-remove-circle');
        }
    }

    /**
     * Loads calendars via ajax and displays them on the page.
     */
    /**
     * Loads calendars via ajax and displays them on the page.
     */
    this.loadCalendars = function(employeeNumber, calendarsToLoad, request_id) {
    	var month = (new Date()).getMonth() + 1,
        	year = (new Date()).getFullYear();
        timeOffCreateRequestHandler.clearSelectedDates();
        $.ajax({
            url : timeOffLoadCalendarUrl,
            type : 'POST',
            data: {
            //                action: 'loadCalendars',
                startMonth : month,
                startYear : year,
                employeeNumber : ((typeof employeeNumber === "string") ? employeeNumber : phpVars.employee_number),
                calendarsToLoad: calendarsToLoad,
                requestId: request_id
            },
            dataType : 'json'
        })
        .success(function(json) {
            if (requestForEmployeeNumber === '') {
                loggedInUserData = json.employeeData;
                loggedInUserData.isManager = json.loggedInUserData.isManager;
                loggedInUserData.isSupervisor = json.loggedInUserData.isSupervisor;
                loggedInUserData.isPayroll = json.loggedInUserData.isPayroll;
                loggedInUserData.isPayrollAdmin = json.loggedInUserData.isPayrollAdmin;
                loggedInUserData.isPayrollAssistant = json.loggedInUserData.isPayrollAssistant;
                loggedInUserData.isProxy = json.loggedInUserData.isProxy;
                loggedInUserData.PROXY_FOR = [];
                if( json.loggedInUserData.isProxy==="Y" ) {
                    for( key in json.proxyFor ) {
                        loggedInUserData.PROXY_FOR.push( json.proxyFor[key].EMPLOYEE_NUMBER );
                    }
                }
            }

            requestForEmployeeNumber = json.employeeData.EMPLOYEE_NUMBER;
            requestForEmployeeObject = json.employeeData;
            if( calendarsToLoad===1 ) {
                timeOffCreateRequestHandler.drawOneCalendar(json.calendarData);
            }
            if( calendarsToLoad===3 ) {
                timeOffCreateRequestHandler.drawThreeCalendars(json.calendarData);
            }
            timeOffCreateRequestHandler.updateButtonsWithEmployeeHours(json.employeeData);
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
                
            timeOffCreateRequestHandler.updateEmployeeSchedule( requestForEmployeeObject );  
            
            /**
             * Allow manager to edit request
             */
            if( calendarsToLoad===1 ) {
                $("#datesSelectedDetails").html("");
                timeOffCreateRequestHandler.addLoadedDatesAsSelected( json.calendarData.highlightDates, request_id );
                timeOffCreateRequestHandler.drawHoursRequested( 'update-', json.calendarData.highlightDates );
            }
            timeOffCreateRequestHandler.postLoadCalendarButtonAdjust( requestForEmployeeObject );
            return;
        }).error(function() {
            console.log('There was some error.');
            return;
        });
    }
    
    /**
     * Decide if we want to hide any category buttons per business rules.
     */
    this.postLoadCalendarButtonAdjust = function( requestForEmployeeObject ) {
    	if( parseFloat(requestForEmployeeObject.GF_REMAINING).toFixed(2) <= 0 ) {
    		$(".buttonDisappearGrandfathered").addClass( "hidden" );
    	} else {
    		$(".buttonDisappearGrandfathered").removeClass( "hidden" );
    	}
    	if( parseFloat(requestForEmployeeObject.SICK_REMAINING).toFixed(2) <= 0 ) {
    		$(".buttonDisappearSick").addClass( "hidden" );
    	} else {
    		$(".buttonDisappearSick").removeClass( "hidden" );
    	}
    }
    
    /**
     * This will add dates as selected so manager can edit the request.
     * 
     * @param {type} highlightDates
     * @returns {undefined}
     */
    this.addLoadedDatesAsSelected = function( highlightDates, request_id ) {
        for (key in highlightDates) {
            var obj = {
                date : highlightDates[key].REQUEST_DATE,
                hours : highlightDates[key].REQUESTED_HOURS,
                category : highlightDates[key].CALENDAR_DAY_CLASS,
                requestId: request_id,
                entryId : highlightDates[key].ENTRY_ID,
                fieldDirty: false
            };
            selectedDatesNew.push(obj);
        }
    }
    
    /**
     * Appends the loaded schedule to the update form.
     * 
     * @param {type} data
     * @returns {undefined}
     */
    this.updateEmployeeSchedule = function( data ) {
        $("#scheduleSUN").html( data.SCHEDULE_SUN );
        $("#scheduleMON").html( data.SCHEDULE_MON );
        $("#scheduleTUE").html( data.SCHEDULE_TUE );
        $("#scheduleWED").html( data.SCHEDULE_WED );
        $("#scheduleTHU").html( data.SCHEDULE_THU );
        $("#scheduleFRI").html( data.SCHEDULE_FRI );
        $("#scheduleSAT").html( data.SCHEDULE_SAT );
        
        $("#employeeScheduleSUN").val( data.SCHEDULE_SUN );
        $("#employeeScheduleMON").val( data.SCHEDULE_MON );
        $("#employeeScheduleTUE").val( data.SCHEDULE_TUE );
        $("#employeeScheduleWED").val( data.SCHEDULE_WED );
        $("#employeeScheduleTHU").val( data.SCHEDULE_THU );
        $("#employeeScheduleFRI").val( data.SCHEDULE_FRI );
        $("#employeeScheduleSAT").val( data.SCHEDULE_SAT );
        
        $("#employeeScheduleFor").val( data.EMPLOYEE_NUMBER );
        $("#employeeScheduleBy").val( requestForEmployeeNumber );
    }

//    var data = {};
//    data.GF_REMAINING
    
    this.updateButtonsWithEmployeeRemainingTime = function( data ) {
    	timeOffCreateRequestHandler.setEmployeeGrandfatheredRemaining( data.GF_REMAINING );
    	timeOffCreateRequestHandler.printEmployeeGrandfatheredRemaining();
    	
    	timeOffCreateRequestHandler.setEmployeePTORemaining( data.PTO_REMAINING );
    	timeOffCreateRequestHandler.printEmployeePTORemaining();
    	timeOffCreateRequestHandler.warnExceededPTORemaining();
    	
    	timeOffCreateRequestHandler.setEmployeeFloatRemaining( data.FLOAT_REMAINING );
        timeOffCreateRequestHandler.printEmployeeFloatRemaining();
        timeOffCreateRequestHandler.warnExceededFloatRemaining();
        
        timeOffCreateRequestHandler.setEmployeeSickRemaining( data.SICK_REMAINING );
        timeOffCreateRequestHandler.printEmployeeSickRemaining();
    }
    
    this.updateButtonsWithEmployeePendingTime = function( data ) {
    	timeOffCreateRequestHandler.setEmployeeGrandfatheredPending( data.GF_PENDING_TOTAL );
        timeOffCreateRequestHandler.printEmployeeGrandfatheredPending();
        
        timeOffCreateRequestHandler.setEmployeePTOPending( data.PTO_PENDING_TOTAL );
        timeOffCreateRequestHandler.printEmployeePTOPending();
        
        timeOffCreateRequestHandler.setEmployeeFloatPending( data.FLOAT_PENDING_TOTAL );
        timeOffCreateRequestHandler.printEmployeeFloatPending();
        
        timeOffCreateRequestHandler.setEmployeeSickPending( data.SICK_PENDING_TOTAL );
        timeOffCreateRequestHandler.printEmployeeSickPending();
        
        timeOffCreateRequestHandler.setEmployeeUnexcusedAbsencePending( data.UNEXCUSED_PENDING_TOTAL );
        timeOffCreateRequestHandler.printEmployeeUnexcusedAbsencePending();
        
        timeOffCreateRequestHandler.setEmployeeBereavementPending( data.BEREAVEMENT_PENDING_TOTAL );
        timeOffCreateRequestHandler.printEmployeeBereavementPending();
        
        timeOffCreateRequestHandler.setEmployeeCivicDutyPending( data.CIVIC_DUTY_PENDING_TOTAL );
        timeOffCreateRequestHandler.printEmployeeCivicDutyPending();
        
        timeOffCreateRequestHandler.setEmployeeApprovedNoPayPending( data.UNPAID_PENDING_TOTAL );
        timeOffCreateRequestHandler.printEmployeeApprovedNoPayPending();
    }
    
    /**
     * Update buttons with employee hours.
     */
    this.updateButtonsWithEmployeeHours = function( data ) {
    	timeOffCreateRequestHandler.updateButtonsWithEmployeeRemainingTime( data );
    	timeOffCreateRequestHandler.updateButtonsWithEmployeePendingTime( data );
    }

    /**
     * Draws the one calendar loaded
     */
    this.drawOneCalendar = function(calendarData) {
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
        
        /** Draw calendars **/
        $("#calendar1Body").html(calendarData.calendars[1]);
        
//        timeOffCreateRequestHandler.highlightDates();
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
        var requestData = { request : {
                forEmployee : { EMPLOYEE_NUMBER : requestForEmployeeNumber },
                byEmployee : loggedInUserData,
                dates : selectedDatesNew,
                reason : requestReason
            } };
//        console.log( "requestData", requestData );
        $.ajax({
            url : timeOffSubmitTimeOffRequestUrl,
            type : 'POST',
            data : requestData,
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
    this.loadNewCalendars = function(startMonth, startYear, calendarsToLoad, request_id) {
        $.ajax({
            url : timeOffLoadCalendarUrl,
            type : 'POST',
            data : {
                action : 'loadCalendar',
                startMonth : startMonth,
                startYear : startYear,
                employeeNumber : requestForEmployeeNumber,
                calendarsToLoad : calendarsToLoad,
                requestId: request_id,
                appendDatesAsHighlighted: selectedDatesNew
            },
            dataType : 'json'
        }).success(function(json) {
            requestForEmployeeObject = json.employeeData;
            if( calendarsToLoad==1 ) {
                timeOffCreateRequestHandler.drawOneCalendar(json.calendarData);
            }
            if( calendarsToLoad==3 ) {
                timeOffCreateRequestHandler.drawThreeCalendars(json.calendarData);
            }
            
            timeOffCreateRequestHandler.updateEmployeeSchedule( requestForEmployeeObject );
            
            /**
             * Allow manager to edit request
             */
            if( calendarsToLoad==1 ) {
//                console.log( "selectedDatesNew &&", selectedDatesNew );
//                console.log( "calendarData &&", json.calendarData );
                
//                timeOffCreateRequestHandler.addLoadedDatesAsSelected( json.calendarData.highlightDates );
            }
            return;
        }).error(function() {
            console.log('There was some error.');
            return;
        });
    }
    
    this.getLoggedInUserEmployeeNumber = function() {
        return phpVars.employee_number;
    }

    /**
     * Prints the Remaining PTO time for selected employee.
     */
    this.setEmployeePTORemaining = function(ptoRemaining) {
    	employeePTORemaining = parseFloat(ptoRemaining).toFixed(2);
    }

    /**
     * Prints the Pending PTO time for selected employee.
     */
    this.setEmployeePTOPending = function(ptoPending) {
        employeePTOPending = parseFloat(ptoPending).toFixed(2);
    }

    /**
     * Sets the Remaining Float time for selected employee.
     */
    this.setEmployeeFloatRemaining = function(floatRemaining) {
        employeeFloatRemaining = parseFloat(floatRemaining).toFixed(2);
    }

    /**
     * Sets the Pending Float time for selected employee.
     */
    this.setEmployeeFloatPending = function(floatPending) {
        employeeFloatPending = parseFloat(floatPending).toFixed(2);
    }

    /**
     * Sets the Remaining Sick time for selected employee.
     */
    this.setEmployeeSickRemaining = function(sickRemaining) {
        employeeSickRemaining = parseFloat(sickRemaining).toFixed(2);
    }

    /**
     * Sets the Pending Sick time for selected employee.
     */
    this.setEmployeeSickPending = function(sickPending) {
        employeeSickPending = parseFloat(sickPending).toFixed(2);
    }

    /**
     * Sets the Remaining Grandfathered time for selected employee.
     */
    this.setEmployeeGrandfatheredRemaining = function(grandfatheredRemaining) {
        employeeGrandfatheredRemaining = parseFloat(grandfatheredRemaining).toFixed(2);
    }

    /**
     * Sets the Pending Grandfathered time for selected employee.
     */
    this.setEmployeeGrandfatheredPending = function(grandfatheredPending) {
        employeeGrandfatheredPending = parseFloat(grandfatheredPending).toFixed(2);
    }

    /**
     * Sets the Pending Unexcused Absence time for selected employee.
     */
    this.setEmployeeUnexcusedAbsencePending = function(unexcusedAbsencePending) {
        employeeUnexcusedAbsencePending = parseFloat(unexcusedAbsencePending).toFixed(2);
    }

    /**
     * Sets the Pending Bereavement time for selected employee.
     */
    this.setEmployeeBereavementPending = function(bereavementPending) {
        employeeBereavementPending = parseFloat(bereavementPending).toFixed(2);
        
    }

    /**
     * Sets the Pending Civic Duty time for selected employee.
     */
    this.setEmployeeCivicDutyPending = function(civicDutyPending) {
        employeeCivicDutyPending = parseFloat(civicDutyPending).toFixed(2);
    }

    /**
     * Sets the Pending Time Off Without Pay time for selected employee.
     */
    this.setEmployeeApprovedNoPayPending = function(approvedNoPayPending) {
        employeeApprovedNoPayPending = parseFloat(approvedNoPayPending).toFixed(2);
    }
    
    this.warnExceededPTORemaining = function() {
    	if (employeePTORemaining <= 0) {
            $('div.buttonDisappearPTO button').addClass('categoryTimeExceeded');
            $('div.buttonDisappearPTO .categoryButtonRemainingLabel').addClass('red');
            $('div.buttonDisappearPTO .categoryButtonNumberRemainingHours').addClass('red');
        } else {
            $('div.buttonDisappearPTO button').removeClass('categoryTimeExceeded');
            $('div.buttonDisappearPTO .categoryButtonRemainingLabel').removeClass('red');
            $('div.buttonDisappearPTO .categoryButtonNumberRemainingHours').removeClass('red');
        }
    }
    
    this.warnExceededFloatRemaining = function() {
    	if (employeeFloatRemaining <= 0) {
            $('div.buttonDisappearFloat button').addClass('categoryTimeExceeded');
            $('div.buttonDisappearFloat .categoryButtonRemainingLabel').addClass('red');
            $('div.buttonDisappearFloat .categoryButtonNumberRemainingHours').addClass('red');
        } else {
            $('div.buttonDisappearFloat button').removeClass('categoryTimeExceeded');
            $('div.buttonDisappearFloat .categoryButtonRemainingLabel').removeClass('red');
            $('div.buttonDisappearFloat .categoryButtonNumberRemainingHours').removeClass('red');
        }
    }

    /**
     * Prints the Remaining PTO time for selected employee.
     */
    this.printEmployeePTORemaining = function() {
    	$("#employeePTORemainingHours").html( parseFloat(employeePTORemaining).toFixed(2) + " hours");
    }

    /**
     * Prints the Pending PTO time for selected employee.
     */
    this.printEmployeePTOPending = function() {
        $("#employeePTOPendingHours").html( parseFloat(employeePTOPending).toFixed(2) + " hours");
    }

    /**
     * Prints the Remaining Float time for selected employee.
     */
    this.printEmployeeFloatRemaining = function() {
        $("#employeeFloatRemainingHours").html( parseFloat(employeeFloatRemaining).toFixed(2) + " hours" );
    }

    /**
     * Prints the Pending Float time for selected employee.
     */
    this.printEmployeeFloatPending = function() {
        $("#employeeFloatPendingHours").html( parseFloat(employeeFloatPending).toFixed(2) + " hours" );
    }

    /**
     * Prints the Remaining Sick time for selected employee.
     */
    this.printEmployeeSickRemaining = function() {
        $("#employeeSickRemainingHours").html( parseFloat(employeeSickRemaining).toFixed(2) + " hours" );
    }

    /**
     * Prints the Pending Sick time for selected employee.
     */
    this.printEmployeeSickPending = function() {
        $("#employeeSickPendingHours").html( parseFloat(employeeSickPending).toFixed(2) + " hours" );
    }

    /**
     * Prints the Remaining Grandfathered time for selected employee.
     */
    this.printEmployeeGrandfatheredRemaining = function() {
    	$("#employeeGrandfatheredRemainingHours").html( parseFloat(employeeGrandfatheredRemaining).toFixed(2) + " hours" );
    }

    /**
     * Prints the Pending Grandfathered time for selected employee.
     */
    this.printEmployeeGrandfatheredPending = function() {
        $("#employeeGrandfatheredPendingHours").html( parseFloat(employeeGrandfatheredPending).toFixed(2) + " hours" );
    }

    /**
     * Prints the Pending Unexcused Absence time for selected employee.
     */
    this.printEmployeeUnexcusedAbsencePending = function() {
        $("#employeeUnexcusedAbsencePendingHours").html( parseFloat(employeeUnexcusedAbsencePending).toFixed(2) + " hours" );
    }

    /**
     * Prints the Pending Bereavement time for selected employee.
     */
    this.printEmployeeBereavementPending = function() {
        $("#employeeBereavementPendingHours").html( parseFloat(employeeBereavementPending).toFixed(2) + " hours" );
    }

    /**
     * Prints the Pending Civic Duty time for selected employee.
     */
    this.printEmployeeCivicDutyPending = function() {
        $("#employeeCivicDutyPendingHours").html( parseFloat(employeeCivicDutyPending).toFixed(2) + " hours" );
    } 

    /**
     * Prints the Pending Time Off Without Pay time for selected employee.
     */
    this.printEmployeeApprovedNoPayPending = function() {
        $("#employeeApprovedNoPayPendingHours").html( parseFloat(employeeApprovedNoPayPending).toFixed(2) + " hours" );
    }

    /**
     * Adds employee defaultHours from the current Category of time Remaining.
     */
    this.addTime = function(category, hours) {
        switch (category) {
            case 'timeOffGrandfathered':
                employeeGrandfatheredRemaining -= hours;
                timeOffCreateRequestHandler.printEmployeeGrandfatheredRemaining();
                break;
                
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
            case 'timeOffGrandfathered':
                employeeGrandfatheredRemaining += hours;
                timeOffCreateRequestHandler.printEmployeeGrandfatheredRemaining();
                break;
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
    
    this.unhighlightSelectedCategoriesByDate = function( object ) {
    	object.removeClass('timeOffPTOSelected');
    	object.removeClass('timeOffFloatSelected');
        object.removeClass('timeOffSickSelected');
        object.removeClass('timeOffGrandfatheredSelected');
        object.removeClass('timeOffBereavementSelected');
        object.removeClass('timeOffApprovedNoPaySelected');
        object.removeClass('timeOffCivicDutySelected');
        object.removeClass('timeOffUnexcusedAbsenceSelected');
    }

    this.highlightDates = function() {
//        console.log( "CHECK>>>>>> " + doRealDelete );
//        var blahhh = typeof timeOffCreateRequestInitHandler;
//        console.log(blahhh);
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
        /***
         *  var isBeingReviewed = ( typeof timeOffApproveRequestHandler==="object" ? true : false );
            if( isBeingReviewed ) {
                if( selectedDatesNew[key].hasOwnProperty('isDeleted') && selectedDatesNew[key].isDeleted===true ) {
                    console.log( "REVIEWED>>RESTORE" );
                    delete selectedDatesNew[deleteIndex].fieldDirty;
                    delete selectedDatesNew[deleteIndex].isDeleted;
                    $('#formDirty').val('false');
                } else {
                    console.log( "REVIEWED>>DELETE" );
                    selectedDatesNew[deleteIndex].fieldDirty = true;
                    selectedDatesNew[deleteIndex].isDeleted = true;
                    $('#formDirty').val('true');
                }
                console.log( "YAYAYA" );
            } else {
                console.log( "CREATE>>DELETE" );
                selectedDatesNew.splice(deleteIndex, 1);
            }
         */
        $.each($(".calendar-day"), function(index, blah) {
            if( $(this).attr("data-date") === moment().format('MM/DD/YYYY') ) {
                $(this).addClass("today");
            }
            for (var i = 0; i < selectedDatesNew.length; i++) {
                var isDeleted = (selectedDatesNew[i].hasOwnProperty('isDeleted') && selectedDatesNew[i].isDeleted===true);
                var isBeingReviewed = ( typeof timeOffApproveRequestHandler==="object" ? true : false );
                console.log( "isBeingReviewed", isBeingReviewed );
                console.log( "@@@ > " + selectedDatesNew[i].date );
                console.log( "@@@ > " + $(this).attr("data-date") );
                console.log( "@@@ > " + isDeleted );
                //  && !isDeleted
                if (selectedDatesNew[i].date && selectedDatesNew[i].date === $(this).attr("data-date")) {
                    thisClass = selectedDatesNew[i].category + "Selected";
                    console.log( "XXX", thisClass );
                    console.log( "YYY", $(this) );
                    if( isBeingReviewed && isDeleted ) {
                        $(this).removeClass(thisClass);
                    } else {
                        $(this).addClass(thisClass);
                    }
//                    $(this).toggleClass(thisClass);
                    console.log( "ZZZ", $(this) );
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
    this.setTwoDecimalPlaces = function( numberToFormat ) {
        return numberToFormat.toFixed(2);
    }
    
    this.getRemainingRequestedTimeByDate = function( thisDate ) {
        for (var index = 0; index < selectedDatesNew.length; index++) {
            if ( selectedDatesNew[index].date==thisDate &&
                 ( selectedDatesNew[index].hours < 8 || selectedDatesNew[index].hours > 12 ) )  {
                return index;
            }
        }
        return null;
    }
    
    /**
     * Will readjust time for a requested date/category if we remove split time.
     * 
     * @param {type} method
     * @param {type} isSelected
     * @returns {undefined}
     */
    this.adjustRemainingDate = function( method, isSelected ) {
        var indexRemaining = timeOffCreateRequestHandler.getRemainingRequestedTimeByDate( isSelected.dateObject.date );
        console.log( "LOOOOK", indexRemaining );
        console.log( selectedDatesNew[indexRemaining] );
        if( indexRemaining!=null &&
            ( selectedDatesNew[indexRemaining].hours < 8 || selectedDatesNew[indexRemaining].hours > 12 ) ) {
            
            var remainingTime = selectedDatesNew[indexRemaining].hours,
                remainingCategory = selectedDatesNew[indexRemaining].category,   
                scheduleDOW = requestForEmployeeObject["SCHEDULE_" + selectedDatesNew[indexRemaining].dow];
            selectedDatesNew[indexRemaining].hours = scheduleDOW;
            console.log( "remainingCategory", remainingCategory );
            console.log( "scheduleDOW", scheduleDOW );
            console.log( "remainingTime", remainingTime );
            timeOffCreateRequestHandler.addTime( remainingCategory, (scheduleDOW-remainingTime) );
        }
    }

    /**
     * Determines if the date and category is selected and returns an object we can handle later.
     */
    this.isSelected = function(object) {
        var thisDate = object.data('date');
        var thisCategory = selectedTimeOffCategory;
        var thisHours = defaultHours;
        dow = moment(thisDate, "MM/DD/YYYY").format("ddd").toUpperCase();
        var obj = {
            date : thisDate,
            hours : ( selectedTimeOffCategory=="timeOffFloat" ? 8.00 : Number( requestForEmployeeObject["SCHEDULE_" + dow] ) ),
            category : selectedTimeOffCategory
        };
        var isSelected = false;
        var deleteIndex = null;
        for (var i = 0; i < selectedDatesNew.length; i++) {
            if (selectedDatesNew[i].date == thisDate && selectedDatesNew[i].category == thisCategory) {
                return { isSelected : true, deleteIndex : i, dateObject : obj };
            }
        }
        return { isSelected : isSelected, deleteIndex : i, dateObject : obj };
    }

    this.getLastSelectedDateIndex = function() {
       return selectedDatesNew.length - 1;
    }
    
    this.datesAlreadyInRequestArray = function( dateObject ) {
        var found = null;
        var counter = 0;
        $.each( selectedDatesNew, function( index, requestedDateObject ) {
            if( requestedDateObject.date==dateObject.date ) {
                found = index;
                counter++;
            }
        });
        
        return ( counter===1 ? found : null );
    }
    
    this.getSplitHours = function( firstObject, secondObject, scheduleThisDay ) {
    	var hoursFirst = 0,
    	    hoursSecond = 0;
    	firstObject.hours = +firstObject.hours;
    	secondObject.hours = +secondObject.hours;
    	
    	if( firstObject.category=="timeOffGrandfathered" && parseFloat(employeeGrandfatheredRemaining).toFixed(2) <= 4 ) {
    		hoursFirst = parseFloat(employeeGrandfatheredRemaining).toFixed(2);
    	}
    	if( secondObject.category=="timeOffGrandfathered" && parseFloat(employeeGrandfatheredRemaining).toFixed(2) <= 4 ) {
    		hoursSecond = parseFloat(employeeGrandfatheredRemaining).toFixed(2);
    	}
    	if( firstObject.category=="timeOffSick" && parseFloat(employeeSickRemaining).toFixed(2) <= 4 ) {
    		hoursFirst = parseFloat(employeeSickRemaining).toFixed(2);
    	}
    	if( secondObject.category=="timeOffSick" && parseFloat(employeeSickRemaining).toFixed(2) <= 4 ) {
    		hoursSecond = parseFloat(employeeSickRemaining).toFixed(2);
    	}
    	
    	console.log( "hoursFirst", hoursFirst );
    	console.log( "hoursSecond", hoursSecond );
    	
    	if( firstObject.category=="timeOffFloat" ) {
        	hoursFirst = 8;
//        	hoursSecond = ( hoursSecond==0 ? scheduleThisDay - hoursFirst : hoursSecond );
        } else if( secondObject.category=="timeOffFloat" ) {
        	hoursSecond = 8;
//        	hoursFirst = ( hoursFirst==0 ? scheduleThisDay - hoursSecond : hoursFirst );
        }
//        else {
//        	hoursFirst = ( hoursFirst==0 ? scheduleThisDay / 2 : hoursFirst );
//        	hoursSecond = ( hoursSecond==0 ? scheduleThisDay / 2 : hoursSecond );
//        }
    	
    	if( hoursFirst==0 && firstObject.hours > 0 ) {
    		hoursFirst = +firstObject.hours;
    	}
    	if( hoursSecond==0 && secondObject.hours > 0 ) {
    		hoursSecond = +secondObject.hours;
    	}
    	
    	if( +hoursFirst + +hoursSecond > +scheduleThisDay ) {
    		console.log( "ADJUST SOMETHING!!!" );
    		hoursFirst = ( +hoursFirst == +scheduleThisDay ) ? +scheduleThisDay - +hoursSecond : +hoursFirst;
    		hoursSecond = ( +hoursSecond == +scheduleThisDay ) ? +scheduleThisDay - +hoursFirst : +hoursSecond;
    	}
    	
    	console.log( "hoursFirst", hoursFirst );
    	console.log( "hoursSecond", hoursSecond );
    	
    	return splitHours = {
    		first: { hours: +hoursFirst, locked: 0 },
    		second: { hours: +hoursSecond, locked: 0 },
    		scheduleThisDay: +scheduleThisDay,
    		totalHoursOff: +hoursFirst + +hoursSecond
    	};
    }
    
    /**
     * Splits a date requested
     * 
     * @param {type} method
     * @param {type} isSelected
     * @returns {undefined}
     */
    this.splitRequestedDate = function( method, isSelected, foundIndex ) {
        var dateObject = isSelected.dateObject;
        dateObject.dow = moment(dateObject.date, "MM/DD/YYYY").format("ddd").toUpperCase();
        scheduleThisDay = requestForEmployeeObject["SCHEDULE_" + dateObject.dow];
        
        var splitHours = timeOffCreateRequestHandler.getSplitHours( selectedDatesNew[foundIndex], dateObject, scheduleThisDay );
        totalHoursForThisSplit = splitHours.first.hours + splitHours.second.hours;
        console.log( "BEFORE SPLIT CHECK" );
        console.log( selectedDatesNew[foundIndex] );
        console.log( dateObject );
        console.log( scheduleThisDay );
        console.log( "splitHours", splitHours );
//        if( splitHours.first.hours<=0 || splitHours.second.hours<=0 || totalHoursForThisSplit < scheduleThisDay ) {
//        	timeOffCreateRequestHandler.alertUserUnableToSplitTime();
//        	return;
//        } else {
//        	// Add back the time first for the category we're going to end up splitting.
//            timeOffCreateRequestHandler.addTime( selectedDatesNew[foundIndex].category, 0-selectedDatesNew[foundIndex].hours );
//            
//            // Subtract the split times
//            timeOffCreateRequestHandler.addTime( selectedDatesNew[foundIndex].category, splitHours.first.hours );
//            timeOffCreateRequestHandler.addTime( dateObject.category, splitHours.second.hours );
//            
//	        selectedDatesNew[foundIndex].hours = splitHours.first.hours;
//	        dateObject.hours = splitHours.second.hours;
//	        selectedDatesNew.push( dateObject );
//	        timeOffCreateRequestHandler.toggleDateCategorySelection( dateObject.date, dateObject.category );
//        }
    }
    
    this.getHoursToAdd = function( dateObject ) {
    	var hoursToAdd = dateObject.hours;
    	if( dateObject.category=="timeOffGrandfathered" && employeeGrandfatheredRemaining <= 8 ) {
    		hoursToAdd = employeeGrandfatheredRemaining;
    	} else if( dateObject.category=="timeOffSick" && employeeSickRemaining <= 8 ) {
    		hoursToAdd = employeeSickRemaining;
    	}
    	return hoursToAdd;
    }
    
    /**
     * Adds date to current request
     */
    this.addRequestedDate = function( method, isSelected ) {
        var index = isSelected.deleteIndex,
            dateObject = isSelected.dateObject,
            found = timeOffCreateRequestHandler.datesAlreadyInRequestArray( dateObject ),
            hoursToAdd = timeOffCreateRequestHandler.getHoursToAdd( dateObject );
        dateObject.dow = moment(dateObject.date, "MM/DD/YYYY").format("ddd").toUpperCase();
        dateObject.hours = parseFloat(hoursToAdd).toFixed(2);
        timeOffCreateRequestHandler.addTime( dateObject.category, dateObject.hours );
        selectedDatesNew.push( dateObject );
        if( method == 'mark' ) {
            if( selectedDatesNew[index].hasOwnProperty('isDeleted') && selectedDatesNew[index].isDeleted===true ) {
                selectedDatesNew[index].isDeleted = false;
                selectedDatesNew[index].fieldDirty = false;
            } else {
                selectedDatesNew[index].fieldDirty = true;
                selectedDatesNew[index].isAdded = true;
                $('#formDirty').val('true');
            }
        }
    }
    
    /**
     * Removes a date or marks a date as deleted from current request
     * 
     * @param {type} method
     * @param {type} isSelected
     * @returns {undefined}
     */
    this.removeRequestedDate = function( method, isSelected ) {
        var index = isSelected.deleteIndex;
        timeOffCreateRequestHandler.subtractTime( selectedDatesNew[index].category, Number( selectedDatesNew[index].hours ) );
        switch( method ) {
            case 'do':
                selectedDatesNew.splice(index, 1);
                break;
                
            case 'mark':
                if( selectedDatesNew[index].hasOwnProperty('isDeleted') && selectedDatesNew[index].isDeleted===true ) {
                    delete selectedDatesNew[index].fieldDirty;
                    delete selectedDatesNew[index].isDeleted;
                } else {
                    selectedDatesNew[index].fieldDirty = true;
                    selectedDatesNew[index].isDeleted = true;
                }
                $('#formDirty').val('true');
                break;
        }
    }
    
    this.getHoursRequestedHeader = function() {
        return '<strong>Hours Requested:</strong><br /><br />' +
               '<table class="employeeSchedule" style="width:100%">' +
               '<thead>' +
                    '<tr>' +
                        '<th style="width:40px;">Day</th>' +
                        '<th style="width:60px;">Date</th>' +
                        '<th style="width:40px;">Hours</th>' +
                        '<th>Category</th>' +
                        '<th style="width:15px;text-align:center;">Delete</th>' +
                    '</tr>' +
                '</thead>' +
                '<tbody>';
    }
    
    this.getHoursRequestedRow = function( dow, hideMe, selectedIndex ) {    	
        return '<tr' + hideMe + '>' +
            '<td>' + dow + '</td>' +
            '<td>' + selectedDatesNew[selectedIndex].date + '</td>' +
            '<td><input class="selectedDateHours" value="' +
            parseFloat( selectedDatesNew[selectedIndex].hours ).toFixed(2) +
            '" data-key="' + selectedIndex + '" ' +
            timeOffCreateRequestHandler.disableHoursInputField( selectedDatesNew[selectedIndex].category ) + '></td>' +
            '<td>' +
            '<span class="badge ' + selectedDatesNew[selectedIndex].category + '">' +
            timeOffCreateRequestHandler.getCategoryText(selectedDatesNew[selectedIndex].category) +
            '</span>' +
            '</td>' +
            '<td style="width:15px;text-align:center;"><span class="glyphicon glyphicon-remove-circle red remove-date-requested" ' +
            'data-date="' + selectedDatesNew[selectedIndex].date + '" ' +
            'data-category="' + selectedDatesNew[selectedIndex].category + '" ' +
            'data-selecteddatesnew-key="' + selectedIndex + '" ' +
            'title="Remove date from request">' + '</span></td>' +
            '</tr>';
    }

    this.updateTotalsPerCategory = function() {
    	totalPTORequested = 0;
    	totalFloatRequested = 0;
    	totalSickRequested = 0;
    	totalUnexcusedAbsenceRequested = 0;
    	totalBereavementRequested = 0;
    	totalCivicDutyRequested = 0;
    	totalGrandfatheredRequested = 0;
    	totalApprovedNoPayRequested = 0;
    	
        for (var selectedIndex = 0; selectedIndex < selectedDatesNew.length; selectedIndex++) {
        	switch (selectedDatesNew[selectedIndex].category) {
	            case 'timeOffPTO':
	                totalPTORequested += +selectedDatesNew[selectedIndex].hours;
	                break;
	            case 'timeOffFloat':
	                totalFloatRequested += +selectedDatesNew[selectedIndex].hours;
	                break;
	            case 'timeOffSick':
	            	totalSickRequested += +selectedDatesNew[selectedIndex].hours;
	            	break;
	            case 'timeOffUnexcusedAbsence':
	                totalUnexcusedAbsenceRequested += +selectedDatesNew[selectedIndex].hours;
	                break;
	            case 'timeOffBereavement':
	                totalBereavementRequested += +selectedDatesNew[selectedIndex].hours;
	                break;
	            case 'timeOffCivicDuty':
	                totalCivicDutyRequested += +selectedDatesNew[selectedIndex].hours;
	                break;
	            case 'timeOffGrandfathered':
	                totalGrandfatheredRequested += +selectedDatesNew[selectedIndex].hours;
	                break;
	            case 'timeOffApprovedNoPay':
	                totalApprovedNoPayRequested += +selectedDatesNew[selectedIndex].hours;
	                break;
	        }
        }
        totalPTORequested = parseFloat(totalPTORequested).toFixed(2);
        totalFloatRequested = parseFloat(totalFloatRequested).toFixed(2);
        totalSickRequested = parseFloat(totalSickRequested).toFixed(2);
        totalUnexcusedAbsenceRequested = parseFloat(totalUnexcusedAbsenceRequested).toFixed(2);
        totalBereavementRequested = parseFloat(totalBereavementRequested).toFixed(2);
        totalCivicDutyRequested = parseFloat(totalCivicDutyRequested).toFixed(2);
        totalGrandfatheredRequested = parseFloat(totalGrandfatheredRequested).toFixed(2);
        totalApprovedNoPayRequested = parseFloat(totalApprovedNoPayRequested).toFixed(2);
    }
    
    /**
     * Draws form fields we can submit for the user.
     */
    this.drawHoursRequested = function() {
        totalPTORequested = 0;
        totalFloatRequested = 0;
        totalSickRequested = 0;
        totalUnexcusedAbsenceRequested = 0;
        totalBereavementRequested = 0;
        totalCivicDutyRequested = 0;
        totalGrandfatheredRequested = 0;
        totalApprovedNoPayRequested = 0;
            
        datesSelectedDetailsHtml = timeOffCreateRequestHandler.getHoursRequestedHeader();
        
        for (var selectedIndex = 0; selectedIndex < selectedDatesNew.length; selectedIndex++) {
            var dow = moment(selectedDatesNew[selectedIndex].date, "MM/DD/YYYY").format("ddd").toUpperCase();
            var hideMe = ( selectedDatesNew[selectedIndex].hasOwnProperty('isDeleted') && selectedDatesNew[selectedIndex].isDeleted===true ?
                           ' style="display:none;"' : '' );
            datesSelectedDetailsHtml += timeOffCreateRequestHandler.getHoursRequestedRow( dow, hideMe, selectedIndex );

            switch (selectedDatesNew[selectedIndex].category) {
                case 'timeOffPTO':
                    totalPTORequested += parseInt(selectedDatesNew[selectedIndex].hours, 10);
                    break;
                case 'timeOffFloat':
                    totalFloatRequested += parseInt(selectedDatesNew[selectedIndex].hours, 10);
                    break;
                case 'timeOffSick':
                    totalSickRequested += parseInt(selectedDatesNew[selectedIndex].hours, 10);
                    break;
                case 'timeOffUnexcusedAbsence':
                    totalUnexcusedAbsenceRequested += parseInt( selectedDatesNew[selectedIndex].hours, 10);
                    break;
                case 'timeOffBereavement':
                    totalBereavementRequested += parseInt(selectedDatesNew[selectedIndex].hours, 10);
                    break;
                case 'timeOffCivicDuty':
                    totalCivicDutyRequested += parseInt(selectedDatesNew[selectedIndex].hours, 10);
                    break;
                case 'timeOffGrandfathered':
                        totalGrandfatheredRequested += parseInt(selectedDatesNew[selectedIndex].hours, 10);
                    break;
                case 'timeOffApprovedNoPay':
                        totalApprovedNoPayRequested += parseInt(selectedDatesNew[selectedIndex].hours, 10);
                    break;
            }
        }

//        console.log( "selectedDatesNew", selectedDatesNew );

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
        timeOffCreateRequestHandler.checkIfRequestFormShouldBeDisabled();
    }
    
    this.disableHoursInputField = function( category ) {
        if( timeOffCommon.empty( category ) ) {
            return settingsDisableHoursInputFields===true ? ' disabled="disabled"' : '';
        }
        return category==="timeOffFloat" ? ' disabled="disabled"' : '';
    }
    
    this.checkIfRequestFormShouldBeDisabled = function() {
    	var wow = timeOffCreateRequestHandler.verifySalaryTakingRequiredHoursPerDay();
//    	console.log( wow )
    }

    /**
     * Sorts dates in the selected array.
     * Uses bubble sort algorithm.
     */
    this.sortDatesSelected = function() {
        selectedDatesNew.sort(function(a, b) {
            var dateA = new Date(a.date).getTime();
            var dateB = new Date(b.date).getTime();
            return dateA > dateB ? 1 : - 1;
        });
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
        console.log( "Selected Employee:", requestForEmployeeObject );
        if ( ( loggedInUserData.isManager == "Y" ||
               loggedInUserData.isSupervisor == "Y" || 
               loggedInUserData.isPayrollAdmin == "Y" ||
               loggedInUserData.isPayrollAssistant == "Y" ||
               loggedInUserData.isProxy === "Y" )
        ) {
//            alert("ENABLE");
            timeOffCreateRequestHandler.enableSelectRequestFor();
            $("#requestFor").prop('disabled', false);
        } else {
//            alert("DISABLE");
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
                        isProxy : loggedInUserData.isProxy,
                        proxyFor : loggedInUserData.proxyFor,
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
        selectedTimeOffCategory = null;
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
        if (employeeGrandfatheredRemaining > 0 && categoryButton.attr("data-category")=="timeOffPTO") {
            timeOffCreateRequestHandler.resetTimeoffCategory(categoryButton);
            timeOffCreateRequestHandler.alertUserToTakeGrandfatheredTime();
        }
        if (selectedTimeOffCategory === null) {
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

        selectedTimeOffCategory = null;
        timeOffCreateRequestHandler.maskCalendars('hide');
        timeOffCreateRequestHandler.setStep('1');
    }

    /**
     * Warns user to take Grandfathered time off first if there is a balance.
     * 
     * @returns {undefined}
     */
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
    
    /**
     * Warns user they can not split time selected.
     * 
     * @returns {undefined}
     */
    this.alertUserUnableToSplitTime = function() {
        $("#dialogUnableToSplitTimeAlert").dialog({
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
        if ((items.length === 1 && selectedTimeOffCategory === "timeOffFloat")
            || (items.length === 0) || (items.length > 1)) {
            allowSplitDate = false;
        }
        if (items.length === 1 && selectedTimeOffCategory != "timeOffFloat") {
            allowSplitDate = true;
        }

        return {
            allowSplitDate : allowSplitDate,
            items : items
        };
    }

    /**
     * Toggles the highlighting of a calendar day based on the date and category passed in.
     * 
     * @param {type} date
     * @param {type} category
     * @returns {undefined}
     * 
     */
    this.toggleDateCategorySelection = function( date, category ) {
        if( timeOffCommon.empty( category ) ) {
            category = selectedTimeOffCategory;
        }
        $("td[data-date='" + date + "']").toggleClass( category + "Selected" );
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
        var foundCounter = 0;
        var copy = null;
        var newOne = null;
        var deleteKey = null;
        $.each(selectedDatesNew, function(key, dateObject) {
            if ( object.date == dateObject.date && object.category === dateObject.category ) {
                found = true;
                foundCounter++;
                deleteKey = key;
            }
            if ( object.date == dateObject.date && object.category != dateObject.category ) {
                found = true;
                foundCounter++;
                copy = dateObject;
                newOne = object;
                newOne.dow = moment(object.date, "MM/DD/YYYY").format("ddd").toUpperCase();
                deleteKey = key;
            }
        });
        
        /**
         * Add date to request.
         */
        if (copy === null && deleteKey === null) {
            timeOffCreateRequestHandler.addDataToRequest( calendarDateObject, object );
        }

        /**
         * Delete date from request.
         */
        console.log( "XYZ", calendarDateObject );
        console.log( "XYZ", deleteKey );
        console.log( "XYZ", object );
        if ( copy == null && deleteKey !== null && foundCounter === 1 ) {
            console.log( "XYZ", "addDa" );
            timeOffCreateRequestHandler.deleteRequestedDateByIndex(calendarDateObject, deleteKey, object);
        }
        
        /**
         * Warn before delete date from request where day is split.
         */
        if ( copy !== null && deleteKey !== null && foundCounter > 1 ) {
            $("#dialogDeleteSplitDateAlert").dialog({
                modal : true,
                buttons : {
                    Ok : function() {
                        $(this).dialog("close");
                    }
                }
            });
        }

        /**
         * Split the data.
         */
        if ( copy !== null && deleteKey !== null && foundCounter === 1 ) {
            timeOffCreateRequestHandler.splitDataFromRequest(calendarDateObject, deleteKey, copy, newOne);
        }

        timeOffCreateRequestHandler.sortDatesSelected();
        timeOffCreateRequestHandler.drawHoursRequested();
    }
       
    /**
     * Shows warning that request needs Payroll review if first date
     * requested is 14 or more days old.
     * 
     * @returns {undefined}
     */
    this.warnFirstDateRequestedAgeTooOld = function() {
        var warnFirstDateRequestedTooOld = false;
        var counter = 0;
        var compareToDate = moment().locale("en").subtract(14, 'd').format("MM/DD/YYYY");
        //moment().add(7, 'days').subtract(1, 'months').year(2009).hours(0).minutes(0).seconds(0);
        $.each( selectedDatesNew, function( index, selectedDateNewObject ) {
            if( selectedDateNewObject.date <= compareToDate ) {
                counter++;
            }
        });
        if( counter>0 ) {
            warnFirstDateRequestedTooOld = true;
        }
        
        return warnFirstDateRequestedTooOld;
    }

    /**
     * Add the following to the day requested:
     * 1. Day of week (i.e. MON, TUE)
     * 2. Default hours for this employee's schedule, unless Float.
     *
     * @param {string} object
     * @returns {object}     */
    this.formatDayRequested = function(object) {
        object.dow = moment(object.date, "MM/DD/YYYY").format("ddd").toUpperCase();
        var scheduleDay = "SCHEDULE_" + object.dow;
        object.hours = ( ( object.category=="timeOffFloat" ) ? '8.00' : requestForEmployeeObject[scheduleDay] );
        return object;
    }
    
    this.toggleFirstDateRequestedTooOldWarning = function() {
        warnFirstDateRequestedTooOld = timeOffCreateRequestHandler.warnFirstDateRequestedAgeTooOld();
        if( warnFirstDateRequestedTooOld ) {
            $("#warnFirstDateRequestedTooOld").show();
        } else {
            $("#warnFirstDateRequestedTooOld").hide();
        }
    }

    /**
     * Deletes a day from the request.
     *
     * @param {type} calendarDateObject
     * @param {type} deleteKey
     * @param {type} object
     * @returns {undefined} */
    this.deleteRequestedDateByIndex = function( deleteIndex ) {
        console.log( "CHECKING...", selectedDatesNew[deleteIndex] );
    	timeOffCreateRequestHandler.subtractTime( selectedDatesNew[deleteIndex].category, Number( selectedDatesNew[deleteIndex].hours ) );
        timeOffCreateRequestHandler.toggleDateCategorySelection( selectedDatesNew[deleteIndex].date, selectedDatesNew[deleteIndex].category );
        selectedDatesNew.splice(deleteIndex, 1);
        timeOffCreateRequestHandler.drawHoursRequested();
//        timeOffCreateRequestHandler.toggleFirstDateRequestedTooOldWarning();
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