/**
 * Javascript timeOffQueueHandler 'class'
 *
 */
var timeOffManagerQueueHandler = new function ()
{
	var currentManagerReportFilter = 'D'; // Defaults to Direct Reports
	
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
                      data: { reportFilter: currentManagerReportFilter },
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

                table.columns().every( function () {
                    var column = this;
                    var idx = this.index();
                    var title = table.column( idx ).header();

                    if( $(title).html()=="Employee" ) {
                        var select = $('<br /><select><option value="D" selected>Direct Reports</option><option value="I">Indirect Reports</option><option value="B">Both</option></select>')
                            .appendTo( $(column.header()) )
                            .on( 'change', function () {
                                var val = $.fn.dataTable.util.escapeRegex(
                                    $(this).val()
                                );
                                currentManagerReportFilter = val; // Update the value first to the selection, then search again. *IMPORTANT* to update before doing .search again.
                                column
                                    .search( val ? val : '', true, false )
                                    .draw();
                            } );
                    }
                    if( $(title).html()=="Request Status" ) {
                        var select = $('<br /><select><option value="All" selected>All</option></select>')
                            .appendTo( $(column.header()) )
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