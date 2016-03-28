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
    this.initialize = function () {
        $(document).ready(function () {
            timeOffApproveRequestHandler.handleApiButtonClick();
        });
    }

    this.handleApiButtonClick = function () {
        $('body').on('click', '.apiRequest', function () {
            var apiaction = $(this).attr("data-apiaction");
            switch( apiaction ) {
                case 'managerActionApproveRequest':
                    timeOffApproveRequestHandler.managerActionApproveRequest();
                    break;
                    
                case 'managerActionDenyRequest':
                    timeOffApproveRequestHandler.managerActionDenyRequest();
                    break;
                    
                case 'payrollActionApproveRequest':
                    timeOffApproveRequestHandler.payrollActionApproveRequest();
                    break;
                    
                case 'payrollActionDenyRequest':
                    timeOffApproveRequestHandler.payrollActionDenyRequest();
                    break;
                    
                    
                case 'payrollActionUpload':
                    timeOffApproveRequestHandler.payrollActionUpload();
                    break;
                    
                    
                case 'payrollActionUpdateOfficeChecks':
                    timeOffApproveRequestHandler.payrollActionUpdateOfficeChecks();
                    break;
            }
        });
    }

    /**
     * Submits Manager approval response to API.
     * 
     * @returns {undefined}
     */
    this.managerActionApproveRequest = function () {
        $.ajax({
            url: apiSubmitManagerApprovedUrl,
            type: 'POST',
            data: {
                request_id: $("#requestId").val(),
                review_request_reason: $("#reviewRequestReason").val()
            },
            dataType: 'json'
        })
        .success(function (json) {
            if (json.success == true) {
                window.location.href = redirectManagerApprovedCompleteUrl;
            } else {
                alert(json.message);
            }
            return;
        })
        .error(function () {
            console.log('There was some error.');
            return;
        });
    }

    /**
     * Submits Manager deny response to API.
     * 
     * @returns {undefined}
     */
    this.managerActionDenyRequest = function () {
        $.ajax({
            url: apiSubmitManagerDeniedUrl,
            type: 'POST',
            data: {
                request_id: $("#requestId").val(),
                review_request_reason: $("#reviewRequestReason").val()
            },
            dataType: 'json'
        })
        .success(function (json) {
            if (json.success == true) {
                window.location.href = redirectManagerDeniedCompleteUrl;
            } else {
                alert(json.message);
            }
            return;
        })
        .error(function () {
            console.log('There was some error.');
            return;
        });
    }
    
    /**
     * Submits Payroll approval response to API.
     * 
     * @returns {undefined}
     */
    this.payrollActionApproveRequest = function () {
        $.ajax({
            url: apiSubmitPayrollApprovedUrl,
            type: 'POST',
            data: {
                request_id: $("#requestId").val(),
                review_request_reason: $("#reviewRequestReason").val()
            },
            dataType: 'json'
        })
        .success(function (json) {
            if (json.success == true) {
                window.location.href = redirectManagerApprovedCompleteUrl;
            } else {
                alert(json.message);
            }
            return;
        })
        .error(function () {
            console.log('There was some error.');
            return;
        });
    }
    
    /**
     * Submits Payroll denied response to API.
     * 
     * @returns {undefined}
     */
    this.payrollActionDenyRequest = function () {
        $.ajax({
            url: apiSubmitPayrollDeniedUrl,
            type: 'POST',
            data: {
                request_id: $("#requestId").val(),
                review_request_reason: $("#reviewRequestReason").val()
            },
            dataType: 'json'
        })
        .success(function (json) {
            if (json.success == true) {
                window.location.href = redirectManagerApprovedCompleteUrl;
            } else {
                alert(json.message);
            }
            return;
        })
        .error(function () {
            console.log('There was some error.');
            return;
        });
    }
    
    /**
     * Submits Payroll upload response to API.
     * 
     * @returns {undefined}
     */
    this.payrollActionUpload = function () {
        $.ajax({
            url: apiSubmitPayrollUploadUrl,
            type: 'POST',
            data: {
                request_id: $("#requestId").val(),
                review_request_reason: $("#reviewRequestReason").val()
            },
            dataType: 'json'
        })
        .success(function (json) {
            if (json.success == true) {
                window.location.href = redirectManagerApprovedCompleteUrl;
            } else {
                alert(json.message);
            }
            return;
        })
        .error(function () {
            console.log('There was some error.');
            return;
        });
    }
    
    /**
     * Submits Payroll update checks response to API.
     * 
     * @returns {undefined}
     */
    this.payrollActionUpdateOfficeChecks = function () {
        $.ajax({
            url: apiSubmitPayrollUpdateChecksUrl,
            type: 'POST',
            data: {
                request_id: $("#requestId").val(),
                review_request_reason: $("#reviewRequestReason").val()
            },
            dataType: 'json'
        })
        .success(function (json) {
            if (json.success == true) {
                window.location.href = redirectManagerApprovedCompleteUrl;
            } else {
                alert(json.message);
            }
            return;
        })
        .error(function () {
            console.log('There was some error.');
            return;
        });
    }
};

//Initialize the class
timeOffApproveRequestHandler.initialize();