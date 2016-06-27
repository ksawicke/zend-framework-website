/**
 * Javascript timeOffCompanyHolidayHandler 'class'
 *
 */
var timeOffCompanyHolidayHandler = new function ()
{
    /**
     * Initializes binding
     */
    this.initialize = function () {
        $(document).ready(function () {
            timeOffCompanyHolidayHandler.handleLoadingCompanyHolidayList();
        });
    }
    
    /**
     * Loads the Company Holiday list.
     * 
     * @returns {undefined}
     */
    this.handleLoadingCompanyHolidayList = function () {
        $('#company-holiday-list').DataTable({
            dom: 'ltrip', //fltirp',
            searching: true,
            processing: true,
            serverSide: true,
            oLanguage: {
                sProcessing: "<img src='" + phpVars.basePath + "/img/loading/clock.gif'>"
            },
            columns: [
                {"data": "DATE"},
                {"data": "ACTIONS"}
            ],
            order: [],
//            columnDefs: [{"orderable": false,
//                    "targets": [1, 2]
//                }
//            ],
            ajax: {
                url: phpVars.basePath + "/api/request/company-holidays",
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
timeOffCompanyHolidayHandler.initialize();