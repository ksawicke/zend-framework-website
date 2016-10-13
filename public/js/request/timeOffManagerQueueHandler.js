/**
 * Javascript timeOffQueueHandler 'class'
 *
 */
var timeOffManagerQueueHandler = new function ()
{
	var currentManagerReportFilter = 'D', // Defaults to Direct Reports
	    currentStatusFilter = 'All';
	
    /**
     * Initializes binding
     */
    this.initialize = function () {
        $(document).ready(function () {
        	timeOffManagerQueueHandler.loadManagerEmployeeRequestsView();
            timeOffManagerQueueHandler.loadPendingManagerApprovalQueue();
            timeOffManagerQueueHandler.handleDownloadMyEmployeeRequestsReport();
        });
    }
    
    this.handleDownloadMyEmployeeRequestsReport = function() {
    	$( '#downloadReportMyEmployeeRequests' ).on( 'click', function(e) {
    		e.preventDefault();
    		var hyperlink = $( "#downloadReportMyEmployeeRequests" );
            var href = hyperlink.attr("href");
            $.ajax( { type: 'post',
                      url: href,
                      dataType: 'json',
                      data: { reportFilter: currentManagerReportFilter, statusFilter: currentStatusFilter },
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
    	});
    }
    
    this.loadManagerEmployeeRequestsView = function() {
    	$('#manager-queue-my-employee-requests').DataTable({
            dom: 'fltirp',
            searching: true,
            processing: true,
            serverSide: true,
            oLanguage: {
                sProcessing: "<img src='" + phpVars.basePath +  "/img/loading/clock.gif'>"
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
                url: phpVars.basePath + "/api/queue/manager/my-employee-requests",
                data: function (d) {
                    return $.extend({}, d, {
                        "employeeNumber": phpVars.employee_number
                    });
                },
                type: "POST",
            },
            initComplete: function () {
                var table = $('#manager-queue-my-employee-requests').DataTable();

                $('#myEmployeeRequestsQueueViewColumnEmployeeFilter')
	                .on( 'change', function () {
	                    var val = $.fn.dataTable.util.escapeRegex(
	                        $(this).val()
	                    );
	                    currentManagerReportFilter = val; // Update the value first to the selection, then search again. *IMPORTANT* to update before doing .search again.
	                    table.column( 0 ).search( val ? val : '', true, false ).draw();
	                } );
                
                $('#myEmployeeRequestsQueueViewColumnRequestStatusFilter')
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
     * Loads Pending Manager Approval Queue.
     *
     * @returns {undefined}
     */
    this.loadPendingManagerApprovalQueue = function() {
        $('#manager-queue-pending-manager-approval').DataTable({
            dom: 'fltirp',
            searching: true,
            processing: true,
            serverSide: true,
            oLanguage: {
                sProcessing: "<img src='" + phpVars.basePath +  "/img/loading/clock.gif'>"
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
                url: phpVars.basePath + "/api/queue/manager/pending-manager-approval",
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
timeOffManagerQueueHandler.initialize();