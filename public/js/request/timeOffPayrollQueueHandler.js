/**
 * Javascript timeOffQueueHandler 'class'
 *
 */
var timeOffPayrollQueueHandler = new function ()
{
    /**
     * Initializes binding
     */
    this.initialize = function () {
        $(document).ready(function () {
            timeOffPayrollQueueHandler.handleLoadingPendingPayrollQueue();
            timeOffPayrollQueueHandler.handleLoadingUpdateChecksQueue();
            timeOffPayrollQueueHandler.handleLoadingCompletedPAFsQueue();
            timeOffPayrollQueueHandler.handleLoadingPendingAS400UploadQueue();
            timeOffPayrollQueueHandler.handleLoadingDeniedQueue();
            timeOffPayrollQueueHandler.handleLoadingByStatusQueue();
            timeOffPayrollQueueHandler.handleLoadingManagerActionQueue();
        });
    }
    
    /**
     * Loads the Pending Payroll Queue.
     * 
     * @returns {undefined}
     */
    this.handleLoadingPendingPayrollQueue = function () {
        $('#payroll-queue-pending-payroll-approval').DataTable({
            dom: 'fltirp',
            searching: true,
            processing: true,
            serverSide: true,
            oLanguage: {
                sProcessing: "<img src='" + phpVars.basePath + "/img/loading/clock.gif'>"
            },
            columns: [
                {"data": "EMPLOYEE_DESCRIPTION"},
                {"data": "APPROVER_QUEUE"},
                {"data": "REQUEST_STATUS_DESCRIPTION"},
                {"data": "REQUESTED_HOURS"},
                {"data": "REQUEST_REASON"},
                {"data": "MIN_DATE_REQUESTED"},
                {"data": "ACTIONS"}
            ],
            order: [],
            columnDefs: [{"orderable": false,
                    "targets": [1, 2, 3, 4, 6]
                }
            ],
            ajax: {
                url: phpVars.basePath + "/api/queue/payroll/pending-payroll-approval",
                data: function (d) {
                    return $.extend({}, d, {
                        "employeeNumber": phpVars.employee_number
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
     * Loads the Update Checks Queue.
     * 
     * @returns {undefined}
     */
    this.handleLoadingUpdateChecksQueue = function () {
        $('#payroll-queue-update-checks').DataTable({
            dom: 'fltirp',
            searching: true,
            processing: true,
            serverSide: true,
            oLanguage: {
                sProcessing: "<img src='" + phpVars.basePath + "/img/loading/clock.gif'>"
            },
            columns: [
                {"data": "EMPLOYEE_DESCRIPTION"},
                {"data": "APPROVER_QUEUE"},
                {"data": "REQUEST_STATUS_DESCRIPTION"},
                {"data": "REQUESTED_HOURS"},
                {"data": "REQUEST_REASON"},
                {"data": "MIN_DATE_REQUESTED"},
                {"data": "ACTIONS"}
            ],
            order: [],
            columnDefs: [{"orderable": false,
                    "targets": [1, 2, 3, 4, 6]
                }
            ],
            ajax: {
                url: phpVars.basePath + "/api/queue/payroll/update-checks",
                data: function (d) {
                    return $.extend({}, d, {
                        "employeeNumber": phpVars.employee_number
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
     * Loads the Completed PAFs Queue.
     * 
     * @returns {undefined}
     */
    this.handleLoadingCompletedPAFsQueue = function () {
        $('#payroll-queue-completed-pafs').DataTable({
            dom: 'fltirp',
            searching: true,
            processing: true,
            serverSide: true,
            oLanguage: {
                sProcessing: "<img src='" + phpVars.basePath + "/img/loading/clock.gif'>"
            },
            columns: [
                {"data": "EMPLOYEE_DESCRIPTION"},
                {"data": "APPROVER_QUEUE"},
                {"data": "REQUEST_STATUS_DESCRIPTION"},
                {"data": "REQUESTED_HOURS"},
                {"data": "REQUEST_REASON"},
                {"data": "MIN_DATE_REQUESTED"},
                {"data": "ACTIONS"}
            ],
            order: [],
            columnDefs: [{"orderable": false,
                    "targets": [1, 2, 3, 4, 6]
                }
            ],
            ajax: {
                url: phpVars.basePath + "/api/queue/payroll/completed-pafs",
                data: function (d) {
                    return $.extend({}, d, {
                        "employeeNumber": phpVars.employee_number
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
     * Loads the Pending AS400 Upload Queue.
     * 
     * @returns {undefined}
     */
    this.handleLoadingPendingAS400UploadQueue = function () {
        $('#payroll-queue-pending-as400-upload').DataTable({
            dom: 'fltirp',
            searching: true,
            processing: true,
            serverSide: true,
            oLanguage: {
                sProcessing: "<img src='" + phpVars.basePath + "/img/loading/clock.gif'>"
            },
            columns: [
                {"data": "EMPLOYEE_DESCRIPTION"},
                {"data": "APPROVER_QUEUE"},
                {"data": "REQUEST_STATUS_DESCRIPTION"},
                {"data": "REQUESTED_HOURS"},
                {"data": "REQUEST_REASON"},
                {"data": "MIN_DATE_REQUESTED"},
                {"data": "ACTIONS"}
            ],
            order: [],
            columnDefs: [{"orderable": false,
                    "targets": [1, 2, 3, 4, 6]
                }
            ],
            ajax: {
                url: phpVars.basePath + "/api/queue/payroll/pending-as400-upload",
                data: function (d) {
                    return $.extend({}, d, {
                        "employeeNumber": phpVars.employee_number
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
     * Loads the Denied Queue.
     * 
     * @returns {undefined}
     */
    this.handleLoadingDeniedQueue = function () {
        $('#payroll-queue-denied').DataTable({
            dom: 'fltirp',
            searching: true,
            processing: true,
            serverSide: true,
            oLanguage: {
                sProcessing: "<img src='" + phpVars.basePath + "/img/loading/clock.gif'>"
            },
            columns: [
                {"data": "EMPLOYEE_DESCRIPTION"},
                {"data": "APPROVER_QUEUE"},
                {"data": "REQUEST_STATUS_DESCRIPTION"},
                {"data": "REQUESTED_HOURS"},
                {"data": "REQUEST_REASON"},
                {"data": "MIN_DATE_REQUESTED"},
                {"data": "ACTIONS"}
            ],
            order: [],
            columnDefs: [{"orderable": false,
                    "targets": [1, 2, 3, 4, 6]
                }
            ],
            ajax: {
                url: phpVars.basePath + "/api/queue/payroll/denied",
                data: function (d) {
                    return $.extend({}, d, {
                        "employeeNumber": phpVars.employee_number
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
     * Loads the By Status Queue.
     * 
     * @returns {undefined}
     */
    this.handleLoadingByStatusQueue = function () {
        // Setup - add a text input to each footer cell
//        $('#payroll-queue-by-status tfoot th').each( function () {
//            $(this).html( '<select><option value=""></option></select>' );
//        } );
    
        $('#payroll-queue-by-status').DataTable({
            dom: 'fltirp',
            searching: true,
            processing: true,
            serverSide: true,
            oLanguage: {
                sProcessing: "<img src='" + phpVars.basePath + "/img/loading/clock.gif'>"
            },
            columns: [
                {"data": "EMPLOYEE_DESCRIPTION"},
                {"data": "APPROVER_QUEUE"},
                {"data": "REQUEST_STATUS_DESCRIPTION"},
                {"data": "REQUESTED_HOURS"},
                {"data": "REQUEST_REASON"},
                {"data": "MIN_DATE_REQUESTED"},
                {"data": "ACTIONS"}
            ],
            order: [],
            columnDefs: [{"orderable": false,
                    "targets": [1, 2, 3, 4, 6]
                }
            ],
            ajax: {
                url: phpVars.basePath + "/api/queue/payroll/by-status",
                data: function (d) {
                    return $.extend({}, d, {
                        "employeeNumber": phpVars.employee_number,
                        "startDate": $("#startDate").val(),
                        "endDate": $("#endDate").val()
                    });
                },
                type: "POST"
            },
            initComplete: function () {
                var table = $('#payroll-queue-by-status').DataTable();
        
                table.columns().every( function () {
                    var column = this;
                    var idx = this.index();
                    var title = table.column( idx ).header();
                    
                    if( $(title).html()=="Request Status" ) {
                        var select = $('<select><option value="Select one:" selected></option></select>')
                            .appendTo( $(column.footer()).empty() )
                            .on( 'change', function () {
                                var val = $.fn.dataTable.util.escapeRegex(
                                    $(this).val()
                                );

                                column
                                    .search( val ? val : '', true, false )
                                    .draw();
                            } );
                        column.data().unique().sort().each( function ( d, j ) {
                            select.append( '<option value="'+d+'">'+d+'</option>' )
                        } );
                    }
                } );
            }
        })
        .on("error.dt", function (e, settings, techNote, message) {
            console.log("An error has been reported by DataTables: ", message);
        });
        
        
        
        // Apply the search
        table.columns().every( function () {            
            // SAVE...this appends 'a' value to each dropdown
//            $('select', this.footer() ).append( '<option value="a">a</option>' );
        } );
    }
    
    /**
     * Loads the Manager Action Queue.
     * 
     * @returns {undefined}
     */
    this.handleLoadingManagerActionQueue = function () {
        $('#payroll-queue-manager-action').DataTable({
            dom: 'fltirp',
            searching: true,
            processing: true,
            serverSide: true,
            oLanguage: {
                sProcessing: "<img src='" + phpVars.basePath + "/img/loading/clock.gif'>"
            },
            columns: [
                {"data": "EMPLOYEE_DESCRIPTION"},
                {"data": "APPROVER_QUEUE"},
                {"data": "REQUEST_STATUS_DESCRIPTION"},
                {"data": "REQUESTED_HOURS"},
                {"data": "REQUEST_REASON"},
                {"data": "MIN_DATE_REQUESTED"},
                {"data": "ACTIONS"}
            ],
            order: [],
            columnDefs: [{"orderable": false,
                    "targets": [1, 2, 3, 4, 6]
                }
            ],
            ajax: {
                url: phpVars.basePath + "/api/queue/payroll/manager-action",
                data: function (d) {
                    return $.extend({}, d, {
                        "employeeNumber": phpVars.employee_number
                    });
                },
                type: "POST",
            }
        })
        .on("error.dt", function (e, settings, techNote, message) {
            console.log("An error has been reported by DataTables: ", message);
        });
    }
};

// Initialize the class
timeOffPayrollQueueHandler.initialize();