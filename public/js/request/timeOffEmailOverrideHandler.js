/**
 * Javascript timeOffProxyHandler 'class'
 *
 */
var timeOffEmailOverrideHandler = new function ()
{
    var timeOffGetEmailOverrideListUrl = phpVars.basePath + '/api/request/get-email-override-list';

    /**
     * What to run on initialize of this class.
     * 
     * @returns {undefined}
     */
    this.initialize = function() {
        $(document).ready( function() {            
            timeOffEmailOverrideHandler.getEmailOverrideList();
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
            console.log( json );
            return;
        }).error(function() {
            console.log('There was an error loading the email override list.');
            return;
        });
    }
}

// Initialize the class
timeOffEmailOverrideHandler.initialize();