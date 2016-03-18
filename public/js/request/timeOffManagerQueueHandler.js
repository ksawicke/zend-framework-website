/**
 * Javascript timeOffQueueHandler 'class'
 *
 */
var timeOffManagerQueueHandler = new function ()
{
    /**
     * Initializes binding
     */
    this.initialize = function () {
        $(document).ready(function () {
            $('#pending-manager-approval').DataTable({
                dom: 'fltirp',
                searching: true,
                processing: true,
                serverSide: true,
                oLanguage: {
                    sProcessing: "<img src='/sawik/timeoff/public/img/loading/clock.gif'>"
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
                    url: "/sawik/timeoff/public/api/queue/manager/p",
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
        });
    }
};

// Initialize the class
timeOffManagerQueueHandler.initialize();