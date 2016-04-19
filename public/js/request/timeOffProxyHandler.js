/**
 * Javascript timeOffProxyHandler 'class'
 *
 */
var timeOffProxyHandler = new function ()
{
    var timeOffProxySearchUrl = phpVars.basePath + '/api/search/proxies',
        timeOffGetProxiesUrl = phpVars.basePath + '/api/proxy/get',
        timeOffAddProxyUrl = phpVars.basePath + '/api/proxy',
        timeOffRemoveProxyUrl = phpVars.basePath + '/api/proxy/delete',
        timeOffToggleProxyUrl = phpVars.basePath + '/api/proxy/toggle',
        selectedProxyEmployeeNumber = null;

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
//                timeOffCreateRequestHandler.resetCategorySelection();
                var selectedEmployee = e.params.data;
                selectedProxyEmployeeNumber = selectedEmployee.id;
                
//                requestForEmployeeName = selectedEmployee.text;
//                timeOffCreateRequestHandler.loadCalendars(requestForEmployeeNumber);
//                $('.requestIsForMe').show();
            }).on("select2:open", function(e) {
                /**
                 * SELECT2 is opened
                 */
//                if (loggedInUserData.IS_LOGGED_IN_USER_PAYROLL === "N") {
//                    $("span").remove(".select2CustomTag");
//                    var $filter = '<form id="directReportForm" style="display:inline-block;padding 5px;">'
//                        + '<input type="radio" name="directReportFilter" value="B"'
//                        + ((directReportFilter === 'B') ? ' checked'
//                            : '')
//                        + '> Both&nbsp;&nbsp;&nbsp;'
//                        + '<input type="radio" name="directReportFilter" value="D"'
//                        + ((directReportFilter === 'D') ? ' checked'
//                            : '')
//                        + '> Direct Reports&nbsp;&nbsp;&nbsp;'
//                        + '<input type="radio" name="directReportFilter" value="I"'
//                        + ((directReportFilter === 'I') ? ' checked'
//                            : '')
//                        + '> Indirect Reports&nbsp;&nbsp;&nbsp;'
//                        + '</form>';
//                    $("<span class='select2CustomTag' style='padding-left:6px;'>"
//                        + $filter
//                        + "</span>")
//                    .insertBefore('.select2-results');
//                }
            }).on("select2:close", function(e) {
                /**
                 * SELECT2 is closed
                 */
            });
            
            $("#requestFor").prop('disabled', false);
//            $("#requestFor").empty().append(
//                '<option value="SELECT PROXY HERE</option>').val('229589').trigger('change');
            
            $requestForEventSelect.select2({
                ajax : {
                    url : timeOffProxySearchUrl,
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
            
            $(document).on('click', '.submitAddProxyRequest', function() {
                if( timeOffCommon.empty(selectedProxyEmployeeNumber)===false ) {
                    timeOffProxyHandler.handleAddProxy();
                } else {
                    $("#dialogSelectAProxy").dialog({
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
            
            $(document).on('click', '.remove-proxy', function() {
                timeOffProxyHandler.handleRemoveProxy( $(this).data('proxy-employee-number') );
            });
            
            $(document).on('click', '.clearProxyRequest', function() {
                $("#requestFor").select2("val", "");
            });
            
            $(document).on('click', '.cmn-toggle', function() {
                timeOffProxyHandler.handleToggleProxy( $(this).data('proxy-employee-number'), $(this).data('status') );
            });
            
            timeOffProxyHandler.getProxies( phpVars.employee_number );
        });
    }
    
    this.handleAddProxy = function() {
        $.ajax({
            url : timeOffAddProxyUrl,
            type : 'POST',
            data : {
                EMPLOYEE_NUMBER : phpVars.employee_number,
                PROXY_EMPLOYEE_NUMBER : selectedProxyEmployeeNumber
            },
            dataType : 'json'
        }).success(function(json) {
            if (json.success == true) {
                timeOffProxyHandler.reloadProxies();
            } else {
                alert(json.message);
            }
            return;
        }).error(function() {
            console.log('There was an error submitting request to add a proxy.');
            return;
        });
    }
    
    this.handleRemoveProxy = function( selectedProxyEmployeeNumber ) {
        $.ajax({
            url : timeOffRemoveProxyUrl,
            type : 'POST',
            data : {
                EMPLOYEE_NUMBER : phpVars.employee_number,
                PROXY_EMPLOYEE_NUMBER : selectedProxyEmployeeNumber
            },
            dataType : 'json'
        }).success(function(json) {
            timeOffProxyHandler.reloadProxies();
            return;
        }).error(function() {
            console.log('There was an error submitting request to add a proxy.');
            return;
        });
    }
    
    this.handleToggleProxy = function( selectedProxyEmployeeNumber, status ) {
        $.ajax({
            url : timeOffToggleProxyUrl,
            type : 'POST',
            data : {
                EMPLOYEE_NUMBER : phpVars.employee_number,
                PROXY_EMPLOYEE_NUMBER : selectedProxyEmployeeNumber,
                STATUS : ( status==1 ? 0 : 1 )
            },
            dataType : 'json'
        }).success(function(json) {
            return;
        }).error(function() {
            console.log('There was an error submitting request to toggle a proxy.');
            return;
        });
    }
    
    /**
     * Gets the proxies for passed in Employee Number.
     * 
     * @param {type} employeeNumber
     * @returns {undefined}
     */
    this.getProxies = function( employeeNumber ) {
        $('#proxy-list').DataTable({
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
                url: timeOffGetProxiesUrl,
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
    this.reloadProxies = function() {
        $("#proxy-list").DataTable().ajax.reload( function() {} );
        $("#requestFor").select2("val", "");
    }
    
    this.resetErrors = function() {
        $("#warnNoProxiesSelected").hide();
        $("#warnErrorLoadingProxies").hide();
    }
    
}

// Initialize the class
timeOffProxyHandler.initialize();