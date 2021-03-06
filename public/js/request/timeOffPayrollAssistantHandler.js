/**
 * Javascript timeOffProxyHandler 'class'
 *
 */
var timeOffPayrollAssistantHandler = new function ()
{
    var timeOffPayrollAssistantSearchUrl = phpVars.basePath + '/api/search/payroll-assistants',
        timeOffGetPayrollAssistantsUrl = phpVars.basePath + '/api/payroll-assistants/get',
        timeOffAddPayrollAssistantUrl = phpVars.basePath + '/api/payroll-assistant',
        timeOffRemovePayrollAssistantUrl = phpVars.basePath + '/api/payroll-assistant/delete',
        timeOffTogglePayrollAssistantUrl = phpVars.basePath + '/api/payroll-assistant/toggle',
        selectedPayrollAssistantEmployeeNumber = null;

    /**
     * What to run on initialize of this class.
     * 
     * @returns {undefined}
     */
    this.initialize = function() {
        $(document).ready( function() {            
            var $eventLog = $(".js-event-log");
            var $requestForEventSelect = $("#requestFor");
            /**
             * When we change the for dropdown using select2,
             * set the employee number and name as a local variable
             * for form submission, and refresh the calendars.
             */
            $requestForEventSelect.on("select2:select", function(e) {
                var selectedEmployee = e.params.data;
                selectedPayrollAssistantEmployeeNumber = selectedEmployee.id;
            }).on("select2:open", function(e) {
                /**
                 * SELECT2 is opened
                 */
            }).on("select2:close", function(e) {
                /**
                 * SELECT2 is closed
                 */
            });
            
            $("#requestFor").prop('disabled', false);

            $requestForEventSelect.select2({
                ajax : {
                    url : timeOffPayrollAssistantSearchUrl,
                    method : 'post',
                    dataType : 'json',
                    delay : 250,
                    data : function(params) {
                        return {
                        search : params.term,
//                                directReportFilter : directReportFilter,
                                employeeNumber : phpVars.employee_number,
                                page : params.page
                        };
                    },
                    processResults : function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results : data,
                            pagination : {
                            more : (params.page * 30) < data.total_count
                            }
                        };
                    },
                },
                allowClear : true,
                minimumInputLength : 2,
            });
            
            $(document).on('click', '.submitAddPayrollAssistantRequest', function() {
                if( timeOffCommon.empty(selectedPayrollAssistantEmployeeNumber)===false ) {
                    timeOffPayrollAssistantHandler.handleAddPayrollAssistant();
                } else {
                    $("#dialogSelectAPayrollAssistant").dialog({
                        modal : true,
                        closeOnEscape: false,
                        buttons : {
                            OK : function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
            
            $(document).on('click', '.remove-payroll-assistant', function() {
                timeOffPayrollAssistantHandler.handleRemovePayrollAssistant( $(this).data('payroll-assistant-employee-number') );
            });
            
            $(document).on('click', '.clearPayrollAssistantRequest', function() {
                $("#requestFor").select2("val", "");
            });
            
            $(document).on('click', '.cmn-toggle', function() {
                timeOffPayrollAssistantHandler.handleTogglePayrollAssistant( $(this).data('payroll-assistant-employee-number'), $(this).data('status') );
            });
            
            timeOffPayrollAssistantHandler.getPayrollAssistants( phpVars.employee_number );
        });
    }
    
    this.handleAddPayrollAssistant = function() {
        $.ajax({
            url : timeOffAddPayrollAssistantUrl,
            type : 'POST',
            data : {
                CREATED_BY : phpVars.employee_number,
                EMPLOYEE_NUMBER : selectedPayrollAssistantEmployeeNumber
            },
            dataType : 'json'
        }).success(function(json) {
            if (json.success == true) {
                timeOffPayrollAssistantHandler.reloadPayrollAssistants();
            } else {
                alert(json.message);
            }
            return;
        }).error(function() {
            console.log('There was an error submitting request to add a Payroll Assistant.');
            return;
        });
    }
    
    this.handleRemovePayrollAssistant = function( selectedPayrollAssistantEmployeeNumber ) {
        $.ajax({
            url : timeOffRemovePayrollAssistantUrl,
            type : 'POST',
            data : {
                EMPLOYEE_NUMBER : phpVars.employee_number,
                PAYROLLASSISTANT_EMPLOYEE_NUMBER : selectedPayrollAssistantEmployeeNumber
            },
            dataType : 'json'
        }).success(function(json) {
            timeOffPayrollAssistantHandler.reloadPayrollAssistants();
            return;
        }).error(function() {
            console.log('There was an error submitting request to add a payroll assistant.');
            return;
        });
    }
    
    this.handleTogglePayrollAssistant = function( selectedPayrollAssistantEmployeeNumber, status ) {
        $.ajax({
            url : timeOffTogglePayrollAssistantUrl,
            type : 'POST',
            data : {
                PAYROLLASSISTANT_EMPLOYEE_NUMBER : selectedPayrollAssistantEmployeeNumber,
                STATUS : ( status=='1' ? '0' : '1' )
            },
            dataType : 'json'
        }).success(function(json) {
            return;
        }).error(function() {
            console.log('There was an error submitting request to toggle a payroll assistant.');
            return;
        });
    }
    
    /**
     * Gets the proxies for passed in Employee Number.
     * 
     * @param {type} employeeNumber
     * @returns {undefined}
     */
    this.getPayrollAssistants = function( employeeNumber ) {
        $('#payrollassistant-list').DataTable({
            dom: 'ltirp',
            searching: false,
            processing: true,
            serverSide: true,
            oLanguage: {
                sProcessing: "<img src='" + phpVars.basePath +  "/img/loading/clock.gif'>"
            },
            columns: [
                {"data": "EMPLOYEE_DESCRIPTION"},
                {"data": "STATUS"},
                {"data": "ACTIONS"}
            ],
            order: [],
//            columnDefs: [{"orderable": false,
//                    "targets": [1, 2, 3]
//                }
//            ],
            ajax: {
                url: timeOffGetPayrollAssistantsUrl,
                data: function (d) {
                    return $.extend({}, d, {
                        "employeeNumber": employeeNumber
                    });
                },
                type: "POST",
            }
        })
        .on("error.dt", function (e, settings, techNote, message) {
            console.log("An error has been reported by DataTables: ", message);
        });
    }
    
    /**
     * Reload datatable for proxies
     */
    this.reloadPayrollAssistants = function() {
        $("#payrollassistant-list").DataTable().ajax.reload( function() {} );
        $("#requestFor").select2("val", "");
    }
    
    this.resetErrors = function() {
        $("#warnNoPayrollAssistantsSelected").hide();
        $("#warnErrorLoadingPayrollAssistants").hide();
    }
    
}

// Initialize the class
timeOffPayrollAssistantHandler.initialize();