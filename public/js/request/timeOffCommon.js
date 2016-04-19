/**
 * Javascript timeOffCommon 'class'
 *
 */
var timeOffCommon = new function ()
{
    var timeOffSubmitEmployeeScheduleRequestUrl = phpVars.basePath + '/api/employee-schedule',
        employeeScheduleFormErrors = 0;
    
    /**
     * What to run on initialize of this class.
     * 
     * @returns {undefined}
     */
    this.initialize = function () {
        $(document).ready(function () {
            timeOffCommon.fadeOutFlashMessage();
            timeOffCommon.autoOpenDropdownOnHover();
            
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
                            
//                            if( $('#employeeScheduleForm').parsley().validate() &&
//                                $('#employeeScheduleForm').parsley().isValid() ) {
//                                console.log( "Form looks good" );
//                            } else {
//                                console.log( "Form is sad." );
//                            }
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
    
    this.submitEmployeeScheduleUpdate = function () {
        var validates = timeOffCommon.checkFormValidates();
        if( validates==true ) {
            $.ajax({
                url : timeOffSubmitEmployeeScheduleRequestUrl,
                type : 'POST',
                data : {
                    request : {
                        forEmployee : {
                            EMPLOYEE_NUMBER : $("#employeeScheduleFor").val(),
                            SCHEDULE_SUN: $("#employeeScheduleSUN").val(),
                            SCHEDULE_MON: $("#employeeScheduleMON").val(),
                            SCHEDULE_TUE: $("#employeeScheduleTUE").val(),
                            SCHEDULE_WED: $("#employeeScheduleWED").val(),
                            SCHEDULE_THU: $("#employeeScheduleTHU").val(),
                            SCHEDULE_FRI: $("#employeeScheduleFRI").val(),
                            SCHEDULE_SAT: $("#employeeScheduleSAT").val()
                        },
                        byEmployee : $("#employeeScheduleBy").val()
                    }
                },
                dataType : 'json'
            }).success(function(json) {
                if (json.success == true) {
                    timeOffCreateRequestHandler.loadCalendars( $("#employeeScheduleFor").val() );
                } else {
                    timeOffCommon.setEmployeeScheduleFormError( 'saveError' );
                }
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
}

// Initialize the class
timeOffCommon.initialize();