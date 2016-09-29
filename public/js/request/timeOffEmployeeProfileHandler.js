/**
 * Javascript timeOffProxyHandler 'class'
 *
 */
var timeOffEmployeeProfileHandler = new function ()
{
    var timeOffToggleSendCalendarInvitesUrl = phpVars.basePath + '/api/request/calendar-invite-toggle',
        timeOffGetEmployeeProfileUrl = phpVars.basePath + '/api/request/get-employee-profile',
        sendInvitationsForMyself = 0,
        sendInvitationsForMyReports = 0;

    /**
     * What to run on initialize of this class.
     * 
     * @returns {undefined}
     */
    this.initialize = function() {
        $(document).ready( function() {            
            timeOffEmployeeProfileHandler.getEmployeeProfile( phpVars.employee_number );
            timeOffEmployeeProfileHandler.handleToggleCalendarInvitesToMe();
            timeOffEmployeeProfileHandler.handleToggleCalendarInvitesForReports();
        });
    }
    
    this.handleToggleCalendarInvitesToMe = function() {
        $("#send_cal_inv_me").click(function() {
            timeOffEmployeeProfileHandler.toggleSendCalendarInvites( $(this).data('employee-number'), 'me' );
        });
    }
    
    this.handleToggleCalendarInvitesForReports = function() {
        $("#send_cal_inv_rpt").click(function() {
            timeOffEmployeeProfileHandler.toggleSendCalendarInvites( $(this).data('employee-number'), 'rpt' );
        });
    }
    
    this.toggleSendCalendarInvites = function( employeeNumber, type ) {
        $.ajax({
            url : timeOffToggleSendCalendarInvitesUrl,
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
    
    /**
     * Gets the proxies for passed in Employee Number.
     * 
     * @param {type} employeeNumber
     * @returns {undefined}
     */
    this.getEmployeeProfile = function( employeeNumber ) {
        $.ajax({
            url : timeOffGetEmployeeProfileUrl,
            type : 'POST',
            data: {
                employeeNumber : ((typeof employeeNumber === "string") ? employeeNumber : phpVars.employee_number)
            },
            dataType : 'json'
        })
        .success(function(json) {
            sendInvitationsForMyself = json.sendInvitationsForMyself;
            sendInvitationsForMyReports = json.sendInvitationsForMyReports;
            if( sendInvitationsForMyself==1 ) {
                $( "#send_cal_inv_me").prop('checked', true);
            }
            if( sendInvitationsForMyReports==1 ) {
                $( "#send_cal_inv_rpt").prop('checked', true);
            }
            return;
        }).error(function() {
            console.log('There was some error.');
            return;
        });
    }
    
}

// Initialize the class
timeOffEmployeeProfileHandler.initialize();