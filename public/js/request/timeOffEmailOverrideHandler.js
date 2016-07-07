/**
 * Javascript timeOffProxyHandler 'class'
 *
 */
var timeOffEmailOverrideHandler = new function ()
{
    var timeOffGetEmailOverrideSettingsUrl = phpVars.basePath + '/api/request/get-email-override-settings',
        timeOffEditEmailOverrideSettingsUrl = phpVars.basePath + '/api/request/edit-email-override-settings',
        timeOffToggleOverrideEmailsUrl = phpVars.basePath + '/api/request/email-override-toggle',
        overrideEmails = 0;

    /**
     * What to run on initialize of this class.
     * 
     * @returns {undefined}
     */
    this.initialize = function() {
        $(document).ready( function() {            
            timeOffEmailOverrideHandler.getEmailOverrideSettings();
            timeOffEmailOverrideHandler.handleToggleEmailOverrides();
            $(document).on('click', '.submitEditEmailOverrides', function() {
                timeOffEmailOverrideHandler.handlePleaseWaitStatus( $(this) );
                timeOffEmailOverrideHandler.handleSubmitEditEmailOverrides( $(this) );
            });
        });
    }
    
    /**
     * Gets the current email override settings.
     * 
     * @returns {undefined}
     */
    this.getEmailOverrideSettings = function() {
        $.ajax({
            url : timeOffGetEmailOverrideSettingsUrl,
            type : 'POST',
            dataType : 'json'
        })
        .success(function(json) {
            overrideEmails = json.overrideEmails;
            if( overrideEmails==1 ) {
                $( "#override_emails").prop('checked', true);
            }
            $("#emailOverrideList").val( json.emailOverrideList );
            timeOffEmailOverrideHandler.warnIfFeatureTurnedOff();
            return;
        }).error(function() {
            console.log('There was an error loading the email override list.');
            return;
        });
    }
    
    this.warnIfFeatureTurnedOff = function() {
        if( overrideEmails==1 ) {
            $("#warningEmailOverridesTurnedOff").hide();
        } else {
            $("#warningEmailOverridesTurnedOff").show();
        }
    }
    
    this.getOverrideEmails = function() {
        return overrideEmails;
    }
    
    this.setOverrideEmails = function(newOverrideEmails) {
        overrideEmails = newOverrideEmails;
    }
    
    this.handleToggleEmailOverrides = function() {
        $("#override_emails").click(function() {
            newOverrideEmails = ( overrideEmails==1 ? 0 : 1 );
            timeOffEmailOverrideHandler.setOverrideEmails( newOverrideEmails );
            timeOffEmailOverrideHandler.warnIfFeatureTurnedOff();
        });
    }
    
    this.handleSubmitEditEmailOverrides = function( selectedButton ) {
        $.ajax({
            url : timeOffEditEmailOverrideSettingsUrl,
            type : 'POST',
            data : {
                CREATED_BY : phpVars.employee_number,
                OVERRIDE_EMAILS: timeOffEmailOverrideHandler.getOverrideEmails(),
                NEW_EMAIL_OVERRIDE_LIST : $("#emailOverrideList").val()
            },
            dataType : 'json'
        })
        .success(function(json) {
            timeOffEmailOverrideHandler.undoWaitStatus( selectedButton );
            return;
        }).error(function(request, status, error) {
            var jsonValue = $.parseJSON( request.responseText );
            alert( jsonValue.message );
            timeOffEmailOverrideHandler.undoWaitStatus( selectedButton );
            return;
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
    
    this.undoWaitStatus = function( selectedButton ) {
        $( '.btn' ).removeClass( 'disabled' ); // Disable all buttons from being selected first.
        selectedButton.blur(); // Click out of button.
        
        // Add a spinning icon and a couple of spaces before the button text.
        selectedButton.html( 'Save settings' );
    }
    
    this.toggleOverrideEmails = function( employeeNumber, type ) {
        $.ajax({
            url : timeOffToggleOverrideEmailsUrl,
            type : 'POST',
            data : {
                EMPLOYEE_NUMBER : employeeNumber,
                TYPE : type
            },
            dataType : 'json'
        }).success(function(json) {
            return;
        }).error(function() {
            console.log('There was an error submitting request to change calendar invites preferences.');
            return;
        });
    }
}

// Initialize the class
timeOffEmailOverrideHandler.initialize();