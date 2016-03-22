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
            $('#pending-payroll-approval').DataTable({
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
            
            $('#update-checks').DataTable({
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
            
            $('#completed-pafs').DataTable({
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
            
            $('#pending-as400-upload').DataTable({
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
        });
    }
};

// Initialize the class
timeOffPayrollQueueHandler.initialize();