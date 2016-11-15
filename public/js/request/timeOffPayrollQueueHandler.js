/**
 * Javascript timeOffQueueHandler 'class'
 *
 */
var timeOffPayrollQueueHandler = new function ()
{
    var currentStatusFilter = 'All',
        currentCycleCodeFilter = 'All';
	
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
            timeOffPayrollQueueHandler.handleDownloadManagerActionReport();
            timeOffPayrollQueueHandler.handleDownloadUpdateChecksReport();
        });
    }
    
    this.handleDownloadManagerActionReport = function () {
    	$( '#downloadReportManagerActionNeeded' ).on( 'click', function(e) {
    		e.preventDefault();
    		var hyperlink = $( "#downloadReportManagerActionNeeded" );
            var href = hyperlink.attr("href");
            if( !timeOffCommon.empty( hyperlink.attr("href") ) ) {
            	var dataParams = { statusFilter: currentStatusFilter };
            	timeOffPayrollQueueHandler.dynamicallyLoadExcelSpreadsheet( href, dataParams );
            }
    	});
    }
    
    this.handleDownloadUpdateChecksReport = function () {
    	$( '#downloadReportUpdateChecks' ).on( 'click', function(e) {
    		e.preventDefault();
    		var hyperlink = $( "#downloadReportUpdateChecks" );
            var href = hyperlink.attr("href");
            if( !timeOffCommon.empty( hyperlink.attr("href") ) ) {
            	var dataParams = { cycleCodeFilter: currentCycleCodeFilter };
            	timeOffPayrollQueueHandler.dynamicallyLoadExcelSpreadsheet( href, dataParams );
            }
    	});
    }
    
    this.dynamicallyLoadExcelSpreadsheet = function( href, dataParams ) {
    	$.ajax( { type: 'post',
            url: href,
            dataType: 'json',
            data: JSON.stringify( dataParams ),
            success: function(data) {
          	  /**
          	   * Dynamically load the Excel spreadsheet.
          	   */
          	  var $a = $("<a>");
          	  $a.attr( "href", data.fileContents );
          	  $( "body" ).append( $a );
          	  $a.attr( "download", data.fileName );
          	  $a[0].click();
          	  $a.remove();
            }
    	} );
    }

    /**
     * Loads the Pending Payroll Queue.
     *
     * @returns {undefined}
     */
    this.handleLoadingPendingPayrollQueue = function () {
        $('#payroll-queue-pending-payroll-approval').DataTable({
            dom: 'ftirp',
            searching: true,
            processing: true,
            serverSide: true,
            pageLength: 50,
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
                },
                { className: "breakLongWord", "targets": [ 4 ] }
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
            dom: 'ftirp',
            searching: true,
            processing: true,
            serverSide: true,
            pageLength: 50,
            oLanguage: {
                sProcessing: "<img src='" + phpVars.basePath + "/img/loading/clock.gif'>"
            },
            columns: [
                {"data": "CYCLE_CODE"},
                {"data": "EMPLOYEE_DESCRIPTION"},
                {"data": "APPROVER_QUEUE"},
                {"data": "REQUEST_STATUS_DESCRIPTION"},
                {"data": "REQUESTED_HOURS"},
                {"data": "LAST_PAYROLL_COMMENT"},
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
            },
            initComplete: function () {
                var table = $('#payroll-queue-update-checks').DataTable();

                var rowCount = table.rows().data();

                if (rowCount.length == 0) {
                    $("#downloadReportUpdateChecks").removeAttr('href');
                    $("#downloadReportUpdateChecks").addClass('hrefDisabled');
                } else {
                    $("#downloadReportUpdateChecks").attr('href', phpVars.basePath + '/request/download-report-update-checks');
                    $("#downloadReportUpdateChecks").removeClass('hrefDisabled');
                }

                table.columns().every( function () {
                    var column = this;
                    var idx = this.index();
                    var title = table.column( idx ).header();

                    if( $(title).html()=="Cycle Code" ) {
                        var select = $('<br /><select><option value="All" selected>All</option></select>')
                            .appendTo( $(column.header()) )
                            .on( 'change', function () {
                                var val = $.fn.dataTable.util.escapeRegex(
                                    $(this).val()
                                );
                                
                                currentCycleCodeFilter = val;

                                column.search( val ? val : '', true, false ).draw();
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
    }

    /**
     * Loads the Completed PAFs Queue.
     *
     * @returns {undefined}
     */
    this.handleLoadingCompletedPAFsQueue = function () {
        $('#payroll-queue-completed-pafs').DataTable({
            dom: 'ftirp',
            searching: true,
            processing: true,
            serverSide: true,
            pageLength: 50,
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
                },
                { className: "breakLongWord", "targets": [ 4 ] }
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
            dom: 'ftirp',
            searching: true,
            processing: true,
            serverSide: true,
            pageLength: 50,
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
                },
                { className: "breakLongWord", "targets": [ 4 ] }
            ],
            ajax: {
                url: phpVars.basePath + "/api/queue/payroll/by-status",
                data: function (d) {
                    return $.extend({}, d, {
                        "employeeNumber": phpVars.employee_number,
                        "startDate": $("#startDate").val(),
                        "endDate": $("#endDate").val(),
                        "columns[2][search][value]": 'Pending AS400 Upload'
                    });
                },
                type: "POST"
            },
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
            dom: 'ftirp',
            searching: true,
            processing: true,
            serverSide: true,
            pageLength: 50,
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
                },
                { className: "breakLongWord", "targets": [ 4 ] }
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
        $('#payroll-queue-by-status').DataTable({
            dom: 'ftirp',
            searching: true,
            processing: true,
            serverSide: true,
            pageLength: 50,
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
                },
                { className: "breakLongWord", "targets": [ 4 ] }
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

                $('#columnRequestStatusFilter')
	                .on( 'change', function () {
	                    var val = $.fn.dataTable.util.escapeRegex(
	                        $(this).val()
	                    );
	                    currentStatusFilter = val; // Update the value first to the selection, then search again. *IMPORTANT* to update before doing .search again.
	                    table.column( 2 ).search( val ? val : '', true, false ).draw();
	                } );
            }
        })
        .on("error.dt", function (e, settings, techNote, message) {
            console.log("An error has been reported by DataTables: ", message);
        });
    }

    /**
     * Loads the Manager Action Queue.
     *
     * @returns {undefined}
     */
    this.handleLoadingManagerActionQueue = function () {
        $('#payroll-queue-manager-action').DataTable({
            dom: 'ftirp',
            searching: true,
            processing: true,
            serverSide: true,
            pageLength: 50,
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
                },
                { className: "breakLongWord", "targets": [ 4 ] }
            ],
            ajax: {
                url: phpVars.basePath + "/api/queue/payroll/manager-action",
                data: function (d) {
                    return $.extend({}, d, {
                        "employeeNumber": phpVars.employee_number
                    });
                },
                type: "POST",
            },
            initComplete: function () {
                var table = $('#payroll-queue-manager-action').DataTable();

                $('#columnRequestStatusFilter')
	                .on( 'change', function () {
	                    var val = $.fn.dataTable.util.escapeRegex(
	                        $(this).val()
	                    );
	                    currentStatusFilter = val; // Update the value first to the selection, then search again. *IMPORTANT* to update before doing .search again.
	                    table.column( 2 ).search( val ? val : '', true, false ).draw();
	                } );
            }
        })
        .on("error.dt", function (e, settings, techNote, message) {
            console.log("An error has been reported by DataTables: ", message);
        });
    }
};

// Initialize the class
timeOffPayrollQueueHandler.initialize();