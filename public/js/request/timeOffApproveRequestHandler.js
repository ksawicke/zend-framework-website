/**
 * Javascript timeOffQueueHandler 'class'
 *
 */
var timeOffApproveRequestHandler = new function ()
{
    var apiSubmitManagerApprovedUrl = phpVars.basePath + '/api/request/manager-approved',
        apiSubmitManagerDeniedUrl = phpVars.basePath + '/api/request/manager-denied',
        apiSubmitPayrollApprovedUrl = phpVars.basePath + '/api/request/payroll-approved',
        apiSubmitPayrollDeniedUrl = phpVars.basePath + '/api/request/payroll-denied',
        apiSubmitPayrollUploadUrl = phpVars.basePath + '/api/request/payroll-upload',
        apiSubmitPayrollUpdateChecksUrl = phpVars.basePath + '/api/request/payroll-update-checks',
        redirectManagerApprovedCompleteUrl = phpVars.basePath + '/request/approved-request',
        redirectManagerDeniedCompleteUrl = phpVars.basePath + '/request/denied-request';

    /**
     * Initializes binding
     */
    this.initialize = function() {
        $(document).ready(function () {
            timeOffApproveRequestHandler.handleApiButtonClick();
        });
    }

    this.handleApiButtonClick = function() {
        $('body').on('click', '.apiRequest', function () {
            var apiaction = $(this).attr("data-apiaction");
            var formDirty = $("#formDirty").val();
            var data = {
                request_id: $("#requestId").val(),
                review_request_reason: $("#reviewRequestReason").val(),
                formDirty: formDirty,
                selectedDatesNew: selectedDatesNew
            };
            
            switch( apiaction ) {
                case 'managerActionApproveRequest':
                    timeOffApproveRequestHandler.managerActionApproveRequest( data );
                    break;
                    
                case 'managerActionDenyRequest':
                    timeOffApproveRequestHandler.managerActionDenyRequest( data );
                    break;
                    
                case 'payrollActionApproveRequest':
                    timeOffApproveRequestHandler.payrollActionApproveRequest( data );
                    break;
                    
                case 'payrollActionDenyRequest':
                    timeOffApproveRequestHandler.payrollActionDenyRequest( data );
                    break;
                    
                    
                case 'payrollActionUpload':
                    timeOffApproveRequestHandler.payrollActionUpload( data );
                    break;
                    
                    
                case 'payrollActionUpdateOfficeChecks':
                    timeOffApproveRequestHandler.payrollActionUpdateOfficeChecks( data );
                    break;
            }
        });
    }

    /**
     * Submits Manager approval response to API.
     * 
     * @returns {undefined}
     */
    this.managerActionApproveRequest = function( data ) {
        timeOffApproveRequestHandler.roundTripAPICall(
            data, apiSubmitManagerApprovedUrl, redirectManagerApprovedCompleteUrl, "Unable to Approve Request." );
    }

    /**
     * Submits Manager deny response to API.
     * 
     * @returns {undefined}
     */
    this.managerActionDenyRequest = function( data ) {
        timeOffApproveRequestHandler.roundTripAPICall(
            data, apiSubmitManagerDeniedUrl, redirectManagerDeniedCompleteUrl, "Unable to Deny Request." );
    }
    
    /**
     * Submits Payroll approval response to API.
     * 
     * @returns {undefined}
     */
    this.payrollActionApproveRequest = function( data ) {
        timeOffApproveRequestHandler.roundTripAPICall(
            data, apiSubmitPayrollApprovedUrl, redirectManagerApprovedCompleteUrl, "Unable to Approve Request." );
    }
    
    /**
     * Submits Payroll denied response to API.
     * 
     * @returns {undefined}
     */
    this.payrollActionDenyRequest = function( data ) {
        timeOffApproveRequestHandler.roundTripAPICall(
            data, apiSubmitPayrollDeniedUrl, redirectManagerApprovedCompleteUrl, "Unable to Deny Request." );
    }
    
    /**
     * Submits Payroll upload response to API.
     * 
     * @returns {undefined}
     */
    this.payrollActionUpload = function( data ) {
        timeOffApproveRequestHandler.roundTripAPICall(
            data, apiSubmitPayrollUploadUrl, redirectManagerApprovedCompleteUrl, "Unable to Upload Request." );
    }
    
    /**
     * Submits Payroll update checks response to API.
     * 
     * @returns {undefined}
     */
    this.payrollActionUpdateOfficeChecks = function( data ) {
        timeOffApproveRequestHandler.roundTripAPICall(
            data, apiSubmitPayrollUpdateChecksUrl, redirectManagerApprovedCompleteUrl, "Unable to mark as Update Checks." );
    }
    
    this.roundTripAPICall = function( data, initialUrl, successUrl, errorMessage ) {
        $.ajax({
            url: initialUrl,
            type: 'POST',
            data: data,
            dataType: 'json'
        })
        .success(function (json) {
            if (json.success == true) {
                window.location.href = successUrl;
            } else {
                alert(json.message);
            }
            return;
        })
        .error(function () {
            console.log( errorMessage );
            return;
        });
    }
};

//Initialize the class
timeOffApproveRequestHandler.initialize();