/**
 * Javascript timeOffCommon 'class'
 *
 */
var timeOffCommon = new function ()
{
    var timeOffSubmitEmployeeScheduleRequestUrl = phpVars.basePath + '/api/employee-schedule',
        employeeScheduleFormErrors = 0,
        updateUserSetting = phpVars.basePath + '/api/update-user-setting',
        getUserSettings = phpVars.basePath + '/api/get-user-settings';

    /**
     * What to run on initialize of this class.
     *
     * @returns {undefined}
     */
    this.initialize = function () {
        $(document).ready(function () {
            timeOffCommon.fadeOutFlashMessage();
            timeOffCommon.autoOpenDropdownOnHover();
            timeOffCommon.handleToggleLegend();
            timeOffCommon.checkSettings();

            $( "#dialogEditEmployeeSchedule" ).on( "dialogopen", function( event, ui ) {
                $('#employeeScheduleForm').parsley().validate();
            });

            $( "form#employeeScheduleForm :input" ).on( 'blur', function() {
                if( timeOffCommon.checkFormValidates() ) {
                    timeOffCommon.setEmployeeScheduleFormError( 'success' );
                } else {
                    timeOffCommon.setEmployeeScheduleFormError( 'error' );
                }
            });

            $( ".launchDialogEditEmployeeSchedule" ).on( 'click', function() {
                $("#dialogEditEmployeeSchedule").dialog({
                    modal : true,
                    closeOnEscape: false,
                    buttons : {
                        Save : function() {
                            if( $('#employeeScheduleForm').parsley().validate() &&
                                $('#employeeScheduleForm').parsley().isValid() ) {
                                timeOffCommon.setEmployeeScheduleFormError( 'success' );
                                $(this).dialog("close");
                                timeOffCommon.submitEmployeeScheduleUpdate();
                            } else {
                                timeOffCommon.setEmployeeScheduleFormError( 'error' );
                            }
                        },
                        Cancel : function() {
                            $(this).dialog("close");
                        }
                    }
                });
            });

            $('#employeeScheduleForm').parsley( { showErrors: true } );
        });
    }

    /**
     * Sets error message for the Employee Schedule form.
     *
     * @param {type} type
     * @returns {undefined}
     */
    this.setEmployeeScheduleFormError = function (type) {
        switch( type ) {
            case 'error':
                $('.employeeScheduleFormValidates').addClass('hidden');
                $('.employeeScheduleDailyHoursExceeded').removeClass('hidden');
                break;

            case 'uploadError':
                $('.employeeScheduleFormValidates').addClass('hidden');
                $('.employeeScheduleUploadError').removeClass('hidden');
                break;

            case 'saveError':
                $('.employeeScheduleFormValidates').addClass('hidden');
                $('.employeeScheduleSaveError').removeClass('hidden');
                break;

            case 'success':
            default:
                $('.employeeScheduleFormValidates').removeClass('hidden');
                $('.employeeScheduleDailyHoursExceeded').addClass('hidden');
                break;
        }
    }

    /**
     * Validate the number of hours input.
     *
     * @param {type} hours
     * @returns {Boolean}
     */
    this.validateNumberHours = function( hours ) {
        if( hours <= 12 && hours >= 0 ) {
            return true;
        }
        return false;
    }

    /**
     * Check if the form validates
     *
     * @returns {_L5.checkFormValidates.validates|Boolean}
     */
    this.checkFormValidates = function() {
        var validates = false;
        if( timeOffCommon.validateNumberHours( $("#employeeScheduleSUN").val() ) &&
            timeOffCommon.validateNumberHours( $("#employeeScheduleMON").val() ) &&
            timeOffCommon.validateNumberHours( $("#employeeScheduleTUE").val() ) &&
            timeOffCommon.validateNumberHours( $("#employeeScheduleWED").val() ) &&
            timeOffCommon.validateNumberHours( $("#employeeScheduleTHU").val() ) &&
            timeOffCommon.validateNumberHours( $("#employeeScheduleFRI").val() ) &&
            timeOffCommon.validateNumberHours( $("#employeeScheduleSAT").val() ) ) {
                validates = true;
        }

        return validates;
    }

    this.getEmployeeScheduleObject = function() {
        var days = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
        var employeeSchedule = {
            EMPLOYEE_NUMBER : $("#employeeScheduleFor").val()
        };

        $.map(days, function(val, i) {
            var key = 'SCHEDULE_' + val;
            var key2 = val;
            employeeSchedule[key] = $("#employeeSchedule" + key2).val();
        });

        return employeeSchedule;
    }

    this.submitEmployeeScheduleUpdate = function () {
        var validates = timeOffCommon.checkFormValidates();
        if( validates==true ) {
            var employeeScheduleObject = timeOffCommon.getEmployeeScheduleObject();
            var jsonData = {};

            jsonData['forEmployee'] = employeeScheduleObject;
            jsonData['byEmployee'] = $("#employeeScheduleBy").val();

            console.log(jsonData);
            $.ajax({
                url : timeOffSubmitEmployeeScheduleRequestUrl,
                type : 'POST',
                data : JSON.stringify(jsonData),
                dataType : 'json'
            }).success(function(json) {
                if (json.success == true) {
                    timeOffCreateRequestHandler.loadCalendars( $("#employeeScheduleFor").val() );
                } else {
                    timeOffCommon.setEmployeeScheduleFormError( 'saveError' );
                }
                timeOffCreateRequestHandler.setRequestForEmployeeSchedule( employeeScheduleObject );
                return;
            }).error(function() {
                timeOffCommon.setEmployeeScheduleFormError( 'uploadError' );
                return;
            });
        } else {
            timeOffCommon.setEmployeeScheduleFormError( 'error' );
        }
    }

    /**
     * Fade out flash messages automatically.
     */
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

    /**
     * Handles the site navigation menus on hover.
     *
     * @returns {undefined}
     */
    this.autoOpenDropdownOnHover = function() {
        $(".dropdown").hover(
            function() {
                $('.dropdown-menu', this).stop( true, true ).fadeIn("fast");
                $(this).toggleClass('open');
                $('b', this).toggleClass("caret caret-up");
            },
            function() {
                $('.dropdown-menu', this).stop( true, true ).fadeOut("fast");
                $(this).toggleClass('open');
                $('b', this).toggleClass("caret caret-up");
            });
    }

    this.empty = function(data) {
        if(typeof(data) == 'number' || typeof(data) == 'boolean')
        {
          return false;
        }
        if(typeof(data) == 'undefined' || data === null)
        {
          return true;
        }
        if(typeof(data.length) != 'undefined')
        {
          return data.length == 0;
        }
        var count = 0;
        for(var i in data)
        {
          if(data.hasOwnProperty(i))
          {
            count ++;
          }
        }
        return count == 0;
    }

    /**
     * Toggle the category color legend
     */
    this.handleToggleLegend = function() {
        $(document).on('click', '.toggleLegend', function() {
            timeOffCommon.toggleLegend();
        });
    }

    /**
     * Toggle the calendar legend showing the wonderful color system for categories.
     *
     * @returns {undefined}     */
    this.toggleLegend = function() {
        $("#calendarLegend").toggle();

        var myJson = {};
        var myEmployee = {};
        var mySetting = {};

        myEmployee['employeeId'] = phpVars.logged_in_employee_number;

        mySetting['showCalendarLegend'] = encodeURIComponent($("#calendarLegend").is(':visible'));

        myJson['employee'] = myEmployee;
        myJson['setting'] = mySetting;

        $.ajax({
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            url : updateUserSetting,
            type : 'POST',
            data : JSON.stringify(myJson),
            dataType : 'json'
        });
    }

    this.checkSettings = function() {
        var myEmployee = {};

        myEmployee['employeeId'] = phpVars.logged_in_employee_number;

        $.ajax({
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            url : getUserSettings,
            type : 'POST',
            data : JSON.stringify(myEmployee),
            dataType : 'json',
            success: function(data) {
                $.each(data, function(key, value) {
                    if (key == "showCalendarLegend" ) {
                        if ((value == 'false' && $("#calendarLegend").is(':visible')) ||
                            (value == 'true' && $("#calendarLegend").is(':hidden'))) {
                            $("#calendarLegend").toggle();
                        }
                    }
                });
            }
        });
    };
}

// Initialize the class
timeOffCommon.initialize();