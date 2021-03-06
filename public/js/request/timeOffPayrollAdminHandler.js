/**
 * Javascript timeOffProxyHandler 'class'
 *
 */
var timeOffPayrollAdminHandler = new function ()
{
    var timeOffPayrollAdminSearchUrl = phpVars.basePath + '/api/search/payroll-admins',
        timeOffGetPayrollAdminsUrl = phpVars.basePath + '/api/payroll-admins/get',
        timeOffAddPayrollAdminUrl = phpVars.basePath + '/api/payroll-admin',
        timeOffRemovePayrollAdminUrl = phpVars.basePath + '/api/payroll-admin/delete',
        timeOffTogglePayrollAdminUrl = phpVars.basePath + '/api/payroll-admin/toggle',
        selectedPayrollAdminEmployeeNumber = null;

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
                selectedPayrollAdminEmployeeNumber = selectedEmployee.id;
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
                    url : timeOffPayrollAdminSearchUrl,
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
            
            $(document).on('click', '.submitAddPayrollAdminRequest', function() {
                if( timeOffCommon.empty(selectedPayrollAdminEmployeeNumber)===false ) {
                    timeOffPayrollAdminHandler.handleAddPayrollAdmin();
                } else {
                    $("#dialogSelectAPayrollAdmin").dialog({
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
            
            $(document).on('click', '.remove-payroll-admin', function() {
                timeOffPayrollAdminHandler.handleRemovePayrollAdmin( $(this).data('payroll-admin-employee-number') );
            });
            
            $(document).on('click', '.clearPayrollAdminRequest', function() {
                $("#requestFor").select2("val", "");
            });
            
            $(document).on('click', '.cmn-toggle', function() {
                timeOffPayrollAdminHandler.handleTogglePayrollAdmin( $(this).data('payroll-admin-employee-number'), $(this).data('status') );
            });
            
            timeOffPayrollAdminHandler.getPayrollAdmins( phpVars.employee_number );
        });
    }
    
    this.handleAddPayrollAdmin = function() {
        $.ajax({
            url : timeOffAddPayrollAdminUrl,
            type : 'POST',
            data : {
                CREATED_BY : phpVars.employee_number,
                EMPLOYEE_NUMBER : selectedPayrollAdminEmployeeNumber
            },
            dataType : 'json'
        }).success(function(json) {
            if (json.success == true) {
                timeOffPayrollAdminHandler.reloadPayrollAdmins();
            } else {
                alert(json.message);
            }
            return;
        }).error(function() {
            console.log('There was an error submitting request to add a Payroll Admin.');
            return;
        });
    }
    
    this.handleRemovePayrollAdmin = function( selectedPayrollAdminEmployeeNumber ) {
        $.ajax({
            url : timeOffRemovePayrollAdminUrl,
            type : 'POST',
            data : {
                EMPLOYEE_NUMBER : phpVars.employee_number,
                PAYROLLADMIN_EMPLOYEE_NUMBER : selectedPayrollAdminEmployeeNumber
            },
            dataType : 'json'
        }).success(function(json) {
            timeOffPayrollAdminHandler.reloadPayrollAdmins();
            return;
        }).error(function() {
            console.log('There was an error submitting request to remove a Payroll Admin.');
            return;
        });
    }
    
    this.handleTogglePayrollAdmin = function( selectedPayrollAdminEmployeeNumber, status ) {
        $.ajax({
            url : timeOffTogglePayrollAdminUrl,
            type : 'POST',
            data : {
                PAYROLLADMIN_EMPLOYEE_NUMBER : selectedPayrollAdminEmployeeNumber,
                STATUS : ( status=='1' ? '0' : '1' )
            },
            dataType : 'json'
        }).success(function(json) {
            return;
        }).error(function() {
            console.log('There was an error submitting request to toggle a Payroll Admin.');
            return;
        });
    }
    
    /**
     * Gets the proxies for passed in Employee Number.
     * 
     * @param {type} employeeNumber
     * @returns {undefined}
     */
    this.getPayrollAdmins = function( employeeNumber ) {
        $('#payrolladmin-list').DataTable({
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
                url: timeOffGetPayrollAdminsUrl,
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
    this.reloadPayrollAdmins = function() {
        $("#payrolladmin-list").DataTable().ajax.reload( function() {} );
        $("#requestFor").select2("val", "");
    }
    
    this.resetErrors = function() {
        $("#warnNoPayrollAdminsSelected").hide();
        $("#warnErrorLoadingPayrollAdmins").hide();
    }
    
}

// Initialize the class
timeOffPayrollAdminHandler.initialize();