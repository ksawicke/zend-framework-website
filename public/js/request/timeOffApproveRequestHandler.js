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
        apiSubmitPayrollSaveChangesToCompletedRequestUrl = phpVars.basePath + '/api/request/payroll-modify-completed',
        apiSubmitPayrollSaveChangesToPayrollCommentUrl = phpVars.basePath + '/api/request/payroll-modify-comment',
        redirectManagerApprovedCompleteUrl = phpVars.basePath + '/request/approved-request',
        redirectManagerDeniedCompleteUrl = phpVars.basePath + '/request/denied-request',
        redirectUpdateChecksQueue = phpVars.basePath + '/request/view-payroll-queue/update-checks',
        redirectCompletedPAFsQueue = phpVars.basePath + '/request/view-payroll-queue/completed-pafs',
        redirectCommentSavedCompleteUrl = phpVars.basePath + '/request/review-request',
        timeOffApproveRequestInUpdateChecksQueue = phpVars.basePath + '/api/request/approve-update-checks-request',
        doRealDelete = false;

    /**
     * Initializes binding
     */
    this.initialize = function() {
        $(document).ready(function () {
            timeOffApproveRequestHandler.handleApiButtonClick();
            timeOffApproveRequestHandler.handleEditPayrollCommentButtonClick();
            timeOffApproveRequestHandler.handleSavePayrollCommentButtonClick();
        });
    }

    /**
     * Enable editing a payroll comment.
     */
    this.handleEditPayrollCommentButtonClick = function() {
      $('body').on('click', '.payrollEditPayrollComment', function() {
        var requestLogId = $(this).attr("data-payroll-request-log-id");
        $('*[data-payroll-comment-wrapper-id="' + requestLogId + '"').toggle();
      });
    }

    /**
     * Handle saving a payroll comment.
     */
    this.handleSavePayrollCommentButtonClick = function() {
      $('body').on('click', '.payrollSavePayrollComment', function() {
        var requestLogId = $(this).attr("data-payroll-request-log-id");
        var data = {
        requestLogId: requestLogId,
        updatedCommentText: $('*[data-payroll-comment-text-id="' + requestLogId + '"').val()
            };
        var requestId = ( !timeOffCommon.empty( $("#requestId").val() ) ? $("#requestId").val() : $(this).attr("data-request-id") );
        var redirectUrl = redirectCommentSavedCompleteUrl + '/' + requestId;

        timeOffApproveRequestHandler.handlePleaseWaitStatus( $(this) );
        timeOffApproveRequestHandler.roundTripAPICall(
            data, apiSubmitPayrollSaveChangesToPayrollCommentUrl, redirectUrl, "Unable to Save Comment." );
      });
    }

    this.handleApiButtonClick = function() {
        $('body').on('click', '.apiRequest', function () {
            var apiaction = $(this).attr("data-apiaction");
            var formDirty = ($("#formDirty").val() == "true"); // Converts from string to boolean
            $.each(selectedDatesNew, function(index, blah) {
              selectedDatesNew[index].dow = moment(selectedDatesNew[index].date, "MM/DD/YYYY").format("ddd").toUpperCase();
            });
            var data = {
                request_id: ( !timeOffCommon.empty( $("#requestId").val() ) ? $("#requestId").val() : $(this).attr("data-request-id") ),
                review_request_reason: $("#reviewRequestReason").val(),
                manager_comment: $("#managerComment").val(),
                payroll_comment: $("#payrollComment").val(),
                formDirty: formDirty,
                selectedDatesNew: selectedDatesNew,
                loggedInUserEmployeeNumber: timeOffCreateRequestHandler.getLoggedInUserEmployeeNumber()
            };

            timeOffApproveRequestHandler.handlePleaseWaitStatus( $(this) );

            switch( apiaction ) {
                case 'managerActionApproveRequest':
                    timeOffApproveRequestHandler.managerActionApproveRequest( data );
                    break;

                case 'managerActionDenyRequest':
                    timeOffApproveRequestHandler.managerActionDenyRequest( data, $(this) );
                    break;

                case 'payrollActionApproveRequest':
                    timeOffApproveRequestHandler.payrollActionApproveRequest( data );
                    break;

                case 'payrollActionDenyRequest':
                    timeOffApproveRequestHandler.payrollActionDenyRequest( data, $(this) );
                    break;


                case 'payrollActionUpload':
                    timeOffApproveRequestHandler.payrollActionUpload( data );
                    break;


                case 'payrollActionUpdateOfficeChecks':
                    timeOffApproveRequestHandler.payrollActionUpdateOfficeChecks( data );
                    break;

                case 'payrollActionCompleteRequest':
                    timeOffApproveRequestHandler.payrollActionCompleteRequest( data );
                    break;

                case 'payrollActionSaveChangesToCompletedRequest':
                  timeOffApproveRequestHandler.payrollActionSaveChangesToCompletedRequest( data, $(this) );
                  break;
            }
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
        selectedButton.blur(); // Click out of button.

        // Add a spinning icon and a couple of spaces before the button text.
        selectedButton.prepend( '<i class="glyphicon glyphicon-refresh gly-spin"></i>&nbsp;&nbsp;' );
    }

    this.stopPleaseWaitStatus = function( selectedButton, buttonText ) {
        $( '.btn' ).removeClass( 'disabled' ); // Disable all buttons from being selected first.
        selectedButton.blur(); // Click out of button.

        // Remove spinning icon
        selectedButton.html( buttonText );
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
    this.managerActionDenyRequest = function( data, selectedButton ) {
        if ($.trim($("#managerComment").val()) == '') {
            $("#noCommentEnteredWarning").removeClass("hidden");
            $("#managerComment").addClass("borderColorRed");
            /* enable DENY button */
            timeOffApproveRequestHandler.stopPleaseWaitStatus( selectedButton, 'Deny' );
        } else {
            $("#noCommentEnteredWarning").addClass("hidden");
            $("#managerComment").removeClass("borderColorRed");

            timeOffApproveRequestHandler.roundTripAPICall(
                    data, apiSubmitManagerDeniedUrl, redirectManagerDeniedCompleteUrl, "Unable to Deny Request." );

        }
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
    this.payrollActionDenyRequest = function( data, selectedButton ) {
        if ($.trim($("#payrollComment").val()) == '') {
            $("#noCommentEnteredWarning").removeClass("hidden");
            $("#payrollComment").addClass("borderColorRed");
            /* enable DENY button */
            timeOffApproveRequestHandler.stopPleaseWaitStatus( selectedButton, 'Deny' );
        } else {
            $("#noCommentEnteredWarning").addClass("hidden");
            $("#payrollComment").removeClass("borderColorRed");
            timeOffApproveRequestHandler.roundTripAPICall(
                data, apiSubmitPayrollDeniedUrl, redirectManagerApprovedCompleteUrl, "Unable to Deny Request." );
        }
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
                window.location.href = phpVars.basePath + successUrl;
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

    this.payrollActionCompleteRequest = function( data ) {
        timeOffApproveRequestHandler.roundTripAPICall(
            data, timeOffApproveRequestInUpdateChecksQueue, redirectUpdateChecksQueue, "Unable to mark as Completed PAF." );
    }

    this.payrollActionSaveChangesToCompletedRequest = function( data, selectedButton ) {
      if ($.trim($("#payrollComment").val()) == '') {
            $("#noCommentEnteredWarning").removeClass("hidden");
            $("#payrollComment").addClass("borderColorRed");
            /* enable DENY button */
            timeOffApproveRequestHandler.stopPleaseWaitStatus( selectedButton, 'Save' );
        } else {
            $("#noCommentEnteredWarning").addClass("hidden");
            $("#payrollComment").removeClass("borderColorRed");

            timeOffApproveRequestHandler.roundTripAPICall(
                    data, apiSubmitPayrollSaveChangesToCompletedRequestUrl, redirectCompletedPAFsQueue, "Unable to update this Completed PAF." );
        }
    }
};

//Initialize the class
timeOffApproveRequestHandler.initialize();