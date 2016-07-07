/**
 * Javascript timeOffProxyHandler 'class'
 *
 */
var timeOffEmailOverrideHandler = new function ()
{
    var timeOffGetEmailOverrideListUrl = phpVars.basePath + '/api/request/get-email-override-list',
        timeOffEditEmailOverrideListUrl = phpVars.basePath + '/api/request/edit-email-override-list';

    /**
     * What to run on initialize of this class.
     * 
     * @returns {undefined}
     */
    this.initialize = function() {
        $(document).ready( function() {            
            timeOffEmailOverrideHandler.getEmailOverrideList();
            $(document).on('click', '.submitEditEmailOverrides', function() {
                timeOffEmailOverrideHandler.handlePleaseWaitStatus( $(this) );
                timeOffEmailOverrideHandler.handleSubmitEditEmailOverrides( $(this) );
            });
        });
    }
    
    /**
     * Gets the current list for email overrides.
     * 
     * @returns {undefined}
     */
    this.getEmailOverrideList = function() {
        $.ajax({
            url : timeOffGetEmailOverrideListUrl,
            type : 'POST',
            dataType : 'json'
        })
        .success(function(json) {
            $("#emailOverrideList").val( json.emailOverrideList );
            return;
        }).error(function() {
            console.log('There was an error loading the email override list.');
            return;
        });
    }
    
    this.handleSubmitEditEmailOverrides = function( selectedButton ) {
        $.ajax({
            url : timeOffEditEmailOverrideListUrl,
            type : 'POST',
            data : {
                CREATED_BY : phpVars.employee_number,
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
}

// Initialize the class
timeOffEmailOverrideHandler.initialize();