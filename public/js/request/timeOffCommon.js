/**
 * Javascript timeOffCommon 'class'
 *
 */
var timeOffCommon = new function ()
{
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
            
            $("#navbar > ul > li > ul > li > a").on( 'click', function() {
                $('.dropdown ul').hide();
                $('li.dropdown.open').removeClass('open');
            });
            
            $( ".launchDialogEditEmployeeSchedule" ).on( 'click', function() {
//                var navbar = $("#navbar");
//                navbar.on( "click", "a", null, function() {
//                    navbar.collapse( 'hide' );
//                });
//                $("#menuMyAccount").removeClass("open");
//                console.log( $("#navbar > ul > li.dropdown.open") );
//                console.log( $(this).parent().parent() );
                console.log( "CLICKED BUTTON TO LAUNCH EMPLOYEE SCHEDULE" );
//                dialogEditEmployeeSchedule.dialog( "open" );
            });
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
    
    this.submitEditEmployeeSchedule = function() {
        alert( "SAVE STUFF" );
//        var valid = true;
//        allFields.removeClass( "ui-state-error" );
//
//        valid = valid && checkLength( name, "username", 3, 16 );
//        valid = valid && checkLength( email, "email", 6, 80 );
//        valid = valid && checkLength( password, "password", 5, 16 );
//
//        valid = valid && checkRegexp( name, /^[a-z]([0-9a-z_\s])+$/i, "Username may consist of a-z, 0-9, underscores, spaces and must begin with a letter." );
//        valid = valid && checkRegexp( email, emailRegex, "eg. ui@jquery.com" );
//        valid = valid && checkRegexp( password, /^([0-9a-zA-Z])+$/, "Password field only allow : a-z 0-9" );
//
//        if ( valid ) {
//            $( "#users tbody" ).append( "<tr>" +
//                    "<td>" + name.val() + "</td>" +
//                    "<td>" + email.val() + "</td>" +
//                    "<td>" + password.val() + "</td>" +
//            "</tr>" );
//            dialog.dialog( "close" );
//        }
        
//        return valid;
    }
}

// Initialize the class
timeOffCommon.initialize();