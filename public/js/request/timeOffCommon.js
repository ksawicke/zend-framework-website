/**
 * Javascript timeOffCommon 'class'
 *
 */
var timeOffCommon = new function ()
{
    var timeOffSubmitEmployeeScheduleRequestUrl = phpVars.basePath + '/api/employee-schedule';
//    var dialogEditEmployeeSchedule = $( "#dialogEditEmployeeSchedule" ).dialog({
//            autoOpen: false,
//            height: 300,
//            width: 350,
//            modal: true,
//            buttons: {
//                "Create an account": timeOffCommon.submitEditEmployeeSchedule(),
//                Cancel: function() {
//                    dialog.dialog( "close" );
//                }
//            },
//            close: function() {
//                form[ 0 ].reset();
//    //                allFields.removeClass( "ui-state-error" );
//            }
//        }),
//        form = dialogEditEmployeeSchedule.find( "form" ).on( "submit", function( event ) {
//            event.preventDefault();
//            addUser();
//        });
    
    this.initialize = function () {
        $(document).ready(function () {
            timeOffCommon.fadeOutFlashMessage();
            timeOffCommon.autoOpenDropdownOnHover();
            
//            $("#navbar > ul > li > ul > li > a").on( 'click', function() {
//                $('.dropdown ul').hide();
//                $('li.dropdown.open').removeClass('open');
//            });
            
            $( ".launchDialogEditEmployeeSchedule" ).on( 'click', function() {
                $("#dialogEditEmployeeSchedule").dialog({
                    modal : true,
                    closeOnEscape: false,
                    buttons : {
                        Save : function() {
                            $(this).dialog("close");
                            timeOffCommon.submitEmployeeScheduleUpdate();
                        },
                        Cancel : function() {
                            $(this).dialog("close");
                        }
                    }
                });
            });
        });
    }
    
    this.submitEmployeeScheduleUpdate = function () {
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
                alert( "There was an error saving the new schedule." );
            }
            return;
        }).error(function() {
            alert( "There was an error uploading the new schedule." );
            return;
        });
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
}

// Initialize the class
timeOffCommon.initialize();