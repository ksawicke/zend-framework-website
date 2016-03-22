/**
 * Javascript timeOffQueueHandler 'class'
 *
 */
var timeOffApproveRequestHandler = new function ()
{
    var timeOffApiSubmitApprovalUrl = phpVars.basePath + '/api/request/approve',
        timeOffApiSubmitDenyUrl = phpVars.basePath + '/api/request/deny',
        timeOffApprovedRequestSuccessUrl = phpVars.basePath + '/request/approved-request',
        timeOffDeniedRequestSuccessUrl = phpVars.basePath + '/request/denied-request';

    /**
     * Initializes binding
     */
    this.initialize = function () {
        $(document).ready(function () {

            $(document).on('click', '.approveTimeOffRequest', function () {
                timeOffApproveRequestHandler.approveTimeOffRequest();
            });

            $(document).on('click', '.denyTimeOffRequest', function () {
                requestReason = $("#requestReason").val();
                timeOffApproveRequestHandler.denyTimeOffRequest();
            });

        });
    }

    this.approveTimeOffRequest = function () {
        timeOffApproveRequestHandler.submitApprovalResponse('submitApprovalResponse');
    }

    this.denyTimeOffRequest = function () {
        timeOffApproveRequestHandler.submitApprovalResponse('submitDenyResponse');
    }

    this.submitApprovalResponse = function (action) {
        url = timeOffApiSubmitApprovalUrl;
        if( action==='submitDenyResponse' ) {
            url = timeOffApiSubmitDenyUrl;
        }
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                request_id: $("#requestId").val(),
                review_request_reason: $("#reviewRequestReason").val()
            },
            dataType: 'json'
        })
        .success(function (json) {
            if (json.success == true) {
                console.log(action);
                if (action === 'submitApprovalResponse') {
                    window.location.href = timeOffApprovedRequestSuccessUrl;
                }
                if (action === 'submitDenyResponse') {
                    window.location.href = timeOffDeniedRequestSuccessUrl;
                }
            } else {
                alert(json.message);
            }
            return;
        })
        .error(function () {
            console.log('There was some error.');
            return;
        });
    };
};

//Initialize the class
timeOffApproveRequestHandler.initialize();