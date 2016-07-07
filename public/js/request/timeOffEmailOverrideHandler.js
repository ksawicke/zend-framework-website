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
                timeOffEmailOverrideHandler.handleSubmitEditEmailOverrides();
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
    
    this.handleSubmitEditEmailOverrides = function() {
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
            console.log( "edited email override list" );
            return;
        }).error(function() {
            console.log('There was an error loading the email override list.');
            return;
        });
    }
}

// Initialize the class
timeOffEmailOverrideHandler.initialize();